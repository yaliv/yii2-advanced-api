<?php

namespace api\modules\v1\controllers;

use yii\rest\Controller;

/**
 * Class AuthController
 * @package api\modules\v1\controllers
 * @author Haqqi <me@haqqi.net>
 */
class AuthController extends Controller {

  protected function verbs() {
    return [
      'login'           => ['post'],
      'logout'          => ['post'],
      'forgot-password' => ['post'],
      'reset-password'  => ['get', 'post'],
      'register'        => ['post'],
      'change-password' => ['post']
    ];
  }

  public function actionRegister() {
    return [];
  }

  public function actionLogin() {
    return [];
  }

  public function actionForgotPassword() {
    return [];
  }

  /**
   * @param $resetPasswordToken
   * 
   * This method accept GET and POST method. If it is GET, this
   * will return the status of token, wheter it is true or false.
   * 
   * @return array
   */
  public function actionResetPassword($resetPasswordToken) {
    return [];
  }

  public function actionChangePassword() {
    return [];
  }

  public function actionLogout() {
    return [];
  }
}