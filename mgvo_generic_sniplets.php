<?php

namespace MGVO;

class GenericMgvoSniplet extends MgvoSniplet
{

    /****************************
     * Generic Sniplets
     *
     * Mit diesen Funktion lassen sich generisch beliebige HTML-Tabellen aus MGVO erstellen.
     * Mit den Einträgen $vkal_use_fields_table, $vkal_head_fields_tablen und $vkal_sanitize_fields_table
     * wird gesteuert, welche Felder ausgegeben werden.
     *
     * Die verfügbaren Felder können dem XML (<objfieldlist>) entnommen werden und sind auch im Array der API
     * verfügbar)
     * $vkal_use_fields_table =  zu verwendendene Felder
     * $vkal_head_fields_table = dazugehörige Überschriften (gleiche Anzahl wie Felder erforderlich)
     * $vkal_sanitize_fields_table = Felder, die eine Datumsbehandlung benötigen
     * vkal_sort gibt den sortfeldnamen vor. Übergabe als String (mit Komma) oder als Array
     * $vkal_filter gibt Filterkritieren vor. Übergabe als Feld=String mit Komma ("ortid=saal1,startzeit=20:00:00")
     * oder als Array
     * (array(array('field'=>'ortid','value'=>'saal1'),array('field'=>'startzeit','value'=>'20:00:00'), Nur im Array
     * werten Leer- und Sonderzeichen unterstützt. Aktuell wir nur das "=" unterstützt. (kine "<" oder "!=")
     * $vkal_rewrite_fildes Hiermit können Felder in andere Felder geschrieben werden (wenn diese leer sind)
     * z.B. resstarttime in startzeit für Ortsreservierungen.
     * Im MGVO Kalender werden, je nach Eintragsursprung eine Vielzahl unterschiedlicher Felder gefüllt,
     * daher ist dies in manchen Fällen notwendig.
     * $max_count - Die maxminale Anzahl von Einträgen je Seite
     * $page - Die Seite (nur in Verbindung mit $maxcount)
     * Die Spalten haben jeweils eine CSS-Klasse mgvo-f-<feldname>, z.B. mgvo-f-bez,
     * somit lassen sich im CSS die Spaltenbreiten individuell angeben.
     * Wenn im Namen "NOWEB" steht, wird der Eintrag niemals ausgegeben
     * Wenn im Namen EXTERN: steht, wird nur der Teil nach EXTERN: ausgegeben.
     * Praktisch um z.B. Extern die Details auszulassen "Saal vermietet an Hans Schmidt EXTERN:Private Veranstaltung"
     * *************************/


    /**
     * Liest die Gruppen ein und gibt sie aus
     *
     * @param   string[]           $use_fields_table
     * @param   string[]           $head_fields_table
     * @param   string|array|null  $sort
     * @param   string|array|null  $filter
     * @param   null               $rewrite_fields
     * @param   int                $max_count
     * @param   int                $page
     *
     * @return string
     */
    public function generateGruppenSniplet(
        array $use_fields_table = ["grubez", "grutxt", "startzeit", "endzeit", "turnus", "ortid", "tridall"],
        array $head_fields_table = ["Gruppenname", "Beschreibung", "Start", "Ende", "Turnus", "Ort", "Trainer"],
        $sort = null,
        $filter = null,
        $rewrite_fields = null,
        $max_count = 0,
        $page = 0
    ) {
        if (count($use_fields_table) !== count($head_fields_table)) {
            return "Anzahl der Felder und Überschriften in mgvo_sniplet_vkal() nicht gleich";
        }

        $sanitize_fields_table = ["startzeit", "endzeit"];

        // Liest die gruppen ein
        $resar = $this->api->readGruppen();

        return $this->genericSniplet(
            $resar,
            "mgvo-vkal",
            $use_fields_table,
            $head_fields_table,
            $sanitize_fields_table,
            $rewrite_fields,
            $sort,
            $filter,
            $max_count,
            $page
        );
    }


