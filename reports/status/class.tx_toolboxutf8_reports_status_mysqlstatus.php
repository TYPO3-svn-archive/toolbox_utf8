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
 * class.tx_toolboxutf8_reports_status_mysqlstatus.php
 *
 * UTF8 status report about the TYPO3 table collations  
 *
 * @version 	$Id: class.tx_toolboxutf8_reports_status_mysqlstatus.php 7571 2010-07-01 18:50:06Z sebastian.fuchs $
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
 * * display mysql server variables
 * * display mysql server version informations
 * * display available collations
 * * display availbale charsets 
 *
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */
class tx_toolboxutf8_reports_status_MysqlStatus extends tx_toolboxutf8_reports_StatusProvider {
	
	
	/**
	 * This method assembles a list of all defined search paths
	 *
	 * @return	string $content HTML representing this status
	 */
	public function renderStatus(array $classes) {

		/*
		 Collect the informations from the api
		 */
		$server_vars = tx_toolboxutf8_Util::getDatabaseVersion();
		$db_vars = tx_toolboxutf8_Util::getDatabaseVariables();
		$collations = tx_toolboxutf8_Util::getCollations('utf8',true);
		$charsets = tx_toolboxutf8_Util::getDatabaseCharsets();

		/*
		 check server variables for 'utf8' apperance & render result
		 */
		$error = $warning = array();

		//just display a warning if these vars are not set to 'utf8'
		//only display an error if character_set_client or character_set_connection or character_set_results
		// http://dev.mysql.com/doc/refman/5.0/en/server-system-variables.html#sysvar_character_set_client
		// http://dev.mysql.com/doc/refman/5.0/en/charset-connection.html
		$exclude_warning = array('character_set_database','collation_database','character_set_server','collation_server');
		$exclude_notice = array('character_set_server','collation_server','character_set_filesystem','character_sets_dir');
		$exclude_error = array('character_set_filesystem','character_sets_dir');


		if (count($db_vars) == 0) {
			$content .= '<p>' . $GLOBALS['LANG']->getLL('no_search_paths') . '</p>';
		} else {

			$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_toolboxutf8_reportlist">';
			$content .= '<tbody>';

			// display mysql server runtime variables
			foreach ($db_vars as $sql => $vars) {
				$content .= '<tr class="bgColor2">';
				$content .= '<td class="cell"  colspan="2">' . $sql . '</td>';
				$content .= '</tr>';
				foreach($vars as $key=>$val) {
					$content .= '<tr class="bgColor3-20">';
					$content .= '<td class="cell" >' . $key . '</td>';
					$class = 'message-error';
					//check if utf8 is set?
					if(preg_match('/utf8/i',$val)) {
						$class = 'message-ok';
					} else {
						if( in_array($key,$exclude_warning) ) {
							$class = 'message-warning';
							$warning[$val][$key]=1;
						} else if( in_array($key,$exclude_notice) ) {
							$class = 'message-notice';
						} else if( in_array($key,$exclude_error) ) {

						} else {
							$error[$val][$key]=1;
						}
					}
					$class = 'typo3-message '.$class;
					$content .= '<td class="cell ' . $class . '">' .$val . '</td>';
					$content .= '</tr>';
				}
			}
			// display the server version variables
			foreach ($server_vars as $sql => $vars) {
				$content .= '<tr class="bgColor2">';
				$content .= '<td class="cell"  colspan="2">' . $sql . '</td>';
				$content .= '</tr>';
				foreach($vars as $key=>$val) {

					$content .= '<tr class="bgColor3-20">';
					$content .= '<td class="cell" >' . $key . '</td>';
					$class = 'typo3-message message-notice';
					$content .= '<td class="cell ' . $class . '">' .$val . '</td>';
					$content .= '</tr>';
				}
			}
			$content .= '</tbody>';
			$content .= '</table>';
			$content = $this->getHeader('utf8dbvars',$GLOBALS['LANG']->getLL('utf8status_runtime_vars'),$content,3);
			
			//display available charstes
			$tmp_charsets = '<table cellspacing="1" cellpadding="2" border="0" class="tx_toolboxutf8_reportlist">';
			$tmp_charsets .= '<tbody>';
			foreach ($charsets as $sql => $vars) {
				$tmp_charsets .= '<tr class="bgColor2">';
				$tmp_charsets .= '<td class="cell"  colspan="2">' . $sql . '</td>';
				$tmp_charsets .= '</tr>';
				foreach($vars as $key=>$val) {
					$tmp_charsets .= '<tr class="bgColor3-20">';
					$tmp_charsets .= '<td class="cell" >' . $key . '</td>';
					$class = 'typo3-message message-notice';
					$tmp_charsets .= '<td class="cell ' . $class . '">' .$val . '</td>';
					$tmp_charsets .= '</tr>';
				}
			}
			$tmp_charsets .= '</tbody>';
			$tmp_charsets .= '</table>';
			$content .= $this->getHeader('utf8dbcharsets',$GLOBALS['LANG']->getLL('utf8status_charset_vars'),$tmp_charsets,3);
			
			//display available collations
			$tmp_collation = '<table cellspacing="1" cellpadding="2" border="0" class="tx_toolboxutf8_reportlist">';
			$tmp_collation .= '<tbody>';
			foreach ($collations as $sql => $vars) {
				$tmp_collation .= '<tr class="bgColor2">';
				$tmp_collation .= '<td class="cell"  colspan="2">' . $sql . '</td>';
				$tmp_collation .= '</tr>';
				foreach($vars as $key=>$val) {
					$tmp_collation .= '<tr class="bgColor3-20">';
					$tmp_collation .= '<td class="cell" >' . $key . '</td>';
					$class = 'typo3-message message-notice';
					$tmp_collation .= '<td class="cell ' . $class . '">' .$val . '</td>';
					$tmp_collation .= '</tr>';
				}
			}
			$tmp_collation .= '</tbody>';
			$tmp_collation .= '</table>';
			$content .= $this->getHeader('utf8dbcollations',$GLOBALS['LANG']->getLL('utf8status_collation_vars'),$tmp_collation,3);

		}
		//check for errors or warnings
		if(count($error)||count($warning)) {
			if(count($error)) {
				$err_msg = $GLOBALS['LANG']->getLL('utf8status_mysqlVariablesError');
				$severity = tx_reports_reports_status_Status::ERROR;
			} else {
				$err_msg = $GLOBALS['LANG']->getLL('utf8status_mysqlVariablesWarning');
				$severity = tx_reports_reports_status_Status::WARNING;
			}
			$message = t3lib_div::makeInstance(
    			't3lib_FlashMessage',
				$err_msg,
				'Variables: '.implode(',',array_keys($error)),
				$severity
			);
			$content = $message->render().$content;
		}

		$content = $this->getHeader('utfvars',$GLOBALS['LANG']->getLL('utf8status_database_vars'),$content,2);
		return $content;

	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_tx_toolboxutf8_reports_status_mysqlutf8status.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_toolboxutf8_reports_status_mysqlutf8status.php']);
}

?>