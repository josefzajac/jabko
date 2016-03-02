<?php

ob_start();

$container = require __DIR__ . '/../app/bootstrap.php';
$container->getByType('Nette\Application\Application')->run();
$out1 = ob_get_contents();
ob_end_clean();

echo \zz\Html\HTMLMinify::minify($out1, [\zz\Html\HTMLMinify::OPTIMIZATION_ADVANCED]);