    /**
     * Liest den Vereinskalender mit Nr. vkalnr mit Terminen des Jahres seljahr als HTML.
     * Verfügbare Felder: startdat,bez,prio,vkalnr,evnr,startzeit,enddat,endzeit,ortid,notiz,rec_day_freq,rec_wk_freq,
     * rec_mon_freq1,rec_mon_tag,rec_mon_freq2,rec_yr_freq1,rec_yr_tag,rec_yr_freq2,rec_range_enddat,ort,ortkb
     * ortid,eventnr,ressdat,resstime,resedat,resetime,ebez,eltxt,eort,startdat,startzeit,enddat,endzeit,email_org,
     * betr01,betrbez01,ticketbis,saalplan,publish,pub_helfer,resmaildat,helfermaildat,abrechdat,pubdate,url_eventinfo,
     * pm_bar,teilnehmerflag,emtickflag,ort,ortkb,bez,prio,stdabrech,bgcol,ticketmail,verkaufsintro,vorldat,
     * wartelisteaktiv,notiz,betr02,betrbez02,porto,sonderueber,pm_ueber,pm_ls
     *
     * @param   int                $vkalnr
     * @param   int                $seljahr
     * @param   string[]           $vkal_use_fields_table
     * @param   string[]           $vkal_head_fields_table
     * @param   string|array|null  $vkal_sort
     * @param   string|array|null  $vkal_filter
     * @param   array|null         $vkal_rewrite_fields
     * @param   int                $max_count
     * @param   int                $page
     *
     * @return string
     */
    public function generateVkalSniplet(
        $vkalnr,
        $seljahr,
        array $vkal_use_fields_table = ["startdat", "startzeit", "bez", "ort"],
        array $vkal_head_fields_table = ["Datum", "Start", "Veranstaltungen", "ort"],
        $vkal_sort = null,
        $vkal_filter = null,
        array $vkal_rewrite_fields = null,
        $max_count = 0,
        $page = 0
    ) {
        if (count($vkal_use_fields_table) !== count($vkal_head_fields_table)) {
            return "Anzahl der Felder und Überschriften in mgvo_sniplet_vkal() nicht gleich";
        }

        if ($vkal_rewrite_fields === null) {
            $vkal_rewrite_fields = ['resstime' => 'starzeit', 'resetime' => 'endzeit', 'ressdat' => 'startdat'];
        }

        $vkal_sanitize_fields_table = ["startdat", "startzeit", "enddat", "endzeit"];

        // Liest den Vereinskalender mit Nr. vkalnr mit Terminen des Jahres seljahr
        $resar = $this->api->readVkal($vkalnr, $seljahr);

        return $this->genericSniplet(
            $resar,
            "mgvo-vkal",
            $vkal_use_fields_table,
            $vkal_head_fields_table,
            $vkal_sanitize_fields_table,
            $vkal_rewrite_fields,
            $vkal_sort,
            $vkal_filter,
            $max_count,
            $page
        );
    }


