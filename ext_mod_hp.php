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
         extract(vp_assign($vp,"vcryptkey,cachemin"));
         // call_id: call_id des Vereins
         // vcryptkey: Schlüssel für die synchrone Verschlüsselung. Wird in MGVO in den technischen Parametern eingetragen 
         // cachetime: Legt die Cachezeit in Minuten fest. Wenn nicht angegeben, werden 5 Minuten gesetzt
         $this->call_id = $call_id;
         $this->vcryptkey = $vcryptkey;
         $cachemin = isset($cachemin) ? $cachemin : 5;
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
            // Prüfen, ob der Aufsruf erfolgreich war
            if (empty($ret) || strpos($ret, "Nicht erlaubt!") && strpos($ret,"Sicherheitsversto") || !strpos($ret, "DOCTYPE xml" )) { 
               mgvo_log("XML nicht korekt geladen, versuche Cache zu verwenden",$ret,MGVO_DEBUG_ERR);
               // Prüfen, ob es einen Cache gibt
               if (is_file($fn)) {
                  $ret = file_get_contents($fn);
               } else {
                  $ret = false; // wenn weder ein XML geladen werden konnte noch ein Cache da ist, wird False zurückgegeben
               }
            } 
            else {
               if ($this->cachetime > 0) file_put_contents($fn,$ret);
               mgvo_log("XML-Returnstring",$ret,MGVO_DEBUG_DATA);
            }
         }
         return $ret;
      }
      
      function xml2subtab($xml,$exElar) {
         while($xml->read()) {
            switch($xml->nodeType) {
               case XMLReader::ELEMENT:
                  mgvo_log("Element",$xml->name,MGVO_DEBUG_XMLTRANS);
                  if (isset($exElar) && in_array($xml->name,$exElar)) continue 2;
                  if (isset($icnt[$xml->name]) && $icnt[$xml->name] > 0) {
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
                  mgvo_log("Textelement",$xml->value,MGVO_DEBUG_XMLTRANS);
                  $node = $xml->value;
                  break;
               case XMLReader::END_ELEMENT:
               mgvo_log("Endeelement","",MGVO_DEBUG_XMLTRANS);
                  return $node;
            }
         }
      }
         
      function xml2table($url,$paras,...$vp) {
         extract(vp_assign($vp,"exElar"));
         $ret = $this->http_get_cached($url,$paras);
         $xml = new XMLReader();
         if ($xml->xml($ret) === false) {
            mgvo_log("kein XML-Code",NULL,MGVO_DEBUG_ERR);
            return false;
         }
         do {
            $xml->read();
         } while ($xml->nodeType != XMLReader::ELEMENT);
         mgvo_log("Wurzelelement",$xml->name,MGVO_DEBUG_XMLTRANS);
         if ($xml->nodeType == XMLReader::ELEMENT) $tab[$xml->name] = $this->xml2subtab($xml,$exElar);
         $xml->close();
         mgvo_log("Aus XML erzeugte Tabelle",$tab,MGVO_DEBUG_XML);
         return $tab;
      }
      
      function create_ergar($rootname,$objname) {
         $rootar = saveassign($this->tab,$rootname,array());
         $this->headline = saveassign($rootar,"headline","");
         $this->verein = saveassign($rootar,"verein","");
         $this->version = saveassign($rootar,"version","");
         $this->objfieldlist = saveassign($rootar,"objfieldlist","");
         $object = isset($this->tab[$rootname][$objname]) ? $this->tab[$rootname][$objname] : NULL;
         if (!isset($object[0])) $this->objar[0] = $object;
         else $this->objar = $object;
         $fldar = explode(",",$this->objfieldlist);
         foreach($this->objar as $objr) {
            foreach($fldar as $fld) {
               $zr[$fld] = isset($objr[$fld]) ? $objr[$fld] : "";
            }
            $zar[] = $zr;
         }
         $ergar['headline'] = $this->headline;
         $ergar['verein'] = $this->verein;
         $ergar['version'] = $this->version;
         $ergar['objar'] = $zar;
         mgvo_log("Ergebnistabelle",$ergar,MGVO_DEBUG_ERG);
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
      
      function read_betreuer() {
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_trainer_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("betreuerlist","betreuer");
         return $ergar;
      }
      
      function read_events() {
         // Liest die öffentlichen Veranstaltungen
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_events_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("eventlist","event");
         return $ergar;
      }
      
      function read_gruppen() {
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_gruppen_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("grouplist","group");
         return $ergar;
      }
      
      function read_abt() {
         // Liest die Abteilungen
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_abt_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("abtlist","abteilung");
         return $ergar;
      }
      
      function read_training_fail() {
         // Liest die Trainingsausfallzeiten
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_ortreserv_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("reservelist","cancellation");
         return $ergar;
      }
      
      function read_mitglieder($selparar) {
         // Selektion von Mitgliedern.
         // Das Array selparar umfasst eine Auswahl aus folgenden Selektionsfelder der Mitgliedermaske
         // Allgemeiner Suchbegriff: suchbeg
         // Suchalter/Geburtsdatum: suchalterv - suchalterb
         // Austritt: suchaustrittv - suchaustrittb
         // Gruppen-ID: suchgruid
         // Beitragsgruppe: suchbeigru
         // Lastschriftzahler: lssel (Selektionswert: 1)
         // Barzahler/Überweiser: barsel (Selektionswert: 1)
         // Dauerauftrag: dasel (Selektionswert: 1)
         // Geschlecht: geschl (x,m,w)
         // Mitglied: ausgetr (x,m,a)
         // Aktiv/Passiv: aktpass (x,a,p)
         // Mailempfänger: mailempf (x,e,s)
         // Inland/Ausland: (x,i,a)
         // Mahnstufe: (a,1,2,3)
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
         $this->read_mitglieder($selparar);
         $mr = $this->objar[0];
         return $mr;
      }
      
      function get_mitpict($mgnr) {
         // mgnr: Mitgliedsnummer
         // Liefert das Passbild eines Mitglieds inklusive mimetype und fsize (Dateigröße).
         // Das eigentliche Bild ist base64 codiert.
         $cipher = new Cipher();                         // Initialisierung der Verschlüsselung
         $cipher->init($this->vcryptkey);
         
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;         // Zusammenstellung der Parameter call_id verschlüsselt
         $vars['mgnr'] = $mgnr;
         $suchparas = http_build_query($vars);      // Parameterstring zusammensetzen
         $cparas = $cipher->encrypt($suchparas);        // verschlüsseln
         $cpe = rawurlencode($cparas);
         
         $url = "$this->urlroot/pub_mitpict_xml.php?paras=$cpe&call_id=$this->call_id";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("mitpassbild","bilddaten");
         return $ergar;
      }
      
      function list_documents(...$vp) {
         extract(vp_assign($vp,"dokart"));
         // dokart: Es werden öffentliche Dokumente der spezifizierten Dokumentart aufgelistet
         $this->cacheon = 1;
         $vars['call_id'] = $this->call_id;
         $vars['dokart'] = $dokart;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_documents_xml.php?$paras";
         $this->tab = $this->xml2table($url,$paras);
         $ergar = $this->create_ergar("documentlist","document");
         return $ergar;
      }
      
      function login($email_id,$passwd,$pis,$smscode) {
         // Die Methode hat folgende Returncodes:
         // 0  : Passwort nicht in Ordnung / User nicht vorhanden
         // 1  : Login ok
         // 10 : PIS nicht gefüllt
         // 11 : Max. Logon-Versuche überschritten
         // 12 : Geheimcode generiert und an Mobilgerät versendet, Logon muss mit Code erfolgen
         // 13 : Geheimcode (SMS-Code) stimmt nicht überein
         
         // Der Personal Identity String (PIS) wird durch Aufruf der Javascript-Funktion "get_browserpis"
         // erzeugt und muss an den Login übergeben werden, wenn eine Zwei-Faktor-Authentifizierung genutzt werden soll.
        
         $this->cacheon = 0;
         $vars['call_id'] = $this->call_id;
         $vars['email_id'] = $email_id;
         $vars['passwd'] = $passwd;
         $vars['pis'] = $pis;
         $vars['smscode'] = $smscode;
         $paras = http_build_query($vars);
         $url = "$this->urlroot/pub_mgb_validate.php?$paras";
         $ret = http_get($url);
         $retcode = (int) $ret;
         return $retcode;
      }
      
   }
   
?>