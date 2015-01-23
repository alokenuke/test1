<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

use kartik\mpdf\Pdf;

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && strpos(Yii::$app->requestedRoute, "api")!==false) {
                    $response->data["statusCode"] = $response->statusCode;
                }
            },
        ],
        'request' => [
            'parsers' => [
            'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'user' => [
            'identityClass' => 'backend\models\User',
            'enableAutoLogin' => true,
        ],
        'pdf' => [
            'class' => Pdf::classname(),
            'destination' => Pdf::DEST_BROWSER,
            // refer settings section for all configuration options
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
		    ['class' => 'yii\rest\UrlRule', 'controller' => ['api', 'users', 'userlevels', 'usergroups', 'roles', 'items', 'tags', 'labeltemplates'], 'pluralize'=>false, 'extraPatterns' => ['GET search' => 'search']],
		    'api/fields' => 'api/fields',
                    'dashboard' => 'site/dashboard',
                    '<controller>/<action>' => '<controller>/<action>',
                    '<controller:\w+>/' => '<controller>/index',
                    '<controller:\w+>/<id:\d+>' => '<controller>/view',
                    '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                    '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                    '' => 'site/index',
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    'params' => $params,
];
