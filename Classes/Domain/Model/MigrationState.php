<?php

namespace PunktDe\PtMigrations\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Ivan Riera Sanchez <riera-sanchez@punkt.de>, punkt.de
 *
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 *
 *
 * @package pt_migrations
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class MigrationState {

	/**
	 * Timestamp
	 *
	 * @var integer
	 */
	protected $timestamp;


	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $dbObj;



	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct($timestamp) {
		$this->timestamp = $timestamp;
		$this->dbObj = $GLOBALS['TYPO3_DB'];
	}



	/**
	 *
	 *
	 * @return string
	 */
	public function isMigrated() {
		$migration = $this->dbObj->exec_SELECTgetSingleRow('version', 'pt_migrations', 'version="' . $this->timestamp . '"');
		if (!empty($migration)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}



	/**
	 * Returns the timestamp
	 *
	 * @return float $timestamp
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

}
