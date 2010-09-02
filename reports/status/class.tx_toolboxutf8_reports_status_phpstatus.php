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
 * class.tx_toolboxutf8_reports_status_phpstatus.php
 *
 * UTF8 status report about some PHP varaibles  
 *
 * @version 	$Id: $
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
class tx_toolboxutf8_reports_status_PhpStatus extends tx_toolboxutf8_reports_StatusProvider {

	/**
	 * Check some php constants and function return values
	 *
	 * @return	array	List of statuses
	 * @see typo3/sysext/reports/interfaces/tx_reports_StatusProvider::getStatus()
	 */
	public function getStatus() {
		$statuses = array();
		//get the db vars
		$this->php_vars = array(
			'PHP_INI default_charset' => ini_get('default_charset'),
			'mysql_client_encoding(TYPO3_DB)' => mysql_client_encoding($GLOBALS['TYPO3_DB']->link),
			'Locale settings [setlocale(LC_ALL,0)]' => setlocale(LC_ALL,0)
		);
		
		return $this->php_vars;
	}


	/**
	 * Returns the html representation of the class
	 * @return	string $content HTML representing this status
	 */
	public function renderStatus(array $classes) {
		foreach($this->php_vars as $k=>$v) {
			$message = t3lib_div::makeInstance(
	    			't3lib_FlashMessage',
					$v,
					$k,
					( (!preg_match('/utf8/i',$v) )?tx_reports_reports_status_Status::WARNING : tx_reports_reports_status_Status::OK)
			);
			$content .= $message->render();
		}
		return $this->getHeader('utf8phpvars',$GLOBALS['LANG']->getLL('utf8status_php_vars'),$content,2);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_tx_toolboxutf8_reports_status_phpstatus.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_toolboxutf8_reports_status_phpstatus.php']);
}

?>