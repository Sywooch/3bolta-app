<?php
namespace app\components;

use Yii;

use app\components\PhoneValidator;

use advert\models\PartAdvert;
use user\models\User;

/**
 * Объявление нельзя публиковать на телефон, на который уже зарегистрирован пользователь,
 * либо на которое уже существует другое объявление незарегистрированного пользователя.
 */
class AdvertPhoneValidator extends \yii\validators\Validator
{
    /**
     * Валидация
     *
     * @param mixed $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $value = PhoneValidator::getCanonicalPhone($model->{$attribute});

        if ($value) {
            $hasError = false;

            if (User::find()->where(['phone_canonical' => $value])->exists()) {
                $hasError = true;
            }

            if (!$hasError && PartAdvert::find()->where(['user_phone_canonical' => $value])->exists()) {
                $hasError = true;
            }

            if ($hasError) {
                $this->addError($model, $attribute,
                    Yii::t(
                        'main',
                        'Can\'t create advert to this phone. <a href="{url}">Details...</a>',
                        [
                            'url' => \yii\helpers\Url::toRoute(Yii::$app->params['rulesRoute'])
                        ]
                    )
                );
            }
        }
    }
}