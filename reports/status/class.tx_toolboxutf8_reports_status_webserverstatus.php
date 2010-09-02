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
 * class.tx_toolboxutf8_reports_status_webserverstatus.php
 *
 * UTF8 status report about httpd variables  
 *
 * @version 	$Id: class.tx_toolboxutf8_reports_status_webserverstatus.php 7649 2010-07-08 16:00:50Z sebastian.fuchs $
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public License, version 2 
 *
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @author 		Sebastian Fuchs <sebastian.fuchs@mensemedia.net>
 * @package 	TYPO3
 * @subpackage 	toolbox_utf8
 */


/**
 * Performs some checks about the http server varaibles by makeing http requests 
 *
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */
class tx_toolboxutf8_reports_status_WebserverStatus extends tx_toolboxutf8_reports_StatusProvider {

	/**
	 * Check the system configuration, connection, database variables
	 *
	 * @return	array	List of status
	 * @see typo3/sysext/reports/interfaces/tx_reports_StatusProvider::getStatus()
	 */
	public function getStatus() {
		$status = array();
		$status['webserver'] = '';
		return $status;
	}


	/**
	 * Returns the html representation of the class
	 * @return	string $content HTML representing this status
	 * TODO: Refactor to use getStatus()
	 */
	public function renderStatus(array $classes) {

		//TODO: files to check for, configuable??
		$site_url = t3lib_div::getIndpEnv('TYPO3_SITE_URL')  . t3lib_extMgm::siteRelPath('toolbox_utf8') . 'resources/testfiles/';
			//file path to check
		$files = array(
			'notfound' => $site_url.'testfile.notfound',
			'nothing '=> $site_url.'testfile.no_vaild_filetype',
			'txt' => $site_url.'testfile.txt',
			'html' => $site_url.'testfile.html',
			'xml' => $site_url.'testfile.xml',
			'typo3 FE' => t3lib_div::getIndpEnv('TYPO3_SITE_URL'),
			'typo3 BE' => t3lib_div::getIndpEnv('TYPO3_SITE_URL').'typo3/backend.php',
		);
			//fake the requests to the BE-users login session
		$request_headers = array(
			'User-Agent: ' . t3lib_div::getIndpEnv('HTTP_USER_AGENT'),
			'Accept: ' . $_SERVER['HTTP_ACCEPT'],
			'Accept-Language: ' . t3lib_div::getIndpEnv('HTTP_ACCEPT_LANGUAGE'),
			'Accept-Encoding: ' . $_SERVER['HTTP_ACCEPT_ENCODING'],
			'Accept-Charset: ' . $_SERVER['HTTP_ACCEPT_CHARSET'],
			'X-Requested-With: XMLHttpRequest',
			'Referer: ' . (t3lib_div::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://') . t3lib_div::getIndpEnv('TYPO3_SITE_URL').'typo3/backend.php',
			'Cookie: be_typo_user='.$_COOKIE['be_typo_user'].'; PHPSESSID='.$_COOKIE['PHPSESSID'].'; '
		);

		
		$meta_report = array();

		foreach($files as $file) {
			$headers = t3lib_div::getURL($file, 2,$request_headers,$meta_report) . "\n";
//			debug(str_replace(chr(10),'<br />',$headers),$file);
//			debug($meta_report,$file);
			
			$result = $this->getContentType($headers);
			$severity = tx_reports_reports_status_Status::NOTICE;
			if (preg_match('/utf-8/i', $result['charset'])) {
				$severity = tx_reports_reports_status_Status::OK;
			}
			$message = t3lib_div::makeInstance(
    			't3lib_FlashMessage',
				'Content-Type:' . $result['mime'] . '<br />' . 
				'Charset:' . $result['charset'] . '<br />' 
//				. print_r($meta_report,true). '<br />' 
//				. str_replace(chr(10),'<br />',$result['headers'])
				,
				$file,
				$severity
			);
			$content .= $message->render();
			
		}
			
		return $this->getHeader('webservervars', $GLOBALS['LANG']->getLL('utf8status_webserver_vars'), $content, 2);
	}


	/**
	 *  Get the content type from the HTTP response
	 *  @param array $headers Response headers
	 *  @return array of parsed response header
	 */
	public function getContentType($headers) {
		
		if (!is_array($headers)) {
			$headers = t3lib_div::trimExplode(chr(10), $headers, TRUE);
		}

		$nlines = count($headers);
		for ($i = 0; $i < $nlines; $i++) {
			$line = $headers[$i];
			$search_str = 'Content-Type';
			$search_str_len = strlen($search_str);
			if ((strlen($line)>$search_str_len) && (substr_compare($line, $search_str, 0, 12, TRUE) == 0)) {
				$content_type = $line;
				break;
			} 
		}
		
		$matches = array();
		/* Get the MIME type and character set */
		preg_match( '@Content-Type:\s+([\w/+]+)(;\s*charset=(\S+))?@i', $content_type, $matches);
		
		$return_arr = array(
			'mime' => '??',
			'charset' => '??',
			'headers' => implode(chr(10),$headers) 
			);
			if (isset($matches[1])) {
				$return_arr['mime'] = $matches[1];
			}
			if (isset($matches[3])) {
				$return_arr['charset'] = $matches[3];
			}
			return $return_arr;
	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_tx_toolboxutf8_reports_status_mysqlutf8status.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/toolbox_utf8/reports/status/class.tx_toolboxutf8_reports_status_mysqlutf8status.php']);
}

?>