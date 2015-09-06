<?php

namespace advert;

use app\components\Module as BaseModule;
use sammaye\solr\Client;
use Yii;

/**
 * Модуль объявлений
 */
class Module extends BaseModule
{
    /**
     * @var array параметры для подключения к Solr
     */
    public $solrParams;

    public function init()
    {
        parent::init();

        Yii::configure($this, \yii\helpers\ArrayHelper::merge(include __DIR__ . '/config.php', [
            'components' => [
                'partsIndex' => [
                    'options' => [
                        'endpoint' => [
                            'parts' => $this->solrParams['parts'],
                        ]
                    ]
                ]
            ]
        ]));
    }
}