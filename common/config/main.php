<?php

use yii\web\UrlNormalizer;

return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'normalizer'      => [
                'class'  => UrlNormalizer::className(),
                'action' => UrlNormalizer::ACTION_REDIRECT_PERMANENT
            ],
            'rules'           => [
                // rules described in each app
            ],
        ],
        'cache'      => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];
