<?php
use app\components\migrations\AccessRules;

class m150204_185928_auto__access_rules extends AccessRules
{
    protected function getPermissions()
    {
        return [
            'backendViewAuto' => 'View automobiles',
            'backendUpdateAuto' => 'Update automobiles',
        ];
    }
}
