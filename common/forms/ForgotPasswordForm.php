<?php

namespace common\forms;

use common\models\User;
use yii\base\Model;

/**
 * Class ForgotPasswordForm
 * @package api\forms
 *
 */
class ForgotPasswordForm extends Model
{

    /**
     * const SCENARIO_FORGOT_LOGIN_USERNAME
     */
    const SCENARIO_FORGOT_LOGIN_USERNAME = 'forgotLoginUsername';
    const SCENARIO_FORGOT_LOGIN_EMAIL    = 'forgotLoginEmail';
    /**
     * @var $email
     */
    public $email;

    /**
     * @var $username
     */
    public $username;

    public function rules()
    {
        return [
          [['email'], 'required', 'on' => self::SCENARIO_FORGOT_LOGIN_EMAIL],
          [['username'], 'required', 'on' => self::SCENARIO_FORGOT_LOGIN_USERNAME],
          [['email', 'username'], 'trim'],
          [['email'], 'email'],
          [
            'email',
            'exist',
            'targetClass' => User::className(),
            'message'     => 'Email does not exist',
            'filter'      => ['status' => User::STATUS_ACTIVE]
          ],
          [
            'username',
            'exist',
            'targetClass' => User::className(),
            'message'     => 'Username does not exist',
            'filter'      => ['status' => User::STATUS_ACTIVE]
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

    public function getUser()
    {
        if ($this->scenario === self::SCENARIO_FORGOT_LOGIN_USERNAME) {
//            return $class::findByUsername($this->username);
            return User::findOne([
              'username' => $this->username,
              'status'   => User::STATUS_ACTIVE
            ]);
        }

        return User::findOne([
          'email'  => $this->email,
          'status' => User::STATUS_ACTIVE
        ]);
    }

}