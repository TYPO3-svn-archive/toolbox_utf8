<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 BQMP Gruppe GmbH (typo3@bqmp.de)
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * class.tx_toolboxutf8_reports_status_mysqlutf8status.php
 *
 * Implementation of a small status entry for the regular reports task  
 *
 * @version 	$Id: class.tx_toolboxutf8_reports_status_mysqlutf8status.php 7571 2010-07-01 18:50:06Z sebastian.fuchs $
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public License, version 2 
 *
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @author 		Sebastian Fuchs <sebastian.fuchs@mensemedia.net>
 * @package 	TYPO3
 * @subpackage 	toolbox_utf8
 */

/**
 * Performs some checks about the mysql connection and database configuration regarding utf8 issues
 *
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */
class tx_toolboxutf8_reports_status_MysqlUtf8Status implements tx_reports_StatusProvider {

	/**
	 * Constructor for class tx_toolboxutf8_reports_status_MysqlUtf8Status
	 *
	 * @param	tx_reports_Module	Back-reference to the calling reports module
	 */
	public function __construct() {
		$GLOBALS['LANG']->includeLLFile('EXT:toolbox_utf8/reports/locallang.xml');
	}

	/**
	 * Check the system configuration, connection, database variables
	 *
	 * @return	array	List of statuses
	 * @see typo3/sysext/reports/interfaces/tx_reports_StatusProvider::getStatus()
	 */
	public function getStatus() {
		$statuses = array();
		/*
		 get typo3 vars
		 */
		$this->t3_vars = array(
			'forceCharset' => $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'],
			'setDBinit' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['setDBinit'],
			'multiplyDBfieldSize' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['multiplyDBfieldSize'],
			't3lib_cs_convMethod' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_convMethod'],
			't3lib_cs_utils' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_utils'],
		);
		$this->forceCharsetUtf8 = ($this->t3_vars['forceCharset']!='' && preg_match('/utf-8/i',$this->t3_vars['forceCharset']))?true:false;
		$this->setDBinitUtf8 = preg_match('/utf8/i',$this->t3_vars['setDBinit'])?true:false;


		$statuses['forceCharset'] = $this->getForceCharsetStatus();


		return $statuses;
	}



	/**
	 * Simply gets the current TYPO3 version.
	 *
	 * @return	tx_reports_reports_status_Status
	 */
	protected function getForceCharsetStatus() {
		$value    = $this->t3_vars['forceCharset'].'<br />'.$this->t3_vars['setDBinit'];
		$message  = '';
		$severity = tx_reports_reports_status_Status::OK;
		if ($this->forceCharsetUtf8 && !$this->setDBinitUtf8 ) {
			$value    = $this->t3_vars['forceCharset'].'<br />'.$this->t3_vars['setDBinit'];
			$message  = 'You have TYPO3 forced to use utf8, but did not set setDBinit to use utf8 connection.';
			$severity = tx_reports_reports_status_Status::ERROR;
		}
		if (!$this->forceCharsetUtf8 && $this->setDBinitUtf8 ) {
			$value    = $this->t3_vars['forceCharset'].'<br />'.$this->t3_vars['setDBinit'];
			$message  = 'You have  set setDBinit to use utf8 connections, but did not forced TYPO3 to use utf8.';
			$severity = tx_reports_reports_status_Status::ERROR;
		}
		if  (!$this->forceCharsetUtf8 && !$this->setDBinitUtf8 ) {
			$value    = $this->t3_vars['forceCharset'].'<br />'.$this->t3_vars['setDBinit'];
			$message  = 'You have no utf8 configuration set.';
			$severity = tx_reports_reports_status_Status::NOTICE;
		}
		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			'forceCharset'.'<br />'.'setDBinit', $value, $message, $severity
		);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_tx_toolboxutf8_reports_status_mysqlutf8status.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_toolboxutf8_reports_status_mysqlutf8status.php']);
}

?>