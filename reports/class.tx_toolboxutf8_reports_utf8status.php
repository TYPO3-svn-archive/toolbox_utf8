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
 * class.tx_toolboxutf8_reports_utf8status.php
 *
 * UTF8 report for the reports module  
 *
 * @version 	$Id: class.tx_toolboxutf8_reports_utf8status.php 7571 2010-07-01 18:50:06Z sebastian.fuchs $
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public License, version 2 
 *
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @author 		Sebastian Fuchs <sebastian.fuchs@mensemedia.net>
 * @package 	TYPO3
 * @subpackage 	toolbox_utf8
 */

/**
 * Displays the different status reports regarding utf8 settings
 *
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */
class tx_toolboxutf8_reports_utf8status implements tx_reports_Report {

	protected $statusProviders = array();

	/**
	 * Back-reference to the calling reports module
	 *
	 * @var	tx_reports_Module	$reportObject
	 */
	protected $reportObject;


	public $t3_vars = array();
	/**
	 * constructor for class tx_reports_report_Status
	 */
	public function __construct(tx_reports_Module $reportObject) {
		$this->reportObject = $reportObject;
		$this->setupT3Vars();
		$this->getStatusProviders();

		$GLOBALS['LANG']->includeLLFile('EXT:toolbox_utf8/locallang.xml');
	}


	/**
	 * Read TYPO3 variables (mostly TYPO3_CONF_VARS) and sets them internally
	 */
	public function setupT3Vars() {
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

	/**
	 * Takes care of creating / rendering the status report
	 *
	 * @return	string	The status report as HTML
	 */
	public function getReport() {

		// Add custom stylesheet
		$this->reportObject->doc->getPageRenderer()->addCssFile(t3lib_extMgm::extRelPath('toolbox_utf8') . 'reports/tx_toolboxutf8_report.css');

		$status  = array();
		$content = '';


		$content .= '<p class="help">'
		. $GLOBALS['LANG']->getLL('utf8status_report_explanation')
		. '</p>';
		return $content . $this->renderStatus($this->statusProviders);
	}


	/**
	 * Renders the system's status
	 *
	 * @param	array	An array of statuses as returned by the available status providers
	 * @return	string	The system status as an HTML table
	 */
	protected function renderStatus(array $statusCollection) {
		$content = '';
		$statusCollection = $this->statusProviders;

		$statuses = $this->sortStatusProviders($statusCollection);

		foreach($statuses as $provider => $providerStatus) {
			$providerState = $this->sortStatuses($providerStatus);

			$id = str_replace(' ', '-', $provider);
			if (isset($GLOBALS['BE_USER']->uc['reports']['states'][$id]) && $GLOBALS['BE_USER']->uc['reports']['states'][$id]) {
				$collapsedStyle = 'style="display:none"';
				$collapsedClass = 'collapsed';
			} else {
				$collapsedStyle = '';
				$collapsedClass = 'expanded';
			}


			$classes = array(
			tx_reports_reports_status_Status::NOTICE  => 'notice',
			tx_reports_reports_status_Status::INFO    => 'information',
			tx_reports_reports_status_Status::OK      => 'ok',
			tx_reports_reports_status_Status::WARNING => 'warning',
			tx_reports_reports_status_Status::ERROR   => 'error',
			);

			$icon[tx_reports_reports_status_Status::WARNING] = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/warning.png', 'width="16" height="16"') . ' alt="" />';
			$icon[tx_reports_reports_status_Status::ERROR] = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/error.png', 'width="16" height="16"') . ' alt="" />';
			$icon[tx_reports_reports_status_Status::NOTICE] = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/notice.png', 'width="16" height="16"') . ' alt="" />';
			$messages = '';
			$headerIcon = '';
			$sectionSeverity = tx_reports_reports_status_Status::NOTICE;


			foreach ($providerState as $status) {
				//$severity = $status->getSeverity();
				//$sectionSeverity = $severity > $sectionSeverity ? $severity : $sectionSeverity;
				//$statusProviderInstance = t3lib_div::makeInstance($providerStatus[0]);
				$status->getStatus();
				$messages .= $status->renderStatus($classes);
			}
			//if ($sectionSeverity > 0) {
				$headerIcon = $icon[$sectionSeverity];
			//}
			$content .= '<h1 id="' . $id . '" class="section-header ' . $collapsedClass . '">' . $headerIcon . $provider . '</h1>
				<div ' . $collapsedStyle . '>' . $messages . '</div>';
		}
		return $content;
	}

	/**
	 * Gets all registered status providers and creates instances of them.
	 *
	 * @return	void
	 */
	protected function getStatusProviders() {
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_toolboxutf8']['status']['providers'] as $key => $statusProvidersList) {
			$this->statusProviders[$key] = array();
			foreach ($statusProvidersList as $statusProvider) {
				$statusProviderInstance = t3lib_div::makeInstance($statusProvider);
				if ($statusProviderInstance instanceof tx_reports_StatusProvider) {
					$this->statusProviders[$key][] = $statusProviderInstance;
				}
			}
		}
	}

