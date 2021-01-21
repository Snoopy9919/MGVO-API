<?php

namespace MGVO;

// In der Datei wird eine Sniplet-Klasse definiert, welche die Methoden der Klasse MGVO_HPAPI (ext_mod_hp.php)
// aufruft und zu den jeweiligen Daten HTML-Code zur Ausgabe der Daten generiert.
// Die Klasse repräsentiert Beispielcode und muss den individuellen Anforderungen angepasst werden.



class MgvoSniplet
{

    protected MgvoAPI $api;

    protected string $headline;

    public function __construct($call_id, $vcryptkey, $cachemin)
    {
        // call_id: call_id des Vereins
        // vcryptkey: Schlüssel für die synchrone Verschlüsselung.
        // Wird in MGVO in den technischen Parametern eingetragen
        // $vcryptkey = $vp[0];
        // $cachemin = Legt die Cachezeit in Minuten fest. Wenn nicht angegeben, werden 5 Minuten gesetzt
        $this->api = new MgvoAPI($call_id, $vcryptkey, $cachemin);
    }

    public function setDebuglevel($debuglevel)
    {
        $this->api->setDebuglevel($debuglevel);
    }

    public function setHeadline($headline)
    {
        $this->headline = $headline;
    }

    public function writeHeadline($mgvo_headline = ""): string
    {
        $headline = empty($this->headline) ? $mgvo_headline : $this->headline;
        return "<h2>$headline</h2>";
    }

