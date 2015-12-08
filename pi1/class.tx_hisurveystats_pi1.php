<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 - 2014 Andreas Kraemer <a.kraemer@horn-verlag.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   69: class tx_hisurveystats_pi1 extends tslib_pibase
 *  105:     function main($content, $conf)
 *  275:     function addChoice(&$tx_ncpbsurveyfesimplestats_item, $key)
 *  335:     function addTrueFalse(&$tx_ncpbsurveyfesimplestats_item, $key)
 *  371:     function addMatrixNumeric(&$tx_ncpbsurveyfesimplestats_item, $key)
 *  483:     function addComment($title, $key)
 *  527:     function addMatrix(&$tx_ncpbsurveyfesimplestats_item, $key)
 *  540:     function addMatrixInput(&$tx_ncpbsurveyfesimplestats_item, $key)
 *  552:     function generateMatrixOverview()
 *  605:     function generateBarChart($filecontent, $labels_array, $y_label, $max, $key)
 *  646:     function generatePieChart($filecontent, $key)
 *  672:     function generateDataFile($key, $content)
 *  701:     function generateHeaderJS($key, $width = 500, $height = 300)
 *  725:     function generateHTML($title, $key, $overallAverage = '', $infoTable = '')
 *  742:     function getStep($max)
 *  759:     function utf8($string)
 *  774:     function shortenLabel($string, $linelength, $maxlength = 0)
 *  803:     function getBardiagramWidth($amountOfBars)
 *  815:     function addPaginationJS($key)
 *  841:     function customAnswer($key)
 *  859:     function getCustomAnswers($key)
 *  900:     function getColor($average)
 *
 * TOTAL FUNCTIONS: 21
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once (t3lib_extMgm::extPath('hi_surveystats') . 'lib/class.tx_ncpbsurveyfesimplestats_questions.php');
require_once (t3lib_extMgm::extPath('hi_surveystats') . 'lib/class.tx_ncpbsurveyfesimplestats_results.php');


/**
 * Plugin 'Survey stats' for the 'hi_surveystats' extension.
 *
 * @author	Andreas Kraemer <a.kraemer@horn-verlag.de>
 * @package	TYPO3
 * @subpackage	tx_hisurveystats
 */
