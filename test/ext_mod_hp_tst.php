<?php

   require("../ext_mod_hp.php");
   
   $glob_debug = 0;
   
   $call_id = "9a052167eb8a71f51b686e35c18a665a";  // Demoverein
   $vcryptkey = "f4jd8Nzhfr4f8tbhkHGZ765VGVujg";
   
   echo "<html><body<><center>";
   
   $hp1 = new MGVO_HPAPI($call_id,$vcryptkey,0);
   
   /* $selparar['suchbeg'] = "h*";
   $resar = $hp1->sel_mitglieder($selparar);
   
   echo "<table cellpadding=2 cellspacing=0 border=1>";
   echo "<tr>";
   echo "<th>MgNr.</th>";
   echo "<th>Nachname</th>";
   echo "<th>Vorname</th>";
   echo "<th>Stra&szlig;e</th>";
   echo "<th>PLZ</th>";
   echo "<th>Ort</th>";
   echo "<th>Eintritt</th>";
   echo "<th>Austritt</th>";
   foreach($resar['objar'] as $mr) {
      echo "<tr>";
      echo "<td>$mr[mgnr]</td>";
      echo "<td>$mr[nachname]</td>";
      echo "<td>$mr[vorname]</td>";
      echo "<td>$mr[str]</td>";
      echo "<td>$mr[plz]</td>";
      echo "<td>$mr[ort]</td>";
      echo "<td>$mr[eintritt]</td>";
      echo "<td>$mr[austritt]</td>";
      echo "</tr>";
   }
   echo "</table><br>";
   
   $mr = $hp1->show_mitglied(12);    // Aufruf der Daten eines einzelnen Mitglieds.
   print_ar($mr);
   
   $resar = $hp1->list_documents();  // Liest alle öffentlichen Dokumente und listet sie auf
   
   echo "<table cellpadding=2 cellspacing=0 border=1>";
   echo "<tr>";
   echo "<th>DokNr.</th>";
   echo "<th>Dokart</th>";
   echo "<th>Name</th>";
   echo "<th>Gr&ouml;&szlig;e</th>";
   echo "</tr>";
   foreach($resar['objar'] as $dokr) {
      echo "<tr>";
      echo "<td>$dokr[doknr]</td>";
      echo "<td>$dokr[dokart]</td>";
      echo "<td><a href='$dokr[url_display]' target=_blank>$dokr[dokbez]</a></td>";
      echo "<td>$dokr[fsize]</td>";
      echo "</tr>";
   }
   echo "</table><br>"; */
   
   $resar = $hp1->read_orte();
   echo "<table cellpadding=2 cellspacing=0 border=1>";
   echo "<tr>";
   echo "<th>Orts-ID</th>";
   echo "<th>Ortsbezeichnung</th>";
   echo "</tr>";
   foreach($resar['objar'] as $or) {
      echo "<tr>";
      echo "<td>$or[ortid]</td>";
      echo "<td>$or[ortbez]</td>";
      echo "</tr>";
   }
   echo "</table><br>";
   echo "</center></body></html>";
?>