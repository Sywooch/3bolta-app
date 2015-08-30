<?php
namespace advert\assets;

/**
 * Ассетсы для карточки объявления
 */
class AdvertDetail extends \yii\web\AssetBundle
{
    public $sourcePath = '@advert/_assets/detail';

    public $baseUrl = '@web';

    public $css = [
        'css/styles.css',
    ];

    public $js = [
        'js/app.js',
    ];

    public $depends = [
        'app\assets\FrontendAssets',
    ];

    /**
     * @var boolean регистрировать отправку вопроса
     */
    protected static $_allowQuestion = false;

    /**
     * @var boolean регистрировать отправку ответа
     */
    protected static $_allowAnswer = false;

    public function init()
    {
        if (self::$_allowQuestion) {
            $this->js[] = 'js/question.js';
        }
        if (self::$_allowAnswer) {
            $this->js[] = 'js/answer.js';
        }

        parent::init();
    }
    /**
     * Регистрация ассетсов
     * @param \yii\web\View $view
     * @param boolean $allowQuestion если равно true, то зарегистрирует также скрипт для отправки вопроса
     * @param boolean $allowAnswer если равно true, то зарегистрирует также скрипт для отправки ответа на вопрос
     * @return type
     */
    public static function register($view, $allowQuestion = false, $allowAnswer = false)
    {
        if (!self::$_allowQuestion && $allowQuestion) {
            self::$_allowQuestion = $allowQuestion;
        }
        if (!self::$_allowAnswer && $allowAnswer) {
            self::$_allowAnswer = $allowAnswer;
        }
        return parent::register($view);
    }
}