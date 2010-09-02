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
 * class.tx_toolboxutf8_util.php
 * 
 * Utility functions whith are used by the converter (cli and be modul) as well as the reports module
 *
 * @version 	$Id: class.tx_toolboxutf8_util.php 7590 2010-07-02 16:51:58Z sebastian.fuchs $
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 *
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @author 		Sebastian Fuchs <sebastian.fuchs@mensemedia.net>
 * @package 	TYPO3
 * @subpackage 	toolbox_utf8
 */

/**
 * This class contains different utility functions. 
 *
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */
class tx_toolboxutf8_Util {
	
	/**
	 * @var cache for result of db->admin_get_charset
	 */
	static $cached_charsets = null;
	

	/**
	 * Fetches database server variables
	 * @param boolean $return_info Return array with SQL statments or plain key=>value array
	 * @return array Database vars
	 */
	public static function getDatabaseVersion($return_info=false) {
		$queries = array(
			//"select 'version' as Variable_name ,version() as Value",
			//'select "character_set_database" as Variable_name ,@@character_set_database  as Value',
			//'STATUS;'
			'SHOW VARIABLES LIKE "%version%"',
			//'SHOW VARIABLES LIKE "system_charset_info"'
		);
		$db_vars = array();
		foreach ($queries as $query) {
			$variables = array();
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			//TODO: WTF:  
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$variables[$row['Variable_name']] = $row['Value'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			
			if($return_info) {
				$db_vars = $variables;
			} else {
				$db_vars[$query] = $variables;
			}
		}
		return $db_vars;
	}
	
	/**
	 * Determine collations supported by the database
	 * @param string $charset
	 */
	public static function getCollations($charset='utf8', $displaySQL = TRUE, $negate = FALSE) {
		$query = 'SHOW COLLATION WHERE Charset' . ($negate ? '!' : '') . '="' . $charset . '"';
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$collations = array();
		$i = 1;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $res ) ) {
			$collations[$i] = $row['Collation'];
			$i++;
		}
		return $displaySQL ? array($query => $collations) : $collations;
	}
	
	
	/**
	 * Fetches database server variables
	 * @param boolean $returnPlain Return array with SQL statments or plain key=>value array
	 * @return array Database vars 
	 */
	public static function getDatabaseVariables($returnPlain=false) {
		$queryType = "SHOW VARIABLES LIKE '";
		$queries = array(
			"SHOW VARIABLES LIKE 'character_set%'",
			"SHOW VARIABLES LIKE 'collation%'",
		);
		$db_vars = array();
		foreach ($queries as $query) {
			$variables = array();
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				
				$variables[$row['Variable_name']] = $row['Value'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);

			if($returnPlain) {
				$db_vars = array_merge($db_vars,$variables);
			} else {
				$db_vars[$query] = $variables;
			}
		}
		return $db_vars;
	}
	
	/**
	 * Get all tables from datatbase
	 */
	public static function getTables() {
		$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW TABLE STATUS');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$query = 'SHOW CREATE TABLE ' . $row['Name'];
			$res_create = $GLOBALS['TYPO3_DB']->admin_query($query);
			$create = $GLOBALS['TYPO3_DB']->sql_fetch_row($res_create);
			$createStatement = $create[1];
			$matches = array();
			preg_match('/DEFAULT CHARSET=(\w+)/', $createStatement, $matches);
			$data['charset'] = $matches[1];
			
//			$fieldMatches = array();
//			preg_match_all('/`(\w+)`.*character set (\w+)/', $createStatement, $fieldMatches);
//			$data['field_charset'] = array();
//			foreach ($fieldMatches[0] as $key => $fieldMatch) {
//				$data['field_charset'][$fieldMatches[1][$key]] = $fieldMatches[2][$key]; 
//			}
			$table_vars[$row['Name']] = array(
					'Charset' => $data['charset'],
					'Engine' => $row['Engine'],
					'Collation'=> $row['Collation'],
					'Rows'=> $row['Rows'],
			);
		}
		return $table_vars;
	}
	
	/**
	 * Get all accepted charsets from datatbase (SHOW CHARACTER SET)
	 */
	public static function getDatabaseCharsets() {
		if(tx_toolboxutf8_Util::$cached_charsets == null) {
			tx_toolboxutf8_Util::$cached_charsets = $GLOBALS['TYPO3_DB']->admin_get_charsets();
		}  
		return tx_toolboxutf8_Util::$cached_charsets;
	}
	
	private function setCharsets($charsets){
		//self::
	}
	
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/classes/class.tx_toolboxutf8_util.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/classes/class.tx_toolboxutf8_util.php']);
}
?>