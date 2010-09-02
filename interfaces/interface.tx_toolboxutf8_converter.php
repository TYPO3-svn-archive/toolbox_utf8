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
 * interface.tx_toolboxutf8_converter.php
 * 
 * Interface for the converter
 *  
 * @version 	$Id$
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public License, version 2 $Id: $
 * 
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */

/**
 * Interface for the converter
 * 
 * @author 		Marc Bastian Heinrichs <marc.bastian@heinrichs-mail.de>
 * @package		TYPO3
 * @subpackage	toolbox_utf8
 */
interface tx_toolboxutf8_ConverterInterface {
	
	public function echoContent($content, $eol = TRUE);
	
	/**
	 * @return boolean
	 */
	public function getAlterCharsetId();
	
	/**
	 * @return boolean
	 */
	public function getErrorReportingEnabled();

	
	/**
	 * @return boolean
	 */
	public function getForceWriteToDb();
	
	/**
	 * @return int
	 */
	public function getMaxRows();
	
	/**
	 * @return boolean
	 */
	public function getVerbose();
	
	/**
	 * @return boolean
	 */
	public function getVerboseDetails();
	
	/**
	 * Enter description here...
	 * 
	 * @return string
	 *
	 */
	public function getEol();
	
	public function getLineMarker();
	
	public function getInteractiveModeAllowed();
	
	public function updateProgressBar();
	
}

?>