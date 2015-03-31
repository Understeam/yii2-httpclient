<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

$baseDir = dirname(__DIR__);

if(!is_dir($baseDir . '/vendor')) {
    echo "Composer requirements are not installed!\n";
    exit(1);
}

require($baseDir . '/vendor/autoload.php');
require($baseDir . '/vendor/yiisoft/yii2/Yii.php');

\Yii::setAlias('@tests', __DIR__);