    /**
     * @param   array              $resar
     * @param   string             $css_class
     * @param   string[]           $use_fields_table
     * @param   string[]           $head_fields_table
     * @param   array              $sanitize_fields_table
     * @param   array              $rewrite
     * @param   string|array|null  $sort
     * @param   string|array       $filter
     * @param   int                $max_count
     * @param   int                $page
     *
     * @return string
     */
    public function genericSniplet(
        array $resar,
        $css_class,
        array $use_fields_table,
        array $head_fields_table,
        array $sanitize_fields_table,
        array $rewrite,
        $sort,
        $filter,
        $max_count,
        $page
    ) {
        $sniplet = "<div class='mgvo $css_class'>";
        $sniplet .= parent::writeHeadline($resar['headline']);
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";
        $sniplet .= "<tr>";

        // Filter können entweder als String mit
        // "feld=Wert,feld=wert,...' übergeben werden (mit Komma getrennt) oder als Array.
        // Leerzeichen im Vergleichswert, Sonderzeichen, etc. werden nur im Array unterstützt.
        // Groß/Klein wird ignoriert, es wird bisher nur "=" unterstützt. (Kein !=, etc.)
        if (is_string($filter)) {
            $filtersets = explode(",", $filter);
            $filter     = [];
            foreach ($filtersets as $id => $filterset) {
                $filters = explode('=', $filterset);
                if (count($filters) != 2) {
                    error_log("MGVO:mgvo_generic_sniplet: nicht korrekte Filterkritieren" . print_r($filtersets));
                    continue;
                }
                $filter[$id]['field'] = trim($filters[0]);
                $filter[$id]['value'] = trim($filters[1]);
                //$filter['operator']   = 'EQ'; // For future use
            }
        }


        // Sortierwerte können entweder als String übergeben werden (mit Komma getrennt) oder als Array
        if (is_string($sort)) {
            $sort = explode(",", $sort);
        }

        $listarray = $resar['objar'];
        if ($sort !== null) { // Es ist nicht gesichert, ob das mit mehreren Sortiervorgaben funktioniert)
            foreach ($sort as $sortfield) {
                uasort($listarray, function ($a, $b) use (&$sortfield) {
                    if ($a == $b) {
                        return 0;
                    }
                    return ($a[$sortfield] < $b[$sortfield]) ? -1 : 1;
                });
            }
        }

        foreach ($head_fields_table as $header) {
            $sniplet .= "<th class='mgvo-h-$header'>$header</th>";
        }
        $sniplet .= "</tr>";

        $count = 1;

        foreach ($listarray as $idx => $vkr) {
            // Sind filterkriterien aktiv, prüfen, ob diese zutreffen, sonst weiter.
            if ($filter !== null) {
                foreach ($filter as $filter2) {
                    if (!$vkr[$filter2['Field']] == $filter2['value']) {
                        continue;
                    }
                }
            }

            if ($count !== 0 &&  // keine Anzahl angegeben
              (
                  !($count > $max_count && $page === 0)
                // keine Seite angeben, aktuelle Anzahl größer Max_Count
                || !(($count * $page <= $max_count) &&
                  !(($count * ($page + 1)) <= $max_count))  // nicht auf der aktuellen Seite
              )
            ) {
                $count++;
                continue;
            }

            if (strstr($vkr['bez'], "NO_WEB")) {
                continue;
            }

            if (strstr($vkr['bez'], "EXTERN:")) {
                $vkr['bez'] = str_replace(
                    "EXTERN:",
                    "",
                    strstr($vkr['bez'], "EXTERN:")
                ); // Titel ab Extern: (ohne Extern:)
            }

            // Rewrite
            if ($rewrite !== null) {
                foreach ($rewrite as $source => $dest) {
                    if ($vkr[$dest] = "") {
                        $vkr[$dest] = $vkr[$source];
                    }
                }
            }

            $sniplet .= "<tr>";
            foreach ($use_fields_table as $field) {
                if (isset($vkr[$field])) {
                    if (in_array($field, $sanitize_fields_table)) {
                        $sniplet .= "<td class='mgvo-f-$field'>" . date2user($vkr[$field]) . "</td>";
                    } else {
                        $sniplet .= "<td class='mgvo-f-$field'>$vkr[$field]</td>";
                    }
                } else {
                    $sniplet .= "<td class='mgvo-f-$field'></td>";
                }
            }
            $sniplet .= "</tr>";
            $count++;
        }
        $sniplet .= "</table>";
        $sniplet .= "</div>";
        return $sniplet;
    }


    public function generateVkalSnipetEntry(
        $vkalnr,
        $seljahr,
        $arrayindex,
        $use_fields_table = null,
        $head_fields_table = null,
        $vkal_rewrite_fields = null
    ) {
        // Liest den Vereinskalender mit Nr. vkalnr mit Terminen des Jahres seljahr
        // und gibt den Temrin mit der EventID aus.
        // Verfügbare Felder: startdat,bez,prio,vkalnr,evnr,startzeit,enddat,endzeit,
        // ortid,notiz,rec_day_freq,rec_wk_freq,rec_mon_freq1,rec_mon_tag,rec_mon_freq2,rec_yr_freq1,
        // rec_yr_tag,rec_yr_freq2,rec_range_enddat,ort,ortkb
        // Konfig-Anleitung siehe oben
        if (empty($use_fields_table) or empty($head_fields_table)) {
            // Alle Felder
            //$use_fields_table = explode(",","startdat,bez,prio,vkalnr,evnr,startzeit,enddat,endzeit,ortid,notiz,rec_day_freq,rec_wk_freq,rec_mon_freq1,rec_mon_tag,rec_mon_freq2,rec_yr_freq1,rec_yr_tag,rec_yr_freq2,rec_range_enddat,ort,ortkb");
            //$head_fields_table = explode(",","startdat,bez,prio,vkalnr,evnr,startzeit,enddat,endzeit,ortid,notiz,rec_day_freq,rec_wk_freq,rec_mon_freq1,rec_mon_tag,rec_mon_freq2,rec_yr_freq1,rec_yr_tag,rec_yr_freq2,rec_range_enddat,ort,ortkb");
            $use_fields_table  = explode(",", "startdat,startzeit, bez,ortid");
            $head_fields_table = explode(",", "Datum,Start,Veranstaltungen, ortid");
        }
        if (count($use_fields_table) != count($head_fields_table)) {
            return "Anzahl der Felder und Überschriften in mgvo_sniplet_vkal_entry() nicht gleich";
        }
        if (empty($vkal_rewrite_fields)) { // Überschreiben von Feldern
            $vkal_rewrite_fields = array('starzeit' => 'resstime', 'endzeit' => 'resetime', 'startdat' => 'ressdat');
        }

        $sanitize_fields_table = explode(",", "startdat,startzeit,enddat,endzeit");

        // Liest den Vereinskalender mit Nr. vkalnr mit Terminen des Jahres seljahr
        $resar = $this->api->readVkal($vkalnr, $seljahr);

        return $this->genericSnipletEntry(
            $resar,
            $arrayindex,
            "mgvo-vkal-entry",
            $use_fields_table,
            $head_fields_table,
            $sanitize_fields_table,
            $vkal_rewrite_fields
        );
    }

