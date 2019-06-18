<?php

   function localecho($ipadr,$out,...$vp) {
      $t = $vp[0];
      if ($_ENV['REMOTE_ADDR'] == $ipadr) {
         if (is_array($out)) print_ar($out);
         else echo $out."<br>";
      }
      flush();
      if (!empty($t)) sleep($t);
   }

   function utf8_dec($str) {
      $cstr = mb_convert_encoding($str,"CP1252","UTF-8");
      return $cstr;
   }

   function prep_ar($ar) {
      foreach($ar as $key => $value) {
         if (is_array($value)) prep_ar($value);
         else $ar[$key] = htmlentities($value);
      }
      return $ar;
   }
   
   function print_ar($ar) {
      echo "<pre>";
      $ar = prep_ar($ar);
      print_r($ar);
      echo "</pre>";
   }
   
   function http_get($url,...$vp) {
      $auth = $vp[0];
      $optar = $vp[1];
      global $glob_debug,$glob_curlerror_no,$glob_curlerror_msg;
      if ($glob_debug) {
         echo "</center>";
         echo "URL: $url<br>";
         echo "Auth:$auth<br>";
      }
      $ch = curl_init();
      curl_setopt($ch,CURLOPT_URL,$url);
      if (!empty($auth)) {
         curl_setopt($ch,CURLOPT_USERPWD,$auth);
         curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
      }
      curl_setopt($ch,CURLOPT_HEADER,0);
      curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
      if ($optar['sslcheck_off']) curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
      if ($glob_debug) {
         curl_setopt($ch,CURLOPT_HEADER,1);
         curl_setopt($ch,CURLINFO_HEADER_OUT,true);
         curl_setopt($ch,CURLOPT_VERBOSE,1);
         curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,1);
      }
      $result = curl_exec($ch);
      $glob_curlerror_no = 0;
      $glob_curlerror_msg = "";
      if (curl_errno($ch)) {
         $glob_curlerror_no = curl_errno($ch) ;
         $glob_curlerror_msg = curl_error($ch);
         if ($glob_debug) {
            echo "FehlerNr.: $glob_curlerror_no Fehler: $glob_curlerror_msg<br>";
            echo "HTTP-Code: ".curl_getinfo($ch,CURLINFO_HTTP_CODE)."<br>";
            echo "Lookup-Time: ".curl_getinfo($ch,CURLINFO_NAMELOOKUP_TIME)."<br>";
            echo "Connect-Time: ".curl_getinfo($ch,CURLINFO_CONNECT_TIME)."<br>";
            echo "Primary Port: ".curl_getinfo($ch,CURLINFO_PRIMARY_PORT)."<br>";
            echo "Header Size: ".curl_getinfo($ch,CURLINFO_HEADER_SIZE)."<br>";
            echo "SSL Verify Result: ".curl_getinfo($ch,CURLINFO_SSL_VERIFYRESULT)."<br>";
         }
      }
      curl_close($ch);
      if ($glob_debug) echo "Returnwert: $result<br><br>";
      return $result;
   }
   
?>