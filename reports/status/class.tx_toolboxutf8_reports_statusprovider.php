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
 * class.tx_toolboxutf8_reports_statusprovider.php
 *
 * Base class for utf8 status provider classes  
 *
 * @version 	$Id: class.tx_toolboxutf8_reports_statusprovider.php 7571 2010-07-01 18:50:06Z sebastian.fuchs $
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
class tx_toolboxutf8_reports_StatusProvider implements tx_reports_StatusProvider {

	/**
	 * Constructor for class tx_toolboxutf8_reports_status_MysqlUtf8Status
	 *
	 * @param	tx_reports_Module	Back-reference to the calling reports module
	 */
	public function __construct() {
		$this->getTypo3Vars();
		$GLOBALS['LANG']->includeLLFile('EXT:toolbox_utf8/locallang.xml');
	}
	
	
	
	public function getHeader($id='noid',$title='',$content='',$header_type=1,$header_class='section-header') {
		if (isset($GLOBALS['BE_USER']->uc['reports']['states'][$id]) && $GLOBALS['BE_USER']->uc['reports']['states'][$id]) {
			$collapsedStyle = 'style="display:none"';
			$collapsedClass = 'collapsed';
		} else {
			$collapsedStyle = '';
			$collapsedClass = 'expanded';
		}
		return '<div style="clear:both;"><h' . (string)$header_type . ' id="' . $id . '" class="' . $header_class . ' ' . $collapsedClass . '">' . $title . '</h' . (string)$header_type . '>
		<div ' . $collapsedStyle . ' class="indentlevel-'.(string)$header_type.'">'.$content.'</div></div>';

	}

	/**
	 * Read TYPO3 variables (mostly TYPO3_CONF_VARS) and sets them internally
	 */
	public function getTypo3Vars() {
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

	}
	
	public function getStatus() {
		
	}



}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_tx_toolboxutf8_reports_status_mysqlutf8status.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_toolboxutf8_reports_status_mysqlutf8status.php']);
}

?>