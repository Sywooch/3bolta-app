<?php
namespace partner\models;

use handbook\models\HandbookValue;
use user\models\User;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Модель партнера.
 * Партнер в обязательном порядке должен быть прикреплен к пользователю.
 * user_id - уникальное поле в таблице партнеров
 */
class Partner extends ActiveRecord
{
    /**
     * @var string фейковый инпут для установки специализации
     */
    protected $_mark;

    /**
     * @var array массив для установки специализации
     */
    protected $_markArray;

    /**
     * Название таблицы
     * @return string
     */
    public static function tableName()
    {
        return '{{%partner}}';
    }

    /**
     * Правила валидации
     * @return string
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'company_type'], 'required'],
            ['company_type', 'in', 'range' => array_keys(self::getCompanyTypes())],
            ['user_id', 'integer'],
            ['user_id', 'unique'],
            ['name', 'string', 'max' => 100],
            ['mark', 'safe'],
        ];
    }

    /**
     * Получение специализаций в текстовом виде
     * @return string
     */
    public function getMark()
    {
        return $this->_mark;
    }

    /**
     * Установка специализаций. Если пришел массив то его запоминаем в _partnerSpecializationArray
     * @param array $value
     */
    public function setMark($value)
    {
        if (is_array($value)) {
            $this->_markArray = [];
            foreach ($value as $v) {
                $v = (int) $v;
                if ($v) {
                    $this->_markArray[] = $v;
                }
            }
        }
    }

    /**
     * Получить массив специализаций
     * @return array
     */
    public function getMarkArray()
    {
        if (!empty($this->_markArray)) {
            return $this->_markArray;
        }
        else {
            $ret = [];
            foreach ($this->specialization as $v) {
                /* @var $v Specialization */
                $ret[] = $v->mark_id;
            }
            return $ret;
        }
    }

    /**
     * Подписи атрибутов
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'created' => Yii::t('main', 'Created'),
            'edited' => Yii::t('main', 'Edited'),
            'user_id' => Yii::t('partner', 'Owner'),
            'name' => Yii::t('partner', 'Partner name'),
            'company_type' => Yii::t('partner', 'Company type'),
            'mark' => Yii::t('partner', 'Specialization'),
        ];
    }

    /**
     * Получить пользователя
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Получить список типов организации для выпадающего списка
     * @return array
     */
    public static function getCompanyTypes()
    {
        $ret = ArrayHelper::map(
            HandbookValue::find()->andWhere(['handbook_code' => 'company_type'])->all(),
            'id', 'name'
        );
        $ret[''] = '';
        ksort($ret);
        return $ret;
    }

    /**
     * Получить название типа компании
     * @return string
     */
    public function getCompanyType()
    {
        $values = self::getCompanyTypes();
        if (isset($values[$this->company_type])) {
            return $values[$this->company_type];
        }
        return '';
    }

    /**
     * Получить торговые точки
     * @return ActiveQuery
     */
    public function getTradePoints()
    {
        return $this->hasMany(TradePoint::className(), ['partner_id' => 'id']);
    }

    /**
     * Получить специализации
     * @return ActiveQuery
     */
    public function getSpecialization()
    {
        return $this->hasMany(Specialization::className(), ['partner_id' => 'id']);
    }

    /**
     * Действия перед сохранением
     */
    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created = date('Y-m-d H:i:s');
        }
        $this->edited = date('Y-m-d H:i:s');

        return parent::beforeSave($insert);
    }

    /**
     * При сохранение необходимо обновить массив марок
     *
     * @param boolean $runValidation
     * @param array $attributeNames
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $ret = false;

        $transaction = $this->getDb()->beginTransaction();

        try {
            $ret = parent::save($runValidation, $attributeNames);
            if ($ret && is_array($this->_markArray)) {
                // удалить предыдущие записи
                Specialization::deleteAll('partner_id=:partner_id', [
                    ':partner_id' => $this->id,
                ]);
                // создать новые
                foreach ($this->_markArray as $v) {
                    $v = (int) $v;
                    $spec = new Specialization();
                    $spec->setAttributes([
                        'mark_id' => $v,
                        'partner_id' => $this->id,
                    ]);
                    $spec->save();
                }
            }

            $transaction->commit();
        }
        catch (Exception $ex) {
            $transaction->rollBack();

            $ret = false;
        }

        return $ret;
    }
}