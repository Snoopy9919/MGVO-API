<?php

   require("../mgvo_sniplets.php");   
   
   $mgvo_debug = MGVO_DEBUG_ERR | MGVO_DEBUG_DATA | MGVO_DEBUG_XMLTRANS;
   
   // call_id zur Identifikation des Vereins (hier: Demoverein)
   $call_id = "9a052167eb8a71f51b686e35c18a665a";
   // Symetrischer Schlüssel, muss identisch sein mit dem Schlüssel, der in den technischen Parametern abgelegt wird.
   $vcryptkey = "f4jd8Nzhfr4f8tbhkHGZ765VGVujg";
   
   echo "<html><body>";
   
   // Instanziierung der Klasse MGVO_SNIPLET
   // Der dritte Parameter sollte unbedingt im Produktivbetrieb auf 5 (Minuten) oder höher eingestellt werden.
   
   $msc = new MGVO_SNIPLET($call_id,$vcryptkey,0);  
   
   $html = $msc->mgvo_sniplet_training_fail();
   
   echo "<center>$html";
   echo "</center></body></html>";
?>