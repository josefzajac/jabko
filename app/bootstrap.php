<?php

require __DIR__ . '/../vendor/autoload.php';

if (!function_exists('dump')) {
    function dump($args)
    {
        return \Tracy\Debugger::dump($args);
    }
}
function d($a) { return dump($a); }
function dd($a) { dump($a);die(); }
if (!function_exists('barDump')) {
    function barDump($args)
    {
        return \Tracy\Debugger::barDump($args);
    }
}
function b($a) { return barDump($a); }

// header('Access-Control-Allow-Origin:*');

$configurator = new Nette\Configurator();
$configurator->setDebugMode(0 ?
        \Tracy\Debugger::DEVELOPMENT : \Tracy\Debugger::PRODUCTION);
//$configurator->setDebugMode(!(isset($_COOKIE['enableSecretDebug']) && $_COOKIE['enableSecretDebug'] == 'ingressTeam') ?
//    \Tracy\Debugger::DEVELOPMENT : \Tracy\Debugger::PRODUCTION);

$configurator->enableDebugger(__DIR__ . '/../log');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->addDirectory(__DIR__ . '/../libs')
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
