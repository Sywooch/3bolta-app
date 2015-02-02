<?php
use app\components\migrations\AccessRules;

/**
 * Права доступа к справочникам
 */
class m150202_181750_handbook__access_rules extends AccessRules
{
    protected function getPermissions()
    {
        return [
            'backendViewHandbookValues' => 'View handbook values',
            'backendCreateHandbookValues' => 'Create handbook values',
            'backendUpdateHandbookValues' => 'Update handbook values',
        ];
    }

}
