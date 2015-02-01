<?php

use yii\db\Migration;

/**
 * Права доступа к файловому хранилищу
 */
class m150201_081020_storage__access_rules extends app\components\migrations\AccessRules
{
    protected function getPermissions()
    {
        return [
            'backendViewFile' => 'View storage files',
            'backendDeleteFile' => 'Delete storage files',
            'backendUploadFile' => 'Upload storage files',
        ];
    }
}
