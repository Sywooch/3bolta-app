<?php
namespace geo\forms;

use geo\models\Region;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Форма для выбора региона пользователя
 */
class SelectRegion extends Model
{
    /**
     * @var integer идентификатор выбранного региона
     */
    public $regionId;

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            ['regionId', 'in', 'range' => array_keys($this->getRegionDropDown()), 'skipOnEmpty' => false],
        ];
    }

    /**
     * Получить список регионов для выпадающего списка
     * @return array
     */
    public function getRegionDropDown()
    {
        $list = Region::find()->all();

        return ArrayHelper::map($list, 'id', 'site_name');
    }
}