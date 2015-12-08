<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007 Patrick Broens (patrick@netcreators.com)
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
require_once (t3lib_extMgm::extPath('hi_surveystats') . 'lib/class.tx_ncpbsurveyfesimplestats_row.php');
require_once (t3lib_extMgm::extPath('hi_surveystats') . 'lib/class.tx_ncpbsurveyfesimplestats_answer.php');

/**
 * DB Class
 *
 * @author Patrick Broens <patrick@netcreators.com>
 * @package TYPO3
 * @subpackage nc_pbsurveyfesimplestats
 */
class tx_ncpbsurveyfesimplestats_item {
	/**
	 * Page ID where the survey items and results are located
	 */
	var $sQuestion = '';
	
	var $iQuestionType = NULL;
	
	var $iTotal = 0;
	
	var $aRows = array();
	
	var $aAnswers = array();

	/***************************************
	 *
	 *	 Setup
	 *
	 ***************************************/


	/**
	 * Initialize the object
	 * PHP4 constructor
	 *
	 * @return	void
	 * @see __construct()
	 */
	function tx_ncpbsurveyfesimplestats_item()	{
		$this->__construct();
	}


	/**
	 * Initialization of class
	 *
	 * @return	void
	 */
	function __construct() {
		
	}
	
	function setQuestion($sQuestion) {
		$this->sQuestion = (string) $sQuestion;
	}
	
	function setQuestionType($iQuestionType) {
		$this->iQuestionType = (int) $iQuestionType;
	}
	
	function setRows($sRows) {
		$aRow = explode(chr(10), $sRows);
		foreach($aRow as $intKey => $sAnswerRow) {
			$this->aRows[$intKey+1] = t3lib_div::makeInstance('tx_ncpbsurveyfesimplestats_row');
			$this->aRows[$intKey+1]->setRow(trim($sAnswerRow));
		}
	}

	function setAnswers($sAnswers) {
		if (in_array($this->iQuestionType, array(1, 23, 2, 3, 6, 8))) {
			$aLine = explode(chr(10), $sAnswers);			
		} elseif ($this->iQuestionType == 4) {
			$aLine = array('Untrue', 'True');
		} elseif ($this->iQuestionType == 5) {
			$aLine = array('No', 'Yes');
		} elseif ($this->iQuestionType == 9) {
			for ($iCount = $sAnswers[0]; $iCount <= $sAnswers[1]; $iCount++) {
				$aLine[] = $iCount;
			}
		}
		if (in_array($this->iQuestionType, array(1, 23, 2, 3, 4, 5))) {
			foreach($aLine as $intKey => $sAnswerLine) {
				$sValues = explode('|',$sAnswerLine);
				$this->aAnswers[$intKey+1] = t3lib_div::makeInstance('tx_ncpbsurveyfesimplestats_answer');
				$this->aAnswers[$intKey+1]->setAnswer(trim($sValues[0]));
			}
		} elseif (in_array($this->iQuestionType, array(6, 8, 9))) {
			foreach($this->aRows as $intRow => $oAnswer) {
				foreach($aLine as $intKey => $sAnswerLine) {
					$sValues = explode('|',$sAnswerLine);
					$this->aRows[$intRow]->aAnswers[$intKey+1] = t3lib_div::makeInstance('tx_ncpbsurveyfesimplestats_answer');
					$this->aRows[$intRow]->aAnswers[$intKey+1]->setAnswer(trim($sValues[0]));
				}
			}
		}
	}
	
	function setAllowAdditional($bAllowAdditional, $sAdditionalText) {
		if (in_array($this->iQuestionType, array(1,3)) && $bAllowAdditional) {
			$this->aAnswers['-1'] = t3lib_div::makeInstance('tx_ncpbsurveyfesimplestats_answer');
			$this->aAnswers['-1']->setAnswer($sAdditionalText);
		}
	}
	
	function addToTotal($iAmount) {
		$this->iTotal += intval($iAmount);
	}
	

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_surveystats/lib/class.tx_ncpbsurveyfesimplestats_item.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_surveystats/lib/class.tx_ncpbsurveyfesimplestats_item.php']);
}
?>