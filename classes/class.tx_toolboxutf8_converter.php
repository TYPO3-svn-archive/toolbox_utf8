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
 * class.tx_toolboxutf8_converter.php
 *
 * Provides the buissines logic for the info/alter/convert tasks
 * Used from the CLI script as well as the BE-module
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
 * Include the utility class
 */
require_once (t3lib_extMgm::extPath('toolbox_utf8') . 'classes/class.tx_toolboxutf8_util.php');

/**
 * Provides the buissines logic for the info/alter/convert tasks
 * 
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */
class tx_toolboxutf8_Converter {
	
	
	/**
	 * Reference to the used output interface (CLI or BE modul)
	 *
	 * @var tx_toolboxutf8_ConverterInterface
	 */
	protected $converterInterface;
	
	/**
	 * Constructor
	 *
	 * @param tx_toolboxutf8_ConverterInterface $converterInterface
	 * @return void
	 */
	public function __construct(tx_toolboxutf8_ConverterInterface $converterInterface) {
		$this->converterInterface = $converterInterface;
	}
	
	
	/**
	 * Task alter table
	 *
	 * @return void
	 */
	public function alter() {
		$collations = tx_toolboxutf8_Util::getCollations('utf8', false);
		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		if ($this->converterInterface->getAlterCharsetId() !== NULL) {
			//TODO check in_array
			$collation = $collations[$this->converterInterface->getAlterCharsetId()];
			$this->converterInterface->echoContent('ALTER DATABASE ' . $collation);
			$this->alterDatabase($collation);
			$this->converterInterface->echoContent('ALTER TABLE STRUCTURE ' . $collation);
			$this->alterTableStructure($collation);
		} elseif ($this->converterInterface->getInteractiveModeAllowed()) {
			$this->converterInterface->echoContent('ALTER TABLE STRUCTURE ');
			$this->converterInterface->echoContent($this->listVariablesRecursive($collations));
			$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
			$this->converterInterface->echoContent('Select your collation character set [1-' . count($collations) . ']: ');
			$input = $this->converterInterface->cli_keyboardInput();
			if ($input) {
				if ($this->converterInterface->getForceWriteToDb()) {
					$this->converterInterface->echoContent('You really want to alter all tables in ' . TYPO3_db . 'Selected collation: ' . $collations[$input] . ' [yes|no]: ');
					$accept_input = $this->cli_keyboardInput();
					if ($accept_input && (strtolower($accept_input) == 'yes' || $accept_input == 'y')) {
						//TODO check in_array
						$this->alterTableStructure($collations[$input]);
					}
				} else {
					//TODO check in_array
					$this->alterTableStructure($collations[$input]);
				}
			}
		} else {
			// TODO error msg
		}
		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		$this->converterInterface->echoContent('Altering completed');
	}

