<?php
namespace advert\components;

use advert\models\Part;
use advert\models\PartIndex;
use app\components\BatchProcessor;
use sammaye\solr\Client;
use Solarium\Client as SolrClient;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;
use Solarium\QueryType\Update\Result as UpdateResult;
use SolariumHttpException as SolariumHttpException;
use UpdateDocument as UpdateDocument;
use Yii;
use yii\console\Application as ConsoleApplication;
use yii\db\ActiveQuery;

/**
 * Класс для работы с индексем запчастей в Solr.
 * Является потомком Solarium, выполняет:
 * - обновление запчастей в индексе;
 * - удаление запчастей;
 * - позволяет обращаться к поиску в Solr.
 */
class PartsIndex extends Client
{
    /**
     * @var SolrClient
     */
    public $solr;

    /**
     * @var boolean режим отладки
     */
    public $debugMode = false;

    /**
     * Записать сообщение в лог, если это режим отладки
     * @param string $message
     */
    protected function log($message)
    {
        if ($this->debugMode && Yii::$app instanceof ConsoleApplication) {
            fwrite(STDOUT, $message . "\n");
        }
    }

    /**
     * Добавить документ в запрос на обновление индекса
     *
     * @param UpdateQuery $update запрос на обновление индекса
     * @param Part $advert модель объявления
     * @return UpdateDocument добавленный документ
     */
    public function addDocumentToUpdate(UpdateQuery $update, Part $advert)
    {
        /* @var $partIndex PartIndex */
        $partIndex = PartIndex::populateFromAdvert($advert);
        /* @var $document UpdateDocument */
        $document = $update->createDocument($partIndex->getAttributes());
        $update->addDocument($document);
        $this->log("Add document: {$partIndex->id}");
        return $document;
    }

    /**
     * Закоммитить и выполнить запрос на обновление документов
     *
     * @param UpdateQuery $update
     * @return UpdateResult результат обновления данных
     * @throws SolariumHttpException
     */
    public function commitUpdate(UpdateQuery $update)
    {
        $this->log("Commit");
        $update->addCommit();
        $result = $this->solr->update($update);
        return $result;
    }

    /**
     * Обновить единичную модель
     *
     * @param Part $advert
     * @return UpdateResult
     */
    public function reindexOne(Part $advert)
    {
        /* @var $update UpdateQuery */
        $update = $this->createUpdate();
        /* @var $document UpdateDocument */
        $this->addDocumentToUpdate($update, $advert);
        return $this->commitUpdate($update);
    }

    /**
     * Обновить индекс объявлений на основе запроса ActiveQuery.
     * Перебирает пачками объявления и добавляет их в апдейт.
     *
     * @param ActiveQuery $query запрос на получение объявлений из persistent БД
     * @return int количество обновленных объявлений
     * @throws SolariumHttpException
     */
    public function reindexByActiveQuery(ActiveQuery $query)
    {
        $updated = 0;

        $update = null;
        $indexer = $this;

        $batchProcessor = new BatchProcessor([
            // коммитим документы пачкой по 1000 штук
            'maxCommitCnt' => 1000,
            'onAdd' => function(Part $row) use (&$indexer, &$update) {
                if (is_null($update)) {
                    $update = $indexer->createUpdate();
                }
                $indexer->addDocumentToUpdate($update, $row);
            },
            'onCommit' => function($cnt) use (&$indexer, &$update, &$updated) {
                $result = $indexer->commitUpdate($update);
                /* @var $result UpdateResult */
                if ($result instanceof UpdateResult && $result->getStatus() == 0) {
                    $updated += $cnt;
                }
                $update = null;
            }
        ]);

        // запрашиваем сразу все данные для индекса
        $query->with(
            'contact', 'contact.tradePoint', 'contact.tradePoint.partner', 'contact.region',
            'mark', 'model', 'serie', 'modification'
        );

        foreach ($query->each(10) as $row) {
            /* @var $row Part */
            // пачкой перебираем объявления
            if (is_null($update)) {
                $update = $this->createUpdate();
            }
            $batchProcessor->add($row);
        }
        $batchProcessor->commit();

        return $updated;
    }

    /**
     * Удаляет просроченные объявления из индекса
     *
     * @return boolean
     */
    public function deleteExpired()
    {
        /* @var $update UpdateQuery */
        $update = $this->createUpdate();
        $update->addDeleteQuery('published_to:[* TO ' . date('Y-m-d') . 'T' . date('H:i:s') . 'Z]');

        $result = $this->commitUpdate($update);
        return $result->getStatus() == 0;
    }

    /**
     * Удалить объявление из индекса
     *
     * @param Part $advert объявление для удаления
     * @return boolean
     */
    public function deleteOne(Part $advert)
    {
        /* @var $update UpdateQuery */
        $update = $this->createUpdate();
        $update->addDeleteQuery('id:' . $advert->id);

        $result = $this->commitUpdate($update);
        return $result->getStatus() == 0;
    }
}