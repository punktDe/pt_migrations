<?php
$doctrineMigrationsPhar = 'phar://doctrine-migrations.phar';

require_once $doctrineMigrationsPhar . '/Doctrine/Common/ClassLoader.php';

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', $doctrineMigrationsPhar);
$classLoader->register();
$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\DBAL', $doctrineMigrationsPhar);
$classLoader->register();
$classLoader = new \Doctrine\Common\ClassLoader('Symfony', $doctrineMigrationsPhar);
$classLoader->register();

use Symfony\Component\Console\Helper\HelperSet;

// TODO: DialogHelper is still used by Doctrine Migration (Migrate, Status and Version)
// TODO: Depends on https://github.com/doctrine/migrations/pull/198
use Symfony\Component\Console\Helper\DialogHelper;

use Symfony\Component\Console\Application;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\DBAL\Migrations\Configuration\YamlConfiguration;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;

function getValueByPath(array &$array, $path)
{
	if (is_string($path)) {
		$path = explode('.', $path);
	} elseif (!is_array($path)) {
		throw new \InvalidArgumentException('getValueByPath() expects $path to be string or array, "' . gettype($path) . '" given.', 1304950007);
	}
	$key = array_shift($path);
	if (isset($array[$key])) {
		if (!empty($path)) {
			return is_array($array[$key]) ? getValueByPath($array[$key], $path) : null;
		}
		return $array[$key];
	} else {
		return null;
	}
}

// Read the project specific yaml configuration
// TODO: find a way to get the document root more easily
// TODO: make the configuration path configurable for the user
$documentRoot = realpath(getcwd() . '/../../../..');
$configurationPath = $documentRoot . '/../config/Configuration.yaml';
$globalConf = \Symfony\Component\Yaml\Yaml::parse($configurationPath);
$contextString = trim(getenv('TYPO3_CONTEXT'));

// Check the application context
$applicationContext = explode('/', $contextString, 2);
$settings = $globalConf['configuration']['nodes'];
$doctrineConf = $globalConf['configuration']['doctrine'];

if (! $contextString || ! (in_array($applicationContext[0], array('Production', 'Development', 'Testing')))) {
	printf(PHP_EOL . PHP_EOL . "\033[31mTYPO3_CONTEXT is empty or none of allowed contexts (Production, Development, Testing) \033[0m" . PHP_EOL);

	$hint = '';
	if (isset($settings) && is_array($settings)) {
		foreach ($settings as $rootContextName => $subContexts) {
			foreach ($subContexts as $subContextName => $context) {
				$hint .= '- ' . $rootContextName . '/' . $subContextName . PHP_EOL;
			}
		}
		$hint = "Following contexts are found from your configurations." . PHP_EOL . $hint;
		printf($hint . PHP_EOL);
	}
	exit;
}

$validatedSettings = getValueByPath($settings, $applicationContext);

if ($validatedSettings === null) {
	printf("\033[31mNot settings found for TYPO3_CONTEXT %s\033[0m" . PHP_EOL, $contextString);
	exit;
}

// Create db connection
$config = new \Doctrine\DBAL\Configuration();
$connectionParams = array(
	'dbname' => $validatedSettings['db'],
	'user' => $validatedSettings['user'],
	'password' => $validatedSettings['password'],
	'host' => $validatedSettings['host'],
	'driver' => 'pdo_mysql'
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

// Create the cli application
$helperSet = new HelperSet(array(
	'db' => new ConnectionHelper($conn),
	'dialog' => new DialogHelper(),
));

$cli = new Application('Doctrine Command Line Interface', \Doctrine\DBAL\Migrations\MigrationsVersion::VERSION);
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);

// Create the configuration object
$migrationConf = new YamlConfiguration($conn);
$migrationConf->setName($doctrineConf['name']);
$migrationConf->setMigrationsNamespace($doctrineConf['migrations_namespace']);
$migrationConf->setMigrationsTableName($doctrineConf['table_name']);

// Loops and registers for all specified directories
foreach ($doctrineConf['migrations_directory'] as $dir) {
	$migrationsDirectory = $documentRoot . '/' . $dir;
	$migrationConf->setMigrationsDirectory($migrationsDirectory);
	$migrationConf->registerMigrationsFromDirectory($migrationsDirectory);
}

// Possibility to register migration tasks via yaml
if (isset($doctrineConf['migrations']) && is_array($doctrineConf['migrations'])) {
	foreach ($doctrineConf['migrations'] as $migration) {
		$migrationConf->registerMigration($migration['version'], $migration['class']);
	}
}


// Setup and configure commands
$migrateCommand = new MigrateCommand();
$migrateCommand->setMigrationConfiguration($migrationConf);

$statusCommand = new StatusCommand();
$statusCommand->setMigrationConfiguration($migrationConf);

$versionCommand = new VersionCommand();
$versionCommand->setMigrationConfiguration($migrationConf);

$cli->addCommands(array($migrateCommand, $statusCommand, $versionCommand));

$cli->run();
