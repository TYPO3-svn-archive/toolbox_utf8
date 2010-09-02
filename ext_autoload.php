<?php
/*
 * Register necessary class names with autoloader
 *
 * $Id: ext_autoload.php 7399 2010-06-19 23:10:33Z sebastian.fuchs $
 */
$extensionPath = t3lib_extMgm::extPath('toolbox_utf8');
return array(
	'tx_toolboxutf8_converter' => $extensionPath . 'classes/class.tx_toolboxutf8_converter.php',
	'tx_toolboxutf8_reports_status_mysqlutf8status' => $extensionPath . 'reports/status/class.tx_toolboxutf8_reports_status_mysqlutf8status.php',
	'tx_toolboxutf8_reports_utf8status' => $extensionPath . 'reports/class.tx_toolboxutf8_reports_utf8status.php',
	'tx_toolboxutf8_reports_statusprovider' => $extensionPath . 'reports/status/class.tx_toolboxutf8_reports_statusprovider.php',
	'tx_toolboxutf8_reports_status_typo3status' => $extensionPath . 'reports/status/class.tx_toolboxutf8_reports_status_typo3status.php',
	'tx_toolboxutf8_reports_status_mysqlstatus' => $extensionPath . 'reports/status/class.tx_toolboxutf8_reports_status_mysqlstatus.php',
	'tx_toolboxutf8_reports_status_databasestatus' => $extensionPath . 'reports/status/class.tx_toolboxutf8_reports_status_databasestatus.php',
	'tx_toolboxutf8_reports_status_webserverstatus' => $extensionPath . 'reports/status/class.tx_toolboxutf8_reports_status_webserverstatus.php',
	'tx_toolboxutf8_reports_status_phpstatus' => $extensionPath . 'reports/status/class.tx_toolboxutf8_reports_status_phpstatus.php',
	'tx_toolboxutf8_util' => $extensionPath . 'classes/class.tx_toolboxutf8_util.php',
	'tx_toolboxutf8_converterinterface' => $extensionPath . 'interfaces/interface.tx_toolboxutf8_converter.php',
	'tx_toolboxutf8_moduleconvert' => $extensionPath . 'mod_convert/index.php',
//	'tx_toolboxutf8_converttask_additionalfieldprovider' => $extensionPath . 'scheduler/class.tx_toolboxutf8_converttask_additionalfieldprovider.php',
//	'tx_toolboxutf8_converttask' => $extensionPath . 'scheduler/class.tx_toolboxutf8_converttask.php',

);
?>
