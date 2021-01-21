<?php

namespace MGVO;

require '../vendor/autoload.php';
// call_id zur Identifikation des Vereins (hier: Demoverein)
$call_id = "9a052167eb8a71f51b686e35c18a665a";
// Symetrischer Schlüssel, muss identisch sein mit dem Schlüssel, der in den technischen Parametern abgelegt wird.
$vcryptkey = "f4jd8Nzhfr4f8tbhkHGZ765VGVujg";

// Instanziierung der Klasse MGVO_SNIPLET
// Der dritte Parameter sollte unbedingt im Produktivbetrieb auf 5 (Minuten) oder höher eingestellt werden.

$sniplet_no = isset($_GET['sniplet_no']) && is_numeric($_GET['sniplet_no']) ? $_GET['sniplet_no'] : 11;

$msc = new MgvoSniplet($call_id, $vcryptkey, 0);

$msc->setDebuglevel(0);//MGVO_DEBUG_ERR | MGVO_DEBUG_DATA | MGVO_DEBUG_XMLTRANS
$htmlout = 0;
$html    = "";

switch ($sniplet_no) {
    case 1:
        $htmlout = 1;
        $html    = $msc->abteilungenSniplet();
        break;
    case 2:
        $htmlout = 1;
        $html    = $msc->betreuerSniplet();
        break;
    case 3:
        $htmlout = 1;
        $html    = $msc->eventsSniplet();
        break;
    case 4:
        $htmlout = 1;
        $html    = $msc->gruppenSniplet();
        break;
    case 5:
        $htmlout = 1;
        $html    = $msc->listDocumentsSniplet();
        break;
    case 6:
        $htmlout = 1;
        $mgnr    = 17;
        $msc->mitPictSniplet($mgnr);
        break;
    case 7:
        $htmlout = 1;
        $html    = $msc->orteSniplet();
        break;
    case 8:
        $htmlout             = 1;
        $selparar['suchbeg'] = "l*";
        $html                = $msc->readMitgliederSniplet($selparar);
        break;
    case 9:
        $htmlout = 1;
        $mgnr    = 8;
        $html    = $msc->showMitgliederSniplet($mgnr);
        break;
    case 10:
        $htmlout = 1;
        $html    = $msc->trainingFailSniplet();
        break;
    case 11:
        $htmlout = 1;
        $vkalnr  = 2;
        $seljahr = 2019;
        $html    = $msc->vkalSniplet($vkalnr, $seljahr);
        break;
}

if ($htmlout) {
    echo "<html lang=\"de\"><head>
    <title>MGVO</title>
    <link rel='stylesheet' href='test.css'>
    </head><body>";

    echo "<div>$html";
    echo "</div></body></html>";
}
