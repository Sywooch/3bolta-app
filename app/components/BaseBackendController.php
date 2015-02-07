<?php
namespace app\components;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * Базовый контроллер для бекенда
 */
abstract class BaseBackendController extends Controller
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

    public function getViewPath()
    {
        if ($this->module->id == 'backend') {
            // для модуля backend все остается по старому
            return parent::getViewPath();
        }
        return $this->module->getViewPath() . DIRECTORY_SEPARATOR . Yii::$app->id . DIRECTORY_SEPARATOR . $this->id;
    }
}