class tx_hisurveystats_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_hisurveystats_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_hisurveystats_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'hi_surveystats';	// The extension key.

	var $pid;				// PID der pbsurvey Datensaetze
	var $votes;				// Gesamtteilnehmer an der Umfrage
	var $tmpFolder;			// Ordner fuer die PHP-Dateien, aus welchen JSON generiert wird
	var $headerJS;			// Javascript, welches die Diagramme erzeugt
	var $html;				// wird mit den DIVs gefuellt, die per Javascript durch die Diagramme ersetzt werden
	var $matrixOverview;	// Gesamtuebersicht aller (numerischen) Matrix-Fragen
	var $advanced;			// 0/1 - Bei 1 erweiterte Statistiken mit genauen Werten darstellen
	var $summary;			// 0/1 Zusammenfassung der numerischen Matrix-Fragen anzeigen
	var $comments_per_page;	// Kommentare pro Seite bei Kommentarfragen
	var $colorGrades;		// 0/1 Notenbalken unterschiedlich einfaerben
	var $maxGrade;			// Hoechstnote oder maximale Punktezahl. Wird fuer die Balkenfaerbung verwendet
	var $bgcolor;			// Hintergrundfarbe der Diagramme
	var $gridcolor;			// Farbe der Gitter
	var $axiscolor;			// Farbe der Achsen
	var $barwidth;			// Breite des Balken (Pixel)
	var $labelmaxlength;	// Maximum length for label text
	var $labellinelength;	// Line length for label text
	var $tooltiplinelength;	// Line length for tooltip text
	var $barchoicecolor;	// Balkenfarbe der Balkendiagramme (Auswahl)
	var $barmatrixcolor;	// Balkenfarbe der Balkendiagramme (Matrix)
	var $pie1color;			// Farbe Kuchendiagramm 1
	var $pie2color;			// Farbe Kuchendiagramm 2


	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	string		The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		// Temporaeren Ordner festlegen bzw. anlegen
		$this->tmpFolder = t3lib_extMgm::extPath('hi_surveystats').'tmp';
		if(!(file_exists($this->tmpFolder) && is_dir($this->tmpFolder))) {
			if(!mkdir($this->tmpFolder, 0775)) return '<strong style="color:red;">'.$this->pi_getLL('folder_not_exists').'</strong>';
		}

		// Starting point data storage?
		if($this->cObj->data["pages"] != null) $this->pid = $this->cObj->data["pages"];
		else $this->pid = $GLOBALS["TSFE"]->id;

		// Flexform
		$this->pi_initPIflexForm();
		$this->flexform = $this->cObj->data["pi_flexform"];
		$this->advanced = $this->pi_getFFvalue($this->flexform, "advanced");
		$this->summary = $this->pi_getFFvalue($this->flexform, "summary");
		$this->comments_per_page = $this->pi_getFFvalue($this->flexform, "comments_per_page");
		$this->colorGrades = $this->pi_getFFvalue($this->flexform, "color_grades");
		$this->maxGrade = $this->pi_getFFvalue($this->flexform, "max_grade");
		$this->bgcolor = $this->pi_getFFvalue($this->flexform, "bgcolor");
		$this->gridcolor = $this->pi_getFFvalue($this->flexform, "gridcolor");
		$this->axiscolor = $this->pi_getFFvalue($this->flexform, "axiscolor");
		$this->barwidth = $this->pi_getFFvalue($this->flexform, "barwidth");
		$this->labellinelength = $this->pi_getFFvalue($this->flexform, "labellinelength");
		$this->tooltiplinelength = $this->pi_getFFvalue($this->flexform, "tooltiplinelength");
		$this->labelmaxlength = $this->pi_getFFvalue($this->flexform, "labelmaxlength");
		$this->barchoicecolor = $this->pi_getFFvalue($this->flexform, "barchoicecolor", "barColor");
		$this->barmatrixcolor = $this->pi_getFFvalue($this->flexform, "barmatrixcolor", "barColor");
		$this->pie1color = $this->pi_getFFvalue($this->flexform, "pie1color", "pieColor");
		$this->pie2color = $this->pi_getFFvalue($this->flexform, "pie2color", "pieColor");

		// Auswertung der Fragen mithilfe der Extension nc_pbsurveyfesimplestats auslesen
		$questions = t3lib_div::makeInstance('tx_ncpbsurveyfesimplestats_questions');
		$questions->setSurveyPid($this->pid);
		$questions->done();
		// Gesamtzahl Teilnehmer mithilfe der Extension nc_pbsurveyfesimplestats auslesen
		$results = t3lib_div::makeInstance('tx_ncpbsurveyfesimplestats_results');
		$results->setSurveyPid($this->pid);
		$results->done();
		$this->votes = $results->iResultCount;

		//t3lib_div::debug($questions);

		$i = 1;
		foreach($questions->questions as $key => $tx_ncpbsurveyfesimplestats_item) {

			// Den Fragetyp bestimmen und die entsprechende Funktion zur Ausgabe aufrufen
			switch($tx_ncpbsurveyfesimplestats_item->iQuestionType) {
				// Auswahl - Mehrfache Antworten (Checkboxen)
				case 1:
					$this->addChoice($tx_ncpbsurveyfesimplestats_item, $key);
					break;
				// Auswahl - Mehrfache Antworten (Selectbox)
				case 23:
					$this->addChoice($tx_ncpbsurveyfesimplestats_item, $key);
					break;
				// Auswahl - Eine Antwort (Auswahllisten)
				case 2:
					$this->addChoice($tx_ncpbsurveyfesimplestats_item, $key);
					break;
				// Auswahl - Eine Antwort (Radio-Buttons)
				case 3:
					$this->addChoice($tx_ncpbsurveyfesimplestats_item, $key);
					break;
				// Auswahl - Wahr / Falsch
				case 4:
					$this->addTrueFalse($tx_ncpbsurveyfesimplestats_item, $key);
					break;
				// Auswahl - Ja / Nein
				case 5:
					$this->addTrueFalse($tx_ncpbsurveyfesimplestats_item, $key);
					break;
				// Matrix - Mehrere Antworten pro Zeile (Checkboxen) - vorerst nicht erlaubt
				case 6:
					$this->addMatrix($tx_ncpbsurveyfesimplestats_item, $key);
					break;
				// Matrix - Mehrere Antworten pro Zeile (Textfelder) - vorerst nicht erlaubt
				case 7:
					$this->addMatrixInput($tx_ncpbsurveyfesimplestats_item, $key);
					break;
				// Matrix - Eine Antwort pro Zeile (Radio-Buttons) - nur Ausgabe einer Auswertungs-Tabelle, keine Uebersicht
				case 8:
					//$this->addMatrix($tx_ncpbsurveyfesimplestats_item, $key);
					$this->addMatrixNumeric($tx_ncpbsurveyfesimplestats_item, $key);
					break;
				// Matrix - Beurteilungsskala (numerisch)
				case 9:
					$this->addMatrixNumeric($tx_ncpbsurveyfesimplestats_item, $key);
					break;
				// Kommentarfeld - nur Text ausgeben
				case 10:
					if($this->advanced) $this->addComment($tx_ncpbsurveyfesimplestats_item->sQuestion, $key);
					break;
			}
			$i++;
		}

		// Gesamtansicht der Matrizenfragen (numerisch) generieren
		// Nur, falls in Flexform aktiviert und  numerische Matrixfragen ausgewertet wurden
		if($this->summary && !empty($this->matrixOverview)) $this->generateMatrixOverview();

		// Erzeugung Content + HeaderData
		if(!empty($this->headerJS)) {
			$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = '
		<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('hi_surveystats').'res/js/jquery.min.js"></script>
		<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('hi_surveystats').'res/js/jquery.pagination.js"></script>
		<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('hi_surveystats').'res/js/swfobject.js"></script>
		<script type="text/javascript">'.
			$this->headerJS.'
		</script>
		<style type="text/css">
			h2 {
				margin-top: 30px;
			}
			.info-table {
				 border-collapse: collapse;
				 margin-bottom: 20px;
			}
			.info-table th,
			.info-table td {
				 border: 1px solid #ccc;
				 text-align: center;
			}
			.info-table th {
				width: 45px;
			}
			.info-table .left {
				text-align: left;
				padding: 0 10px  0 3px;
			}
			.info-table td.right {
				text-align: center;
			}
			.zebra {
				background-color: #f0f0f0;
			}
			.prev, .next {
				cursor: pointer;
				text-decoration: underline;
			}
			li {
				padding: 5px;
			}
		</style>';
		}

		if($this->advanced) $content = '<h3>'.$this->pi_getLL('total_votes').': '.$this->votes.'</h3>';
		$content .= $this->html;

		return $this->pi_wrapInBaseClass($content);
	}


	/**
	 * Auswahl - Fragen als Balkendiagramm (Bar) darstellen.
	 * Fuer jedes Diagramm wird eine PHP-Datei erzeugt, welche den Input fuer das Diagramm generiert.
	 * Diese Funktion erzeugt den PHP-Code fuer die einzelnen Balken (Anzahl Stimmen + Prozent vom Gesamten).
	 * In weiteren Dateien wird diese Code dann in die vollstaendige PHP-Datei eingefuegt, das Javascript, welches
	 * auf die PHP-Datei verweist, wird generiert und das HTML (Ueberschrift + div) wird erzeugt. In das div wird
	 * per Javascript dann der Inhalt geladen.
	 *
	 * @param	item		$tx_ncpbsurveyfesimplestats_item: Objekt der Klasse tx_ncpbsurveyfesimplestats_item
	 * @param	integer		$key: UID der Frage
	 * @return	void
	 */
	function addChoice(&$tx_ncpbsurveyfesimplestats_item, $key) {
		$title = $tx_ncpbsurveyfesimplestats_item->sQuestion;

		// Antworten durchlaufen
		$j = 0;
		$labels_array = '';
		$filecontent = '';
		$sum = array();

		foreach($tx_ncpbsurveyfesimplestats_item->aAnswers as $aAnswer) {

			$labels_array .= '"'.$this->shortenLabel($this->utf8($aAnswer->sAnswer), $this->labellinelength, $this->labelmaxlength).'",';

			$sum[$j] = $aAnswer->iAmount;

			$filecontent .= '
$bar_'.$j.' = new bar_value('.$aAnswer->iAmount.');
$bar_'.$j.'->set_tooltip("'.$this->shortenLabel($this->utf8($aAnswer->sAnswer), $this->tooltiplinelength).'<br>#val# '.$this->pi_getLL('of').' '.$tx_ncpbsurveyfesimplestats_item->iTotal.' '.$this->pi_getLL('votes').' ('.$aAnswer->iPercentage.'%)");
$data[] = $bar_'.$j.';';

			$j++;
		}

		$filecontent .= '
$newbar = new bar_3d();
$newbar->set_values($data);
$newbar->colour = "'.$this->barchoicecolor.'";';

		// "," am Ende entfernen
		$labels_array = 'array('.substr($labels_array, 0, -1).')';

		// Maximum bestimmen
		$max = max($sum);

		// Anzahl Balken und Breite des Diagramms bestimmen
		$amountOfBars = count($sum);
		$width = $this->getBardiagramWidth($amountOfBars);

		// Feld fuer Benutzereingaben, falls vorhanden, ausgeben
		$infoTable = '';
		if($this->advanced && $this->customAnswer($key)) $infoTable = $this->getCustomAnswers($key);

		$this->generateBarChart($filecontent, $labels_array, $this->pi_getLL('votes'), $max, $key);
		$this->generateHeaderJS($key, $width);
		$this->generateHTML($title, $key, '', $infoTable);
	}


	/**
	 * Wahr/Falsch bzw. Ja/Nein - Fragen als Kuchendiagramm (Pie) darstellen.
	 * Fuer jedes Diagramm wird eine PHP-Datei erzeugt, welche den Input fuer das Diagramm generiert.
	 * Diese Funktion erzeugt den PHP-Code fuer die einzelnen Kuchenstuecke (Name der Antwort und Anzahl Stimmen).
	 * In weiteren Dateien wird diese Code dann in die vollstaendige PHP-Datei eingefuegt, das Javascript, welches
	 * auf die PHP-Datei verweist, wird generiert und das HTML (Ueberschrift + div) wird erzeugt. In das div wird
	 * per Javascript dann der Inhalt geladen.
	 *
	 * @param	item		$tx_ncpbsurveyfesimplestats_item: Objekt der Klasse tx_ncpbsurveyfesimplestats_item
	 * @param	integer		$key: UID der Frage
	 * @return	void
	 */
	function addTrueFalse(&$tx_ncpbsurveyfesimplestats_item, $key) {
		$title = $tx_ncpbsurveyfesimplestats_item->sQuestion;

		$filecontent = 'array(';
		foreach($tx_ncpbsurveyfesimplestats_item->aAnswers as $aAnswer) {
			$lbl = '';
			switch($aAnswer->sAnswer) {
				case 'No': $lbl = $this->pi_getLL('no'); break;
				case 'Yes': $lbl = $this->pi_getLL('yes'); break;
				case 'Untrue': $lbl = $this->pi_getLL('untrue'); break;
				case 'True': $lbl = $this->pi_getLL('true'); break;
			}
			$filecontent .= 'new pie_value('.$aAnswer->iAmount.', "'.$lbl.'"),';
		}
		// "," am Ende entfernen
		$filecontent = substr($filecontent, 0, -1);

		$filecontent .= ')';

		$this->generatePieChart($filecontent, $key);
		$this->generateHeaderJS($key);
		$this->generateHTML($title, $key);
	}

	/**
	 * Numerische Matrizen als Balendiagramm (Bar) darstellen.
	 * Fuer jedes Diagramm wird eine PHP-Datei erzeugt, welche den Input fuer das Diagramm generiert.
	 * Diese Funktion erzeugt den PHP-Code fuer die einzelnen Balken (Durchschnittsnote der einzelnen Fragen).
	 * In weiteren Dateien wird diese Code dann in die vollstaendige PHP-Datei eingefuegt, das Javascript, welches
	 * auf die PHP-Datei verweist, wird generiert und das HTML (Ueberschrift + div) wird erzeugt. In das div wird
	 * per Javascript dann der Inhalt geladen.
	 *
	 * @param	item		$tx_ncpbsurveyfesimplestats_item: Objekt der Klasse tx_ncpbsurveyfesimplestats_item
	 * @param	integer		$key: UID der Frage
	 * @return	void
	 */
	function addMatrixNumeric(&$tx_ncpbsurveyfesimplestats_item, $key) {
		$title = $tx_ncpbsurveyfesimplestats_item->sQuestion;

		// Einzelne Punkte durchlaufen
		$j = 0;
		$labels_array = '';
		$sum = array();
		$average = array();
		$total = array();
		$filecontent = '';
		$infoTableHeader = '<th></th>';
		$infoTableRows = '';

		foreach($tx_ncpbsurveyfesimplestats_item->aRows as $aRow) {

			// Sind ueberhaupt Werte vorhanden?
			if($aRow->iTotal === 0) continue;

			$total[$j] = $aRow->iTotal;

			$labels_array .= '"'.$this->shortenLabel($this->utf8($aRow->sRow), $this->labellinelength, $this->labelmaxlength).'",';
			$sum[$j] = 0;

			// Info-Tabelle fuellen - Begriffe nicht umbrechen
			$class = '';
			if($j % 2 == 0) $class = ' class="zebra"';
			$infoTableRows .= '<tr'.$class.'>
				<th scope="row" class="left">'.str_replace(' ', '&nbsp;', htmlspecialchars($aRow->sRow)).'</th>';

			foreach($aRow->aAnswers as $aAnswer) {

				// Info-Tabelle fuellen
				if($j == 0) $infoTableHeader .= '<th>'.$aAnswer->sAnswer.'</th>';
				$infoTableRows .= '<td>'.$aAnswer->iAmount.'</td>';

				// Wert ist eine Zahl? - Falls nicht mit naechster Zeile weiter / Stimmen muessen von gesamtzahl abgezogen werden
				if(intval($aAnswer->sAnswer) === 0) {
					$total[$j] -= $aAnswer->iAmount;
					continue;
				}

				// Werte aufaddieren
				$sum[$j] += $aAnswer->iAmount * $aAnswer->sAnswer;
			}
			// Durchschnitt berechnen und auf 2 Stellen runden
			if($total[$j] == 0) $average[$j] = 0;
			else $average[$j] = round($sum[$j]/$total[$j], 2);

			// Info-Tabelle fuellen
			$infoTableRows .= '<td>'.$aRow->iTotal.'</td>
	</tr>';

			$filecontent .= '
$bar_'.$j.' = new bar_value('.$average[$j].');
$bar_'.$j.'->set_tooltip("'.$this->shortenLabel($this->utf8($aRow->sRow), $this->tooltiplinelength).'<br>'.$this->pi_getLL('average').': #val#");';

			if($this->colorGrades) $filecontent .= '
$bar_'.$j.'->set_colour("'.$this->getColor($average[$j]).'");';

			$filecontent .= '
$data[] = $bar_'.$j.';';

			$j++;
		}
		// Falls gar keine Zahlenwerte zur Auswahl Funktion abbrechen und keine Datei erzeugen
		if(empty($sum)) return;
		
		// Gesamt-Durchschnitt berechnen aus den summierten Ergebnissen und Gesamtstimmen (numerisch)  der einzelnen Fragen
		$overallAverage = array_sum($sum) / array_sum($total);

		// Daten sammeln fuer eine Gesamtuebersicht aller numerischen Matrix-Fragen - falls $sum leer ist, war es keine numerische Frage
		if(array_sum($sum) > 0) $this->matrixOverview[] = array($title, $overallAverage);

		// Info-Tabelle vervollstaendigen
		$infoTable = '';
		if($this->advanced) {
			$infoTable = '<table cellspacing="0" cellpadding="0" border="0" class="info-table">
	<tr>'.$infoTableHeader.'<th>'.$this->pi_getLL('total').'</th></tr>
	'.$infoTableRows.'
</table>';
		}

		$filecontent .= '
$newbar = new bar_cylinder();
$newbar->set_values($data);
$newbar->colour = "'.$this->barmatrixcolor.'";';

		// "," am Ende entfernen
		$labels_array = 'array('.substr($labels_array, 0, -1).')';

		// Maximum bestimmen
		$max = ceil(max($average));

		// Anzahl Balken und Breite des Diagramms bestimmen
		$amountOfBars = count($average);
		$width = $this->getBardiagramWidth($amountOfBars);

		// Diagramm nur generieren, wenn es wirklich eine numerische Matrix war (=> $sum nicht leer)
		// Sonst nur das HTML mit der Tabelle ausgeben
		if(array_sum($sum) > 0) {
			$this->generateBarChart($filecontent, $labels_array, $this->pi_getLL('grade'), $max, $key);
			$this->generateHeaderJS($key, $width);
		}
		$this->generateHTML($title, $key, $overallAverage, $infoTable);
	}

	/**
	 * Antworten der Kommentar-Felder auslesen und ausgeben.
	 * Zur Uebersichtlichkeit die Kommentare per jQuery auf
	 * mehrere Seiten verteilen.
	 *
	 * @param	string		$title: Titel der Frage
	 * @param	integer		$key: UID der Frage
	 * @return	void
	 */
	function addComment($title, $key) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'a.answer',					// SELECT
			'tx_pbsurvey_answers a LEFT JOIN
			tx_pbsurvey_item i ON a.question = i.uid',		// FROM
			'a.deleted = 0 AND
			a.hidden = 0 AND
			i.deleted = 0 AND
			i.hidden = 0 AND
			i.uid = '.$key.' AND
			a.pid = '.$this->pid		// WHERE
		);

		$list = '<ul id="comments'.$key.'">';
		$j = 0;
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

			$answer = trim($row['answer']);

			$class = '';
			if($j % 2 == 0) $class = ' class="zebra"';

			// Leere Antworten entfernen
			if(!empty($answer)) {
				$list .= '<li'.$class.'>'.nl2br($answer).'</li>';
				$j++;
			}
		}
		$list .= '</ul>';

		$pagination = $this->addPaginationJS($key);

		$this->html .= '<h2>'.htmlspecialchars($title).'</h2><h3>'.$this->pi_getLL('number_of_comments').': '.$j.'</h3>'.$list.$pagination;
	}


	/**
	 * Muss noch implementiert werden.
	 * Muesste wohl in mehreren Diagrammen angezeigt werden.
	 *
	 * @param	item		$tx_ncpbsurveyfesimplestats_item: Objekt der Klasse tx_ncpbsurveyfesimplestats_item
	 * @param	integer		$key: UID der Frage
	 * @return	void
	 */
	function addMatrix(&$tx_ncpbsurveyfesimplestats_item, $key) {
		$title = $tx_ncpbsurveyfesimplestats_item->sQuestion;
	}


	/**
	 * Muss noch implementiert werden.
	 * Muesste wohl in mehreren Diagrammen angezeigt werden.
	 *
	 * @param	item		$tx_ncpbsurveyfesimplestats_item: Objekt der Klasse tx_ncpbsurveyfesimplestats_item
	 * @param	integer		$key: UID der Frage
	 * @return	void
	 */
	function addMatrixInput(&$tx_ncpbsurveyfesimplestats_item, $key) {
		$title = $tx_ncpbsurveyfesimplestats_item->sQuestion;
	}


	/**
	 * Uebersicht mit allen Matrizenfragen (numerisch) als Balkendiagramm erstellen.
	 * Nimmt als Parameter die nach und nach erzeugte Klassenvariable matrixOverview.
	 * Nummer des Diagramms ist 0, um nicht in Konflikt mit einem anderen zu kommen.
	 *
	 * @return	void
	 */
	function generateMatrixOverview() {

		$j = 0;
		$values = array();
		foreach($this->matrixOverview as $matrixOverview) {

			$values[] = $this->matrixOverview[$j][1];

			$filecontent .= '
$bar_'.$j.' = new bar_value('.round($this->matrixOverview[$j][1], 2).');
$bar_'.$j.'->set_tooltip("'.$this->utf8($this->matrixOverview[$j][0]).'<br>'.$this->pi_getLL('average').': #val#");';

			if($this->colorGrades) $filecontent .= '
$bar_'.$j.'->set_colour("'.$this->getColor(round($this->matrixOverview[$j][1], 2)).'");';

			$filecontent .= '
$data[] = $bar_'.$j.';';

			$labels_array .= '"'.$this->shortenLabel($this->utf8($this->matrixOverview[$j][0]), $this->labellinelength, $this->labelmaxlength).'",';

			$j++;
		}
		$filecontent .= '
$newbar = new bar_cylinder();
$newbar->set_values($data);
$newbar->colour = "'.$this->barmatrixcolor.'";';

		// Anzahl Balken und Breite des Diagramms bestimmen
		$amountOfBars = count($this->matrixOverview);
		$width = $this->getBardiagramWidth($amountOfBars);

		// Maximalwert
		$max = ceil(max($values));

		$overallAverage = array_sum($values) / count($values);

		$this->generateBarChart($filecontent, 'array('.substr($labels_array, 0, -1).')', $this->pi_getLL('grade'), $max, 0);
		$this->generateHeaderJS(0, $width);
		$this->generateHTML($this->pi_getLL('overview'), 0, $overallAverage);
	}


	/**
	 * Ein Balendiagramm (Bar) generieren.
	 * Mithilfe des uebergebenen PHP-Codes wird der komplette Code fuer die PHP-Datei erstellt.
	 *
	 * @param	string		$filecontent: Generierter PHP-Code fuer das Diagramm
	 * @param	string		$y_label: Beschriftung fuer die Y-Achse
	 * @param	string		$labels_array: PHP-Code fuer die Erzeugung eines Array mit den Beschriftungen fuer die X-Achse
	 * @param	integer		$max: Hoechstwert fuer die Y-Achse
	 * @param	integer		$key: UID der Frage
	 * @return	void
	 */
	function generateBarChart($filecontent, $labels_array, $y_label, $max, $key) {

		// Schritte auf der Y-Achse bestimmen
		$step = $this->getStep($max);

		$content = '
'.$filecontent.'
$newbar->set_on_show(new bar_on_show("grow-up", 0, 1));

$chart->add_element($newbar);

$y = new y_axis();
$y->set_range(0, '.$max.', '.$step.');
$y->set_colour("'.$this->axiscolor.'");
$y->set_grid_colour("'.$this->gridcolor.'");

$y_legend = new y_legend("'.$y_label.'");
$y_legend->set_style("{font-size: 16px}");
$chart->set_y_legend($y_legend);

$x = new x_axis();
$x->set_3d(5);
$x->set_labels_from_array('.$labels_array.');
$x->colour("'.$this->axiscolor.'");
$x->grid_colour("'.$this->gridcolor.'");
$chart->set_x_axis($x);
$chart->set_y_axis($y);
echo $chart->toString();';

		$this->generateDataFile($key, $content);
	}


	/**
	 * Ein Kuchendiagramm (Pie) generieren.
	 * Mithilfe des uebergebenen PHP-Codes wird der komplette Code fuer die PHP-Datei erstellt.
	 *
	 * @param	string		$filecontent: Generierter PHP-Code fuer das Diagramm
	 * @param	integer		$key: UID der Frage
	 * @return	void
	 */
	function generatePieChart($filecontent, $key) {
		$content = '
$pie = new pie();
$pie->colours(array("'.$this->pie1color.'","'.$this->pie2color.'"));
$pie->set_start_angle(35);
$pie->set_animate(true);
$pie->set_tooltip("#percent#<br>#val# '.$this->pi_getLL('of').' #total# '.$this->pi_getLL('votes').'");
$pie->set_values('.$filecontent.');

$chart->add_element($pie);

$chart->x_axis = null;

echo $chart->toPrettyString();';

		$this->generateDataFile($key, $content);
	}


	/**
	 * Ein PHP-Datei erzeugen, welche den JSON-Code fuer ein Diagramm generiert.
	 *
	 * @param	integer		$key: UID der Frage
	 * @param	string		$content: PHP-Code der Datei
	 * @return	void
	 */
	function generateDataFile($key, $content) {
		$handler = fOpen($this->tmpFolder.'/stat_'.$this->pid.'_'.$key.'.php', "w");
		if(!$handler) die ('<strong style="color:red;">'.$this->pi_getLL('no_permission').'</strong>');

		$content = '<?php
include "../res/php-ofc-library/open-flash-chart.php";
header("Content-Type: application/json");
$title = new title();
$chart = new open_flash_chart();
$chart->set_title($title);
$chart->set_bg_colour("'.$this->bgcolor.'");'.
$content.'
?>';
		fWrite($handler, $content);
		fClose($handler);
	}


	/**
	 * Generiert das Javascript fuer den Header.
	 * Dieses Javascript definiert die id des HTML-Elements, in welches
	 * das Diagramm eingefuegt wird. Zudem wird die Breite und Hoehe des
	 * Diagramms, die zugehoerige PHP-Datei sowie die Beschriftung des
	 * Ladebalkens festgelegt.
	 *
	 * @param	integer		$key: UID der Frage
	 * @param	integer		$width: Breite des Diagramms
	 * @param	integer		$height: Hoehe des Diagramms
	 * @return	void
	 */
	function generateHeaderJS($key, $width = 500, $height = 300) {

		if($width < 250) $width = 250;

		$this->headerJS .= '
		swfobject.embedSWF(
			"'.t3lib_extMgm::siteRelPath('hi_surveystats').'res/open-flash-chart.swf", "chart'.$key.'",
			"'.$width.'", "'.$height.'", "9.0.0", "expressInstall.swf",
			{"data-file":"'.t3lib_extMgm::siteRelPath('hi_surveystats').'tmp/stat_'.$this->pid.'_'.$key.'.php", "loading":"'.$this->pi_getLL('loading').'"}
		);';
	}


	/**
	 * Generiert das HTML.
	 * Das HTML besteht aus einer Ueberschrift (H1) und dem div, in
	 * welches das erzeugte Programm hineingeladen wird.
	 *
	 * @param	string		$title: Titel des Diagramms
	 * @param	integer		$key: UID der Frage
	 * @param	float		$overallAverage: Gesamtdurchschnitt dieser Auswertung
	 * @param	string		$infoTable: Info-Tabelle mit Aufschluesselung der einzelnen Werte
	 * @return	void
	 */
	function generateHTML($title, $key, $overallAverage = '', $infoTable = '') {
		$this->html .= '
			<h2>'.htmlspecialChars($title).'</h2>';
		if(!empty($overallAverage)) $this->html .= '
			<h3>'.$this->pi_getLL('overall_grade').': '.round($overallAverage, 2).'</h3>';
		$this->html .=
			$infoTable.'
			<div id="chart'.$key.'"></div>';
	}


	/**
	 * Berechnet die Schritte auf der Y-Achse anhand des Maximalwerts
	 *
	 * @param	integer		$max: Maximalwert auf der Y-Achse
	 * @return	integer		Wert fuer die einzelnen Schritte auf der Y-Achse
	 */
	function getStep($max) {
		if($max <= 10) return 1;
		if($max <= 50) return 5;
		if($max <= 100) return 10;
		if($max <= 500) return 50;
		if($max <= 1000) return 100;
		if($max > 1000) return 200;
	}

	/**
	 * Die Diagramme erwarten Strings in utf-8.
	 * Diese Funktion schreit Strings in utf-8 um, falls die Installation
	 * nicht bereits auf utf-8 laueft.
	 *
	 * @param	string		$string: String der umgeschrieben werden soll
	 * @return	string		Umgeschriebener utf-8 String
	 */
	function utf8($string) {
		if($GLOBALS['TSFE']->metaCharset == 'utf-8') return $string;
		return utf8_encode($string);
	}


	/**
	 * Die Beschriftungen werden mit dieser Funktion
	 * entsprechend den Einstellungen gekuerzt.
	 *
	 * @param	string		$string: Vollstaendige Beschriftung
	 * @param	integer		$linelength: Zeilenlaenge
	 * @param	integer		$maxlength: Maximale Laenge
	 * @return	string		Gekuerzte / auf mehrere Zeilen verteilte Beschriftung
	 */
	function shortenLabel($string, $linelength, $maxlength = 0) {
		$words = explode(" ", $string);
		$output = '';
		$curline = 0;
		foreach($words as $word) {
			$curline += strlen($word)+1;
			if($curline > $linelength) {
				$output .= "\n";
				$curline = strlen($word)+1;
			}
			// String nach maximaler Laenge abschneiden, sofern eine uebergeben wurde
			if($maxlength > 0 && (strlen($output) + strlen($word)) >= $maxlength) {
				$output .= '...';
				return $output;
			}
			$output .= $word . ' ';
		}
		// Leerzeichen hinten wieder entfernen
		return substr($output, 0, -1);
	}


	/**
	 * Die Breite der Balkendiagramme ergibt sich aus der Anzahl an
	 * Balken mal der eingestellten Balkenbreite.
	 *
	 * @param	integer		$amountOfBars: Anzahl Balken des Diagrams
	 * @return	integer		Breite des Diagrams
	 */
	function getBardiagramWidth($amountOfBars) {
		return $amountOfBars * $this->barwidth;
	}


	/**
	 * Generiert das benoetigte Javascript um den Kommentaren
	 * per jQuery eine Navigation hinzuzufuegen.
	 *
	 * @param	integer		$key: UID der Frage
	 * @return	string		Javascript fuer jQuery paginataion
	 */
	function addPaginationJS($key) {
		return '<script type="text/javascript">
$("#comments'.$key.'").after(
	\'<div class="pagination_nav">\' +
	\' <span class="prev'.$key.' prev">&laquo; '.$this->pi_getLL('previous').'<\/span> \' +
	\' <span class="label'.$key.'"><\/span> \' +
	\' <span class="next'.$key.' next">'.$this->pi_getLL('next').' &raquo;<\/span> \' +
	\'<\/div>\'
	);
$("#comments'.$key.'").jPagination({
	btnNext: ".next'.$key.'",
	btnPrev: ".prev'.$key.'",
	labelField: ".label'.$key.'",
	visibleElements: '.$this->comments_per_page.',
	textOf: "'.$this->pi_getLL('of').'",
	textResults: "'.$this->pi_getLL('results').'" });
</script>';
	}


	/**
	 * Prueft, ob zu der Frage eigene Antworten moeglich sind.
	 *
	 * @param	integer		$key: UID der Frage
	 * @return	boolean		0/1 Zusaetzliche Antwort erlaubt oder nicht
	 */
	function customAnswer($key) {
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'answers_allow_additional',	// SELECT
			'tx_pbsurvey_item',			// FROM
			'pid = '.$this->pid.' AND
			uid = '.$key				// WHERE
		);
		return $row[0]['answers_allow_additional'];
	}


	/**
	 * Fuer Fragen mit zusaetzlichem Eingabefeld erstellt diese
	 * Funktion eine Tabelle, in der die Begriffe aufgelistet sind.
	 *
	 * @param	integer		$key: UID der Frage
	 * @return	string		Tabelle mit Begriffen und deren Haeufigkeit
	 */
	function getCustomAnswers($key) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'answer',				// SELECT
			'tx_pbsurvey_answers',	// FROM
			'pid = '.$this->pid.' AND
			question = '.$key		// WHERE
		);

		$answers = array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			// Zahlenwerte nicht ausgeben, da es sich dabei um normale Antworten handelt.
			if(intval($row['answer']) == 0) $answers[$row['answer']] = $answers[$row['answer']] + 1;
		}

		// Tabelle erstellen
		$table = '<h3>'.$this->pi_getLL('user_input').':</h3>
