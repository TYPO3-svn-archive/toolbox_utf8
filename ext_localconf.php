<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TYPO3_CONF_VARS['EXTCONF']['toolbox_utf8']['setup'] = unserialize($_EXTCONF);

if (TYPO3_MODE=='BE')    {
    // Setting up scripts that can be run from the cli_dispatch.phpsh script.
    $TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array('EXT:'.$_EXTKEY.'/cli/class.tx_toolboxutf8_cli.php','_CLI_toolboxutf8');
    
}

?>