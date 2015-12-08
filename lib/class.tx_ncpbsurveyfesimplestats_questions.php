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
require_once (t3lib_extMgm::extPath('hi_surveystats') . 'lib/class.tx_ncpbsurveyfesimplestats_item.php');

/**
 * DB Class
 *
 * @author Patrick Broens <patrick@netcreators.com>
 * @package TYPO3
 * @subpackage nc_pbsurveyfesimplestats
 */
class tx_ncpbsurveyfesimplestats_questions {
	/**
	 * Page ID where the survey items and results are located
	 */
	var $iSurveyPid = NULL;
	
	var $questions = array();

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
	function tx_ncpbsurveyfesimplestats_questions()	{
		$this->__construct();
	}


	/**
	 * Initialization of class
	 *
	 * @return	void
	 */
	function __construct() {
		
	}
	
	function setSurveyPid($iSurveyPid) {
		$this->iSurveyPid = (int) $iSurveyPid;
	} 

	function setQuestions() {
		$sSelectFields = $this->getQuestionSelectFields();
		$sFromTable = $this->getQuestionFromTable();
		$sWhereClause = $this->getQuestionWhereClause();
		$sGroupBy = '';
		$sOrderBy = $this->getOrderBy();
		$dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($sSelectFields, $sFromTable, $sWhereClause, $sGroupBy, $sOrderBy);
		while($aDbRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes))	{
			$this->questions[$aDbRow['uid']] = t3lib_div::makeInstance('tx_ncpbsurveyfesimplestats_item');
			$this->questions[$aDbRow['uid']]->setQuestion($aDbRow['question']);
			$this->questions[$aDbRow['uid']]->setQuestionType($aDbRow['question_type']);
			if (in_array($aDbRow['question_type'], array(6, 8, 9))) {
				$this->questions[$aDbRow['uid']]->setRows($aDbRow['rows']);
			}
			if($aDbRow['question_type'] != 9) {
				$this->questions[$aDbRow['uid']]->setAnswers($aDbRow['answers']);
			} else {
				$aAnswers = array($aDbRow['beginning_number'], $aDbRow['ending_number']);
				$this->questions[$aDbRow['uid']]->setAnswers($aAnswers);
			}
			//$this->questions[$aDbRow['uid']]->setAllowAdditional($aDbRow['answers_allow_additional'], $aDbRow['answers_text_additional']);
		}
	}
	
	function getQuestionSelectFields() {
		$aSelectFields['uid'] = 'tx_pbsurvey_item.uid';
		$aSelectFields['question'] = 'tx_pbsurvey_item.question';
		$aSelectFields['question_type'] = 'tx_pbsurvey_item.question_type';
		$aSelectFields['rows'] = 'tx_pbsurvey_item.rows';
		$aSelectFields['answers'] = 'tx_pbsurvey_item.answers';
		$aSelectFields['answers_allow_additional'] = 'tx_pbsurvey_item.answers_allow_additional';
		$aSelectFields['answers_text_additional'] = 'tx_pbsurvey_item.answers_text_additional';
		$aSelectFields['beginning_number'] = 'tx_pbsurvey_item.beginning_number';
		$aSelectFields['ending_number'] = 'tx_pbsurvey_item.ending_number';
		$sSelectFields = implode(', ', $aSelectFields);
		return $sSelectFields;
	}
	
	function getQuestionFromTable() {
		$aFromTable['table'] = 'tx_pbsurvey_item';
		$sFromTable = implode(' ', $aFromTable);
		return $sFromTable;
	}
	
	function getQuestionWhereClause() {
		$aWhereClause['1'] = '1=1';
		$aWhereClause['pid'] = 'tx_pbsurvey_item.pid=' . $this->iSurveyPid;
		$aWhereClause['deleted'] = 'tx_pbsurvey_item.deleted=0'; 
		$aWhereClause['hidden'] = 'tx_pbsurvey_item.hidden=0';
		$aWhereClause['question_type'] = 'tx_pbsurvey_item.question_type IN (1, 23, 2, 3, 4, 5, 6, 8, 9, 10)';
		$sWhereClause = implode(' AND ', $aWhereClause);
		return $sWhereClause;
	}
	
	function getOrderBy() {
		$sOrderBy = 'tx_pbsurvey_item.sorting';
		return $sOrderBy;
	}

	function setResults() {
		$sSelectFields = $this->getResultsSelectFields();
		$sFromTable = $this->getResultsFromTable();
		$sWhereClause = $this->getResultsWhereClause();
		$sGroupBy = $this->getResultsGroupBy();
		$sOrderBy = $this->getOrderBy();
		$dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($sSelectFields, $sFromTable, $sWhereClause, $sGroupBy, $sOrderBy);
		while($aDbRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes))	{
			if (intval($aDbRow['answer']) != 0) {
				if(count($this->questions[$aDbRow['uid']]->aAnswers) > 0) {
					$this->questions[$aDbRow['uid']]->aAnswers[$aDbRow['answer']]->setAmount($aDbRow['number']);
				} else {
					$this->questions[$aDbRow['uid']]->aRows[$aDbRow['row']]->aAnswers[$aDbRow['answer']]->setAmount($aDbRow['number']);
					$this->questions[$aDbRow['uid']]->aRows[$aDbRow['row']]->addToTotal($aDbRow['number']);
				}
			} else {
				//$this->questions[$aDbRow['uid']]->aAnswers['-1']->setAmount($aDbRow['number']);
			}
			$this->questions[$aDbRow['uid']]->addToTotal($aDbRow['number']);
		}
		
	}
	
	function getResultsSelectFields() {
		$aSelectFields['uid'] = 'tx_pbsurvey_answers.question AS uid';
		$aSelectFields['row'] = 'tx_pbsurvey_answers.row AS row';
		$aSelectFields['col'] = 'tx_pbsurvey_answers.col AS col';
		$aSelectFields['answer'] = 'tx_pbsurvey_answers.answer AS answer';
		$aSelectFields['count'] = 'COUNT(*) AS number';
		$sSelectFields = implode(', ', $aSelectFields);
		return $sSelectFields;
	}
	
	function getResultsFromTable() {
		$aFromTable['table'] = 'tx_pbsurvey_answers';
		$aFromTable['join_results'] = 'JOIN tx_pbsurvey_results ON tx_pbsurvey_answers.result=tx_pbsurvey_results.uid';
		$aFromTable['join_item'] = 'JOIN tx_pbsurvey_item ON tx_pbsurvey_answers.question=tx_pbsurvey_item.uid';
		$sFromTable = implode(' ', $aFromTable);
		return $sFromTable;
	}
	
	function getResultsWhereClause() {
		$aWhereClause['1'] = '1=1';
		$aWhereClause['pid'] = 'tx_pbsurvey_answers.pid=' . $this->iSurveyPid;
		$aWhereClause['answers_hidden'] = 'tx_pbsurvey_answers.hidden=0';
		$aWhereClause['results_finished'] = 'tx_pbsurvey_results.finished=1'; 
		$aWhereClause['results_deleted'] = 'tx_pbsurvey_results.deleted=0';
		$aWhereClause['results_hidden'] = 'tx_pbsurvey_results.hidden=0';
		$aWhereClause['question_type'] = 'tx_pbsurvey_item.question_type IN (1, 23, 2, 3, 4, 5, 6, 8, 9)';
		$sWhereClause = implode(' AND ', $aWhereClause);
		return $sWhereClause;
	}
	
	function getResultsGroupBy() {
		$sGroupBy = 'uid,row,col,answer';
		return $sGroupBy;
	}
	
	function calculatePercentages() {
		foreach($this->questions as $iQuestionKey => $aItem) {
			if(!in_array($aItem->iQuestionType, array(6, 8, 9))) {
				$iTotal = $aItem->iTotal;
				foreach($aItem->aAnswers as $iAnswerKey => $aAnswer) {
					$iAnswerTotal = $aAnswer->iAmount;
					$iAnswerPercentage = $iTotal !=0 ? ($iAnswerTotal / $iTotal) * 100 : 0;
					$this->questions[$iQuestionKey]->aAnswers[$iAnswerKey]->setPercentage($iAnswerPercentage);
				}
			} else {
				foreach($aItem->aRows as $iRowKey => $aRow) {
					$iTotal = $aRow->iTotal;
					foreach($aRow->aAnswers as $iAnswerKey => $aAnswer) {
						$iAnswerTotal = $aAnswer->iAmount;
						$iAnswerPercentage = $iTotal !=0 ? ($iAnswerTotal / $iTotal) * 100 : 0;
						$this->questions[$iQuestionKey]->aRows[$iRowKey]->aAnswers[$iAnswerKey]->setPercentage($iAnswerPercentage);
					}
				}
			}
		}
	}
	
	function done() {
		$this->setQuestions();
		$this->setResults();
		$this->calculatePercentages();
		return $this->questions;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_surveystats/lib/class.tx_ncpbsurveyfesimplestats_questions.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_surveystats/lib/class.tx_ncpbsurveyfesimplestats_questions.php']);
}
?>