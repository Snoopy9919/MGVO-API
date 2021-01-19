<?php

use JetBrains\PhpStorm\ArrayShape;

require_once("includes/ext_hlpfkt.php");
require_once("includes/ext_cipher.php");

class MGVO_HPAPI {

	/** @var string Identifikation des Vereins */
	private string $call_id;

	/** @var string Schlüssel für die synchrone Verschlüsselung. Wird in MGVO in den technischen Parametern eingetragen */
	private string $vcryptkey;

	/** @var string call_id verschlüsslt mit vcryptkey */
	private string $callidc;

	/** @var string immer auf "https://www.mgvo.de/prog" gesetzt */
	private string $urlroot;

	/** @var int Beim readen -> 1, beim login -> 0 */
	private int $cacheon;

	/** @var int Legt die Cachezeit in Minuten fest. Wenn nicht angegeben, werden 5 Minuten gesetzt */
	private int $cachetime;

	/** @var int Legt das debuglevel dieser API fest */
	private int $debuglevel;


	/**
	 * @param   string  $call_id    Identifikation des Vereins
	 * @param   string  $vcryptkey  Schlüssel für die synchrone Verschlüsselung. Wird in MGVO in den technischen
	 *                              Parametern eingetragen
	 * @param   int     $cachetime  Legt die Cachezeit in Minuten fest. Wenn nicht angegeben, werden 5 Minuten gesetzt
	 */
	function __construct(string $call_id, string $vcryptkey = "", int $cachetime = 5) {
		$this->call_id   = $call_id;
		$this->vcryptkey = $vcryptkey;
		$cipher          = new Cipher($vcryptkey);
		$this->callidc   = $cipher->encrypt($call_id);

		$this->urlroot   = "https://www.mgvo.de/prog";
		$this->cacheon   = 0;
		$this->cachetime = $cachetime * 60;                // cachetime in Sekunden
	}

	/**
	 * Sets the debuglevel and
	 * the new global mgvo_debug variable to the debuglevel
	 *
	 * @param   int  $debuglevel
	 */
	function set_debuglevel(int $debuglevel) {
		$this->debuglevel = $debuglevel;
	}

	/**
	 * schreibt $comment: $lv in den errorlog falls debug == true
	 *
	 * @param   string  $comment  der zu loggende Kommentar
	 * @param   mixed   $lv
	 * @param   int     $dtyp     Debugtype
	 */
	private function mgvo_log(string $comment, mixed $lv, int $dtyp) {

		if ($dtyp && $this->debuglevel == 0)
			return;

		if (!empty($comment) && is_string($lv) && !empty($lv)) {
			error_log("$comment: $lv");
		} else {
			if (!empty($comment))
				error_log($comment);
			if (!empty($lv))
				error_log(print_r($lv, true));
		}
	}

	/**
	 * Die Funktion holt die Daten per https und legt sie als Datei auf dem lokalen Dateisystem ab.
	 * Beim Initiieren der MGVO-Klasse kann die Cachedauer festgelegt werden.
	 * Sie wird ohne explizite Angabe auf fünf Minuten festgelegt.
	 *
	 * @param   string  $url    Die aufzurufende URL
	 * @param   array   $paras  Die übergebenen Queryparameter als array
	 *
	 * @return false|string returned einen XML String oder false, falls es einen error gab
	 */
	private function http_get_cached(string $url, array $paras): false|string {
		$filename   = pathinfo(parse_url($url)['path'], PATHINFO_FILENAME);
		$parasquery = http_build_query($paras);
		$fn         = "$filename.$parasquery.cache"; //name of the cached file (pub_vkal_xml.php.paras.cache)

		//Verwende cache oder führe die Webanfrage aus
		$ret = $this->cacheon && is_file($fn) && time() - filemtime($fn) <= $this->cachetime ?
		  file_get_contents($fn) :
		  file_get_contents("$this->urlroot/$url?$parasquery");

		// Prüfen, ob der Aufruf erfolgreich war
		if (empty($ret) ||
		  str_contains($ret, "Nicht erlaubt!") ||
		  str_contains($ret, "Sicherheitsversto") ||
		  !str_contains($ret, "DOCTYPE xml")) {

			$this->mgvo_log("XML nicht korrekt geladen", $ret, MGVO_DEBUG_ERR);
			return false;
		}

		//Speichere Anfrage local
		if ($this->cachetime > 0)
			file_put_contents($fn, $ret);

		$this->mgvo_log("XML-Returnstring", $ret, MGVO_DEBUG_DATA);
		return $ret;
	}

