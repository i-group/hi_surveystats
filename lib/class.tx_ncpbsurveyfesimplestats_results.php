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

/**
 * DB Class
 *
 * @author Patrick Broens <patrick@netcreators.com>
 * @package TYPO3
 * @subpackage nc_pbsurveyfesimplestats
 */
class tx_ncpbsurveyfesimplestats_results {
	/**
	 * Page ID where the survey items and results are located
	 */
	var $iSurveyPid = NULL;
	
	var $iResultCount = 0;

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
	function tx_ncpbsurveyfesimplestats_results()	{
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

	function setResults() {
		$sSelectFields = $this->getResultsSelectFields();
		$sFromTable = $this->getResultsFromTable();
		$sWhereClause = $this->getResultsWhereClause();
		$sGroupBy = $this->getResultsGroupBy();
		$sOrderBy = $this->getOrderBy();
		$dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($sSelectFields, $sFromTable, $sWhereClause, $sGroupBy, $sOrderBy);
		$aDbRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes);
		$iResultCount = $aDbRow['number'];
		return $iResultCount;
	}
	
	function getResultsSelectFields() {
		$aSelectFields['count'] = 'COUNT(*) AS number';
		$sSelectFields = implode(', ', $aSelectFields);
		return $sSelectFields;
	}
	
	function getResultsFromTable() {
		$aFromTable['table'] = 'tx_pbsurvey_results';
		$sFromTable = implode(' ', $aFromTable);
		return $sFromTable;
	}
	
	function getResultsWhereClause() {
		$aWhereClause['1'] = '1=1';
		$aWhereClause['pid'] = 'tx_pbsurvey_results.pid=' . $this->iSurveyPid;
		$aWhereClause['results_finished'] = 'tx_pbsurvey_results.finished=1'; 
		$aWhereClause['results_deleted'] = 'tx_pbsurvey_results.deleted=0';
		$aWhereClause['results_hidden'] = 'tx_pbsurvey_results.hidden=0';
		$sWhereClause = implode(' AND ', $aWhereClause);
		return $sWhereClause;
	}
	
	function getResultsGroupBy() {
		$sGroupBy = '';
		return $sGroupBy;
	}
	
	function getOrderBy() {
		$sOrderBy = '';
		return $sOrderBy;
	}
	
	function done() {
		$this->iResultCount = $this->setResults();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_surveystats/lib/class.tx_ncpbsurveyfesimplestats_results.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_surveystats/lib/class.tx_ncpbsurveyfesimplestats_results.php']);
}
?>