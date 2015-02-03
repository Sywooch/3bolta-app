<?php
namespace app\modules\auto;

use Yii;
use app\modules\auto\models\Mark;
use app\modules\auto\models\Model;
use app\modules\auto\models\Serie;
use app\modules\auto\models\Generation;
use app\modules\auto\models\Modification;

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
                    'class' => '\app\modules\auto\sync\Mark'
                ],
                'syncModel' => [
                    'class' => '\app\modules\auto\sync\Model'
                ],
                'syncSerie' => [
                    'class' => '\app\modules\auto\sync\Serie'
                ],
                'syncGeneration' => [
                    'class' => '\app\modules\auto\sync\Generation'
                ],
                'syncModification' => [
                    'class' => '\app\modules\auto\sync\Modification'
                ],
            ]
        ]);

        return parent::init();
    }
}