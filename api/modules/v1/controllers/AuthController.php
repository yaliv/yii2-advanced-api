<?php

namespace api\modules\v1\controllers;

use api\config\ApiCode;
use api\forms\LoginForm;
use api\forms\RegisterUserForm;
use common\exceptions\BetterHttpException;
use common\models\Device;
use yii\base\InvalidConfigException;
use yii\rest\Controller;
use yii\web\HttpException;

/**
 * Class AuthController
 * @package api\modules\v1\controllers
 * @author Haqqi <me@haqqi.net>
 */
class AuthController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = [
            'login',
            'forgot-password',
            'reset-password',
            'register'
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'login'           => ['post'],
            'logout'          => ['post'],
            'forgot-password' => ['post'],
            'reset-password'  => ['get', 'post'],
            'register'        => ['post'],
            'change-password' => ['post']
        ];
    }

    /**
     * Register user through API.
     *
     * @return array the success response.
     * @throws BetterHttpException if the user failed to register.
     */
    public function actionRegister()
    {
        try {
            $userForm = new RegisterUserForm(\Yii::$app->request->headers->get('X-Device-identifier'));
        } catch (InvalidConfigException $e) {
            throw new HttpException(400, $e->getMessage(), ApiCode::DEVICE_IDENTIFIER_NOT_FOUND);
        }

        $userForm->load(\Yii::$app->request->post(), 'User');

        if ($userForm->validate()) {
            $user = $userForm->register();

            return [
                'name'    => 'Success',
                'message' => 'Registration as user ' . $user->username . ' success.',
                'code'    => ApiCode::REGISTER_SUCCESS,
                'status'  => 200,
                'data'    => $user->toArray([
                    'username',
                    'email',
                ])
            ];
        }

        throw new BetterHttpException(400, 'Registration failed.', [
            'User' => $userForm->getErrors()
        ], ApiCode::REGISTER_FAILED);
    }

    /**
     * Log user in through API. This will create a new device record with active status.
     * Any device that has same identifier will be deactivated.
     *
     * @return array the success response containing accessToken.
     * @throws BetterHttpException if the user failed to login.
     */
    public function actionLogin()
    {
        try {
            $loginForm = new LoginForm(\Yii::$app->request->headers->get('X-Device-identifier'));
        } catch (InvalidConfigException $e) {
            throw new HttpException(400, $e->getMessage(), ApiCode::DEVICE_IDENTIFIER_NOT_FOUND);
        }

        $loginByEmail = \Yii::$app->params['loginByEmail'];

        if ($loginByEmail) {
            $loginForm->setScenario(LoginForm::SCENARIO_SUBMIT_LOGIN_EMAIL);
        } else {
            $loginForm->setScenario(LoginForm::SCENARIO_SUBMIT_LOGIN_USERNAME);
        }

        $loginForm->load(\Yii::$app->request->post(), 'User');

        /*
         * Try to login
         */
        if ($loginForm->login()) {
            /**
             * @var $user User
             */
            $user = \Yii::$app->user->getIdentity();

            $msgLoginBy = $loginByEmail ? 'email ' . $user->email : 'username ' . $user->username;

            return [
                'name'    => 'Success',
                'message' => 'Login by ' . $msgLoginBy . ' success.',
                'code'    => ApiCode::LOGIN_SUCCESS,
                'status'  => 200,
                'data'    => $user->toArray([
                    // main fields
                    'hashId',
                    'username',
                    'email'
                ], [
                    // extra fields
                    'activeDevice'
                ])
            ];
        } else {
            throw new BetterHttpException(401, 'Login Failed', [
                'User' => $loginForm->getErrors()
            ], ApiCode::LOGIN_FAILED);
        }
    }

    /**
     * Log user out. This will need accessToken to log in the user via AccessTokenAuth.
     * The device that the user is using will be deactivated and the token will be invalid to do other request.
     *
     * @return array the success response.
     */
    public function actionLogout()
    {
        /**
         * @var $user User
         */
        $user           = \Yii::$app->user->getIdentity();
        $device         = $user->getActiveDevice();
        $device->status = Device::STATUS_INACTIVE;
        $device->save();

        \Yii::$app->user->logout();

        return [
            'name'    => 'Success',
            'message' => 'You have been logged out.',
            'code'    => ApiCode::LOGOUT_SUCCESS,
            'status'  => 200,
        ];
    }

    public function actionForgotPassword()
    {
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
    public function actionResetPassword($resetPasswordToken)
    {
        return [];
    }

    public function actionChangePassword()
    {
        return [];
    }
}
