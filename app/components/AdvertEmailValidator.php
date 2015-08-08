<?php
namespace app\components;

use Yii;

use advert\models\PartAdvert;
use user\models\User;

/**
 * Объявление нельзя публиковать на е-mail, на который уже зарегистрирован пользователь,
 * либо на которое уже существует другое объявление незарегистрированного пользователя.
 */
class AdvertEmailValidator extends \yii\validators\Validator
{
    /**
     * Валидация
     *
     * @param mixed $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $value = (string) strtolower($model->{$attribute});

        if ($value) {
            $hasError = false;

            if (User::find()->where(['email' => $value])->exists()) {
                $hasError = true;
            }

            if (!$hasError && PartAdvert::find()->where(['user_email' => $value])->exists()) {
                $hasError = true;
            }

            if ($hasError) {
                $this->addError($model, $attribute,
                    Yii::t(
                        'main',
                        'Can\'t create advert to this e-mail. <a href="{url}">Details...</a>',
                        [
                            'url' => \yii\helpers\Url::toRoute(Yii::$app->params['rulesRoute'])
                        ]
                    )
                );
            }
        }
    }
}