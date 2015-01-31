<?php

use app\components\migrations\AccessRules;

/**
 * Права доступа к категориям
 */
class m150131_144335_advert__access_rules extends AccessRules
{
    public function getPermissions()
    {
        return [
            'backendViewAdvertCategory' => 'View advert category',
            'backendCreateAdvertCategory' => 'Create advert category',
            'backendUpdateAdvertCategory' => 'Update advert category',
        ];
    }
}
