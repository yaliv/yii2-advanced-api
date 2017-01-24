<?php

namespace api\filters;

use yii\filters\auth\AuthMethod;

/**
 * Class AccessTokenAuth
 * @package api\filters
 *
 * @author Haqqi <me@haqqi.net>
 *
 * @property string $accessTokenKey The key of access token in header
 */
class AccessTokenAuth extends AuthMethod
{

    public $accessTokenKey = 'X-Device-accessToken';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $accessToken = $request->headers->get($this->accessTokenKey);
        if ($accessToken !== null) {
            $identity = $user->loginByAccessToken($accessToken, AccessTokenAuth::className());
            if ($identity === null) {
                $this->handleFailure($identity);
            }
            return $identity;
        }
        return null;
    }
}