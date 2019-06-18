<?php

   function localecho($ipadr,$out,...$vp) {
      $t = $vp[0];
      if ($_ENV['REMOTE_ADDR'] == $ipadr) {
         if (is_array($out)) print_ar($out);
         else echo $out."<br>";
      }
      flush();
      if (!empty($t)) sleep($t);
   }

   function utf8_dec($str) {
      $cstr = mb_convert_encoding($str,"CP1252","UTF-8");
      return $cstr;
   }

   function prep_ar($ar) {
      foreach($ar as $key => $value) {
         if (is_array($value)) prep_ar($value);
         else $ar[$key] = htmlentities($value);
      }
      return $ar;
   }
   
   function print_ar($ar) {
      echo "<pre>";
      $ar = prep_ar($ar);
      print_r($ar);
      echo "</pre>";
   }
   
   function http_get($url,...$vp) {
      $auth = $vp[0];
      $optar = $vp[1];
      global $glob_debug,$glob_curlerror_no,$glob_curlerror_msg;
      if ($glob_debug) {
         echo "</center>";
         echo "URL: $url<br>";
         echo "Auth:$auth<br>";
      }
      $ch = curl_init();
      curl_setopt($ch,CURLOPT_URL,$url);
      if (!empty($auth)) {
         curl_setopt($ch,CURLOPT_USERPWD,$auth);
         curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
      }
      curl_setopt($ch,CURLOPT_HEADER,0);
      curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
      if ($optar['sslcheck_off']) curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
      if ($glob_debug) {
         curl_setopt($ch,CURLOPT_HEADER,1);
         curl_setopt($ch,CURLINFO_HEADER_OUT,true);
         curl_setopt($ch,CURLOPT_VERBOSE,1);
         curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,1);
      }
      $result = curl_exec($ch);
      $glob_curlerror_no = 0;
      $glob_curlerror_msg = "";
      if (curl_errno($ch)) {
         $glob_curlerror_no = curl_errno($ch) ;
         $glob_curlerror_msg = curl_error($ch);
         if ($glob_debug) {
            echo "FehlerNr.: $glob_curlerror_no Fehler: $glob_curlerror_msg<br>";
            echo "HTTP-Code: ".curl_getinfo($ch,CURLINFO_HTTP_CODE)."<br>";
            echo "Lookup-Time: ".curl_getinfo($ch,CURLINFO_NAMELOOKUP_TIME)."<br>";
            echo "Connect-Time: ".curl_getinfo($ch,CURLINFO_CONNECT_TIME)."<br>";
            echo "Primary Port: ".curl_getinfo($ch,CURLINFO_PRIMARY_PORT)."<br>";
            echo "Header Size: ".curl_getinfo($ch,CURLINFO_HEADER_SIZE)."<br>";
            echo "SSL Verify Result: ".curl_getinfo($ch,CURLINFO_SSL_VERIFYRESULT)."<br>";
         }
      }
      curl_close($ch);
      if ($glob_debug) echo "Returnwert: $result<br><br>";
      return $result;
   }
   
   class Cipher {
      var $key,$sslkey,$td,$iv,$ivlen;
      var $method = "AES-256-CBC";
   
      function init($textkey) {
         $this->ivlen = openssl_cipher_iv_length($this->method);
         $str = openssl_random_pseudo_bytes($this->ivlen);
         $this->iv = substr(hash('sha256',$str),0,$this->ivlen);
         $this->sslkey = hash('sha256',$textkey);
      }
      
      function encrypt($input,...$vp) {
         $nobase64 = $vp[0];
         $encrypted = openssl_encrypt($input,$this->method,$this->sslkey,0,$this->iv);
         $encrypted = $this->iv.$encrypted;
         $erg = empty($nobase64) ? base64_encode($encrypted) : $encrypted;
         return $erg;
      }
      
      function decrypt($input,...$vp) {
         $binarymode = $vp[0];
         $nobase64 = $vp[1];
         $input = empty($nobase64) ? base64_decode($input) : $input;
         $iv = substr($input,0,$this->ivlen);
         $input = substr($input,$this->ivlen);
         $decrypted = openssl_decrypt($input,$this->method,$this->sslkey,0,$iv);
         if (!$binarymode) $decrypted = rtrim($decrypted, "\0");
         return $decrypted;
      }   
   }
   
   class MGVO_HPAPI {
      private $call_id;
      private $vcryptkey;
      private $urlroot;
      private $cacheon;
      private $cachetime;
      private $cacheprogar;
      private $tab;
      private $headline;
      private $version;
      private $verein;
      private $objar;
            
      function __construct($call_id,...$vp) {
         // call_id: call_id des Vereins
         // vcryptkey: Schlüssel für die synchrone Verschlüsselung. Wird in MGVO in den technischen Parametern eingetragen 
         $this->call_id = $call_id;
         $this->vcryptkey = $vp[0];
         $cachemin = isset($vp[1]) ? $vp[1] : 5;
         $this->cachetime = $cachemin * 60;                // cachetime in Sekunden
         $this->urlroot = "https://www.mgvo.de/prog";
      }
      
      function http_get_cached($url,$paras) {
         // Die Funktion holt die Daten per https und legt sie als Datei auf dem lokalen Dateisystem ab.
         // Beim Initiieren der MGVO-Klasse kann die Cachedauer festgelegt werden. Sie wird ohne explizite Angabe 
         // auf fünf Minuten festgelegt.
         $urlinfo = parse_url($url);
         $fi = pathinfo($urlinfo['path']);
         $fn = $fi['filename'].".".$paras.".cache";
         if ($this->cacheon && is_file($fn)) {
            $filetime = filemtime($fn);
            if (time() - $filetime <= $this->cachetime) {
               $ret = file_get_contents($fn);
            }
         }
         if (empty($ret)) {
            $ret = http_get($url);
            if ($this->cachetime > 0) file_put_contents($fn,$ret);
         }
         return $ret;
      }
      
      function xml2subtab($xml,$exElar) {
         while($xml->read()) {
            switch($xml->nodeType) {
               case XMLReader::ELEMENT:
                  if (isset($exElar) && in_array($xml->name,$exElar)) continue 2;
                  if ($icnt[$xml->name] > 0) {
                     if ($icnt[$xml->name] == 1) {  // Umhängen Knoten als Array
                        $oldval = $node[$xml->name];
                        unset ($node[$xml->name]);
                        $node[$xml->name][] = $oldval;
                     }
                     $node[$xml->name][] = $this->xml2subtab($xml,$exElar); // Unterstruktur als Array
                     $icnt[$xml->name]++;
                  }
                  else {
                     $node[$xml->name] = $this->xml2subtab($xml,$exElar);  // Unterstruktur als Knoten
                     $icnt[$xml->name] = 1;
                  }
                  break;
               case XMLReader::TEXT:
               case XMLReader::CDATA:
                  $node = $xml->value;
                  break;
               case XMLReader::END_ELEMENT:
                  return $node;
            }
         }
      }
         
      function xml2table($url,$paras,...$vp) {
         $exElar = $vp[0];
         $ret = $this->http_get_cached($url,$paras);
         $xml = new XMLReader();
         if ($xml->xml($ret) === false) return false;
         do {
            $xml->read();
         } while ($xml->nodeType != XMLReader::ELEMENT);
         if ($xml->nodeType == XMLReader::ELEMENT) $tab[$xml->name] = $this->xml2subtab($xml,$exElar);
         $xml->close();
         return $tab;
      }
      
      function create_ergar($rootname,$objname) {
         $rootar = $this->tab[$rootname];
         $this->headline = $rootar['headline'];
         $this->verein = $rootar['verein'];
         $this->version = $rootar['version'];
         $this->objar = $this->tab[$rootname][$objname];
         $ergar['headline'] = $this->headline;
         $ergar['verein'] = $this->verein;
         $ergar['version'] = $this->version;
         $ergar['objar'] = $this->objar;
         return $ergar;
      }
      
      function read_vkal($vkalnr,$seljahr) {
         // Liest den Vereinskalender mit Nr. vkalnr mit Terminen des Jahres seljahr
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $vars['seljahr'] = $seljahr;
         $vars['vkalnr'] = $vkalnr;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_vkal_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("kalender","event");
         return $ergar;
      }
      
      function read_orte() {
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_orte_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("ortlist","ort");
         return $ergar;
      }
      
      function read_events() {
         // Liest die öffentlichen Veranstaltungen
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_events_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("events","event");
         return $ergar;
      }
      
      function read_gruppen() {
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_gruppen_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("reservelist","cancellation");
         return $ergar;
      }
      
      function read_training_fail() {
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_ortreserv_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("grouplist","group");
         return $ergar;
      }
      
      function sel_mitglieder($selparar) {
         $cipher = new Cipher();                         // Initialisierung der Verschlüsselung
         $cipher->init($this->vcryptkey);
         
         $this->cacheon = 1;
         $selparar['call_id'] = $this->call_id;         // Zusammenstellung der Parameter call_id verschlüsselt
         $suchparas = http_build_query($selparar);      // Parameterstring zusammensetzen
         $cparas = $cipher->encrypt($suchparas);        // verschlüsseln
         $cpe = rawurlencode($cparas);
         
         $url = "$this->urlroot/pub_mit_xml.php?paras=$cpe&call_id=$this->call_id";
         $this->tab = $this->xml2table($url,$suchparas);
         $ergar = $this->create_ergar("memberlist","member");
         return $ergar;
      }
      
      function show_mitglied($mgnr) {
         $selparar['suchbeg'] = $mgnr;
         $this->sel_mitglieder($selparar);
         $mr = $this->objar;
         return $mr;
      }
      
      function list_documents(...$vp) {
         $dokart = $vp[0];
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $vars['dokart'] = $dokart;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_documents_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("documentlist","document","mgar");
         return $ergar;
      }
      
      function login($email_id,$passwd) {
         $this->cacheon = 0;
         $vars['call_id'] = $this->call_id;
         $vars['email_id'] = $email_id;
         $vars['passwd'] = $passwd;
         $url = "$this->urlroot/pub_mgb_validate.php?$paras";
      }
      
   }
   
?>