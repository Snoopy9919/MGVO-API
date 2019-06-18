<?php

   require_once("includes/ext_hlpfkt.php");
   require_once("includes/ext_cipher.php");
   
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
         // vcryptkey: Schl�ssel f�r die synchrone Verschl�sselung. Wird in MGVO in den technischen Parametern eingetragen 
         $this->call_id = $call_id;
         $this->vcryptkey = $vp[0];
         $cachemin = isset($vp[1]) ? $vp[1] : 5;
         $this->cachetime = $cachemin * 60;                // cachetime in Sekunden
         $this->urlroot = "https://www.mgvo.de/prog";
      }
      
      function http_get_cached($url,$paras) {
         // Die Funktion holt die Daten per https und legt sie als Datei auf dem lokalen Dateisystem ab.
         // Beim Initiieren der MGVO-Klasse kann die Cachedauer festgelegt werden. Sie wird ohne explizite Angabe 
         // auf f�nf Minuten festgelegt.
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
                     if ($icnt[$xml->name] == 1) {  // Umh�ngen Knoten als Array
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
         // Liest die �ffentlichen Veranstaltungen
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
         $cipher = new Cipher();                         // Initialisierung der Verschl�sselung
         $cipher->init($this->vcryptkey);
         
         $this->cacheon = 1;
         $selparar['call_id'] = $this->call_id;         // Zusammenstellung der Parameter call_id verschl�sselt
         $suchparas = http_build_query($selparar);      // Parameterstring zusammensetzen
         $cparas = $cipher->encrypt($suchparas);        // verschl�sseln
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