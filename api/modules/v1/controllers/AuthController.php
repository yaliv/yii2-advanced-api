<?php

namespace api\modules\v1\controllers;

use api\config\ApiCode;
use api\forms\LoginForm;
use api\forms\RegisterUserForm;
use common\exceptions\BetterHttpException;
use common\forms\ForgotPasswordForm;
use common\forms\ResetPasswordForm;
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

    /**
     * Accept form submission of email. The system will generate passwordResetToken that can be sent via email.
     *
     * @return array success response
     * @throws BetterHttpException if email is not filled
     * @throws NotFoundHttpException if user is not found from the email
     */
    public function actionForgotPassword()
    {
        $forgotForm = new ForgotPasswordForm();

        $ByEmail = \Yii::$app->params['loginByEmail'];

        if ($ByEmail) {
            $forgotForm->setScenario(ForgotPasswordForm::SCENARIO_FORGOT_LOGIN_EMAIL);
        } else {
            $forgotForm->setScenario(ForgotPasswordForm::SCENARIO_FORGOT_LOGIN_USERNAME);
        }

        $forgotForm->load(\Yii::$app->request->post(), 'User');

        if ($forgotForm->validate()) {
            $user = $forgotForm->getUser();
            $user->generatePasswordResetToken();
            if ($user->save()) {
                // @todo: send password reset link to email
                // for now, just assume that you will create the passwordResetToken and log it to get it for reset password
                return [
                  'name'    => 'Success',
                  'message' => 'Your reset password link has been sent to email ' . $forgotForm->email,
                  'code'    => ApiCode::FORGOT_PASSWORD_SUCCESS,
                  'status'  => 200
                ];
            }
        }

        throw new BetterHttpException(401, 'Forgot Failed', [
          'User' => $forgotForm->getErrors()
        ], ApiCode::FORGOT_PASSWORD_FAILED);
    }

    /**
     * @param $resetPasswordToken
     *
     * This method accept GET and POST method. If it is GET, this
     * will return the status of token, whether it is true or false.
     *
     * @return array
     */
    public function actionResetPassword($resetPasswordToken)
    {
        try {
            $passwordForm = new ResetPasswordForm($resetPasswordToken);
        } catch (InvalidConfigException $e) {
            throw new HttpException(400, $e->getMessage(), ApiCode::RESET_PASSWORD_TOKEN_INVALID);
        }

        // this will catch POST request
        if (\Yii::$app->request->isPost) {

            $passwordForm->load(\Yii::$app->request->post(), 'User');

            if ($passwordForm->validate() && $passwordForm->resetPassword()) {
                return [
                  'name'    => 'Success',
                  'message' => 'You can sign in using new password.',
                  'code'    => ApiCode::RESET_PASSWORD_SUCCESS,
                  'status'  => 200,
                ];
            }
            throw new BetterHttpException(400, 'Reset password failed.', ['User' => $passwordForm->getErrors()],
              ApiCode::FORGOT_PASSWORD_FAILED);
        }

        return [
          'name'    => 'Success',
          'message' => 'Password reset token is valid.',
          'code'    => ApiCode::RESET_PASSWORD_TOKEN_VALID,
          'status'  => 200,
          'data'    => [
            'resetPasswordToken' => $resetPasswordToken
          ]
        ];
    }

    public function actionChangePassword()
    {
        return [];
    }
}
