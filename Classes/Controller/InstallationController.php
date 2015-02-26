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
		$migrations = $this->getAllMigrations();
		$executedMigrations = $this->getExecutedMigrations();
		$comparedMigrations = $this->compareMigrations($migrations, $executedMigrations);

		$this->view->assign('executedMigrations', $executedMigrations);
		$this->view->assign('numberAvailableMigrations', count($migrations));
		$this->view->assign('numberExecutedMigrations', count($executedMigrations));
		$this->view->assign('migrations', $comparedMigrations);
    }

	public function runMigrationAction() {
		$this->executedMissedMigrations();
		$this->redirect('index');
	}

	protected function compareMigrations($migrations,$executedMigrations){
		$comparedMigrations = array();
		foreach ($migrations as $migration){
			$migrationState = array(
				'version' => $migration,
				'timestamp' => substr($migration, 7, 10),
				'executed' => '0'
			);
			foreach($executedMigrations as $executedMigration) {
				if (trim(substr($migration, 7, 10)) == trim($executedMigration['version'])) {
					$migrationState['executed'] = '1';
				}
			}
			$comparedMigrations[] = $migrationState;
		}
		return $comparedMigrations;
	}

	/**
	 * @return array
	 */
	protected function getAllMigrations() {
		$migrationsDirectory = __DIR__ . '/../../../pt_campo/Migrations/';
		return array_diff(scandir($migrationsDirectory), array('..', '.'));
	}

	/**
	 * @return array|NULL
	 */
	protected function getExecutedMigrations() {
		$dbObject = $GLOBALS['TYPO3_DB']; /* @var \TYPO3\CMS\Core\Database\DatabaseConnection $dbObject */
		return $dbObject->exec_SELECTgetRows('version', 'pt_migrations', '1=1');
	}

	protected function executedMissedMigrations() {
		$currentEnvironment = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext();
		$runMigrationCommand = "TYPO3_CONTEXT=$currentEnvironment ./migrate -n migrations:migrate 2>&1";
		$output = array();
		chdir(__DIR__ . '/../../bin/');
		$this->runShellCommand($runMigrationCommand, $output, $exitCode);
		if ($exitCode == 0) {
			$messages = implode('<br>', $output);
			$this->addFlashMessage("Messages from migration command: $messages", 'Migrations successfully run');
		} else {
			$this->addFlashMessage("Error ($exitCode): <br>" . implode('<br>', $output), 'An Error occurred!',\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
		}
	}



	protected function runShellCommand($command, array &$output, &$exitCode) {
		exec($command, $output, $exitCode);
		/*
		$proc = popen($command, 'r');
		while (!feof($proc)) {
			$output .= fread($proc, 4096);
		}
		$output = explode("\n", $output);
		pclose($proc);
		$exitCode = trim(exec('echo $?'));
		*/
	}

}
