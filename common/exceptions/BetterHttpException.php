<?php

namespace common\exceptions;

use yii\web\HttpException;

/**
 * Class BetterHttpException
 * @package common\exceptions
 * @author Haqqi <haqqi@akupeduli.org>
 */
class BetterHttpException extends HttpException {
  private $_data;

  public function __construct($status, $message = null, $data = [], $code = 0, \Exception $previous = null) {
    parent::__construct($status, $message, $code, $previous);
    $this->_data = $data;
  }

  public function getData() {
    return $this->_data;
  }
}