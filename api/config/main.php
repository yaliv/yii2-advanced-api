<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'app-api',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap'           => [
        'log',
        'v1'
    ],
    'modules'             => [
        'v1' => [
            'class' => 'api\modules\v1\Module'   // here is our v1 modules
        ]
    ],
    'components'          => [
        'request'      => [
            // no need CSRF token
            'enableCsrfValidation' => false,
        ],
        'user'         => [
            'identityClass'   => 'api\models\User',
            'enableAutoLogin' => false,
            'enableSession'   => false
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager'   => [
            // to improve the security
            'enableStrictParsing' => true
        ],
    ],
    'params'              => $params,
];
