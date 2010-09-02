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
 * index.php
 * 
 * Starting point of the BE modul 
 *  
 * @version 	$Id: $
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public License, version 2 
 * 
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */

unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
require_once (PATH_t3lib . 'class.t3lib_scbase.php');
require_once (t3lib_extMgm::extPath('toolbox_utf8') . 'interfaces/interface.tx_toolboxutf8_converter.php');
require_once (t3lib_extMgm::extPath('toolbox_utf8') . 'classes/class.tx_toolboxutf8_converter.php');

$LANG->includeLLFile('EXT:toolbox_utf8/mod_convert/locallang.xml');
	// This checks permissions and exits if the users has no permission for entry.
$BE_USER->modAccess($MCONF, 1);

		
/**
 * BE modul to perform utf8 related tasks (info/alter/convert)
 * 
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */
class tx_toolboxutf8_ModuleConvert extends t3lib_SCbase implements tx_toolboxutf8_ConverterInterface {

	/**
	 * tx_toolboxutf8_Converter
	 *
	 * @var unknown_type
	 */
	protected $converter;
	
	/**
	 * Amount of table rows per interval (for converting the db)
	 *
	 * @var integer 
	 */
	protected $maxRows = 500;
	
	
	/**
	 *
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		if (version_compare(TYPO3_version, '4.3.0', '<')) {
			$className = t3lib_div::makeInstanceClassName('tx_toolboxutf8_Converter');
			$this->converter = new $className($this);
		} else {
			$this->converter = t3lib_div::makeInstance('tx_toolboxutf8_Converter', $this);
		}
			
		//ini_set('display_errors' ,1);
		
		
		parent::init();


	}
	
	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = array (
			'function' => array (
				'1' => 'info', //$LANG->getLL('function1'),
				'2' => 'alter', //$LANG->getLL('function2'),
				'3' => 'convert', //$LANG->getLL('function3'),
			),
			'force' => '',
			'verbose' => '',
			'verbose_details' => '',
		);
		$collations = tx_toolboxutf8_Util::getCollations('utf8', false);
		$this->MOD_MENU['collation'] = $collations;
		//debug($collations,'',__LINE__,__FILE__);
		parent::menuConfig();
	}
	
	

	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

			

			// Draw the header.
			$this->doc = t3lib_div::makeInstance('bigDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="post">';
			
			
			// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
			';


			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu('', $GLOBALS['LANG']->getLL('choose_function') . t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			
			$checkBoxSection = '';
			$checkBoxSection .= t3lib_BEfunc::getFuncCheck(0, 'SET[verbose]', $this->MOD_SETTINGS['verbose'], '', '', 'id="checkVerbose"') .
						'<label for="checkVerbose">' . $GLOBALS['LANG']->getLL('verbose', true) . '</label>';
			$checkBoxSection .= t3lib_BEfunc::getFuncCheck(0, 'SET[verbose_details]', $this->MOD_SETTINGS['verbose_details'], '', '', 'id="checkVerboseDetails"') .
						'<label for="checkVerboseDetails">' . $GLOBALS['LANG']->getLL('verbose_details', true) . '</label>';
			$checkBoxSection .= t3lib_BEfunc::getFuncCheck(0, 'SET[force]', $this->MOD_SETTINGS['force'], '', '', 'id="checkForce"') .
						'<label for="checkForce">' . $GLOBALS['LANG']->getLL('force', true) . '</label>';
			
			$collationMenu = '';
			if ($this->MOD_SETTINGS['function'] == 2) {
				$collationMenu = $GLOBALS['LANG']->getLL('choose_collation') . t3lib_BEfunc::getFuncMenu($this->id,'SET[collation]',$this->MOD_SETTINGS['collation'],$this->MOD_MENU['collation']);
			}
			
			$this->content.=$this->doc->section('',$this->doc->funcMenu($checkBoxSection, $collationMenu));
			$this->content.=$this->doc->divider(5);
			
			$this->extConf = $TYPO3_CONF_VARS['EXTCONF']['toolbox_utf8']['setup'];		
			
			

			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
			// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);
		echo $this->content;
	}

	/**
	 * Generates the module content
	 */
	function moduleContent()	{
		
		
				
			if (t3lib_div::_GP('step_process')) {
			
				ob_end_flush();

				ini_set('display_errors' ,1);
				set_time_limit(0);
				
				$charset = $GLOBALS['LANG']->charSet ? $GLOBALS['LANG']->charSet : 'iso-8859-1';
				
				
				$this->echoContent('<html>
				<head>
				<title>indexing</title>
				<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">
				<style>
					body {
						font-familiy: Verdana;
						font-size: 11px;
					}
				</style>
				</head>
				<body>
				');
				
			
				switch($this->MOD_SETTINGS['function'])	{
		
						
					case 1:
						$this->converter->info();
						break;
						
					case 2:
						$this->converter->alter();	
						break;
						
					case 3:
						$this->converter->convert();	
						break;
				}
				
				
				$this->echoContent('</body></html>');

				ob_end_flush();
				exit;
			}

			
			switch (t3lib_div::_GP('step')) {
				case '1':
					$this->doc->addParams['step_process'] = 1;
					$showProgressbar = $this->MOD_SETTINGS['function'] > 1;
					$content = $this->xml_import_progressTable($showProgressbar);
					break;
				default:
					$content = $this->xml_import_start();
					break;
			}

			$this->content.=$this->doc->section('', $content, 0, 1);
		
	}
	
	function xml_import_start() {
		
			
		$content .= '
		<form action="'.t3lib_div::linkThisScript().'" method="post">
		<input type="hidden" name="step" value="1" />
		<input type="submit" value="start" />
		</form>';

		return $content;
	
		
	}
	

	function xml_import_progressTable ($showProgressbar = 0) {
		global  $BE_USER, $LANG, $BACK_PATH, $TYPO3_CONF_VARS;

		$this->doc->inDocStylesArray['import'] = 'th {
		font-family: Verdana, Arial, Helvetica;
		font-size: 10px;
		margin: 0 0 0 0;
		color: black;
		}';

		$this->doc->JScode = $this->doc->wrapScriptTags('
			function progress_bar_update(intCurrentPercent) {
				document.getElementById("progress_bar_left").style.width = intCurrentPercent+"%";
				document.getElementById("progress_bar_left").innerHTML = intCurrentPercent+"&nbsp;%";

				document.getElementById("progress_bar_left").style.background = "#448e44";
				if(intCurrentPercent >= 100) {
					document.getElementById("progress_bar_right").style.background = "#448e44";
				} else {
					document.getElementById("progress_bar_right").style.background = "#eee";
				}
				if(intCurrentPercent < 1) {
					document.getElementById("progress_bar_left").style.background = "#eee";
				}
			}


			function addTableRow(cells) {

				document.getElementById("progressTable").style.visibility = "visible";
								
				var row = document.createElement("TR");

				row.style.backgroundColor = "#D9D5C9";

				for (var cellId in cells) {
					var tdCell = document.createElement("TD");
					tdCell.innerHTML = cells[cellId];
					row.appendChild(tdCell);
				}
				var header = document.getElementById("progressTableheader");
				var headerParent = header.parentNode;
				headerParent.insertBefore(row,header.nextSibling);

			}

			function setMessage(msg) {
				var messageCnt = document.getElementById("message");
				messageCnt.innerHTML = msg;
			}

			function finished() {
				progress_bar_update(100);
				document.getElementById("progress_bar_left").innerHTML = "'.$LANG->getLL('tx_dam_tools_indexupdate.finished',1).'";				
			}
		');

//		if ($this->extConf['debug']) {
			$iframeSize = 'width="100%" style="height: 500px;" border="1" scrolling="yes" frameborder="1"';
//		} else {
//			$iframeSize = 'width="0" height="0" border="0" scrolling="no" frameborder="0"';
//		}

		$content = '';
		if ($showProgressbar) {
		$content .= '
			<br />
			<table width="300px" border="0" cellpadding="0" cellspacing="0" id="progress_bar" summary="progress_bar" align="center" style="border:1px solid #888">
			<tbody>
			<tr>
			<td id="progress_bar_left" width="0%" align="center" style="background:#eee; color:#fff">&nbsp;</td>
			<td id="progress_bar_right" style="background:#eee;">&nbsp;</td>
			</tr>
			</tbody>
			</table>
		';
		}
		
		$content .= '<br />
			<iframe src="'.htmlspecialchars(t3lib_div::linkThisScript($this->doc->addParams)).'" name="indexiframe" '.$iframeSize.'>
			Error!
			</iframe>
			<br />
		';

		$content .= '
			 <div id="message"></div><br />
			 <table id="progressTable" style="visibility:hidden;" cellpadding="1" cellspacing="1" border="0" width="100%">
			 <tr id="progressTableheader" bgcolor="'.$this->doc->bgColor5.'">
				 <th>'.'Datei'.'</th>				 
				 <th>'.'# aktualisiert'.'</th>
				 <th>'.'# eingefuegt'.'</th>				 
				 <th>'.'# gesamt'.'</th>
			</tr>
			</table>
		';

		return $content;
					
		 
	}
	
	function updateProgressBar($intCurrentCount = 100, $intTotalCount = 100) {

		static $intNumberRuns = 0;
		static $intDisplayedCurrentPercent = 0;
		$strProgressBar = '';
		$dblPercentIncrease = (100 / $intTotalCount);
		$intCurrentPercent = intval($intCurrentCount * $dblPercentIncrease);
		$intNumberRuns ++;

		if (($intNumberRuns>1) AND ($intDisplayedCurrentPercent != $intCurrentPercent))  {
			$intDisplayedCurrentPercent = $intCurrentPercent;
			$strProgressBar = '
				<script type="text/javascript" language="javascript"> parent.progress_bar_update('.$intCurrentPercent.'); </script>';
		}
		
		$this->echoContent($strProgressBar, FALSE);
		
	}
	
	
	public function echoContent($content, $eol = TRUE) {
		echo $content . ($eol ? $this->getEol() : '');
		flush();
		ob_flush();	
	}
	
	/**
	 * @return boolean
	 */
	public function getAlterCharsetId() {
		return $this->MOD_SETTINGS['collation'];
	}
	
	/**
	 * @return boolean
	 */
	public function getErrorReportingEnabled() {
		
	}

	
	/**
	 * @return boolean
	 */
	public function getForceWriteToDb() {
		return $this->MOD_SETTINGS['force'];
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
		$verbose = $this->MOD_SETTINGS['verbose'] | $this->MOD_SETTINGS['verbose_details'];
		return $verbose;
	}
	
	/**
	 * @return boolean
	 */
	public function getVerboseDetails() {
		return $this->MOD_SETTINGS['verbose_details'];
	}
	
	/**
	 * Enter description here...
	 * 
	 * @return string
	 *
	 */
	public function getEol() {
		return '<br />'.PHP_EOL;
	}
	
	public function getLineMarker() {
		return '----------------';
	}
	
	public function getInteractiveModeAllowed() {
		return false;
	}
	
	/**
	 * Indentation function for 75 char wide lines.
	 *
	 * @param	string		String to break and indent.
	 * @param	integer		Number of space chars to indent.
	 * @return	string		Result
	 */
	function cli_indent($str,$indent)	{
		$lines = explode(chr(10),wordwrap($str,75-$indent));
		$indentStr = str_pad('',$indent,' ');
		foreach($lines as $k => $v)	{
			$lines[$k] = $indentStr.$lines[$k];
		}
		return implode(chr(10),$lines);
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/mod_convert/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/mod_import/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_toolboxutf8_ModuleConvert');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();

?>