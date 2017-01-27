<?php

namespace common\forms;

use common\models\User;
use yii\base\Model;

/**
 * Class LoginForm
 * @package common\forms
 * @author Haqqi <me@haqqi.net>
 */
class LoginForm extends Model
{

    const SCENARIO_SUBMIT_LOGIN_USERNAME = 'submitLoginUsername';
    const SCENARIO_SUBMIT_LOGIN_EMAIL    = 'submitLoginEmail';
    /**
     * @var
     */
    public $email;

    /**
     * @var
     */
    public $username;

    /**
     * @var
     */
    public $password;


    /**
     * @var User
     */
    protected $_user;

    /** @var string */
    protected $_userClass;

    public function __construct(array $config = [])
    {
        $this->_userClass = User::className();

        parent::__construct($config);
    }


    public function rules()
    {
        return [
          [['email', 'password'], 'required', 'on' => self::SCENARIO_SUBMIT_LOGIN_EMAIL],
          [['username', 'password'], 'required', 'on' => self::SCENARIO_SUBMIT_LOGIN_USERNAME],
          [['email', 'password', 'username'], 'trim'],
          ['email', 'email'],
          [
            'email',
            'exist',
            'targetClass' => $this->_userClass,
            'on'          => self::SCENARIO_SUBMIT_LOGIN_EMAIL,
            'message'     => 'Email does not exist'
          ],
          [
            'username',
            'exist',
            'targetClass' => $this->_userClass,
            'on'          => self::SCENARIO_SUBMIT_LOGIN_USERNAME,
            'message'     => 'User does not exist'
          ],
          ['email', 'validateActive', 'on' => self::SCENARIO_SUBMIT_LOGIN_EMAIL],
          ['username', 'validateActive', 'on' => self::SCENARIO_SUBMIT_LOGIN_USERNAME],
          ['password', 'validatePassword']
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->getUser()->validatePassword($this->{$attribute})) {
                $field = ($this->scenario === self::SCENARIO_SUBMIT_LOGIN_EMAIL) ? 'email' : 'username';
                $this->addError($attribute, 'Incorrect ' . $field . ' or password.');
            }
        }
    }

    public function validateActive($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->getUser()->status !== User::STATUS_ACTIVE) {
                $field = ($this->scenario === self::SCENARIO_SUBMIT_LOGIN_EMAIL) ? 'email' : 'username';
                $this->addError($attribute, 'User ' . $field . ' is being suspended');
            }
        }
    }

    public function scenarios()
    {
        $scenarios                                       = parent::scenarios();
        $scenarios[self::SCENARIO_SUBMIT_LOGIN_EMAIL]    = ['email', 'password'];
        $scenarios[self::SCENARIO_SUBMIT_LOGIN_USERNAME] = ['username', 'password'];

        return $scenarios;
    }

    public function setUserClass($userClass)
    {
        $this->_userClass = $userClass;
    }

    /**
     * Login and setup a new device
     *
     * @return bool
     */
    public function login()
    {
        if ($this->validate()) {
            // login to Yii2 system
            // @todo: implement this in backend
            return \Yii::$app->user->login($this->getUser());
        }
        return false;
    }

    /**
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $class = $this->_userClass;
            if ($this->scenario === self::SCENARIO_SUBMIT_LOGIN_USERNAME) {
                return $class::findByUsername($this->username);
            }

            return $this->_user = $class::findByEmail($this->email);
        }

        return $this->_user;
    }
}