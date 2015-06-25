<?php
namespace app\components;

use Yii;
use app\assets\FrontendAssets;

/**
 * Базовый контроллер для всех приложений
 */
abstract class Controller extends \yii\web\Controller
{
    /**
     * Получить URL к папке _assets
     *
     * @return string
     */
    public static function getFrontendAssetsUrl()
    {
        /* @var $assetManager \yii\web\AssetManager */
        $assetManager = Yii::$app->assetManager;
        $frontendAsset = new FrontendAssets();

        return $assetManager->getPublishedUrl($frontendAsset->sourcePath);
    }

    /**
     * Получить папку с шаблонами
     *
     * @return string
     */
    public function getViewPath()
    {
        if ($this->module->id == 'backend' || $this->module->id == Yii::$app->id) {
            // для модуля backend все остается по старому
            return parent::getViewPath();
        }
        return $this->module->getViewPath() . DIRECTORY_SEPARATOR . Yii::$app->id . DIRECTORY_SEPARATOR . $this->id;
    }
}