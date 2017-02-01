<?php

namespace common\forms;

use common\models\User;
use yii\base\Model;
use yii\validators\EmailValidator;

/**
 * Class LoginForm
 * @package common\forms
 * @author Haqqi <me@haqqi.net>
 */
class LoginForm extends Model
{
    /** @var string */
    public $username;
    /** @var string */
    public $password;

    /** @var string */
    protected $_userClass;
    /** @var User */
    protected $_user;

    public function __construct(array $config = [])
    {
        // set default user class to common user
        $this->_userClass = User::className();

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username', 'password'], 'trim'],
            [['username'], 'validateExist'],
            [['password'], 'validatePassword'],
            [['username'], 'validateActive']
        ];
    }

    /**
     * Setter method
     *
     * @param $userClass
     */
    public function setUserClass($userClass)
    {
        $this->_userClass = $userClass;
    }

    /**
     * Validate whether the username exists or not. We do not use core validator
     * because we need to distinguish between username or email based on the input
     * of the user. Therefore, we use the getUser if we can return the existing
     * user or not.
     *
     * @param $attribute
     * @param $params
     */
    public function validateExist($attribute, $params)
    {
        if ($this->getUser() === null) {
            $this->addError($attribute, 'User ' . $this->{$attribute} . ' is not found.');
        }
    }

    /**
     * This validation will only be processed if there is no error before it
     *
     * @author Haqqi <me@haqqi.net>
     * @since 0.1.0
     * @param $attribute
     * @param $params
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->getUser()->validatePassword($this->{$attribute})) {
                $this->addError($attribute, 'Incorrect username/email or password.');
            }
        }
    }

    /**
     * This validation will only be processed if there is no error before it
     *
     * @author Haqqi <me@haqqi.net>
     * @since 0.1.0
     * @param $attribute
     * @param $params
     */
    public function validateActive($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->getUser()->status !== User::STATUS_ACTIVE) {
                $this->addError($attribute, 'User ' . $this->{$attribute} . ' is being suspended');
            }
        }
    }

    /**
     * Process the login through Yii2 system
     *
     * @author Haqqi <me@haqqi.net>
     * @since 0.1.0
     * @return bool
     */
    public function login()
    {
        if ($this->validate()) {
            return \Yii::$app->user->login($this->$this->getUser());
        }
        return false;
    }

    /**
     * Get the user based on the class
     *
     * @author Haqqi <me@haqqi.net>
     * @since 0.1.0
     * @return User
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $class = $this->_userClass;

            // must check wheter it is email or username
            $emailValidator = new EmailValidator();

            // if it is email, find user by email
            if ($emailValidator->validate($this->username)) {
                $this->_user = $class::findOne(['email' => $this->username]);
            } else {
                $this->_user = $class::findOne(['username' => $this->username]);
            }
        }

        return $this->_user;
    }
}
