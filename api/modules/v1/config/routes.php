<?php

return [
  /* only allow POST */
  'v1/auth/login'                                       => 'v1/auth/login',
  /* only allow POST */
  'v1/auth/logout'                                      => 'v1/auth/logout',
  /* allow only POST */
  'v1/auth/forgot-password'                             => 'v1/auth/forgot-password',
  /* allow GET and POST */
  'v1/auth/reset-password/<resetPasswordToken:[\w\W]+>' => 'v1/auth/reset-password',
  /* allow only POST */
  'v1/auth/change-password'                             => 'v1/auth/change-password',
  /* allow only POST */
  'v1/auth/register'                                    => 'v1/auth/register',

];
