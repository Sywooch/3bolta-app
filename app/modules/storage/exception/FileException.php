<?php
namespace storage\exception;

use app\exception\AppException;

/**
 * Исключения для класса File
 */
class FileException extends AppException
{
    const FILE_COPY_ERROR = 100;
}