    /**
     * Liest den Vereinskalender mit Nr. $vkalnr mit Terminen des Jahres seljahr
     *
     * @param   int  $vkalnr   Vereinskalendernummer
     * @param   int  $seljahr  Auszuwählendes Jahr
     *
     * @return string
     */
    public function vkalSniplet(int $vkalnr, int $seljahr): string
    {
        $resar = $this->api->readVkal($vkalnr, $seljahr);
        $sniplet = "<div class='mgvo mgvo-vkal'>";
        $sniplet .= $this->writeHeadline($resar['headline']);
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";
        $sniplet .= "<tr><th>Bezeichnung</th><th>Startdatum</th><th>Startzeit</th>";
        $sniplet .= "<th>Enddatum</th><th>Endzeit</th><th>Ort</th></tr>";

        foreach ($resar['objar'] as $idx => $vkr) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$vkr[bez]</td>";
            $sniplet .= "<td>" . date2user($vkr['startdat']) . "</td>";
            $sniplet .= "<td>$vkr[startzeit]</td>";
            $sniplet .= "<td>" . date2user($vkr['enddat']) . "</td>";
            $sniplet .= "<td>$vkr[endzeit]</td>";
            $sniplet .= "<td>" . ($vkr['ort'] ?? "") . "</td>";
            $sniplet .= "</tr>";
        }
        $sniplet .= "</table>";
        $sniplet .= "</div>";
        return $sniplet;
    }

    public function orteSniplet(): string
    {
        // Liest die Ortsliste ein
        $resar = $this->api->readOrte();

        $sniplet = "<div class='mgvo mgvo-orte'>";
        $sniplet .= $this->writeHeadline($resar['headline']);
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";
        $sniplet .= "<tr>";
        $sniplet .= "<th>Orts-ID</th>";
        $sniplet .= "<th>Ortsbezeichnung</th>";
        $sniplet .= "</tr>";
        foreach ($resar['objar'] as $or) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$or[ortid]</td>";
            $sniplet .= "<td>$or[ortbez]</td>";
            $sniplet .= "</tr>";
        }
        $sniplet .= "</table><br>";
        $sniplet .= "</div>";
        return $sniplet;
    }

    public function betreuerSniplet(): string
    {
        // Liest die Betreuer ein
        $resar = $this->api->readBetreuer();

        $sniplet = "<div class='mgvo mgvo-betreuer'>";
        $sniplet .= $this->writeHeadline($resar['headline']);
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";
        $sniplet .= "<tr><th>Trainer-ID</th><th>Name</th><th>Straße</th></tr>";
        foreach ($resar['objar'] as $or) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$or[trid]</td>";
            $sniplet .= "<td>$or[nachname], $or[vorname]</td>";
            $sniplet .= "<td>$or[str]</td>";
            $sniplet .= "</tr>";
        }
        $sniplet .= "</table><br>";
        $sniplet .= "</div>";
        return $sniplet;
    }

    public function eventsSniplet(): string
    {
        // Liest die öffentlichen Veranstaltungen
        $resar = $this->api->readEvents();

        $sniplet = "<div class='mgvo mgvo-events'>";
        $sniplet .= $this->writeHeadline($resar['headline']);
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";
        $sniplet .= "<tr>";
        $sniplet .= "<th>Event</th>";
        $sniplet .= "<th>Beschreibung</th>";
        $sniplet .= "<th>Ort</th>";
        $sniplet .= "<th>Datum</th>";
        $sniplet .= "<th>Zeit</th>";
        $sniplet .= "<th>Bestell-URL</th>";
        $sniplet .= "</tr>";
        foreach ($resar['objar'] as $or) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$or[name]</td>";
            $sniplet .= "<td>$or[description]</td>";
            $sniplet .= "<td>$or[ort]</td>";
            $sniplet .= "<td>" . date2user($or['startdate'] ?? "") . "</td>";
            $sniplet .= "<td>$or[starttime]</td>";
            if (!empty($or['besturl'])) {
                $sniplet .= "<td><a href='$or[besturl]' target=_blank>Bestell-URL</a></td>";
            } else {
                $sniplet .= "<td></td>";
            }
            $sniplet .= "</tr>";
        }
        $sniplet .= "</table><br>";
        $sniplet .= "</div>";
        return $sniplet;
    }

    public function gruppenSniplet(): string
    {
        $resar = $this->api->readGruppen();

        $sniplet = "<div class='mgvo mgvo-gruppen'>";
        $sniplet .= $this->writeHeadline($resar['headline']);
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";
        $sniplet .= "<tr>";
        $sniplet .= "<th>Gruppen-ID</th>";
        $sniplet .= "<th>Name</th>";
        $sniplet .= "<th>Betreuer</th>";
        $sniplet .= "</tr>";
        foreach ($resar['objar'] as $or) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$or[gruid]</td>";
            $sniplet .= "<td>$or[grubez]</td>";
            $sniplet .= "<td>$or[trnameall]</td>";
            $sniplet .= "</tr>";
        }
        $sniplet .= "</table><br>";
        $sniplet .= "</div>";

        return $sniplet;
    }

    public function abteilungenSniplet(): string
    {
        $resar = $this->api->readAbt();

        $sniplet = "<div class='mgvo mgvo-abteilungen'>";
        $sniplet .= $this->writeHeadline($resar['headline']);
        //$sniplet .= "<table>";
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";
        $sniplet .= "<tr>";
        $sniplet .= "<th>Abteilungs-ID</th>";
        $sniplet .= "<th>Name</th>";
        $sniplet .= "</tr>";
        foreach ($resar['objar'] as $or) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$or[abtid]</td>";
            $sniplet .= "<td>$or[abtbez]</td>";
            $sniplet .= "</tr>";
        }
        $sniplet .= "</table><br>";
        $sniplet .= "</div>";

        return $sniplet;
    }

    public function trainingFailSniplet(): string
    {
        $resar = $this->api->readTrainingFail();

        $sniplet = "<div class='mgvo mgvo-trainingfail'>";
        $sniplet .= $this->writeHeadline($resar['headline']);
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";
        $sniplet .= "<tr>";
        $sniplet .= "<th>Gruppe / Belegung</th>";
        $sniplet .= "<th>Datum</th>";
        $sniplet .= "<th>Zeit</th>";
        $sniplet .= "<th>Ort</th>";
        $sniplet .= "<th colspan=3>neu</th>";
        $sniplet .= "<th>Grund / Veranstaltung</th>";
        $sniplet .= "</tr>";
        foreach ($resar['objar'] as $tfr) {
            $sniplet .= "<tr>";
            if (array_key_exists('grbez', $tfr)) {
                $sniplet .= "<td>$tfr[grbez] ($tfr[gruid])</td>";
            } else {
                $sniplet .= "<td>$tfr[belbez]</td>";
            }
            $sniplet .= "<td>" . date2user($tfr['sdat']) . "</td>";
            $sniplet .= "<td>" . time2user($tfr['starttime']) . " - " . time2user($tfr['endtime']) . "</td>";
            $sniplet .= "<td>$tfr[ortsbez]</td>";
            if (array_key_exists('neudat', $tfr)) {
                $sniplet .= "<td>" . date2user($tfr['neudat']) . "</td>";
            } else {
                $sniplet .= "<td></td>";
            }

            $sniplet .= "<td>" . ($tfr['neuzeithtml'] ?? "") . "</td>";
            $sniplet .= "<td>" . ($tfr['neuorthtml'] ?? "") . "</td>";
            $sniplet .= "<td>$tfr[ebez]</td>";
            $sniplet .= "</tr>";
        }
        $sniplet .= "</table><br>";
        $sniplet .= "</div>";
        return $sniplet;
    }

    public function readMitgliederSniplet($selparar = null): string
    {
        // Selektion von Mitgliedern.
        $resar   = $this->api->readMitglieder($selparar);
        $sniplet = "<div class='mgvo mgvo-mitglieder'>";
        $sniplet .= $this->writeHeadline($resar['headline']);
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";
        $sniplet .= "<tr>";
        $sniplet .= "<th>MgNr.</th>";
        $sniplet .= "<th>Nachname</th>";
        $sniplet .= "<th>Vorname</th>";
        $sniplet .= "<th>Straße</th>";
        $sniplet .= "<th>PLZ</th>";
        $sniplet .= "<th>Ort</th>";
        $sniplet .= "<th>Eintritt</th>";
        $sniplet .= "<th>Austritt</th>";
        $sniplet .= "</tr>";
        foreach ($resar['objar'] as $mr) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$mr[mgnr]</td>";
            $sniplet .= "<td>$mr[nachname]</td>";
            $sniplet .= "<td>$mr[vorname]</td>";
            $sniplet .= "<td>$mr[str]</td>";
            $sniplet .= "<td>$mr[plz]</td>";
            $sniplet .= "<td>$mr[ort]</td>";
            $sniplet .= "<td>" . date2user($mr['eintritt']) . "</td>";
            $sniplet .= "<td>" . date2user($mr['austritt']) . "</td>";
            $sniplet .= "</tr>";
        }
        $sniplet .= "</table><br>";
        $sniplet .= "</div>";
        return $sniplet;
    }

    /**
     * Gibt einen HTML String zurück, der ein Mitglied mit der Mitgliednummer $mgnr anzeigt
     *
     * @param   int  $mgnr  Mitgliednummer
     *
     * @return string
     */
    public function showMitgliederSniplet(int $mgnr): string
    {
        $mr      = $this->api->showMitglied($mgnr);
        $sniplet = "<div class='mgvo mgvo-mitglieder'>";
        $sniplet .= $this->writeHeadline("Anzeige Mitglied");
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";
        foreach ($mr as $fieldname => $value) {
            $sniplet .= "<tr><td>$fieldname:</td><td>$value</td></th>";
        }
        $sniplet .= "</table>";
        $sniplet .= "</div>";
        return $sniplet;
    }

    public function listDocumentsSniplet($dokart = null): string
    {
        $resar   = $this->api->listDocuments($dokart);
        $sniplet = "<div class='mgvo mgvo-documents'>";
        $sniplet .= $this->writeHeadline($resar['headline']);
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";
        $sniplet .= "<tr>";
        $sniplet .= "<th>DokNr.</th>";
        $sniplet .= "<th>Dokart</th>";
        $sniplet .= "<th>Name</th>";
        $sniplet .= "<th>Größe</th>";
        $sniplet .= "</tr>";
        foreach ($resar['objar'] as $dokr) {
            $sniplet .= "<tr>";
            $sniplet .= "<td>$dokr[doknr]</td>";
            $sniplet .= "<td>$dokr[dokart]</td>";
            $sniplet .= "<td><a href='$dokr[url_display]' target=_blank>$dokr[dokbez]</a></td>";
            $sniplet .= "<td>$dokr[fsize]</td>";
            $sniplet .= "</tr>";
        }
        $sniplet .= "</table><br>";
        $sniplet .= "</div>";
        return $sniplet;
    }

    public function mitPictSniplet($mgnr)
    {
        $resar = $this->api->getMitpict($mgnr);

        $mpr     = $resar['objar'][0];
        $dokname = $mpr['dokname'];
        $ctype   = $mpr['mimetype'];
        $content = base64_decode($mpr['content']);

        header("Content-Type: $ctype");
        header("Content-Length: " . strlen($content));
        header("Content-disposition: inline; filename=\"$dokname\"");

        echo $content;
    }



}
