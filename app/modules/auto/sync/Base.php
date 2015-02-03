<?php
namespace app\modules\auto\sync;

use Yii;

/**
 * Базовый класс для синхронизации
 */
abstract class Base extends yii\base\Component
{
    /**
     * @var int количество новых записей
     */
    protected $insert = 0;

    /**
     * @var int количество существующих записей
     */
    protected $exists = 0;

    /**
     * Подключение к БД сайта
     * @return \yii\db\Connection
     */
    public function getDb()
    {
        return Yii::$app->db;
    }

    /**
     * Подключение к внешней БД
     * @return \yii\db\Connection
     */
    public function getExternalDb()
    {
        return Yii::$app->getModule('auto')->externalDb;
    }

    /**
     * Название таблицы в БД сайта
     * @return string
     */
    abstract protected function getTableName();

    /**
     * Название таблицы во внешней таблице
     * @return string
     */
    abstract protected function getExternalTableName();

    /**
     * Сформатировать значение для вставки во внутренню таблицу.
     * На входе - запись из внешней таблицы.
     * @param [] $external
     * @return []
     */
    abstract protected function formatValue($external);

    /**
     * Очистить внутренню таблицу
     */
    protected function truncateTable()
    {
        $this->getDb()->createCommand()->truncateTable($this->getTableName())->execute();
    }

    /**
     * Выполнение синхронизации - внешняя функция
     * @return [] массив вида:
     *   insert - количество новых записей,
     *   update - количество обновленных записей,
     *   delete - количество удаленных записей,
     *   error - количество ошибок.
     */
    public function sync()
    {
        $this->insert = 0;
        $this->exists = $this->getExistsItemsCnt();

        $this->truncateTable();
        $externalRes = $this->getExternalItems();
        while ($external = $externalRes->read()) {
            $item = $this->formatValue($external);
            $this->create($item);
        }

        return [
            'insert' => $this->insert,
            'exists' => $this->exists,
            'delete' => $this->exists > $this->insert ? $this->exists - $this->insert : 0,
        ];
    }

    /**
     * Получить существующее количество элементов
     * @return int
     */
    protected function getExistsItemsCnt()
    {
        $sql = 'SELECT COUNT(*) as cnt FROM ' . $this->getTableName();

        $ret = $this->getDb()->createCommand($sql)->queryOne();

        return !empty($ret['cnt']) ? $ret['cnt'] : 0;
    }

    /**
     * Получить существующие записи во внешней БД.
     * @return \yii\db\DataReader
     */
    protected function getExternalItems()
    {
        $sql = 'SELECT * FROM ' . $this->getExternalTableName();

        return $this->getExternalDb()->createCommand($sql)->query();
    }

    /**
     * Создать новую запись
     * @param [] $item
     */
    protected function create($item)
    {
        if ($this->getDb()->createCommand()
            ->insert($this->getTableName(), $item)->execute()) {
            $this->insert++;
        }
    }
}