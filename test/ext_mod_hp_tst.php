<?php

   require("../ext_mod_hp.php");
   
   $glob_debug = 0;
   
   // call_id zur Identifikation des Vereins (hier: Demoverein)
   $call_id = "9a052167eb8a71f51b686e35c18a665a";
   // Symetrischer Schl�ssel, muss identisch sein mit dem Schl�ssel, der in den technischen Parametern abgelegt wird.
   $vcryptkey = "f4jd8Nzhfr4f8tbhkHGZ765VGVujg";   
   
   echo "<html><body><center>";

   // Instanziierung der Klasse MGVO_HPAPI
   // Der dritte Parameter sollte unbedingt im Produktivbetrieb auf 5 (Minuten) oder h�her eingestellt werden.
   
   $hp1 = new MGVO_HPAPI($call_id,$vcryptkey,0);
   
   $resar = $hp1->read_vkal(3,2019);
   print_ar($resar);
   
   echo "</center></body></html>";
?>