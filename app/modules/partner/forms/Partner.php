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
     * @var string специализации партнера в текстовом виде для саггеста
     */
    protected $_specialization;

    /**
     * @var array массив идентификаторов специализаций
     */
    protected $_specializationArray = [];

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
            ['specialization', 'safe'],
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
            'specialization' => Yii::t('frontend/partner', 'Specialization'),
        ];
    }


    /**
     * Получение специализаций в текстовом виде
     * @return string
     */
    public function getSpecialization()
    {
        return $this->_specialization;
    }

    /**
     * Установка специализаций. Если пришел массив то его запоминаем в _partnerSpecializationArray
     * @param array $value
     */
    public function setSpecialization($value)
    {
        if (is_array($value)) {
            $this->_specializationArray = [];
            foreach ($value as $v) {
                $v = (int) $v;
                if ($v) {
                    $this->_specializationArray[] = $v;
                }
            }
        }
    }

    /**
     * Получить массив специализаций
     * @return array
     */
    public function getSpecializationArray()
    {
        return $this->_specializationArray;
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

        $form->_specializationArray = [];
        foreach ($model->specialization as $v) {
            $form->_specializationArray[] = $v->mark_id;
        }

        return $form;
    }
}