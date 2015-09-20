<?php
namespace user\exception;

use app\exception\AppException;

/**
 * Исключения для класса UserApi
 */
class UserApiException extends AppException
{
    const REGISTRATION_ERROR = 100;
    const CONFIRMATION_ERROR = 200;
}