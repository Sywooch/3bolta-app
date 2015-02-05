<?php
namespace auto;

use Yii;
use auto\models\Mark;
use auto\models\Model;
use auto\models\Serie;
use auto\models\Generation;
use auto\models\Modification;

/**
 * Модуль структуры автомобилей.
 * В данном модуле хранятся модели:
 * - марки;
 * - модели;
 * - серии;
 * - поколения;
 * - модификации.
 *
 * База автомобилей синхронизована с базой auto.basebuy.ru.
 * Для синхронизации требуется отдельная БД MySQL и подключение к ней.
 * Подключение указывается в компоненте sync.
 *
 * Для sync требуется указать префикс (по умолчанию - car).
 */
class Module extends \yii\base\Module
{
    /**
     * @var \yii\db\Connection подключение к БД сайта
     */
    protected $db;

    public function init()
    {
        $this->db = Yii::$app->db;

        Yii::configure($this, [
            'components' => [
                'syncMark' => [
                    'class' => '\auto\sync\Mark'
                ],
                'syncModel' => [
                    'class' => '\auto\sync\Model'
                ],
                'syncSerie' => [
                    'class' => '\auto\sync\Serie'
                ],
                'syncGeneration' => [
                    'class' => '\auto\sync\Generation'
                ],
                'syncModification' => [
                    'class' => '\auto\sync\Modification'
                ],
            ]
        ]);

        return parent::init();
    }
}