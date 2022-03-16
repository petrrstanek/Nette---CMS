<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;


class Bootstrap
{
	public static function boot(): Configurator
	{
		$configurator = new Configurator;
		$appDir = dirname(__DIR__);

		$configurator->setTimeZone('Europe/Prague');
		$configurator->addParameters(['wwwDir' => dirname(__DIR__) . '/web']);

		$configurator->setDebugMode(true);
		if (isset($_ENV['APP_ENVIRONMENT']) && $_ENV['APP_ENVIRONMENT'] === 'DEV') {
			$configurator->setTempDirectory('/app-temp');
		} else {
			$configurator->setTempDirectory($appDir . '/temp');
		}

		$configurator->enableTracy($appDir . '/log');
		$configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();

		$configurator->addConfig($appDir . '/config/common.neon');
		$configurator->addConfig($appDir . '/config/services.neon');
		$configurator->addConfig($appDir . '/config/local.neon');

		return $configurator;
	}
}

use Tracy\Debugger;

Debugger::enable();