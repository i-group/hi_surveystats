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
class tx_ncpbsurveyfesimplestats_answer {
	/**
	 * Page ID where the survey items and results are located
	 */
	var $sAnswer = '';
	
	var $iAmount = 0;
	
	var $iPercentage = 0;

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
	function tx_ncpbsurveyfesimplestats_answer()	{
		$this->__construct();
	}


	/**
	 * Initialization of class
	 *
	 * @return	void
	 */
	function __construct() {
		
	}
	
	function setAnswer($sAnswer) {
		$this->sAnswer = (string) $sAnswer;
	}
	
	function setAmount($iAmount) {
		$this->iAmount = (int) $iAmount;
	}
	
	function setPercentage($iPercentage) {
		$this->iPercentage = round($iPercentage, 1);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_surveystats/lib/class.tx_ncpbsurveyfesimplestats_answer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hi_surveystats/lib/class.tx_ncpbsurveyfesimplestats_answer.php']);
}
?>