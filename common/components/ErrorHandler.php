<?php

namespace common\components;

use common\exceptions\BetterHttpException;

class ErrorHandler extends \yii\web\ErrorHandler {
  protected function convertExceptionToArray($exception) {
    $array = parent::convertExceptionToArray($exception);
    
    if($exception instanceof BetterHttpException) {
      $array['data'] = $exception->getData();
    }
    
    return $array;
  }
}