	/**
	 * Alter all database tables to use a different collation
	 * @param string $collation
	 * @return void
	 */
	function alterDatabase($collation = 'utf_8_general_ci') {
		$query = 'ALTER DATABASE ' . TYPO3_db . ' DEFAULT CHARACTER SET utf8 COLLATE ' . $collation;
		if ($this->converterInterface->getVerbose()) {
			$this->converterInterface->echoContent($query);
		}
		if ($this->converterInterface->getForceWriteToDb()) {
			if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
				$this->converterInterface->echoContent($GLOBALS['TYPO3_DB']->sql_error());
			}
		}
	}
	
	/**
	 * Alter all database tables to use a different collation
	 * @param string $collation
	 * @return void
	 */
	function alterTableStructure($collation = 'utf_8_general_ci') {

		//generate table with updated database tables
		$tables = tx_toolboxutf8_Util::getTables();
		$counter = 0;

		
		foreach ($tables as $name => $data) {
			
			$alterTableQuery = 'ALTER TABLE ' . TYPO3_db . '.' . $name . ' DEFAULT CHARACTER SET utf8 COLLATE ' . $collation;
			if ($this->converterInterface->getVerbose()) {
				$this->converterInterface->echoContent($alterTableQuery);
			}
			if ($this->converterInterface->getForceWriteToDb()) {
				if (!$GLOBALS['TYPO3_DB']->sql_query($alterTableQuery)) {
					$this->converterInterface->echoContent($GLOBALS['TYPO3_DB']->sql_error());
				}
			}
			
			$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW FULL COLUMNS FROM '.$name.' WHERE Collation != \'\'');
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($row['Default']) {
					$default = ' DEFAULT \''.$row['Default'].'\'';
				} else {
					$default = '';
				}
				if ($row['Null'] == 'NO') {
					$null = ' NOT NULL';
				} else {
					$null = '';
				}
				$query = 'ALTER TABLE '.$name.' CHANGE '.$row['Field'].' '.$row['Field'].' '.$row['Type'].' CHARACTER SET utf8 COLLATE '.$collation.$default.$null;
				if ($this->converterInterface->getVerboseDetails()) {
					$this->converterInterface->echoContent($query);
				}
				if ($this->converterInterface->getForceWriteToDb()) {
					if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
						if (!$this->converterInterface->getVerboseDetails()) {
							$this->converterInterface->echoContent($query);
						}
						$this->converterInterface->echoContent($GLOBALS['TYPO3_DB']->sql_error());
					}
				} 

			}
			
			$counter++;
			$this->converterInterface->updateProgressBar($counter, sizeof($tables));
		}
		return;
	}


	/**
	 * Task info:
	 * display system,database,table information
	 */
	function info() {

		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		$this->converterInterface->echoContent('APPLICATION INFORMATION ');
		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		$this->showTypoVariables();
		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		$this->converterInterface->echoContent('');
		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		$this->converterInterface->echoContent('DATABASE INFORMATION ');
		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		$this->showDatabaseVariables();
		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		$this->converterInterface->echoContent('');
		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		$this->converterInterface->echoContent('TABLE INFORMATION ');
		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		$this->showTableInformation();
		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		$this->converterInterface->echoContent('');

	}

	/**
	 * List TYPO3 system variables
	 */
	function showTypoVariables() {
		$vars = array(
			'TYPO3 VERSION' => TYPO3_version,
			'forceCharset' => $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'],
			'setDBinit' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['setDBinit'],
			'multiplyDBfieldSize' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['multiplyDBfieldSize'],
			't3lib_cs_convMethod' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_convMethod'],
			't3lib_cs_utils' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['t3lib_cs_convMethod'],

		);

		$this->converterInterface->echoContent($this->listVariablesRecursive($vars));
	}


	/**
	 * List database server variables
	 */
	function showDatabaseVariables() {
		$vars = tx_toolboxutf8_Util::getDatabaseVariables();
		$this->converterInterface->echoContent($this->listVariablesRecursive($vars));
	}

	/**
	 * Get all tables from datatbase
	 */
	function showTableInformation() {
		//show all tables with additional settings
		$tables = tx_toolboxutf8_Util::getTables();
		$this->converterInterface->echoContent(count($tables).' Tabellen');
		if ($this->converterInterface->getVerbose()) {
			$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
			$used_table_charsets = array();
			$used_field_charsets = array();
			foreach ($tables as $name => $data) {
				$field_vars = array();
				if ($this->converterInterface->getVerboseDetails()) {
					$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW FULL COLUMNS FROM ' . $name . ' WHERE Collation != \'\'');

					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						$field_vars[$row['Field']] = array(
						'Type' => $row['Type'],
						'Collation' => $row['Collation']
						);
						if (!isset($used_field_charsets[$row['Collation']])) {
							$used_field_charsets[$row['Collation']] = 0;
						}
						$used_field_charsets[$row['Collation']] += 1;
					}
					$table_vars[$name] = array(
						'Engine' => $data['Engine'],
						'Collation' => $data['Collation'],
						'Rows' => $data['Rows'],
						'Fields' => $field_vars
					);
				} else {
					$table_vars[$name] = array(
					'Collation' => $data['Collation'],
					);
				}
				if (!isset($used_table_charsets[$data['Collation']])) {
					$used_table_charsets[$data['Collation']] = 0;
				}
				$used_table_charsets[$data['Collation']] += 1;
			}
			$this->converterInterface->echoContent(
				$this->lineMarker.
				$this->converterInterface->getEol().
				'RESULT COUNT (charsets)'.
				$this->converterInterface->getEol().
				$this->lineMarker.
				$this->converterInterface->getEol().
				'Table charsets ' . $used_table_charsets.
				'Field charsets ' . $used_field_charsets
			);
		}

	}


	/**
	 * Helper function to display an array of elements as a list
	 * @param mixed $array_name Array of elements
	 * @param integer $ident Amount of blanks to indent on next level
	 * @return string List of elements
	 */
	function listVariablesRecursive($array_name, $ident = 0){
		$out = '';
		if (is_array($array_name)){
			$maxLen = 0;
				// calculate max line length
			foreach ($array_name as $key => $value) {
				if (strlen($key) > $maxLen) {
					$maxLen = strlen($key);
				}
			}

			foreach ($array_name as $key => $value){
				if (is_array($value)){
					$out .= str_repeat(' ',$ident);
					$out .= $key.' '.$this->converterInterface->getEol();
					$out .= $this->listVariablesRecursive($value, $ident + 3).$this->converterInterface->getEol();
				} else {
					$out .= str_repeat(' ',$ident);
					$out .= $key.substr($this->converterInterface->cli_indent(rtrim($value),$maxLen + 4),strlen($key)).$this->converterInterface->getEol();
				}
			}
		} else {
			$out .= $array_name;
		}
		return $out;
	}


	/**
	 * the convert task
	 * 
	 * @return void
	 */
	function convert() {

		//TODO: check the exculded tables (something missing / too many tables?)
		//TODO: make the excluded tables configurable (cli-option or _extensionConfiguration)
		$exclude_tables = array(
			'cache_extensions',
			'cache_hash',
			'cache_imagesizes',
			'cache_md5params',
			'cache_pages',
			'cache_pagesection',
			'cache_typo3temp_log',
			'index_rel',
			'index_config',
			'index_debug',
			'index_grlist',
			'index_section',
			'sys_refindex',
			'tx_realurl_redirects',
			'tx_realurl_urldecodecache',
			'tx_realurl_urlencodecache',
			'tx_ccdevlog',
			'tx_devlog');

		$live_tables = array_keys(tx_toolboxutf8_Util::getTables());
		$tables = array_diff($live_tables, $exclude_tables);
		//$tables = array('pages');
		$tableCounter = 0;
		foreach ($tables as $table) {
			
			if (substr($table, -3) === '_mm') {
				continue;
			}
			
			$this->converterInterface->echoContent('UPDATING TABLE '.$table);

			$counter = 1;
			while (TRUE) {
				if ($this->converterInterface->getVerbose()) {
					$this->converterInterface->echoContent('&nbsp;&nbsp;try to fetch next '.$this->converterInterface->getMaxRows().' rows');
				}
				$data = $this->getData($table, $counter);
				
				if (!$data) {
					$this->converterInterface->echoContent('COMPLETED '.$table.' (no more data)');
					break;
				}

				$data = $this->encodeFields($data);

				$this->saveData($table,$data);
				$counter++;
			}
			$tableCounter++;
			$this->converterInterface->updateProgressBar($tableCounter, sizeof($tables));
		}
		$this->converterInterface->echoContent($this->converterInterface->getLineMarker());
		$this->converterInterface->echoContent('Convert completed');
	}

	/**
	 * Write data from db asuming old charset (latin1)
	 * @param string $table table name
	 * @param int $limitCounter factor by which to multiple _maxRows to get the limit
	 * @return array $data array of rows
	 */
	function getData($table, $limitCounter) {

		$data = array();

		//TODO: is this right? what about a different input charset?
		$GLOBALS['TYPO3_DB']->sql_query('SET NAMES latin1;');

		$limit = (($this->converterInterface->getMaxRows() * $limitCounter) - $this->converterInterface->getMaxRows()) . ',' . $this->converterInterface->getMaxRows();
		if ($res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$table,'1=1','','',$limit)) {

			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0) {
				return false;
			}
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$data[] = $row;
			}
		}
		return $data;
	}

	/**
	 * Save the converted data (chunk by chunk)
	 * only if -f is set otherwise just generate the queries
	 * 
	 * @param string $table table name
	 * @param array $data array of rows
	 * @return void
	 */
	function saveData($table, array $data) {

		$GLOBALS['TYPO3_DB']->sql_query('SET NAMES utf8;');
		$uidField = '';
		foreach ($data as $row) {

			/*
			if (isset($row['tstamp'])) {
				$goOn = $this->checkTstamp($table, $row);
				if (!$goOn) {
					continue;
				}
			}
			*/
			
			//TODO: use primary key 
			
			if (isset($row['uid'])) {
				$uidField = 'uid';
			} elseif (isset($row['wid'])) {
				$uidField = 'wid';
			} elseif (isset($row['uniqid'])) {
				$uidField = 'uniqid';
			} elseif (isset($row['phash'])) {
				$uidField = 'phash';
			} elseif (isset($row['hash'])) {
				$uidField = 'hash';
			} elseif ($table == 'tx_crawler_queue' && isset($row['qid'])) {
				$uidField = 'qid';
			} elseif ($table == 'tx_realurl_errorlog' && isset($row['url_hash']) && isset($row['rootpage_id'])) {
				$uidField = array('url_hash', 'rootpage_id');
			} elseif (in_array($table, array('be_sessions', 'fe_sessions')) && isset($row['ses_id']) && isset($row['ses_name'])) {
				$uidField = array('ses_id', 'ses_name');
			} else {
				$this->converterInterface->echoContent('  ERROR: no uid field found for table: '.$table.'');
				return;
			}
			if (is_array($uidField)) {
				$where = array();
				foreach ($uidField as $uidFieldElement) {
					$where[] = $uidFieldElement . ' = ' . $row[$uidFieldElement];
					unset($row[$uidFieldElement]);
				}
				$query = $GLOBALS['TYPO3_DB']->UPDATEquery($table, implode(' AND ', $where), $row);
				if ($this->converterInterface->getVerboseDetails()) {
					$this->converterInterface->echoContent($query);
				}
				if ($this->converterInterface->getForceWriteToDb()) {
					if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
						$this->converterInterface->echoContent($GLOBALS['TYPO3_DB']->sql_error());
					}
				}
			} else {
				$uid = $row[$uidField];
				unset($row[$uidField]);
				$query = $GLOBALS['TYPO3_DB']->UPDATEquery($table, $uidField . ' = ' . $uid, $row);
				if ($this->converterInterface->getVerboseDetails()) {
					$this->converterInterface->echoContent($query);
				}
				if ($this->converterInterface->getForceWriteToDb()) {
					if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
						$this->converterInterface->echoContent($GLOBALS['TYPO3_DB']->sql_error());
					}
				}
			}
		}
		return;

	}

	/**
	 * Check if the data is already utf8 converted, otherwise use utf8_encode() on the data to convert it
	 * @param array $data chunk of table data
	 * @return array
	 */
	function encodeFields(array $data) {
		$out = array();
		foreach ($data as $key => $row) {
			foreach ($row as $rowKey => $value) {

				if ($this->checkSerialization($value)) {
					$data = unserialize($value);
					if (is_array($data)) {
						$data = $this->arrayUtf8Encode($data);
					} else {
						// TODO implement hook
						//debug('serialized data but no array',$rowKey,__LINE__,__FILE__);
						if (is_object($data)) {
							//debug(get_class($data),'is object of type',__LINE__,__FILE__);
						}
					}
					$value = serialize($data);
				} else {
					if (!$this->isUtf8($value)) {
						$value = utf8_encode($value);
					}
				}

				$out[$key][$rowKey] = $value;
			}
		}
		return $out;
	}

	
	/**
	 * Enter description here...
	 *
	 * @param string $string
	 * @return boolean
	 */
	function isUtf8($string) { // v1.01
		
		$maxChar = 3000;
		
	    if (strlen($string) > $maxChar) {
	        // Based on: http://mobile-website.mobi/php-utf8-vs-iso-8859-1-59
	        for ($i = 0, $s = $maxChar, $j = ceil(strlen($string) / $maxChar); $i < $j; $i++, $s += $maxChar) {
	            if ($this->isUtf8(substr($string, $s, $maxChar))) {
	                return true;
	            }
	        }
	        return false;
	    } else {
	        // From http://w3.org/International/questions/qa-forms-utf-8.html
	        $check = preg_match('%^(?:
	                [\x09\x0A\x0D\x20-\x7E]            # ASCII
	            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
	        )*$%xs', $string);
	        return $check;
	    }
	} 
	
		
	/**
	 * Enter description here...
	 *
	 * @param array $array
	 * @return array
	 */
	function arrayUtf8Encode(array $array) {
		$out = array();
		foreach ($array as $key => $element) {
			if (is_array($element)) {
				$out[$key] = $this->arrayUtf8Encode($element);
			} else {
				if (!$this->isUtf8($element)) {
					 $element = utf8_encode($element);
				}
				$out[$key] = $element;
			}
		}
		return $out;
	}

	/**
	 * TODO: NOT USED
	 * For converting only unchanged rows
	 *
	 * @param string $table
	 * @param array $rowBackup
	 * @return unknown
	 */	
	function checkTstamp($table, $backupRow) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table,'uid = ' . $backupRow['uid']);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$goOn = $backupRow['tstamp'] == $row['tstamp'];
		if (!$goOn) {
			$this->converterInterface->echoContent($this->converterInterface->getEol().'  ERROR: row was changed: '.$backupRow['uid'].'');
			$diff = array_diff($row, $backupRow);

			foreach ($diff as $diffKey => $diffElement) {
				$live = htmlspecialchars($row[$diffKey]);
				$original = htmlspecialchars($backupRow[$diffKey]);
				$this->converterInterface->echoContent('    FIELD: '.$diffKey.'');
				$this->converterInterface->echoContent('    DIFF LIVE: '.$live.'');
				$this->converterInterface->echoContent('    DIFF ORG: '.$original.'');
			}
		}
		return $goOn;
	}
	
	/**
	 * NOT USED ANY MORE
	 * Fix template voila mapping code
	 */
	function fixingTemplavoila() {

		$GLOBALS['TYPO3_DB']->sql_query('SET NAMES latin1');

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_templavoila_tmplobj','uid = 40','','');

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		$map = unserialize($row['templatemapping']);

		$map = $this->arrayUtf8Encode($map);

		$GLOBALS['TYPO3_DB']->sql_query('SET NAMES utf8');

		$data = array(
			'templatemapping' => serialize($map),
		);

		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_templavoila_tmplobj', 'uid = '.$row['uid'], $data);


		return;
	}
	
	/**
	 *
	 * @param string $string
	 * @return boolean
	 */
	function checkSerialization($string) {
	
		$data = @unserialize($string);
		if ($string === 'b:0;' || $data !== false) {
		    return true;
		} else {
		    return false;
		}
	}
	
	
	
}

?>