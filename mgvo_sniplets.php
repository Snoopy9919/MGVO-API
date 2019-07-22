<?php

   // In der Datei wird eine Sniplet-Klasse definiert, welche die Methoden der Klasse MGVO_HPAPI (ext_mod_hp.php) 
   // aufruft und zu den jeweiligen Daten HTML-Code zur Ausgabe der Daten generiert.
   // Die Klasse repräsentiert Beispielcode und muss den individuellen Anforderungen angepasst werden.

   require_once(dirname(__FILE__)."/ext_mod_hp.php");
   
   class MGVO_SNIPLET {
      public $api;
      private $headline;
      
      function __construct($call_id,$vcryptkey,$cachemin) {
         // call_id: call_id des Vereins
         // vcryptkey: Schlüssel für die synchrone Verschlüsselung. Wird in MGVO in den technischen Parametern eingetragen 
         // $vcryptkey = $vp[0];
         // $cachemin = isset($vp[1]) ? $vp[1] : 5;
         $this->api = new MGVO_HPAPI($call_id,$vcryptkey,$cachemin);
      }
      
      function set_debuglevel($debuglevel=0) {
         $this->api->set_debuglevel($debuglevel);
         
      }
      
      function set_headline($headline) {
         $this->headline = $headline;
      }
      
      function write_headline($mgvo_headline="") {
         $headline = empty($this->headline) ? $mgvo_headline : $this->headline;
         $sniplet = "<h2>$headline</h2>";
         return $sniplet;
      }
      
      function mgvo_sniplet_vkal($vkalnr,$seljahr) {
         // Liest den Vereinskalender mit Nr. vkalnr mit Terminen des Jahres seljahr
         $resar = $this->api->read_vkal($vkalnr,$seljahr);
      
         $sniplet = "<div class='mgvo mgvo-vkal'>";
         $sniplet .= $this->write_headline($resar['headline']);
         $sniplet .= "<table cellpadding=1 cellspacing=0 border=1>";
         $sniplet .= "<tr>";
         $sniplet .= "<th>Bezeichnung</th>";
         $sniplet .= "<th>Startdatum</th>";
         $sniplet .= "<th>Startzeit</th>";
         $sniplet .= "<th>Enddatum</th>";
         $sniplet .= "<th>Endzeit</th>";
         $sniplet .= "<th>Ort</th>";
         $sniplet .= "</tr>";
         foreach($resar['objar'] as $idx => $vkr) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$vkr[bez]</td>";
            $sniplet .= "<td>".date2user($vkr['startdat'],1)."</td>";
            $sniplet .= "<td>$vkr[startzeit]</td>";
            $sniplet .= "<td>".date2user($vkr['enddat'],1)."</td>";
            $sniplet .= "<td>$vkr[endzeit]</td>";
            $sniplet .= "<td>$vkr[ort]</td>";
            $sniplet .= "</tr>";
         }
         $sniplet .= "</table>";
         $sniplet .= "</div>";
         return $sniplet;
      }
      
      function mgvo_sniplet_orte() {
         // Liest die Ortsliste ein
         $resar = $this->api->read_orte();
       
         $sniplet = "<div class='mgvo mgvo-orte'>";
         $sniplet .= $this->write_headline($resar['headline']);
         $sniplet .= "<table cellpadding=2 cellspacing=0 border=1>";
         $sniplet .= "<tr>";
         $sniplet .= "<th>Orts-ID</th>";
         $sniplet .= "<th>Ortsbezeichnung</th>";
         $sniplet .= "</tr>";
         foreach($resar['objar'] as $or) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$or[ortid]</td>";
            $sniplet .= "<td>$or[ortbez]</td>";
            $sniplet .= "</tr>";
         }
         $sniplet .= "</table><br>";
         $sniplet .= "</div>";
         return $sniplet;
      }
            
      function mgvo_sniplet_betreuer() {
         // Liest die Betreuer ein
         $resar = $this->api->read_betreuer();
         
         $sniplet = "<div class='mgvo mgvo-betreuer'>";
         $sniplet .= $this->write_headline($resar['headline']);
         $sniplet .= "<table cellpadding=2 cellspacing=0 border=1>";
         $sniplet .= "<tr>";
         $sniplet .= "<th>Trainer-ID</th>";
         $sniplet .= "<th>Name</th>";
         $sniplet .= "<th>Stra&szlige</th>";
         $sniplet .= "</tr>";
         foreach($resar['objar'] as $or) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$or[trid]</td>";
            $sniplet .= "<td>$or[nachname], $or[vorname]</td>";
            $sniplet .= "<td>$or[str]</td>";
            $sniplet .= "</tr>";
         }
         $sniplet .= "</table><br>";
         $sniplet .= "</div>";
         return $sniplet;
      }
      
      function mgvo_sniplet_events() {
         // Liest die öffentlichen Veranstaltungen
         $resar = $this->api->read_events();
       
         $sniplet = "<div class='mgvo mgvo-events'>";
         $sniplet .= $this->write_headline($resar['headline']);
         $sniplet .= "<table cellpadding=2 cellspacing=0 border=1>";
         $sniplet .= "<tr>";
         $sniplet .= "<th>Event</th>";
         $sniplet .= "<th>Beschreibung</th>";
         $sniplet .= "<th>Ort</th>";
         $sniplet .= "<th>Datum</th>";
         $sniplet .= "<th>Zeit</th>";
         $sniplet .= "<th>Bestell-URL</th>";
         $sniplet .= "</tr>";
         foreach($resar['objar'] as $or) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$or[name]</td>";
            $sniplet .= "<td>$or[description]</td>";
            $sniplet .= "<td>$or[ort]</td>";
            $sniplet .= "<td>".date2user($or['startdate'],1)."</td>";
            $sniplet .= "<td>$or[starttime]</td>";
            if (!empty($or['besturl'])) $sniplet .= "<td><a href='$or[besturl]' target=_blank>Bestell-URL</a></td>";
            else $sniplet .= "<td></td>";
            $sniplet .= "</tr>";
         }
         $sniplet .= "</table><br>";
         $sniplet .= "</div>";
         return $sniplet;
 
         return $sniplet;
      }
      
      function mgvo_sniplet_gruppen() { 
         $resar = $this->api->read_gruppen();
       
         $sniplet = "<div class='mgvo mgvo-gruppen'>";
         $sniplet .= $this->write_headline($resar['headline']);
         $sniplet .= "<table cellpadding=2 cellspacing=0 border=1>";
         $sniplet .= "<tr>";
         $sniplet .= "<th>Gruppen-ID</th>";
         $sniplet .= "<th>Name</th>";
         $sniplet .= "<th>Betreuer</th>";
         $sniplet .= "</tr>";
         foreach($resar['objar'] as $or) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$or[gruid]</td>";
            $sniplet .= "<td>$or[grubez]</td>";
            $sniplet .= "<td>$or[trnameall]</td>";
            $sniplet .= "</tr>";
         }
         $sniplet .= "</table><br>";
         $sniplet .= "</div>";
 
         return $sniplet;
      }
      
      function mgvo_sniplet_abteilungen() {
         $resar = $this->api->read_abt();
       
         $sniplet = "<div class='mgvo mgvo-abteilungen'>";
         $sniplet .= $this->write_headline($resar['headline']);
         $sniplet .= "<table cellpadding=2 cellspacing=0 border=1>";
         $sniplet .= "<tr>";
         $sniplet .= "<th>Abteilungs-ID</th>";
         $sniplet .= "<th>Name</th>";
         $sniplet .= "</tr>";
         foreach($resar['objar'] as $or) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$or[abtid]</td>";
            $sniplet .= "<td>$or[abtbez]</td>";
            $sniplet .= "</tr>";
         }
         $sniplet .= "</table><br>";
         $sniplet .= "</div>";
 
         return $sniplet;
      }
      
      function mgvo_sniplet_training_fail() {
         $resar = $this->api->read_training_fail();

         $sniplet = "<div class='mgvo mgvo-trainingfail'>";
         $sniplet .= $this->write_headline($resar['headline']);
         $sniplet .= "<table cellpadding=2 cellspacing=0 border=1>";
         $sniplet .= "<tr>";
         $sniplet .= "<th>Gruppe / Belegung</th>";
         $sniplet .= "<th>Datum</th>";
         $sniplet .= "<th>Zeit</th>";
         $sniplet .= "<th>Ort</th>";
         $sniplet .= "<th colspan=3>neu</th>";
         $sniplet .= "<th>Grund / Veranstaltung</th>";
         $sniplet .= "</tr>";
         foreach($resar['objar'] as $tfr) {
            $sniplet .= "<tr>";
            if (!empty($tfr['grbez'])) $sniplet .= "<td>$tfr[grbez] ($tfr[gruid])</td>";
            else $sniplet .= "<td>$tfr[belbez]</td>";
            $sniplet .= "<td>".date2user($tfr['sdat'],1)."</td>";
            $sniplet .= "<td>".time2user($tfr['starttime'])." - ".time2user($tfr['endtime'])."</td>";
            $sniplet .= "<td>$tfr[ortsbez]</td>";
            if (!emptyval($tfr['neudat'])) $sniplet .= "<td>".date2user($tfr['neudat'],1)."</td>";
            else $sniplet .= "<td></td>";
            $sniplet .= "<td>$tfr[neuzeithtml]</td>";
            $sniplet .= "<td>$tfr[neuorthtml]</td>";
            $sniplet .= "<td>$tfr[ebez]</td>";
            $sniplet .= "</tr>";
         }
         $sniplet .= "</table><br>";
         $sniplet .= "</div>";
         return $sniplet;
      }
      
      function mgvo_sniplet_read_mitglieder($selparar=NULL) {
         // Selektion von Mitgliedern. 
         $resar = $this->api->read_mitglieder($selparar);
         $sniplet = "<div class='mgvo mgvo-mitglieder'>";
         $sniplet .= $this->write_headline($resar['headline']);
         $sniplet .= "<table cellpadding=2 cellspacing=0 border=1>";
         $sniplet .= "<tr>";
         $sniplet .= "<th>MgNr.</th>";
         $sniplet .= "<th>Nachname</th>";
         $sniplet .= "<th>Vorname</th>";
         $sniplet .= "<th>Stra&szlig;e</th>";
         $sniplet .= "<th>PLZ</th>";
         $sniplet .= "<th>Ort</th>";
         $sniplet .= "<th>Eintritt</th>";
         $sniplet .= "<th>Austritt</th>";
         $sniplet .= "</tr>";
         foreach($resar['objar'] as $mr) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$mr[mgnr]</td>";
            $sniplet .= "<td>$mr[nachname]</td>";
            $sniplet .= "<td>$mr[vorname]</td>";
            $sniplet .= "<td>$mr[str]</td>";
            $sniplet .= "<td>$mr[plz]</td>";
            $sniplet .= "<td>$mr[ort]</td>";
            $sniplet .= "<td>".date2user($mr['eintritt'],1)."</td>";
            $sniplet .= "<td>".date2user($mr['austritt'],1)."</td>";
            $sniplet .= "</tr>";
         }
         $sniplet .= "</table><br>";
         $sniplet .= "</div>";
         return $sniplet;
      }
      
      function mgvo_sniplet_show_mitglied($mgnr) {
         $mr = $this->api->show_mitglied($mgnr);
         $sniplet = "<div class='mgvo mgvo-mitglieder'>";
         $sniplet .= $this->write_headline("Anzeige Mitglied");
         $sniplet .= "<table cellpadding=2 cellspacing=0 border=1>";
         foreach($mr as $fieldname => $value) {
            $sniplet .= "<tr><td>$fieldname:</td><td>$value</td></th>";
         }
         $sniplet .= "</table>";
         $sniplet .= "</div>";
         return $sniplet;
      }
      
      function mgvo_sniplet_list_documents($dokart=NULL) {
         $resar = $this->api->list_documents($dokart);
         $sniplet = "<div class='mgvo mgvo-documents'>";
         $sniplet .= $this->write_headline($resar['headline']);
         $sniplet .= "<table cellpadding=2 cellspacing=0 border=1>";
         $sniplet .= "<tr>";
         $sniplet .= "<th>DokNr.</th>";
         $sniplet .= "<th>Dokart</th>";
         $sniplet .= "<th>Name</th>";
         $sniplet .= "<th>Gr&ouml;&szlig;e</th>";
         $sniplet .= "</tr>";
         foreach($resar['objar'] as $dokr) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$dokr[doknr]</td>";
            $sniplet .= "<td>$dokr[dokart]</td>";
            $sniplet .= "<td><a href='$dokr[url_display]' target=_blank>$dokr[dokbez]</a></td>";
            $sniplet .= "<td>$dokr[fsize]</td>";
            $sniplet .= "</tr>";
         }
         $sniplet .= "</table><br>";
         $sniplet .= "</div>";
         return $sniplet;
      }
      
      function mgvo_sniplet_mitpict($mgnr) {
         $resar = $this->api->get_mitpict($mgnr);
      
         $mpr = $resar['objar'][0];
         $dokname = $mpr['dokname'];
         $fsize = $mpr['fsize'];
         $ctype = $mpr['mimetype'];
         $content = base64_decode($mpr['content']);
         
         header("Content-Type: $ctype");
         header("Content-Length: " . strlen($content));
         header("Content-disposition: inline; filename=\"$dokname\"");
         
         echo $content;
      }
   }
   
?>
