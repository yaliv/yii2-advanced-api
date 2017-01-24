<?php

namespace common\models;

use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * Class User
 * @package common\models
 * @author Haqqi <me@haqqi.net>
 *
 * @property string $id
 * @property string $username
 * @property string $email
 * @property string $passwordHash
 * @property string $passwordResetToken
 * @property string $status
 * @property string $authKey
 * @property string $createdAt
 * @property string $updatedAt
 */
class User extends ActiveRecord implements IdentityInterface
{

    const SCENARIO_CREATE           = 'create';
    const SCENARIO_EDIT             = 'edit';
    const SCENARIO_REGISTER_VIA_API = 'registerViaApi';

    const STATUS_NEW       = 'new';
    const STATUS_ACTIVE    = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_DELETED   = 'deleted';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::className(),
                'value'              => new Expression("'" . date('Y-m-d H:i:s') . "'"),
                'createdAtAttribute' => 'createdAt',
                'updatedAtAttribute' => 'updatedAt'
            ]
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        // never allow credential to be displayed using toArray
        unset($fields['passwordHash']);
        unset($fields['passwordResetToken']);
        unset($fields['authKey']);

        return $fields;
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username'           => 'Username',
            'email'              => 'Email',
            'passwordHash'       => 'Password Hash',
            'passwordResetToken' => 'Password Reset Token',
            'status'             => 'Status',
            'authKey'            => 'Auth Key',
            'createdAt'          => 'Created at',
            'updatedAt'          => 'Updated at'
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_REGISTER_VIA_API] = ['username', 'email', 'password'];

        return $scenarios;
    }


    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            ['email', 'email'],
            [['email', 'username'], 'unique']
        ];
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param $password
     */
    public function setPassword($password)
    {
        $this->passwordHash = \Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'passwordResetToken' => $token,
            'status'             => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire    = \Yii::$app->params['user.passwordResetTokenExpire'];

        return $timestamp + $expire >= time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->passwordResetToken = null;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return \Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->passwordResetToken = \Yii::$app->security->generateRandomString() . '_' . time();
    }

    public static function getPasswordRules()
    {
        return [
            [['password'], 'string', 'min' => 6],
            [['password'], 'required'],
            [['password'], 'trim']
        ];
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////// Relationship Area //////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function getDevices()
    {
        return $this->hasMany(Device::className(), ['userId' => 'id'])->inverseOf('user');
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////// End of Relationship Area ///////////////////////
    ////////////////////////////////////////////////////////////////////


    /////////////////////////////
    // Identity Interface Area //
    /////////////////////////////

    public static function findIdentity($id)
    {
        return self::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->authKey;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    ///////////////////////////////
    // End of Identity Interface //
    ///////////////////////////////
}
