<?php
namespace advert\exception;

use app\exception\AppException;

/**
 * Исключения для класса Image
 */
class ImageException extends AppException
{
    const IMAGE_NOT_FOUND = 100;
}