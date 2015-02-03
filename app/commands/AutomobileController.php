<?php
namespace app\commands;

use Yii;

use yii\console\Controller;
use app\modules\auto\sync\Base;

/**
 * Работа с автомобилями - синхронизация со внешней БД auto.basebuy.ru.
 */
class AutomobileController extends Controller
{
    /**
     * Синхронизация определенной модели
     * @param Base $sync
     */
    private function _sync(Base $sync)
    {
        $result = $sync->sync();
        $this->stdout('Exists: ' . $result['exists'] . "\n");
        $this->stdout('Insert: ' . $result['insert'] . "\n");
        $this->stdout('Delete: ' . $result['delete'] . "\n");
    }

    /**
     * Синхронизация с MySQL БД auto.basebuy.php
     */
    public function actionSync()
    {
        $this->stdout('Sync marks...' . "\n");
        $sync = Yii::$app->getModule('auto')->syncMark;
        $this->_sync($sync);

        $this->stdout('Sync models...' . "\n");
        $sync = Yii::$app->getModule('auto')->syncModel;
        $this->_sync($sync);

        $this->stdout('Sync generation...' . "\n");
        $sync = Yii::$app->getModule('auto')->syncGeneration;
        $this->_sync($sync);

        $this->stdout('Sync serie...' . "\n");
        $sync = Yii::$app->getModule('auto')->syncSerie;
        $this->_sync($sync);

        $this->stdout('Sync modification...' . "\n");
        $sync = Yii::$app->getModule('auto')->syncModification;
        $this->_sync($sync);
    }
}
