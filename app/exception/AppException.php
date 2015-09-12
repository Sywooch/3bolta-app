<?php
namespace app\exception;

use yii\base\Exception;

/**
 * Класс исключений приложения.
 * @todo логировать и отправлять e-mail при любом важном исключении (описанном в специальном массиве)
 */
class AppException extends Exception
{
    const UNKNOWN_ERROR = 0;
    const DATABASE_ERROR = 1;
    const VALIDATION_ERROR = 2;
    const DATA_ERROR = 3;

    /**
     * Обрабатывает сгенерированное исключение $ex.
     * Если исключение - не объект класса, то генерирует новое исключение.
     *
     * @param Exception $ex входящее исключение
     * @throws ImageException
     */
    public static function throwUp(\Exception $ex)
    {
        $calledClass = get_called_class();
        if (!($ex instanceof $calledClass)) {
            throw new $calledClass('', self::UNKNOWN_ERROR, $ex);
        }
        throw $ex;
    }
}