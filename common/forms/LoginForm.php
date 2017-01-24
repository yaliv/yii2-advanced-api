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

    const SCENARIO_SUBMIT_LOGIN = 'submitLogin';
    /**
     * @var
     */
    public $email;
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
            [['email', 'password'], 'required'],
            [['email', 'password'], 'trim'],
            ['email', 'email'],
            ['email', 'exist', 'targetClass' => $this->_userClass, 'message' => 'Email / user does not exist'],
            ['email', 'validateActive'],
            ['password', 'validatePassword']
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->getUser()->validatePassword($this->{$attribute})) {
                $this->addError($attribute, 'Incorrect password');
            }
        }
    }

    public function validateActive($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->getUser()->status !== User::STATUS_ACTIVE) {
                $this->addError($attribute, 'User is not active or being suspended');
            }
        }
    }

    public function scenarios()
    {
        $scenarios                              = parent::scenarios();
        $scenarios[self::SCENARIO_SUBMIT_LOGIN] = ['email', 'password'];
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
            // get user class
            $class = $this->_userClass;

            // find one
            $this->_user = $class::findOne(['email' => $this->email]);
        }

        return $this->_user;
    }
}