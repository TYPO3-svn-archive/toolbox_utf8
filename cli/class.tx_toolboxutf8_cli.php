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
 * class.tx_toolboxutf8_cli.php
 * 
 * command line script to perform utf8 related tasks (info/alter/convert)
 * 
 * @version 	$Id: class.tx_toolboxutf8_cli.php 7594 2010-07-03 16:51:30Z bastian.heinrichs $
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * 
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @author 		Sebastian Fuchs <sebastian.fuchs@mensemedia.net>
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 * 
 */


if (!defined('TYPO3_cliMode'))  die('You cannot run this script directly!');


require_once(PATH_t3lib.'class.t3lib_cli.php');
require_once(t3lib_extMgm::extPath('toolbox_utf8').'classes/class.tx_toolboxutf8_converter.php');
require_once(t3lib_extMgm::extPath('toolbox_utf8').'interfaces/interface.tx_toolboxutf8_converter.php');

/**
 * CLI class to perform utf8 related tasks (info/alter/convert)
 * 
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @author 		Sebastian Fuchs <sebastian.fuchs@mensemedia.net>
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */
class tx_toolboxutf8_Cli extends t3lib_cli implements tx_toolboxutf8_ConverterInterface {

	/**
	 * Holds the extension configurtion from ext_conf_template.txt
	 *  
	 * @var mixed   
	 */
	protected $extensionConfiguration;

	/**
	 * Amount of table rows per interval (for converting the db)
	 *
	 * @var integer 
	 */
	protected $maxRows = 500;

	/**
	 * Line seperator
	 * 
	 * @var string  
	 */
	protected $lineMarker = '-----------------------------------';
	
	
	/**
	 * tx_toolboxutf8_Converter
	 *
	 * @var unknown_type
	 */
	protected $converter;
	
	protected $errorReportingEnabled = FALSE;
	
	protected $forceWriteToDb = FALSE;
	
	protected $verbose = FALSE;
	
	protected $verboseDetails = FALSE;
	
	protected $alterCharset = NULL;
	
	protected $eol = PHP_EOL;
	

