<?php

// Hilfsfunktionen
/**
 * @param        $ipadr
 * @param        $out
 * @param   int  $t
 */
function localecho($ipadr, $out, $t = 0)
{
    if ($_ENV['REMOTE_ADDR'] == $ipadr) {
        if (is_array($out)) {
            print_ar($out);
        } else {
            echo $out . "<br>";
        }
    }
    flush();
    if (!empty($t)) {
        sleep($t);
    }
}

/**
 * Wenn des Array an der idx-ten Stelle gesetzt ist,
 * gebe es zurück ansonsten den initval
 * kann ersetzt werden durch $arr[$idx] ?? $initval
 *
 * @param   array         $arr
 * @param   string        $idx      Der zu suchende String
 * @param   string|array  $initval  Der alternativ String, falls es idx nicht gibt
 *
 * @return mixed
 */
function saveassign(array $arr, $idx, $initval)
{
    if (isset($arr[$idx])) {
        return $arr[$idx];
    } else {
        return $initval;
    }
}

/**
 * Goes through every array in the $ar recursively and converts them to HTML Entities?
 *
 * @param   array|null  $ar
 *
 * @return array|null
 */
function prep_ar($ar)
{
    if ($ar === null) {
        return null;
    }

    foreach ($ar as $key => $value) {
        if (is_array($value)) {
            prep_ar($value);
        } else {
            $ar[$key] = htmlentities($value);
        }
    }

    return $ar;
}

/**
 * Prints a (nested) array to the Website
 *
 * @param   array  $ar
 */
function print_ar(array $ar)
{
    echo "<pre>";
    $ar = prep_ar($ar);
    print_r($ar);
    echo "</pre>";
}

/**
 * @param   string  $url
 * @param   string  $auth
 *
 * @return false|string the result or false on error
 */
function http_get1($url, $auth = "")
{
    global $glob_debug, $glob_curlerror_no, $glob_curlerror_msg;
    if ($glob_debug) {
        echo "</center>";
        echo "URL: $url<br>";
        echo "Auth:$auth<br>\n";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if (!empty($auth)) {
        curl_setopt($ch, CURLOPT_USERPWD, $auth);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    }
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //if (isset($optar['sslcheck_off']))
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    if ($glob_debug) {
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    }
    $result             = curl_exec($ch);
    $glob_curlerror_no  = 0;
    $glob_curlerror_msg = "";
    if (curl_errno($ch)) {
        $glob_curlerror_no  = curl_errno($ch);
        $glob_curlerror_msg = curl_error($ch);
        if ($glob_debug) {
            echo "FehlerNr.: $glob_curlerror_no Fehler: $glob_curlerror_msg<br>";
            echo "HTTP-Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "<br>";
            echo "Lookup-Time: " . curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME) . "<br>";
            echo "Connect-Time: " . curl_getinfo($ch, CURLINFO_CONNECT_TIME) . "<br>";
            echo "Primary Port: " . curl_getinfo($ch, CURLINFO_PRIMARY_PORT) . "<br>";
            echo "Header Size: " . curl_getinfo($ch, CURLINFO_HEADER_SIZE) . "<br>";
            echo "SSL Verify Result: " . curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT) . "<br>";
        }
    }
    curl_close($ch);
    if ($glob_debug) {
        echo "Returnwert: $result<br><br>";
    }
    return $result;
}

/**
 * @param   string  $url
 * @param   null    $vars
 * @param   string  $auth
 * @param   array   $optar
 *
 * @return false|string the result or false on error
 */
function http_post($url, $vars = null, $auth = "", $optar = [])
{
    global $glob_curlerror_no, $glob_curlerror_msg, $glob_debug;
    if ($glob_debug) {
        echo "</center>";
        echo "URL: $url<br>";
        print_ar($vars);
        echo "Auth: $auth<br>";
    }
    $ch = curl_init($url);
    if (!empty($auth)) {
        curl_setopt($ch, CURLOPT_USERPWD, $auth);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    }
    if (isset($optar['headerflag'])) {
        curl_setopt($ch, CURLOPT_HEADER, true);
    } else {
        curl_setopt($ch, CURLOPT_HEADER, false);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //if (isset($optar['sslcheck_off']))
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if (!empty($vars) && is_array($vars)) {
        curl_setopt($ch, CURLOPT_POST, true);
        if (isset($optar['uploadflag']) && $optar['uploadflag'] == 0) {
            $postvar = http_build_query($vars);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postvar);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        }
    }
    $result = curl_exec($ch);

    $glob_curlerror_no  = 0;
    $glob_curlerror_msg = "";
    if (curl_errno($ch)) {
        $glob_curlerror_no  = curl_errno($ch);
        $glob_curlerror_msg = curl_error($ch);
    }
    curl_close($ch);
    if ($glob_debug) {
        echo "Returnwert: $result<br>";
    }

    return $result;
}

function calc_fingerprint($pis)
{
    return hash("sha256", $_SERVER['HTTP_USER_AGENT'] . $_SERVER['HTTP_ACCEPT'] . $pis);
}

function date2user($db_datum, $typ = 1)
{
    if ($db_datum == "" || $db_datum == "0000-00-00" || strlen($db_datum) < 4) {
        return "";
    }
    $year  = substr($db_datum, 0, 4);
    $syear = substr($db_datum, 2, 2);
    $month = substr($db_datum, 5, 2);
    $day   = substr($db_datum, 8, 2);
    switch ($typ) {
        case 1:
            return "$day.$month.$year";  // 03.07.2005
        case 3:
            return "$day.$month.$syear"; // 03.07.05
        case 4:
            return "$day.$month.";  // 03.07.
        case 5:
            $day   = (int) $day;        // 3.7.
            $month = (int) $month;
            return "$day.$month.";
        case 6:
            $day   = (int) $day;        // 3.7.2005
            $month = (int) $month;
            return "$day.$month.$year";
        case 7:
            $day   = (int) $day;        // 3.7.05
            $month = (int) $month;
            return "$day.$month.$syear";
        default:
            return "";
    }
}

function time2user($time)
{
    if ($time == "") {
        return "";
    }
    $va       = sscanf($time, "%2d:%2d:%2d");
    $usertime = sprintf("%d:%02d", $va[0], $va[1]);
    if ($va[2] != 0) {
        $usertime .= sprintf(":%02d", $va[2]);
    }
    return $usertime;
}

function emptyval($fval)
{
    return empty($fval) ||
      $fval == "0000-00-00" ||
      $fval == "00:00:00" ||
      $fval == "00:00" ||
      $fval == "0000-00-00 00:00:00" ||
      is_numeric($fval) && $fval == 0.0;
}
