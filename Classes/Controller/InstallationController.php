<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Michael Knoll <knoll@punkt.de>, punkt.de GmbH
*
*
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
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Class implements controller for migrations Installation module
 *
 * @package Controller
 */
class Tx_PtMigrations_Controller_InstallationController extends Tx_PtExtbase_Controller_AbstractActionController {

    public function indexAction() {
		$currentDirectory = __DIR__;
		$migrationsDirectory = $currentDirectory . '/../../../pt_campo/Migrations/';
		$migrations = array_diff(scandir($migrationsDirectory), array('..', '.'));
		$dbObject = $GLOBALS['TYPO3_DB']; /* @var \TYPO3\CMS\Core\Database\DatabaseConnection $dbObject */
		$executedMigrations = $dbObject->exec_SELECTgetRows('version', 'pt_migrations', '1=1');
		$this->view->assign('executedMigrations', $executedMigrations);
		$numberAvailableMigrations = count($migrations);
		$numberExecutedMigrations = count($executedMigrations);
		$this->view->assign('numberAvailableMigrations', $numberAvailableMigrations);
		$this->view->assign('numberExecutedMigrations', $numberExecutedMigrations);
		$this->view->assign('migrations', $this->compareMigrations($migrations, $executedMigrations));
    }

	protected function compareMigrations($migrations,$executedMigrations){
		$comparedMigrations = array();
		foreach ($migrations as $migration){
			$migrationState = array();
			$migrationState['version'] = $migration;
			$migrationState['timestamp'] = substr($migration, 0, 7) . " " . substr($migration, 7, 10);
			$executed = FALSE;
			foreach($executedMigrations as $executedMigration) {
				if (trim(substr($migration, 7, 10)) == trim($executedMigration['version'])) {
					$executed = TRUE;
				}
			}
			$migrationState['executed'] = $executed ? '1' : '0';
			$comparedMigrations[] = $migrationState;
		}
		return $comparedMigrations;
	}

}
