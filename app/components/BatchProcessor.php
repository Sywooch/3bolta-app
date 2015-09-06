<?php
namespace app\components;

/**
 * Пакетный обработчик поточных данных.
 * Фиксирует количество объектов для обработки. При достижении порогового значения - автоматически коммитит.
 * Необходимо передавать:
 * - onAdd - метод, который вызывается при добавлении объектов в стек;
 * - onCommit - метод, который вызывается при коммите объектов в стеке.
 *
 * По завершению обработки нужно повторно вызвать commit, чтобы обработать все необработанные объекты.
 */
class BatchProcessor extends \yii\base\Component
{
    /**
     * @var integer количество документов, при котором происходит автокоммит
     */
    public $maxCommitCnt = 100;

    /**
     * @var mixed метод, вызываемый для добавления данных в стек
     */
    public $onAdd;

    /**
     * @var mixed метод, вызываем при коммите данных в стеке
     */
    public $onCommit;

    /**
     * @var integer количество объектов в стеке, итеративно увеличивается при вызове метода add и обнуляется при вызове commit
     */
    protected $currentCnt = 0;

    /**
     * Добавить элемент в стек. Необходимо передавать те параметры, которые используются в onAdd.
     */
    public function add()
    {
        $args = func_get_args();
        call_user_func_array($this->onAdd, $args);
        $this->currentCnt++;
        if ($this->currentCnt >= $this->maxCommitCnt) {
            $this->commit();
        }
    }

    /**
     * Закоммитить объекты
     */
    public function commit()
    {
        if ($this->currentCnt > 0) {
            call_user_func($this->onCommit, $this->currentCnt);
            $this->currentCnt = 0;
        }
    }
}