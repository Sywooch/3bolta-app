<?php
use app\components\migrations\AccessRules;

class m150207_081427_advert__advert_access_rules extends AccessRules
{
    protected function getPermissions()
    {
        return [
            'backendViewAdvert' => 'View advert',
            'backendUpdateAdvert' => 'Update advert',
            'backendCreateAdvert' => 'Create advert',
        ];
    }
}
