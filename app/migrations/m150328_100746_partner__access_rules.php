<?php
use app\components\migrations\AccessRules;

/**
 * Права доступа к партнерам в бекенде
 */
class m150328_100746_partner__access_rules extends AccessRules
{
    protected function getPermissions()
    {
        return [
            'backendViewPartners' => 'View partners',
            'backendUpdatePartners' => 'Update partners',
            'backendCreatePartners' => 'Create partners',
        ];
    }
}
