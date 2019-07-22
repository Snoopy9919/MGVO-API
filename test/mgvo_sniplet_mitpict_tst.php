<?php

   require("../mgvo_sniplets.php");
   
   $glob_debug = 0;
   
   // call_id zur Identifikation des Vereins (hier: Demoverein)
   $call_id = "9a052167eb8a71f51b686e35c18a665a";
   // Symetrischer Schlüssel, muss identisch sein mit dem Schlüssel, der in den technischen Parametern abgelegt wird.
   $vcryptkey = "f4jd8Nzhfr4f8tbhkHGZ765VGVujg";
   
   $msc = new MGVO_SNIPLET($call_id,$vcryptkey,0);  
   $mgnr = 17;
   $msc->mgvo_sniplet_mitpict($mgnr);
   
?>