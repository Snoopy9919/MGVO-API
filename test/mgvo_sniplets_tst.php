<?php

   require("../mgvo_sniplets.php");   
   
   $mgvo_debug = MGVO_DEBUG_ERR | MGVO_DEBUG_DATA | MGVO_DEBUG_XMLTRANS;
   
   // call_id zur Identifikation des Vereins (hier: Demoverein)
   $call_id = "9a052167eb8a71f51b686e35c18a665a";
   // Symetrischer Schlüssel, muss identisch sein mit dem Schlüssel, der in den technischen Parametern abgelegt wird.
   $vcryptkey = "f4jd8Nzhfr4f8tbhkHGZ765VGVujg";
   
   // Instanziierung der Klasse MGVO_SNIPLET
   // Der dritte Parameter sollte unbedingt im Produktivbetrieb auf 5 (Minuten) oder höher eingestellt werden.
   
   $sniplet_no = isset($_GET['sniplet_no']) && is_numeric($_GET['sniplet_no']) ? $_GET['sniplet_no'] : 1;
   
   $msc = new MGVO_SNIPLET($call_id,$vcryptkey,0);
   
   switch($sniplet_no) {
      case 1:
         $htmlout = 1;
         $html = $msc->mgvo_sniplet_abteilungen();
         break;
      case 2:
         $htmlout = 1;
         $html = $msc->mgvo_sniplet_betreuer();
         break; 
      case 3:
         $htmlout = 1;
         $html = $msc->mgvo_sniplet_events();
         break;
      case 4:
         $htmlout = 1;
         $html = $msc->mgvo_sniplet_gruppen();
         break;
      case 5:
         $htmlout = 1;
         $html = $msc->mgvo_sniplet_list_documents();
         break;
      case 6:
         $htmlout = 0;
         $mgnr = 17;
         $msc->mgvo_sniplet_mitpict($mgnr);
         break;
      case 7:
         $htmlout = 1;
         $html = $msc->mgvo_sniplet_orte();
         break;
      case 8:
         $htmlout = 1;
         $selparar['suchbeg'] = "l*";
         $html = $msc->mgvo_sniplet_read_mitglieder($selparar);
         break;
      case 9:
         $htmlout = 1;
         $mgnr = 8;
         $html = $msc->mgvo_sniplet_show_mitglied($mgnr);
         break;
      case 10:
         $htmlout = 1;
         $html = $msc->mgvo_sniplet_training_fail();
         break;
      case 11:
         $htmlout = 1;
         $vkalnr = 2;
         $seljahr = 2019;
         $html = $msc->mgvo_sniplet_vkal($vkalnr,$seljahr);
         break;
   }
   
   if ($htmlout) {
      echo "<html><body>";
      echo "<center>$html";
      echo "</center></body></html>";
   }
   
?>