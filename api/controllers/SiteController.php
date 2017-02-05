<?php

namespace api\controllers;

use yii\rest\Controller;

/**
 * Site controller.
 */
class SiteController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = [
            'error',
        ];

        return $behaviors;
    }

    public function actionError()
    {
        $exception = \Yii::$app->errorHandler->exception;

        if ($exception !== null) {
            return [
                'name'   => $exception->getName(),
                'status' => $exception->statusCode,
            ];
        }
    }
}
