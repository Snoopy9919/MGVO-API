<?php

   require("../ext_mod_hp.php");
   
   $mgvo_debug = 0; // MGVO_DEBUG_ERR | MGVO_DEBUG_DATA | MGVO_DEBUG_XML;
   
   // call_id zur Identifikation des Vereins (hier: Demoverein)
   $call_id = "9a052167eb8a71f51b686e35c18a665a";
   // Symetrischer Schlüssel, muss identisch sein mit dem Schlüssel, der in den technischen Parametern abgelegt wird.
   $vcryptkey = "f4jd8Nzhfr4f8tbhkHGZ765VGVujg";   
   
   echo "<html><body>";

   // Instanziierung der Klasse MGVO_HPAPI
   // Der dritte Parameter sollte unbedingt im Produktivbetrieb auf 5 (Minuten) oder höher eingestellt werden.
   
   $hp1 = new MGVO_HPAPI($call_id,$vcryptkey,0);
   
   $resar = $hp1->read_vkal(1,2018);
   print_ar($resar);
   echo "<center>";
   echo "</center></body></html>";
?>