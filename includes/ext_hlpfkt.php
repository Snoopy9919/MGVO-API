<?php

   // Hilfsfunktionen 
   
   // Werte für mgvo_debug:
   define('MGVO_DEBUG_ERR',1);         // Allg. Fehlerausgaben
   define('MGVO_DEBUG_DATA',2);        // XML-Ergebnis vom Aufruf
   define('MGVO_DEBUG_XML',4);         // Array nach XML-Konvertierung
   define('MGVO_DEBUG_XMLTRANS',8);    // Schritte der XML-Konvertierung
   define('MGVO_DEBUG_ERG',16);        // Ergebnisarray vor Übergabe an Sniplet-Funktionen

   function mgvo_log($comment,$lv,$dtyp) {
      global $mgvo_debug;
      if (($dtyp & $mgvo_debug) > 0) {
         if (!empty($comment) && is_string($lv) && !empty($lv)) {
            $txt = "$comment: $lv";
            error_log($txt);
         }
         else {
            if (!empty($comment)) error_log($comment);
            if (!empty($lv)) {
               $ret = print_r($lv,1);
               error_log($ret);
            }
         }
      }
   }

   function localecho($ipadr,$out,...$vp) {
      $t = $vp[0];
      if ($_ENV['REMOTE_ADDR'] == $ipadr) {
         if (is_array($out)) print_ar($out);
         else echo $out."<br>";
      }
      flush();
      if (!empty($t)) sleep($t);
   }
   
   function vp_assign($vp,$varlist) {
      $varar = explode(",",$varlist);
      foreach($varar as $i => $vn) $valar[$vn] = isset($vp[$i]) ? $vp[$i] : NULL;
      return $valar;
   }

   function utf8_dec($str) {
      $cstr = mb_convert_encoding($str,"CP1252","UTF-8");
      return $cstr;
   }
   
   function saveassign($arr,$idx,$initval) {
      if (isset($arr[$idx])) return $arr[$idx];
      else return $initval;
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
      extract(vp_assign($vp,"auth,optar"));
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
   
   function date2user($db_datum,$typ=1) {
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
   
   function time2user($time) {
      if ($time == "") return "";
      $va = sscanf($time,"%2d:%2d:%2d");
      $usertime = sprintf("%d:%02d",$va[0],$va[1]);
      if ($va[2] != 0) $usertime .= sprintf(":%02d",$va[2]);
      return $usertime;
   }
   
   function emptyval($fval) {
      if (empty($fval) || $fval == "0000-00-00" || $fval == "00:00:00" || $fval == "00:00" || 
          $fval == "0000-00-00 00:00:00" || is_numeric($fval) && $fval == 0.0)
          return true;
      return false;
   }
   
?>