	/**
	 * Sorts the status providers (alphabetically and puts primary status providers at the beginning)
	 *
	 * @param   array   A collection of statuses (with providers)
	 * @return  array   The collection of statuses sorted by provider (beginning with provider "_install")
	 */
	protected function sortStatusProviders(array $statusCollection) {
		// Extract the primary status collections, i.e. the status groups
		// that must appear on top of the status report
		// Change their keys to localized collection titles
		/*		$primaryStatuses = array(
		$GLOBALS['LANG']->getLL('status_typo3')         => $statusCollection['typo3'],
		$GLOBALS['LANG']->getLL('status_system')        => $statusCollection['system'],
		$GLOBALS['LANG']->getLL('status_security')      => $statusCollection['security'],
		$GLOBALS['LANG']->getLL('status_configuration') => $statusCollection['configuration']
		);
		unset(
		$statusCollection['typo3'],
		$statusCollection['system'],
		$statusCollection['security'],
		$statusCollection['configuration']
		);
		// Assemble list of secondary status collections with left-over collections
		// Change their keys using localized labels if available
		$secondaryStatuses = array();
		foreach ($statusCollection as $statusProviderId => $collection) {
		$label = '';
		if (strpos($statusProviderId, 'LLL:') === 0) {
		// Label provided by extension
		$label = $GLOBALS['LANG']->sL($statusProviderId);
		} else {
		// Generic label
		$label = $GLOBALS['LANG']->getLL('status_' . $statusProviderId);
		}
		$providerLabel = (empty($label)) ? $statusProviderId : $label;
		$secondaryStatuses[$providerLabel] = $collection;
		}
		// Sort the secondary status collections alphabetically
		ksort($secondaryStatuses);
		$orderedStatusCollection = array_merge($primaryStatuses, $secondaryStatuses);

		return $orderedStatusCollection;
		*/
		return $statusCollection;
	}

	/**
	 * Sorts the statuses by severity
	 *
	 * @param   array   A collection of statuses per provider
	 * @return  array   The collection of statuses sorted by severity
	 */
	protected function sortStatuses(array $statusCollection) {
		$statuses  = array();
		$sortTitle = array();

		//		foreach ($statusCollection as $status) {
		//			if ($status->getTitle() === 'TYPO3') {
		//				$header = $status;
		//				continue;
		//			}
		//
		//			$statuses[] = $status;
		//			$sortTitle[] = $status->getSeverity();
		//		}
		//		array_multisort($sortTitle, SORT_DESC, $statuses);
		//
		//		// making sure that the core version information is always on the top
		//		if (is_object($header)) {
		//			array_unshift($statuses, $header);
		//		}
		//		return $statuses;
		return $statusCollection;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/reports/reports/class.tx_reports_reports_status.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/reports/reports/class.tx_reports_reports_status.php']);
}

?>