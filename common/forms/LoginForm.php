<?php

namespace common\forms;

use common\models\User;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\validators\EmailValidator;
use yii\web\IdentityInterface;

/**
 * Class LoginForm
 * @package common\forms
 * @author Haqqi <me@haqqi.net>
 */
class LoginForm extends Model
{
    const LOGIN_WITH_EMAIL    = 'email';
    const LOGIN_WITH_USERNAME = 'username';
    const LOGIN_WITH_BOTH     = 'both';

    /** @var string */
    public $username;
    /** @var string */
    public $password;

    /** @var string */
    protected $_userClass;
    /** @var User */
    protected $_user;

    private $_loginWith;

    public function __construct(array $config = [])
    {
        // set default user class to common user
        $this->_userClass = User::className();

        // set loginWith through params
        $this->_loginWith = ArrayHelper::getValue(
            \Yii::$app->params, 'auth.loginWith', self::LOGIN_WITH_BOTH
        );

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
     * Get login column based on the config params
     *
     * @since 0.1.0
     * @return string
     * @throws InvalidConfigException
     */
    protected function getLoginColumn()
    {
        if ($this->_loginWith == self::LOGIN_WITH_BOTH) {
            // check if it is email or not using email validator
            $emailValidator = new EmailValidator();

            if ($emailValidator->validate($this->username)) {
                return 'email';
            } else {
                return 'username';
            }
        } elseif ($this->_loginWith == self::LOGIN_WITH_USERNAME) {
            return 'username';
        } elseif ($this->_loginWith == self::LOGIN_WITH_EMAIL) {
            return 'email';
        } else {
            throw new InvalidConfigException('auth.LoginWith param is not using supported method.');
        }
    }

    /**
     * Get the user based on the class
     *
     * @author Haqqi <me@haqqi.net>
     * @since 0.1.0
     * @return User
     * @throws InvalidConfigException if the userClass is not implement Identity Interface
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            // IdentityInterface class
            $class = $this->_userClass;
            // login column
            $loginColumn = $this->getLoginColumn();

            $this->_user = $class::findOne([$loginColumn => $this->username]);
            
        }

        return $this->_user;
    }
}
