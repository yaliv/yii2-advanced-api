<?php

namespace common\forms;

use common\models\User;
use yii\base\Model;

/**
 * Class ForgotPasswordForm
 * @package api\forms
 *
 */
class ForgotPasswordForm extends Model {

    const SCENARIO_FORGOT_LOGIN_USERNAME = 'submitLoginUsername';
    const SCENARIO_FORGOT_LOGIN_EMAIL    = 'submitLoginEmail';
  /**
   * @var $email
   */
  public $email;

  public function rules() {
    return [
      [['email'], 'required','on' => self::SCENARIO_FORGOT_LOGIN_EMAIL],
      [['email'], 'trim'],
      [['email'], 'email'],
      ['email', 
       'exist',
       'targetClass' => User::className(),
       'message' => 'Email / user does not exist',
       'filter' => ['status' => User::STATUS_ACTIVE]
      ],
    ];
  }

    public function scenarios()
    {
        $scenarios                                       = parent::scenarios();
        $scenarios[self::SCENARIO_FORGOT_LOGIN_EMAIL]    = ['email', 'password'];
        $scenarios[self::SCENARIO_FORGOT_LOGIN_USERNAME] = ['username', 'password'];

        return $scenarios;
    }
  
  public function getUser() {
    $user = User::findOne([
      'email' => $this->email,
      'status' => User::STATUS_ACTIVE
    ]);
    
    return $user;
  }

}