	/**
	 * Converts an XML to a Table
	 *
	 * @param   string  $url      Die aufzurufende URL
	 * @param   array   $paras    Die übergebenen Queryparameter als array
	 * @param   string  $objname  Der Name der auszugebenden Tabelle
	 *
	 * @return false|SimpleXMLElement
	 */
	#[ArrayShape(
	  [
		'headline' => "string",
		'verein'   => "string",
		'version'  => "string",
		'objar'    => "array"])]
	private function xml2table(string $url, array $paras, string $objname): false|array {
		$xml = simplexml_load_string($this->http_get_cached($url, $paras));
		$this->mgvo_log("Aus XML erzeugtes SimpleXMLElement", $xml, MGVO_DEBUG_XML);

		$objfieldlist = $xml->objfieldlist ?? "";//liste aller keys, die das object(array) haben sollte
		$xmlObj       = $xml->{$objname} ?? NULL;

		$fieldnames = explode(",", $objfieldlist);
		$resArr     = [];
		foreach ($xmlObj as $key => $value) {
			$currentArr = [];
			foreach ($fieldnames as $fieldname) {
				$currentArr[$fieldname] = (string) $value->{$fieldname} ?? "";
			}
			$resArr[] = $currentArr;
		}
		$ergar = [
		  'headline' => $xml->headline ?? "",
		  'verein'   => $xml->verein ?? "",
		  'version'  => $xml->version ?? "",
		  'objar'    => $resArr];
		$this->mgvo_log("Ergebnistabelle", $ergar, MGVO_DEBUG_ERG);
		return $ergar;
	}


	/**
	 * Liest den Vereinskalender mit Nr. $vkalnr mit Terminen des Jahres seljahr
	 *
	 * @param   int  $vkalnr   Vereinskalendernummer
	 * @param   int  $seljahr  Auszuwählendes Jahr
	 *
	 * @return array
	 */
	#[ArrayShape([
	  'headline' => "string",
	  'verein'   => "string",
	  'version'  => "string",
	  'objar'    => "array"])]
	function read_vkal(int $vkalnr, int $seljahr): array {
		$this->cacheon = 1;
		$paras         = [
		  'call_id' => $this->call_id,
		  'seljahr' => $seljahr,
		  'vkalnr'  => $vkalnr];
		$url           = "pub_vkal_xml.php";
		return $this->xml2table($url, $paras, "event");
	}

	/**
	 * Liest die Orte
	 *
	 * @return array
	 */
	#[ArrayShape([
	  'headline' => "string",
	  'verein'   => "string",
	  'version'  => "string",
	  'objar'    => "array"])]
	function read_orte(): array {
		$this->cacheon = 1;
		$paras         = ['call_id' => $this->call_id];
		$url           = "pub_orte_xml.php";
		return $this->xml2table($url, $paras, "ort");
	}

	/**
	 * Liest die Betreuer
	 *
	 * @return array
	 */
	#[ArrayShape([
	  'headline' => "string",
	  'verein'   => "string",
	  'version'  => "string",
	  'objar'    => "array"])]
	function read_betreuer(): array {
		$this->cacheon = 1;
		$paras         = ['call_id' => $this->call_id];
		$url           = "pub_trainer_xml.php";
		return $this->xml2table($url, $paras, "betreuer");
	}

	/**
	 * Liest die öffentlichen Veranstaltungen
	 *
	 * @return array
	 */
	#[ArrayShape([
	  'headline' => "string",
	  'verein'   => "string",
	  'version'  => "string",
	  'objar'    => "array"])]
	function read_events(): array {
		$this->cacheon = 1;
		$paras         = ['call_id' => $this->call_id];
		$url           = "pub_events_xml.php";
		return $this->xml2table($url, $paras, "event");
	}

	/**
	 * Liest die Gruppen
	 *
	 * @return array
	 */
	#[ArrayShape([
	  'headline' => "string",
	  'verein'   => "string",
	  'version'  => "string",
	  'objar'    => "array"])]
	function read_gruppen(): array {
		$this->cacheon = 1;
		$paras         = ['call_id' => $this->call_id];
		$url           = "pub_gruppen_xml.php";
		return $this->xml2table($url, $paras, "group");
	}

	/**
	 * Liest die Abteilungen
	 *
	 * @return array
	 */
	#[ArrayShape([
	  'headline' => "string",
	  'verein'   => "string",
	  'version'  => "string",
	  'objar'    => "array"])]
	function read_abt(): array {
		$this->cacheon = 1;
		$paras         = ['call_id' => $this->call_id];
		$url           = "pub_abt_xml.php";
		return $this->xml2table($url, $paras, "abteilung");
	}

	/**
	 * Liest die Trainingsausfallzeiten
	 *
	 * @return array
	 */
	#[ArrayShape([
	  'headline' => "string",
	  'verein'   => "string",
	  'version'  => "string",
	  'objar'    => "array"])]
	function read_training_fail(): array {
		$this->cacheon = 1;
		$paras         = ['call_id' => $this->call_id];
		$url           = "pub_ortreserv_xml.php";
		return $this->xml2table($url, $paras, "cancellation");
	}

	/**
	 * Selektion von Mitgliedern.
	 * Das Array selparar umfasst eine Auswahl aus folgenden Selektionsfelder der Mitgliedermaske
	 * Allgemeiner Suchbegriff: suchbeg
	 * Suchalter/Geburtsdatum: suchalterv - suchalterb
	 * Austritt: suchaustrittv - suchaustrittb
	 * Gruppen-ID: suchgruid
	 * Beitragsgruppe: suchbeigru
	 * Lastschriftzahler: lssel (Selektionswert: 1)
	 * Barzahler/Überweiser: barsel (Selektionswert: 1)
	 * Dauerauftrag: dasel (Selektionswert: 1)
	 * Geschlecht: geschl (x,m,w)
	 * Mitglied: ausgetr (x,m,a)
	 * Aktiv/Passiv: aktpass (x,a,p)
	 * Mailempfänger: mailempf (x,e,s)
	 * Inland/Ausland: (x,i,a)
	 * Mahnstufe: (a,1,2,3)
	 *
	 * @param   array  $selparar  selectionparameters
	 *
	 * @return array
	 */
	#[ArrayShape([
	  'headline' => "string",
	  'verein'   => "string",
	  'version'  => "string",
	  'objar'    => "array"])]
	function read_mitglieder(array $selparar): array {
		$cipher = new Cipher($this->vcryptkey);                         // Initialisierung der Verschlüsselung

		$this->cacheon       = 1;
		$selparar['call_id'] = $this->call_id;

		$paras = [
		  'paras'   => rawurlencode($cipher->encrypt(http_build_query($selparar))),
		  'call_id' => $this->call_id
		];
		$url   = "pub_mit_xml.php";
		return $this->xml2table($url, $paras, "member");
	}

	/**
	 * @param   int  $mgnr  die Mitgliedsnummer
	 *
	 * @return mixed
	 */
	function show_mitglied(int $mgnr): mixed {
		return $this->read_mitglieder(['suchbeg' => $mgnr])['objar'][0];
	}

	/**
	 * Liefert das Passbild eines Mitglieds inklusive mimetype und fsize (Dateigröße).
	 * Das eigentliche Bild ist base64 codiert.
	 *
	 * @param   int  $mgnr  Mitgleidsnummer
	 *
	 * @return array
	 */
	#[ArrayShape([
	  'headline' => "string",
	  'verein'   => "string",
	  'version'  => "string",
	  'objar'    => "array"])]
	function get_mitpict(int $mgnr): array {
		$cipher = new Cipher($this->vcryptkey);                         // Initialisierung der Verschlüsselung

		$this->cacheon = 1;
		$vars          = [
		  'call_id' => $this->call_id,
		  'mgnr'    => $mgnr
		];

		$paras = [
		  'paras'   => rawurlencode($cipher->encrypt(http_build_query($vars))),
		  'call_id' => $this->call_id
		];
		$url   = "pub_mitpict_xml.php";

		return $this->xml2table($url, $paras, "bilddaten");
	}

	/**
	 * dokart: Es werden öffentliche Dokumente der spezifizierten Dokumentart aufgelistet
	 *
	 * @param   string|null  $dokart  kein beispiel aufruf, deswegen nur maybe string
	 *
	 * @return array
	 */
	#[ArrayShape([
	  'headline' => "string",
	  'verein'   => "string",
	  'version'  => "string",
	  'objar'    => "array"])]
	function list_documents($dokart = NULL): array {
		$this->cacheon = 1;
		$paras         = [
		  'call_id' => $this->call_id,
		  'dokart'  => $dokart
		];
		$url           = "pub_documents_xml.php";
		return $this->xml2table($url, $paras, "document");
	}

	/**
	 * Die Methode hat folgende Returncodes:
	 * 0  : Passwort nicht in Ordnung / User nicht vorhanden
	 * 1  : Login ok
	 * 10 : PIS nicht gefüllt
	 * 11 : Max. Logon-Versuche überschritten
	 * 12 : Geheimcode generiert und an Mobilgerät versendet, Logon muss mit Code erfolgen
	 * 13 : Geheimcode (SMS-Code) stimmt nicht überein
	 *
	 * @param   int     $email_id
	 * @param   string  $passwd
	 * @param   string  $pis  Personal Identity String über get_browserpis (wenn Zwei-Faktor-Authentifizierung)
	 * @param           $smscode
	 *
	 * @return int
	 */
	function login(int $email_id, string $passwd, string $pis, $smscode): int {
		// Der Personal Identity String (PIS) wird durch Aufruf der Javascript-Funktion "get_browserpis"
		// erzeugt und muss an den Login übergeben werden, wenn eine Zwei-Faktor-Authentifizierung genutzt werden soll.

		$this->cacheon = 0;
		$vars          = [
		  'call_id'  => $this->call_id,
		  'email_id' => $email_id,
		  'passwd'   => $passwd,
		  'pis'      => $pis,
		  'smscode'  => $smscode
		];
		$url           = "$this->urlroot/pub_mgb_validate.php?" . http_build_query($vars);
		$ret           = http_get1($url);
		return (int) $ret;
	}

	/**
	 * Returncodes:
	 *  -1: Parameter missing
	 * -64: callidc nicht vorhanden
	 * -65: vcryptkey nicht vorhanden
	 * -66: call_id stimmt nicht überein
	 * -16: Mussfeld nicht vorhanden
	 * -17: Feldwert nicht im Definitionsbereich
	 *   0: <Mitgliedsnummer>
	 *
	 * @param   mixed  $inar
	 *
	 * @return array
	 */
	function create_mitstamm(mixed $inar): array {
		$paras = [
		  'call_id' => $this->call_id,
		  'callidc' => $this->callidc,
		  'inarjc'  => json_encode($inar)
		];
		$url   = "$this->urlroot/pub_createmit.php";
		return json_decode(http_post($url, $paras), true);
	}


}