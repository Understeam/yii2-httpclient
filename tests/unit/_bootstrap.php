<?php

$config = [
    'id' => 'app-console',
    'basePath' => \Yii::getAlias('@tests'),
    'runtimePath' => \Yii::getAlias('@tests/_output'),
    'components' => [
        'httpclient' => 'understeam\httpclient\Client',
    ],
];

new yii\console\Application($config);