	/**
	 * constructer
	 * set options and configuration
	 */
	public function __construct() {
		parent::t3lib_cli();
		
		// Setting help texts:
		$this->cli_help['name'] = 'UTF8 analyser and converter';
		$this->cli_help['synopsis'] = '###OPTIONS###';
		$this->cli_help['description'] = 'Analyse and converte your database to UTF-8';
		$this->cli_help['examples'] = '/.../cli_dispatch.phpsh toolboxutf8 info|run|convert|alter [OPTIONS:-s|--s|silent|-m maxRows]';
		$this->cli_help['author'] = 'MBH,SMF (c) 2009,2010';

		$this->setCliOptions();

		$this->extensionConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['toolbox_utf8']['setup'];
		
		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$className = t3lib_div::makeInstanceClassName('tx_toolboxutf8_Converter');
			$this->converter = new $className($this);
		} else {
			$this->converter = t3lib_div::makeInstance('tx_toolboxutf8_Converter', $this);
		}

	}

	/**
	 * @return boolean
	 */
	public function getAlterCharsetId() {
		return $this->alterCharset;
	}
	
	/**
	 * @return boolean
	 */
	public function getErrorReportingEnabled() {
		return $this->errorReportingEnabled;
	}
	
	/**
	 * @return boolean
	 */
	public function getForceWriteToDb() {
		return $this->forceWriteToDb;
	}
	
	/**
	 * @return int
	 */
	public function getMaxRows() {
		return $this->maxRows;
	}
	
	/**
	 * @return boolean
	 */
	public function getVerbose() {
		return $this->verbose;
	}
	
	/**
	 * @return boolean
	 */
	public function getVerboseDetails() {
		return $this->verboseDetails;
	}
	
	public function getEol() {
		return $this->eol;
	}
	
	public function getLineMarker() {
		return $this->lineMarker;
	
	}
	
	public function getInteractiveModeAllowed() {
		return TRUE;
	}
	
	public function updateProgressBar() {
		// nothing to do
	}

	
	protected function setCliOptions() {
		//setting options
		$this->cli_options[] = array(
			'-e',
			'Enable error reporting',
			''
		);
		$this->cli_options[] = array(
			'-a',
			'Alter tables with selected charset ID (only with option "alter")',
			''
		);
		$this->cli_options[] = array(
			'-f',
			'Write to database; otherwise just output the statments (only with option "alter" and "run")',
			''
		);
		$this->cli_options[] = array(
			'-m',
			'max rows per chunk [default=2000]',
			''
		);
		$this->cli_options[] = array(
			'-v',
			'Display a lot of information (only with option "info" or "run")',
			''
		);
		$this->cli_options[] = array(
			'-vv',
			'Display even more informations (only with option "info")',
			''
		);
		$this->cli_options[] = array(
			'info',
			'Display information about database connections',
			''
		);
		$this->cli_options[] = array(
			'convert',
			'Run the utf8 converter script',
			''
		);
		$this->cli_options[] = array(
			'run',
			'Run the utf8 converter script (same as "convert")',
			''
		);
		$this->cli_options[] = array(
			'alter',
			'Convert the table and field collations to UTF-8',
			''
		);
		
	}

	function tx_toolboxutf8_cli() {
		$this->__construct();
	}

	/**
	 * CLI engine
	 *
	 * @param    array        Command line arguments
	 * @return    string
	 */
	function cli_main() {

		$this->checkArgs();
		
		//TODO: do we need the error options?	
		if ($this->errorReportingEnabled) {
			error_reporting(E_ALL | E_STRICT);
			ini_set('display_startup_errors', 1);
		}
		//ini_set('display_errors', 1);
			
		set_time_limit(0);
		
		ob_end_flush();
		
		if (ob_get_level() == 0 ) {
			ob_start();
		}

	
		// get task (function)
		$task = isset($this->cli_args['_DEFAULT'][1]) ? (string) $this->cli_args['_DEFAULT'][1] : NULL;

		if (!$task) {
			$this->cli_validateArgs();
			$this->cli_help();
			exit;
		}

		if ($this->verbose) {
			$this->echoContent($this->lineMarker);
			$this->echoContent('UTF8 status/analyses/update' );
			$this->echoContent('Date: '.date('Y-m-d H:i'));
			$this->echoContent($this->lineMarker);
		}

		switch ($task) {
			case  'run':
			case  'convert':
				$this->converter->convert();
				break;
			case  'alter':
				$this->converter->alter();
				break;
			case 'info':
				$this->converter->info();
				break;
			default:
				$this->cli_help();
				break;
		}

		ob_end_flush();
	}

	public function checkArgs() {
		if ($this->cli_isArg('-e')) {
			$this->errorReportingEnabled = TRUE; 
		}
		
		if ($this->cli_isArg('-a')) {
			$this->alterCharset = intval($this->cli_argValue('-a'));; 
		}
		
		if ($this->cli_isArg('-v')) {
			$this->verbose = TRUE; 
		}
		
		if ($this->cli_isArg('-vv')) {
			$this->verbose = TRUE;
			$this->verboseDetails = TRUE; 
		}
		
		if ($this->cli_isArg('-m')) {
			$this->maxRows = intval($this->cli_argValue('-m'));
		}
		
		if ($this->cli_isArg('-f')) {
			$this->forceWriteToDb = TRUE; 
		}
	}

	
	public function echoContent($content, $eol = TRUE) {
		parent::cli_echo($content . ($eol ? $this->eol : ''));
		flush();
		ob_flush();	
	}
	
	

	
}



// Call the functionality
$object = t3lib_div::makeInstance('tx_toolboxutf8_Cli');
$object->cli_main();

?>