    public function generateGruppenSnipletEntry(
        $arrayindex,
        $use_fields_table = null,
        $head_fields_table = null
    ) {
        // Liest den Vereinskalender mit Nr. vkalnr mit Terminen des Jahres seljahr
        // und gibt den Temrin mit der EventID aus.

        // Verfügbare Felder: startdat,bez,prio,vkalnr,evnr,startzeit,enddat,endzeit,ortid,notiz,rec_day_freq,
        // rec_wk_freq,rec_mon_freq1,rec_mon_tag,rec_mon_freq2,rec_yr_freq1,
        // rec_yr_tag,rec_yr_freq2,rec_range_enddat,ort,ortkb

        if (empty($use_fields_table) or empty($head_fields_table)) {
            // Alle Felder
            // $use_fields_table = explode(",","gruid,grubez,grutxt,grukat,vorgid,kursvon,kursbis,bgcol,gzfld01,gzfld02,gzfld03,gzfld04,gzfld05,kbez,abtid,lfdnr,wotag,startzeit,endzeit,turnus,ortid,ort,ortkb,tridall,trnameall,trid01,trid,trname,trid02,trfid");
            // $head_fields_table = explode(",","gruid,grubez,grutxt,grukat,vorgid,kursvon,kursbis,bgcol,gzfld01,gzfld02,gzfld03,gzfld04,gzfld05,kbez,abtid,lfdnr,wotag,startzeit,endzeit,turnus,ortid,ort,ortkb,tridall,trnameall,trid01,trid,trname,trid02,trfid");
            $use_fields_table  = explode(",", "grubez,wotag,startzeit,endzeit,ortkb,trnameall");
            $head_fields_table = explode(",", "Gruppe,Tag,Start,Ende,Ort,Trainer");
        }
        if (count($use_fields_table) != count($head_fields_table)) {
            return "Anzahl der Felder und Überschriften in mgvo_sniplet_gruppen_entry() nicht gleich";
        }

        $sanitize_fields_table = explode(",", "startdat,startzeit,enddat,endzeit");

        // Liest den Vereinskalender mit Nr. vkalnr mit Terminen des Jahres seljahr
        $resar = $this->api->readGruppen();

        return $this->genericSnipletEntry(
            $resar,
            $arrayindex,
            "mgvo-gruppen-entry",
            $use_fields_table,
            $head_fields_table,
            $sanitize_fields_table,
            null
        );
    }

