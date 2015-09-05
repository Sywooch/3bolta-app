<?php
namespace app\components;

use advert\models\Contact;
use partner\models\TradePoint;
use user\models\User;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\validators\UniqueValidator;

/**
 * Валидатор телефона.
 *
 * Запрещает ввести номер телефона в неправильном формате.
 * Формат установлен в константе PHONE_PATTERN.
 * Маска для отображения в инпуте используется в PHONE_MASK.
 *
 * Проверяет уникальность телефона вокруг всех классов, передаваемых функцией getUnqiueClasses.
 */
class PhoneValidator extends UniqueValidator
{
    /**
     * Атрибут для канонической записи телефона
     * @var string
     */
    public $canonicalAttribute;

    /**
     * Паттерн для проверки валидности телефона
     */
    const PHONE_PATTERN = '#^\+7[\s]\([\d]{3}\)[\s][\d]{3}-[\d]{2}-[\d]{2}$#';

    /**
     * Маска для ввода телефона
     */
    const PHONE_MASK = '+7 (999) 999-99-99';

    /**
     * Возвращает классы и поля для валидации уникальности.
     * @return array
     */
    protected function getUniqueClasses()
    {
        return [
            User::className() => [
                'attribute' => 'phone',
                'canonicalAttribute' => 'phone_canonical',
            ],
            TradePoint::className() => [
                'attribute' => 'phone',
                'canonicalAttribute' => 'phone_canonical',
            ],
        ];
    }

    /**
     * Валидация телефона
     *
     * @param mixed $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        /* @var $model Model */
        if (!preg_match(self::PHONE_PATTERN, $model->$attribute)) {
            // валидация формата телефона
            $this->addError($model, $attribute, Yii::t('main', 'Wrong phone format'));
        }
        else if (!empty($this->canonicalAttribute)) {
            // валидация уникальности телефона
            $canonicalAttribute = $this->canonicalAttribute;
            $canonicalPhone = self::getCanonicalPhone($model->$attribute);
            $model->$canonicalAttribute = $canonicalPhone;
            $classes = $this->getUniqueClasses();
            foreach ($classes as $className => $attributes) {
                if ($className != $model->className() && $className != $this->targetClass) {
                    /* @var $res ActiveQuery */
                    $res = $className::find();
                    $res->andWhere([$attributes['canonicalAttribute'] => $model->$canonicalAttribute]);
                    if ($res->exists()) {
                        $this->addError($model, $attribute, Yii::t('main', 'Phone already exists'));
                        return;
                    }
                }
            }
            parent::validateAttribute($model, $canonicalAttribute);
            if ($errors = $model->getErrors($canonicalAttribute)) {
                $this->addError($model, $attribute, Yii::t('main', 'Phone already exists'));
                return;
            }
        }
    }

    /**
     * Вернуть телефон в каноническом виде.
     *
     * @param string $phone
     * @return string
     */
    public static function getCanonicalPhone($phone)
    {
        $phone = preg_replace('#[\D]+#', '', $phone);
        if (strlen($phone) >= 11 && $phone[0] == 8) {
            // телефон начинается с восьмерки, необходимо ее заменить на 7
            $phone = '7' . substr($phone, 1, strlen($phone) - 1);
        }
        return $phone;
    }
}