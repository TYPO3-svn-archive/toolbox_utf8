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
 * class.tx_toolboxutf8_reports_status_typo3status.php
 *
 * UTF8 status report about TYPO3 variables  
 *
 * @version 	$Id: class.tx_toolboxutf8_reports_status_typo3status.php 7571 2010-07-01 18:50:06Z sebastian.fuchs $
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public License, version 2 
 *
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @author 		Sebastian Fuchs <sebastian.fuchs@mensemedia.net>
 * @package 	TYPO3
 * @subpackage 	toolbox_utf8
 */

/**
 * Performs some checks about the TYPO3 utf8 status and the corresponding mysql connection variables
 *
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */
class tx_toolboxutf8_reports_status_Typo3Status extends tx_toolboxutf8_reports_StatusProvider {

	/**
	 * Check the system configuration, connection, database variables
	 *
	 * @return	array	List of statuses
	 * @see typo3/sysext/reports/interfaces/tx_reports_StatusProvider::getStatus()
	 */
	public function getStatus() {
		$statuses = array();
		//get the db vars
		$this->db_vars = tx_toolboxutf8_Util::getDatabaseVariables(true);
		$this->dbvarsUtf8 = true;

		//check mandatory fields for a running utf8 system
		$check_fields = array('character_set_client','character_set_connection','character_set_results');
		foreach($check_fields as $field) {
			if(!preg_match('/utf8/i',$this->db_vars[$field])) {
				$this->dbvarsUtf8 = false;
				break;
			}
		}

		$statuses['forceCharset'] = $this->getForceCharsetStatus();


		return $statuses;
	}


	/**
	 * Check some variables and determine/set the internal status/messages/servity
	 */
	protected function getForceCharsetStatus() {
		$this->message  = '';
		$this->severity = tx_reports_reports_status_Status::OK;

		if ($this->forceCharsetUtf8 && !$this->setDBinitUtf8 ) {
			if(!$this->dbvarsUtf8) {
				$this->message  = $GLOBALS['LANG']->getLL('utf8status_typo3.msg.forcecharsetutf8_success');
				$this->severity = tx_reports_reports_status_Status::ERROR;
			}
		}
		if (!$this->forceCharsetUtf8 && $this->setDBinitUtf8 ) {
			$this->message  = $GLOBALS['LANG']->getLL('utf8status_typo3.msg.setdbinit_success');
			$this->severity = tx_reports_reports_status_Status::ERROR;
		}
		if  (!$this->forceCharsetUtf8 && !$this->setDBinitUtf8 ) {
			$this->message  = $GLOBALS['LANG']->getLL('utf8status_typo3.msg.notutf8set');
			$this->severity = tx_reports_reports_status_Status::NOTICE;
		}


		//determine severity for each variable
		$this->severity_setDbInit = tx_reports_reports_status_Status::OK;
		$this->severity_forceCharsetUtf8 = tx_reports_reports_status_Status::OK;
		if($this->severity==tx_reports_reports_status_Status::ERROR) {
			$this->severity_forceCharsetUtf8 = tx_reports_reports_status_Status::ERROR;
			$this->severity_setDbInit = tx_reports_reports_status_Status::ERROR;
		} else {
			if(!$this->forceCharsetUtf8 && $this->dbvarsUtf8) {
				$this->severity_forceCharsetUtf8 = tx_reports_reports_status_Status::NOTICE;
			}
				
			if(!$this->setDBinitUtf8) {
				if(!$this->dbvarsUtf8) {
					$this->severity_setDbInit = tx_reports_reports_status_Status::NOTICE;
				} else if($this->forceCharsetUtf8 && $this->dbvarsUtf8) {
					$this->setDBinitUtf8 = true;
				}
			}
		}

	}


	/**
	 * Returns the html representation of the class
	 * @return	string $content HTML representing this status
	 */
	public function renderStatus(array $classes) {
		//output forcecharset
		$message = t3lib_div::makeInstance(
    			't3lib_FlashMessage',
				($this->forceCharsetUtf8?'':'Change it...'),
				'forceCharset: '.$this->t3_vars['forceCharset'],
				$this->severity_forceCharsetUtf8
		);
		$content .= $message->render();
		//output setDBinit
		$message = t3lib_div::makeInstance(
    			't3lib_FlashMessage',
				($this->setDBinitUtf8?'':'Change it...'),
				'setDBinit: '.nl2br($this->t3_vars['setDBinit']),
				$this->severity_setDbInit
		);
		$content .= $message->render();
		if($this->severity!=tx_reports_reports_status_Status::OK) {
			//output advise
			$message = t3lib_div::makeInstance(
    			't3lib_FlashMessage',
				$this->message,
				'Advise',
				$this->severity
			);
			$content .= $message->render();
		}
			
		return $this->getHeader('utf8typovars',$GLOBALS['LANG']->getLL('utf8status_typo3_vars'),$content,2);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_tx_toolboxutf8_reports_status_mysqlutf8status.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_toolboxutf8_reports_status_mysqlutf8status.php']);
}

?>