    public function generateEventSnipletEntry($arrayindex, $use_fields_table = null, $head_fields_table = null)
    {
        // Liest den Veranstaltungseintrag mit Nr.

        // Verfügbare Felder: eventnr,name,ort,startdate,starttime,enddate,endtime,bgcol,email_org,publish,
        // resmaildat,helfermaildat,abrechdat,pubdate,description,vorldat,pub_helfer,wartelisteaktiv,notiz,
        // betr01,betrbez01,ticketbis,saalplan,link,pm_bar,teilnehmerflag,emtickflag,besturl

        if (empty($use_fields_table) or empty($head_fields_table)) {
            // Alle Felder
            // $use_fields_table = explode(",","eventnr,name,ort,startdate,starttime,enddate,endtime,bgcol,email_org,publish,resmaildat,helfermaildat,abrechdat,pubdate,description,vorldat,pub_helfer,wartelisteaktiv,notiz,betr01,betrbez01,ticketbis,saalplan,link,pm_bar,teilnehmerflag,emtickflag,besturl");
            // $head_fields_table = explode(",","eventnr,name,ort,startdate,starttime,enddate,endtime,bgcol,email_org,publish,resmaildat,helfermaildat,abrechdat,pubdate,description,vorldat,pub_helfer,wartelisteaktiv,notiz,betr01,betrbez01,ticketbis,saalplan,link,pm_bar,teilnehmerflag,emtickflag,besturl");
            $use_fields_table  = explode(",", "startdate,starttime,name,ort");
            $head_fields_table = explode(",", "Datum,Uhrzeit,Veranstaltung,Ort");
        }
        if (count($use_fields_table) != count($head_fields_table)) {
            return "Anzahl der Felder und Überschriften in mgvo_sniplet_vkal_entry() nicht gleich";
        }

        $sanitize_fields_table = explode(",", "starttime,endtime");

        // Liest den Vereinskalender mit Nr. vkalnr mit Terminen des Jahres seljahr
        $resar = $this->api->readEvents();

        return $this->genericSnipletEntry(
            $resar,
            $arrayindex,
            "mgvo-event-entry",
            $use_fields_table,
            $head_fields_table,
            $sanitize_fields_table,
            null
        );
    }

    public function generateNotrainingSnipletEntry(
        $arrayindex,
        $use_fields_table = null,
        $head_fields_table = null
    ) {
        // Liest den Veranstaltungseintrag mit Nr.
        // Verfügbare Felder: resdat,starttime,endtime,neustarttime,neuendtime,
        // gruid,abtbez,normbelegung,ortsbez,reservierungsgrund

        if (empty($use_fields_table) or empty($head_fields_table)) {
            // Alle Felder
            // $use_fields_table = explode(",","resdat,starttime,endtime,neustarttime,neuendtime,gruid,abtbez,normbelegung,ortsbez,reservierungsgrund");
            // $head_fields_table = explode(",","resdat,starttime,endtime,neustarttime,neuendtime,gruid,abtbez,normbelegung,ortsbez,reservierungsgrund");
            $use_fields_table  = explode(",", "resdat,starttime,gruid,ortsbez,reservierungsgrund ");
            $head_fields_table = explode(",", "Datum,Uhrzeit,Gruppe,Ort,Grund");
        }
        if (count($use_fields_table) != count($head_fields_table)) {
            return "Anzahl der Felder und Überschriften in mgvo_sniplet_notraining_entry() nicht gleich";
        }

        $sanitize_fields_table = explode(",", "starttime,endtime");

        // Liest die Trainingsausfälle
        $resar = $this->api->readTrainingFail();

        return $this->genericSnipletEntry(
            $resar,
            $arrayindex,
            "mgvo-notraining-entry",
            $use_fields_table,
            $head_fields_table,
            $sanitize_fields_table,
            null
        );
    }


    public function genericSnipletEntry(
        $resar,
        $arrayindex,
        $css_class,
        $use_fields_table,
        $head_fields_table,
        $sanitize_fields_table,
        $rewrite
    ) {
        $sniplet = "<div class='mgvo $css_class'>";
        $sniplet .= parent::writeHeadline($resar['headline']);
        $sniplet .= "<table style='border-collapse: collapse; border: 1px'>";

        error_log("mgvo_generic_sniplet_entry: arrayindex:$arrayindex ccs $css_class use_field " .
          print_r($use_fields_table, true) . " head " . print_r($head_fields_table, true));

        $fields_table = array_combine($use_fields_table, $head_fields_table);
        //error_log("gen_entry:".substr (print_r($resar, true),0,2500));

        // Rewrite fehlt noch

        $sniplet .= "<tr>";
        foreach ($fields_table as $field => $header) {
            $sniplet .= "<tr><th class='mgvo-h-" . $field . "'>" . $header . "</th>";

            if (!empty($rewrite) && in_array($field, $rewrite)) {
                $resar['objar'][$arrayindex][$field] = $resar['objar'][$arrayindex][$rewrite[$field]];
            }

            if (!empty($sanitize_fields_table) && in_array($field, $sanitize_fields_table)) {
                $sniplet .= "<td class='mgvo-f-$field'>" . date2user($resar['objar'][$arrayindex][$field]) .
                  "</td></tr>";
            } else {
                $sniplet .= "<td class='mgvo-f-$field'>" . $resar['objar'][$arrayindex][$field] . "</td></tr>";
            }
        }
        $sniplet .= "</table>";
        $sniplet .= "</div>";
        return $sniplet;
    }
}
