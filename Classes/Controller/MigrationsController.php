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

namespace PunktDe\PtMigrations\MigrationsController;

use PunktDe\PtMigrations\Domain\Model\MigrationState;


/**
 * Class implements controller for migrations Installation module
 *
 * @package Controller
 */
class Tx_PtMigrations_Controller_MigrationsController extends Tx_PtExtbase_Controller_AbstractActionController {

	public function indexAction() {
		$migrations = $this->getAllMigrations();

		$this->view->assign('numberAvailableMigrations', count($migrations));
		$this->view->assign('migrations', $migrations);
	}

	//Run the missed migrations and refresh the view in the backend
	public function runMigrationAction() {
		$this->executedMissedMigrations();
		$this->redirect('index');
	}

	/**
	 * @return array<MigrationState>
	 */
	//Load the migrations from the migrations folder in pt_campo
	protected function getAllMigrations() {
		$migrationsDirectory = __DIR__ . '/../../../pt_campo/Migrations/';
		$migrations = array();
		foreach(array_diff(scandir($migrationsDirectory), array('..', '.')) as $timestamp) {
			$migrations[] = new MigrationState(substr($timestamp, 7, 10));
		}
		return $migrations;
	}

	protected function executedMissedMigrations() {
		//Load the current environment
		$currentEnvironment = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext();
		$runMigrationCommand = "TYPO3_CONTEXT=$currentEnvironment ./migrate -n migrations:migrate 2>&1";
		$output = array();
		//Change the actual directory to the one where the command should be run
		chdir(__DIR__ . '/../../bin/');
		//Run the command in the console
		$this->runShellCommand($runMigrationCommand, $output, $exitCode);
		//This shows flash message with the console output when an error or a warning happens
		if ($exitCode == 0) {
			$messages = implode('<br>', $output);
			$this->addFlashMessage("Messages from migration command: $messages", 'Migrations successfully run');
		} else {
			$this->addFlashMessage("Error ($exitCode): <br>" . implode('<br>', $output), 'An Error occurred!',\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
		}
	}



	protected function runShellCommand($command, array &$output, &$exitCode) {
		exec($command, $output, $exitCode);
	}

}
