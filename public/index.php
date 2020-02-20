<?php

use hiqdev\composer\config\Builder;
use Yiisoft\Di\Container;
use Yiisoft\Yii\Web\Application;
use Yiisoft\Yii\Web\ServerRequestFactory;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/globals.php';

// Don't do it in production, assembling takes it's time
//Builder::rebuild();
$container = new Container(require Builder::path('web'));

/**
 * @var array $params The variable is available after requiring config files.
 */

$debugEnabled = (bool)($params['debug_enabled'] ?? false) && class_exists(\Yiisoft\Yii\Debug\Debugger::class);


if ($debugEnabled) {
    $debugProvider = new \Yiisoft\Yii\Debug\DebugServiceProvider();
    $container->addProvider($debugProvider);

    $dispatcher = $container->get(\Psr\EventDispatcher\EventDispatcherInterface::class);
    $dispatcher->dispatch(new \Yiisoft\Yii\Debug\Event\ApplicationStartup());
}

try {
    $request = $container->get(ServerRequestFactory::class)->createFromGlobals();
    $container->get(Application::class)->handle($request);
} finally {
    $debugEnabled && $dispatcher->dispatch(new \Yiisoft\Yii\Debug\Event\ApplicationShutdown());
}
