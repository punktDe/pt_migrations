<?php

namespace PunktDe\PtMigrations\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2015 Michael Lihs, Sebastian Helzle, punkt.de GmbH <extensions@punkt.de>
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

use PunktDe\PtMigrations\Domain\Model\MigrationState;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class implements controller for migrations module
 *
 * @package Controller
 */
class MigrationsController extends ActionController {

	/**
	 * Path to the yaml configuration
	 * TODO: make this configurable
	 *
	 * @var string
	 */
	protected $configurationPath = '../config/Configuration.yaml';

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * Loads extension configuration from yaml file
	 *
	 * @return array
	 */
	protected function getConfiguration() {
		if (empty($this->configuration)) {
			try {
				chdir(__DIR__ . '/../../bin/');

				$doctrineMigrationsPhar = 'phar://doctrine-migrations.phar';
				require_once $doctrineMigrationsPhar . '/Doctrine/Common/ClassLoader.php';

				$classLoader = new \Doctrine\Common\ClassLoader('Symfony', $doctrineMigrationsPhar);
				$classLoader->register();

				$configurationFile = PATH_site . $this->configurationPath;
				$parsedConfiguration = \Symfony\Component\Yaml\Yaml::parse($configurationFile);

				if (array_key_exists('configuration', $parsedConfiguration) && array_key_exists('doctrine', $parsedConfiguration['configuration'])) {
					$this->configuration = $parsedConfiguration['configuration']['doctrine'];
				}
			} catch (\Exception $e) {
			}
		}
		return $this->configuration;
	}

	/**
	 * Shows overview of existing migrations
	 */
	public function indexAction() {
		$migrations = $this->getAllMigrations();

		$this->view->assign('numberAvailableMigrations', count($migrations));
		$this->view->assign('migrations', $migrations);
	}

	/**
	 * Run the missing migrations and refresh the view in the backend
	 */
	public function runMigrationAction() {
		$this->executedMissedMigrations();
		$this->redirect('index');
	}

	/**
	 * Load the migrations from the migrations folders defined in the configuration
	 * TODO: split migrations by their directories
	 * TODO: read information what each migrations does
	 * @return array<MigrationState>
	 */
	protected function getAllMigrations() {
		$configuration = $this->getConfiguration();
		$migrations = array();

		if (array_key_exists('migrations_directory', $configuration)) {
			foreach ($configuration['migrations_directory'] as $migrationsDirectory) {
				foreach (array_diff(scandir(PATH_site . $migrationsDirectory), array('..', '.')) as $timestamp) {
					$migrations[] = new MigrationState(substr($timestamp, 7, 10));
				}
			}
		}
		return $migrations;
	}

	/**
	 * @return void
	 */
	protected function executedMissedMigrations() {
		//Load the current environment
		$currentEnvironment = GeneralUtility::getApplicationContext();
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
			$this->addFlashMessage("Error (" . $exitCode . "): <br>" . implode('<br>', $output), 'An Error occurred!', FlashMessage::ERROR);
		}
	}

	/**
	 * @param string $command
	 * @param array $output
	 * @param int $exitCode
	 * @return void
	 */
	protected function runShellCommand($command, array &$output, &$exitCode) {
		exec($command, $output, $exitCode);
	}

}
