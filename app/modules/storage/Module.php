<?php

namespace app\modules\advert;

use Yii;
use yii\base\Exception;

/**
 * Модуль файлового хранилища.
 * В конфигурации приложения можно указать неограниченное количество файловых хранилищ.
 * Каждое файловое хранилище должно сопровождаться символьным кодом.
 */
class Module extends \yii\base\Module
{
    public $repository;

    public function init()
    {
        parent::init();

        $config = [
            'components' => []
        ];

        foreach ($this->repository as $code => $repo) {
            if (empty($repo['class'])) {
                $repo['class'] = 'app\modules\storage\components\Storage';
            }
            if (empty($repo['basePath']) || empty($repo['baseUrl'])) {
                throw new Exception();
            }
            $repo['code'] = $code;
            $config['components'][$code] = $repo;
        }

        Yii::configure($this, $config);
    }
}