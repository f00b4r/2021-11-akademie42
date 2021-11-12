<?php declare(strict_types = 1);

namespace App;

use Contributte\Bootstrap\ExtraConfigurator;
use Nette\Configurator;
use Nette\DI\Compiler;

class Bootstrap
{

	public static function boot(): Configurator
	{
		$configurator = new ExtraConfigurator();
		$configurator->setTempDirectory(__DIR__ . '/../var');

		$configurator->onCompile[] = function (ExtraConfigurator $configurator, Compiler $compiler): void {
			// Add env variables to config structure
			$compiler->addConfig(['parameters' => $configurator->getEnvironmentParameters()]);
		};

		// According to NETTE_DEBUG env
		$configurator->setEnvDebugMode();

		// Enable tracy and configure it
		$configurator->enableTracy(__DIR__ . '/../var');

		// Provide some parameters
		$configurator->addParameters([
			'rootDir' => realpath(__DIR__ . '/..'),
			'appDir' => __DIR__,
			'wwwDir' => realpath(__DIR__ . '/../www'),
		]);

		// Configuration
		$configurator->addConfig(__DIR__ . '/../config/config.neon');

		if (file_exists(__DIR__ . '/../config/local.neon')) {
			$configurator->addConfig(__DIR__ . '/../config/local.neon');
		}

		return $configurator;
	}

}
