<?php

namespace storage;

use app\components\Module as BaseModule;
use Yii;
use yii\base\InvalidParamException;

/**
 * Модуль файлового хранилища.
 * В конфигурации приложения можно указать неограниченное количество файловых хранилищ.
 * Каждое файловое хранилище должно сопровождаться символьным кодом.
 */
class Module extends BaseModule
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
                $repo['class'] = 'storage\components\Storage';
            }
            if (empty($repo['basePath']) || empty($repo['baseUrl'])) {
                throw new InvalidParamException();
            }
            $repo['code'] = $code;
            $config['components'][$code] = $repo;
        }

        $newRepo = [];
        foreach ($this->repository as $repoCode => $repo) {
            $newRepo[$repoCode] = $repoCode;
        }
        $this->repository = $newRepo;

        Yii::configure($this, $config);
    }
}
