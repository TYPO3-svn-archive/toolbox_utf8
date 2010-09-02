<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE == 'BE') {
	if (t3lib_extMgm::isLoaded('reports')) {
		
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['typo3utf8'][] = 'tx_toolboxutf8_reports_status_MysqlUtf8Status';
		
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_toolboxutf8']['status'] = array(
			'title'       => 'LLL:EXT:toolbox_utf8/reports/locallang.xml:utf8status_report_title',
			'description' => 'LLL:EXT:toolbox_utf8/reports/locallang.xml:utf8status_report_description',
			'icon'		  => 'EXT:toolbox_utf8/reports/tx_toolboxutf8_report.png',
			'report'      => 'tx_toolboxutf8_reports_utf8status'
		);
		
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_toolboxutf8']['status']['providers']['typo3utf8_typo3variables'][] = 'tx_toolboxutf8_reports_status_Typo3Status';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_toolboxutf8']['status']['providers']['typo3utf8_typo3variables'][] = 'tx_toolboxutf8_reports_status_MysqlStatus';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_toolboxutf8']['status']['providers']['typo3utf8_typo3variables'][] = 'tx_toolboxutf8_reports_status_DatabaseStatus';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_toolboxutf8']['status']['providers']['typo3utf8_typo3variables'][] = 'tx_toolboxutf8_reports_status_WebserverStatus';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_toolboxutf8']['status']['providers']['typo3utf8_typo3variables'][] = 'tx_toolboxutf8_reports_status_PhpStatus';
	}
	
	t3lib_extMgm::addModule('tools','txtoolboxutf8M1','',t3lib_extMgm::extPath($_EXTKEY).'mod_convert/');
	
}

?>