<table cellspacing="0" cellpadding="0" border="0" class="info-table">
	<tr>
		<th>'.$this->pi_getLL('term').'</th>
		<th>'.$this->pi_getLL('frequency').'</th>
	</tr>';
		$i = 0;
		foreach($answers as $key => $value) {
			$class = '';
			if($i % 2 == 0) $class = ' class="zebra"';
			$table .= '<tr'.$class.'><td class="left">'.str_replace(' ', '&nbsp;', $key).'</td><td class="right">'.$value.'</td></tr>';
			$i++;
		}
		$table .= '</table>';

		return $table;
	}


	/**
	 * Berechnet zu einer Note eine bestimmte Farbe,
	 * damit die Unterschiede besser hervorgehoben werden.
	 *
	 * @param	integer		$average: Note, anhand der die Farbe berechnet werden soll
	 * @return	string		Hex-Farbwert
	 */
	function getColor($average) {
		// Zahl vor dem Komma
		$n = floor($average);
		// Nachkommastellen
		$f = ($average - $n) * 10;
		// 255/100 - Umrechnungsfaktor
		$c = 2.55;
		// Schritte - 100/ Anzahl Noten
		$d = 100 / $this->maxGrade;
		// Aus den Nachkommastellen einen Wert zwischen 10 und 20 generieren
		$percent = round($f * 2, 0);
		$r = 0;
		$g = 0;
		$b = '00';

		// Je nach Note den Hex-Wert berechnen fuer rot und gruen. Blau bleibt immer auf 0.
		// Dazu wird ein Prozentwert erstellt, der dann mit 2.55 multipliziert wird.
		$basis = $n * $d;
		$r = dechex(round(($basis + $percent) * $c, 0));
		$g = dechex(round(((100 - $basis) - $percent) * $c, 0));

		// immer 2-stellig - falls 1-stellig eine 0 davor einfuegen
		if(strlen($r) == 1) $r = '0'.$r;
		if(strlen($g) == 1) $g = '0'.$g;

		return '#'.$r.$g.$b;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_surveystats/pi1/class.tx_hisurveystats_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_surveystats/pi1/class.tx_hisurveystats_pi1.php']);
}

?>