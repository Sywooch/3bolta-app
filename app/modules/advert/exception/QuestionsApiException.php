<?php
namespace advert\exception;

use app\exception\AppException;

/**
 * Исключения для класса QuestionsApi
 */
class QuestionsApiException extends AppException
{
    const ADVERT_NOT_FOUND = 100;
    const QUESTION_NOT_FOUND = 200;
}