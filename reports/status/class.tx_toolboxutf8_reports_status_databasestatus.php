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
 * class.tx_toolboxutf8_reports_status_databasestatus.php
 *
 * UTF8 status report about the TYPO3 table collations
 *
 * @version 	$Id: class.tx_toolboxutf8_reports_status_databasestatus.php 7649 2010-07-08 16:00:50Z sebastian.fuchs $
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 *
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @author 		Sebastian Fuchs <sebastian.fuchs@mensemedia.net>
 * @package 	TYPO3
 * @subpackage 	toolbox_utf8
 */


/**
 * Display TYPO3 table charsets
 *
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */
class tx_toolboxutf8_reports_status_DatabaseStatus extends tx_toolboxutf8_reports_StatusProvider {

	private $table_vars = array();

		/**
	 * Check some php cpnstants and function retrun values
	 *
	 * @return	array	List of statuses
	 * @see typo3/sysext/reports/interfaces/tx_reports_StatusProvider::getStatus()
	 */
	public function getStatus() {
		/*
		 Get the table informations
		 */
		$this->table_vars = tx_toolboxutf8_Util::getTables();
		
		$statuses = array();
		foreach($this->table_vars as $table => &$fields) {
				$fields['charset_status'] = $fields['collation_status'] = tx_reports_reports_status_Status::ERROR;
				//result logic
				if($this->forceCharsetUtf8) {
					if(preg_match('/utf8/i',$fields['Charset'])) {
						$fields['charset_status'] = tx_reports_reports_status_Status::OK;
					}
					if(preg_match('/utf8/i',$fields['Collation'])) {
						$fields['collation_status'] = tx_reports_reports_status_Status::OK;
					}
				} else if(!$this->forceCharsetUtf8) {
					$fields['collation_status'] = tx_reports_reports_status_Status::NOTICE;
					$fields['charset_status'] = tx_reports_reports_status_Status::NOTICE;
				} else {
					$this->error[$fields['Collation']][$table] = 1;
					$this->error[$fields['Charset']][$table] = 1;
				}
		}
		//TODO: setup return values
		return $statuses;
	}
	
	
	/**
	 * Display a list of all tables with their charsets
	 *
	 * @return	string $content HTML representing this status
	 */
	public function renderStatus(array $classes) {

		/*
		 Display information
		 */
		$error = array();
		if (count($this->table_vars) == 0) {
			$content .= '<p>' . $GLOBALS['LANG']->getLL('no_search_paths') . '</p>';
		} else {
			$i=0;
			$t_content = '<table cellspacing="1" cellpadding="2" border="0" class="tx_toolboxutf8_reportlist">';
			$t_content .= '<tbody>';
			$t_content .= '<tr class="bgColor2">';
			$t_content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('toolboxutf8_table_typo3') . '</td>';
			$t_content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('toolboxutf8_table_collations') . '</td>';
			$t_content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('toolboxutf8_table_charsets') . '</td>';
			$t_content .= '</tr>';
			foreach ($this->table_vars as $table => $fields) {
				$t_content .= '<tr class="bgColor3-20">';
				$t_content .= '<td class="cell" >' . $table . '</td>';
				$t_content .= '<td class="cell typo3-message message-' . $classes[$fields['collation_status']] . '">' .$fields['Collation'] . '</td>';
				$t_content .= '<td class="cell typo3-message message-' . $classes[$fields['charset_status']] . '">' .$fields['Charset'] . '</td>';
				$t_content .= '</tr>';
				//TODO: display table fields
			}
			$t_content .= '</tbody>';
			$t_content .= '</table>';
			//$content .= $this->getHeader('utf8table'.$i++,$sql,$t_content,3);
			$content  .= $t_content;
		}
		if(count($this->error)||count($this->warning)) {
			$err_msg = $GLOBALS['LANG']->getLL('toolboxutf8_tablesCollationsWarning');
			$err_reco = $GLOBALS['LANG']->getLL('toolboxutf8_tablesCollationsRecommendation');
			if(count($this->error)) {
				$severity = tx_reports_reports_status_Status::ERROR;
			} else {
				$severity = tx_reports_reports_status_Status::OK;
			}

			$message = t3lib_div::makeInstance(
    			't3lib_FlashMessage',
			$err_msg,
				'Charsets: '.implode(',',array_keys($this->error)),
			$severity
			);
			$content = $message->render().$content;
		}

		$content = $this->getHeader('utf8tables',$GLOBALS['LANG']->getLL('utf8status_table_vars'),$content,2);
		return $content;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_tx_toolboxutf8_reports_status_mysqlutf8status.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_toolboxutf8_reports_status_mysqlutf8status.php']);
}

?>