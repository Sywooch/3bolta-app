<?php
namespace partner\forms;

use Yii;

use partner\models\Partner as PartnerModel;
use user\forms\Register;

/**
 * Форма редактирования данных о партнере
 */
class Partner extends \yii\base\Model
{
    /**
     * @var string название компании
     */
    public $name;

    /**
     * @var int тип компании
     */
    public $type;

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            ['name', 'string', 'max' => Register::MAX_PARTNER_NAME_LENGTH],
            ['type', 'in', 'range' => array_keys(PartnerModel::getCompanyTypes())],
        ];
    }

    /**
     * Подписи атрибутов
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('frontend/partner', 'Company name'),
            'type' => Yii::t('frontend/partner', 'Company type'),
        ];
    }

    /**
     * Создать форму на основе текущей компании
     *
     * @param PartnerModel $model
     * @return \self
     */
    public static function createFromPartner(PartnerModel $model)
    {
        $form = new self();

        $form->setAttributes([
            'name' => $model->name,
            'type' => $model->company_type,
        ]);

        return $form;
    }
}