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
      
      function set_headline($headline) {
         $this->headline = $headline;
      }
      
      function write_headline($mgvo_headline) {
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
         // Liest die Ortsliete ein
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
            $sniplet .= "<td>".date2user($vkr['startdate'],1)."</td>";
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
       
         //  Hier sollte der Code stehen, der aus den Ergebnis eine HTML-Struktur macht
         $sniplet = "<div class='mgvo mgvo-trainingfail'>";
         $sniplet .= "";
         $sniplet .= "</div>";
 
         return $sniplet;
      }
      
      function mgvo_sniplet_read_mitglieder($selparar) {
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
         foreach($resar['objar'] as $mr) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$mr[mgnr]</td>";
            $sniplet .= "<td>$mr[nachname]</td>";
            $sniplet .= "<td>$mr[vorname]</td>";
            $sniplet .= "<td>$mr[str]</td>";
            $sniplet .= "<td>$mr[plz]</td>";
            $sniplet .= "<td>$mr[ort]</td>";
            $sniplet .= "<td>$mr[eintritt]</td>";
            $sniplet .= "<td>$mr[austritt]</td>";
            $sniplet .= "</tr>";
         }
         $sniplet .= "</table><br>";
         $sniplet .= "</div>";
         return $sniplet;
      }
      
      function mgvo_sniplet_show_mitglied($mgnr) {
         $mr = $this->api->show_mitglied($mgnr);
         $sniplet = "<div class='mgvo mgvo-mitglieder'>";
         $sniplet .= $this->write_headline($resar['headline']);
         $sniplet .= "<table cellpadding=2 cellspacing=0 border=1>";
         foreach($mr as $fieldname => $value) {
            $sniplet .= "<tr><td>$fieldname:</td><td>$value</td></th>";
         }
         $sniplet = "</table>";
         $sniplet = "</div>";
         return $sniplet;
      }
      
      function mgvo_sniplet_list_documents($dokart) {
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
	  
	  // *************************
	  // Generic Sniplets
	  //
	  //  Mit diesen Funktion lassen sich generisch beliebige HTML-Tabellen aus MGVO erstellen. 
	  //  Mit den Einträgen $vkal_use_fields_table, $vkal_head_fields_tablen und $vkal_sanitize_fields_table  wird gesteuert, welche Felder ausgegeben werden
      //  Die verfügbaren Felder können dem XML (<objfieldlist>) entnommen werden und sind auch im Array der API verfügbar)
      //  $vkal_use_fields_table =  zu verwendendene Felder  
      //  $vkal_head_fields_table = dazugehörige Überschriften (gleiche Anzahl wie Felder erforderlich)
      //  $vkal_sanitize_fields_table = Felder, die eine Datumsbehandlung benötigen
	  //  die Spalten haben jeweils eine CSS-Klasse mgvo-f-<feldname>, z.B. mgvo-f-bez, somit lassen sich im CSS die Spaltenbreiten individuell angeben.
	  //
	  // *************************
	  
	  
	  function mgvo_gen_sniplet_vkal($vkalnr,$seljahr, $vkal_use_fields_table = Null, $vkal_head_fields_table = Null) {
         // Liest den Vereinskalender mit Nr. vkalnr mit Terminen des Jahres seljahr als HTML. 
         //  Verfügbare Felder: startdat,bez,prio,vkalnr,evnr,startzeit,enddat,endzeit,ortid,notiz,rec_day_freq,rec_wk_freq,rec_mon_freq1,rec_mon_tag,rec_mon_freq2,rec_yr_freq1,rec_yr_tag,rec_yr_freq2,rec_range_enddat,ort,ortkb
		 // Konfig-Anleitung siehe oben
		 if (empty($vkal_use_fields_table) or empty($vkal_head_fields_table)) {
            $vkal_use_fields_table = explode(",","startdat,bez,prio,vkalnr,evnr,startzeit,enddat,endzeit,ortid,notiz,rec_day_freq,rec_wk_freq,rec_mon_freq1,rec_mon_tag,rec_mon_freq2,rec_yr_freq1,rec_yr_tag,rec_yr_freq2,rec_range_enddat,ort,ortkb");
            $vkal_head_fields_table = explode(",","startdat,bez,prio,vkalnr,evnr,startzeit,enddat,endzeit,ortid,notiz,rec_day_freq,rec_wk_freq,rec_mon_freq1,rec_mon_tag,rec_mon_freq2,rec_yr_freq1,rec_yr_tag,rec_yr_freq2,rec_range_enddat,ort,ortkb");
        }
        if (count($vkal_use_fields_table) != count($vkal_head_fields_table)) {
            $sniplet .= "Anzahl der Felder und Überschriften in mgvo_sniplet_vkal() nicht gleich";
			return $sniplet;
        }      
        $vkal_sanitize_fields_table  = explode(",","startdat,startzeit,enddat,endzeit");
 
         // Liest den Vereinskalender mit Nr. vkalnr mit Terminen des Jahres seljahr
         $resar = $this->api->read_vkal($vkalnr,$seljahr);
		 
		 return $this->mgvo_generic_sniplet($resar , "mgvo-vkal" ,$vkal_use_fields_table, $vkal_head_fields_table, $vkal_sanitize_fields_table );
	  }
	  
	  
      function mgvo_generic_sniplet($resar, $css_class, $vkal_use_fields_table, $vkal_head_fields_table, $vkal_sanitize_fields_table ) {
             
         $sniplet = "<div class='mgvo ".$css_class."'>";
         $sniplet .= $this->write_headline($resar['headline']);
         $sniplet .= "<table cellpadding=1 cellspacing=0 border=1>";
         $sniplet .= "<tr>";
         
         foreach ($vkal_head_fields_table  as  $header) {
                $sniplet .= "<th class='mgvo-h-".$field."'>".$header."</th>";
         }
         $sniplet .= "</tr>";
		 
         foreach($resar['objar'] as $idx => $vkr) {
            $sniplet .= "<tr>";
            foreach ($vkal_use_fields_table as  $field) {
                if (isset($vkr[$field])) {
                    if (in_array($field, vkal_sanitize_fields_table)) {
                        $sniplet .= "<td class='mgvo-f-".$field."'>".date2user($vkr[$field])."</td>"; 
                    } else {
                        $sniplet .= "<td class='mgvo-f-".$field."'>".$vkr[$field]."</td>"; 
                    }
                } else {
                   $sniplet .= "<td class='mgvo-f-".$field."'></td>";
             }
            }
            $sniplet .= "</tr>"; 
         }
         $sniplet .= "</table>";
         $sniplet .= "</div>";
         return $sniplet;
      }
    
	  
   }
   
?>
