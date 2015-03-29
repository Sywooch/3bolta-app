<?php
use app\components\migrations\AccessRules;

/**
 * Права доступа к торговым точкам
 */
class m150329_084812_partner__trade_point_access_rules extends AccessRules
{
    protected function getPermissions()
    {
        return [
            'backendViewTradePoints' => 'View trade points',
            'backendUpdateTradePoints' => 'Update trade points',
            'backendCreateTradePoints' => 'Create trade points',
        ];
    }
}
