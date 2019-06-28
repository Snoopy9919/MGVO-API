<?php

   // Hilfsfunktionen 

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
      $auth = isset($vp[0]) ? $vp[0] : "";
      $optar = isset($vp[1]) ? $vp[1] : array();
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
   
   function calc_fingerprint($pis) {
      $fps = $_SERVER['HTTP_USER_AGENT'];
      $fps .= $_SERVER['HTTP_ACCEPT'];
      $fps .= $pis;
      $hv = hash("sha256",$fps);
      return $hv;
   }
   
   function date2user($db_datum,...$vp) {
      $typ = empty($vp[0]) ? 1 : $vp[0];
      if ($db_datum == "" || $db_datum == "0000-00-00") return "";
      $year = substr($db_datum,0,4);
      $syear = substr($db_datum,2,2);
      $month = substr($db_datum,5,2);
      $day = substr ($db_datum,8,2);
      $datum = "";
      switch ($typ) {
         case 1:
            $datum .= "$day.$month.$year";  // 03.07.2005
            break;
         case 3:
            $datum .= "$day.$month.$syear"; // 03.07.05
            break;
         case 4:
            $datum .= "$day.$month.";  // 03.07.
            break;
         case 5:
            $day = (int) $day;        // 3.7.
            $month = (int) $month;
            $datum .= "$day.$month.";
            break;
         case 6:
            $day = (int) $day;        // 3.7.2005
            $month = (int) $month;
            $datum .= "$day.$month.$year";
            break;
         case 7:
            $day = (int) $day;        // 3.7.05
            $month = (int) $month;
            $datum .= "$day.$month.$syear";
            break;
      }
      return $datum;
   }
   
?>