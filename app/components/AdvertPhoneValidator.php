<?php
namespace app\components;

use advert\models\Contact;
use app\components\PhoneValidator;
use user\models\User;
use Yii;
use yii\helpers\Url;
use yii\validators\Validator;

/**
 * Объявление нельзя публиковать на телефон, на который уже зарегистрирован пользователь,
 * либо на которое уже существует другое объявление незарегистрированного пользователя.
 */
class AdvertPhoneValidator extends Validator
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

            if (!$hasError && Contact::find()->where(['user_phone_canonical' => $value])->exists()) {
                $hasError = true;
            }

            if ($hasError) {
                $this->addError($model, $attribute,
                    Yii::t(
                        'main',
                        'Can\'t create advert to this phone. <a href="{url}">Details...</a>',
                        [
                            'url' => Url::toRoute(Yii::$app->params['rulesRoute'])
                        ]
                    )
                );
            }
        }
    }
}