<?php
/**
 * Базовый контроллер для бекенда
 */

namespace backend\components;

use yii\web\Controller;
use yii\filters\AccessControl;

abstract class BaseBackendController extends Controller
{
    /**
     * @var string Шаблон по умолчанию.
     */
    public $layout = '@app/modules/backend/views/layouts/main';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [],
            ],
        ];
    }
}