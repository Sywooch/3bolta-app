<?php
namespace app\commands;

use Yii;

use yii\db\Connection;

use yii\console\Controller;
use auto\sync\Base;

use auto\models\Mark;
use auto\models\Model;
use auto\models\Generation;
use auto\models\Serie;
use auto\models\Modification;

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

    /**
     * Обновить названия для марок
     *
     * @param Connection $db
     * @return int количество обновленных данных
     */
    protected function updateMarkSiteName(Connection $db)
    {
        $cnt = 0;

        $cnt = $db->createCommand('UPDATE ' . Mark::tableName() . ' SET '
                . 'full_name = name')
                ->execute();

        return $cnt;
    }

    /**
     * Обновить названия для моделей
     *
     * @param Connection $db
     * @return int количество обновленных данных
     */
    protected function updateModelSiteName(Connection $db)
    {
        $cnt = 0;

        $selectMark = "SELECT full_name FROM " . Mark::tableName() . " mark WHERE mark.id = model.mark_id";

        $cnt = $db->createCommand("UPDATE " . Model::tableName() . " model SET "
                . "full_name = ($selectMark)"
                . " || ' ' || model.name")
                ->execute();

        return $cnt;
    }

    /**
     * Обновить названия для поколений
     *
     * @param Connection $db
     * @return int количество обновленных данных
     */
    protected function updateGenerationSiteName(Connection $db)
    {
        $selectMark = "SELECT mark.full_name FROM " . Mark::tableName() . " mark "
                . "LEFT JOIN " . Model::tableName() . " model ON mark.id = model.mark_id "
                . "WHERE model.id = generation.model_id";

        $cnt = $db->createCommand("UPDATE " . Generation::tableName() . " generation SET "
                . "full_name = ($selectMark)"
                . " || ' ' || generation.name "
                . "WHERE generation.name IS NOT NULL AND generation.name <> ''")
                ->execute();

        $cnt += $db->createCommand("UPDATE " . Generation::tableName() . " generation SET "
                . "full_name = (SELECT model.full_name FROM " . Model::tableName() . " model WHERE model.id = generation.model_id)"
                . "WHERE generation.name IS NULL OR generation.name = ''")
                ->execute();

        $db->createCommand("UPDATE " . Generation::tableName() . " generation SET "
                . "full_name = generation.full_name || ' ' || '(' || generation.year_begin || '-...)' "
                . "WHERE generation.year_begin IS NOT NULL AND generation.year_end IS NULL")
                ->execute();

        $db->createCommand("UPDATE " . Generation::tableName() . " generation SET "
                . "full_name = generation.full_name || ' ' || '(' || generation.year_begin || '-' || generation.year_end || ')' "
                . "WHERE generation.year_begin IS NOT NULL AND generation.year_end IS NOT NULL")
                ->execute();

        return $cnt;
    }

    /**
     * Обновить названия для поколений
     *
     * @param Connection $db
     * @return int количество обновленных данных
     */
    protected function updateSerieSiteName(Connection $db)
    {
        $selectGeneration = "SELECT generation.full_name FROM " . Generation::tableName() . " generation "
                . "WHERE generation.id = serie.generation_id";

        $selectModel = "SELECT model.full_name FROM " . Model::tableName() . " model "
                . "WHERE model.id = serie.model_id";

        $cnt = $db->createCommand("UPDATE " . Serie::tableName() . " serie SET "
                . "full_name = ($selectGeneration) || ' ' || name WHERE "
                . "generation_id IS NOT NULL OR generation_id <> 0")
                ->execute();

        $cnt += $db->createCommand("UPDATE " . Serie::tableName() . " serie SET "
                . "full_name = ($selectModel) WHERE "
                . "generation_id IS NULL OR generation_id = 0")
                ->execute();

        return $cnt;
    }

    /**
     * Обновить названия для модификаций
     *
     * @param Connection $db
     * @return int количество обновленных данных
     */
    protected function updateModificationSiteName(Connection $db)
    {
        $selectSerie = "SELECT serie.full_name FROM " . Serie::tableName() . " serie "
                . "WHERE serie.id = modification.serie_id";

        $cnt = $db->createCommand("UPDATE " . Modification::tableName() . " modification SET "
                . "full_name = ($selectSerie) || ' ' || name")
                ->execute();

        return $cnt;
    }

    /**
     * Установить полные названия у всех автомобилей
     */
    public function actionSetFullName()
    {
        $db = Mark::getDb();

        $this->stdout('Update marks... ');
        $cnt = $this->updateMarkSiteName($db);
        $this->stdout($cnt . ' completed' . "\n");

        $this->stdout('Update models... ');
        $cnt = $this->updateModelSiteName($db);
        $this->stdout($cnt . ' completed' . "\n");

        $this->stdout('Update generations... ');
        $cnt = $this->updateGenerationSiteName($db);
        $this->stdout($cnt . ' completed' . "\n");

        $this->stdout('Update series... ');
        $cnt = $this->updateSerieSiteName($db);
        $this->stdout($cnt . ' completed' . "\n");

        $this->stdout('Update modifications... ');
        $cnt = $this->updateModificationSiteName($db);
        $this->stdout($cnt . ' completed' . "\n");
    }
}
