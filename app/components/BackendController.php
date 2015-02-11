<?php
namespace app\components;

use Yii;
use yii\filters\AccessControl;

/**
 * Базовый контроллер для бекенда
 */
abstract class BackendController extends Controller
{
    /**
     * Перевод сущности в родительном падеже
     * @return string
     */
    public function getSubstanceName()
    {
        return '';
    }

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