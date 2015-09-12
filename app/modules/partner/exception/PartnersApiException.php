<?php
namespace partner\exception;

use app\exception\AppException;

/**
 * Исключения для класса PartnersApi
 */
class PartnersApiException extends AppException
{
    const USER_TYPE_ERROR = 100;
}