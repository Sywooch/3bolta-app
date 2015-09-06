<?php
namespace advert\models;

use geo\models\Region;
use partner\forms\TradePoint;
use partner\models\Partner;
use sammaye\solr\SolrDocumentInterface;
use yii\base\Model;

class PartIndex extends Model implements SolrDocumentInterface
{
    /**
     * @var int идентификатор объявления
     */
    public $id;

    /**
     * @var string заголовок объявления
     */
    public $name;

    /**
     * @var boolean активность
     */
    public $active;

    /**
     * @var string дата начала публикации
     */
    public $published_from;

    /**
     * @var string дата окончания публикации
     */
    public $published_to;

    /**
     * @var string название региона
     */
    public $region_name;

    /**
     * @var int идентификатор региона
     */
    public $region_id;

    /**
     * @var float цена
     */
    public $price;

    /**
     * @var int идентификатор типа продавца
     */
    public $seller_type;

    /**
     * @var string название компании
     */
    public $company_name;

    /**
     * @var string путь к превью
     */
    public $preview_url;

    /**
     * @var int[] массив марок
     */
    public $mark_id = [];

    /**
     * @var int[] массив моделей
     */
    public $model_id = [];

    /**
     * @var int[] массив серий
     */
    public $serie_id = [];

    /**
     * @var int[] массив модификаций
     */
    public $modification_id = [];

    /**
     * Правила валидации
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'region_id', 'seller_type'], 'integer'],
            [['name', 'region_name', 'company_name', 'preview_url'], 'string'],
            ['active', 'boolean'],
            ['price', 'number'],
            [['mark_id', 'model_id', 'serie_id', 'modification_id'], 'filter', 'filter' => function($data) {
                // все значения массива преобразовать в integer
                $ret = is_array($data) ? $data : (array) $data;
                foreach ($ret as $k => $v) {
                    $ret[$k] = (int) $v;
                }
                return $ret;
            }],
            [['published_from', 'published_to'], 'filter', 'filter' => function($data) {
                /* @var $date \DateTime */
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $data);
                if ($date === false) {
                    return null;
                }
                return $date->format('Y-m-d') . 'T' . $date->format('H:i:s') . 'Z';
            }],
        ];
    }

    public static function populateFromSolr($doc)
    {
        print_r($doc);exit();
    }

    /**
     * Создать объект на основе объявления
     *
     * @param \advert\models\Part $advert
     * @return \self
     */
    public static function populateFromAdvert(Part $advert)
    {
        $model = new self();

        $model->setAttributes([
            'id' => $advert->id,
            'name' => $advert->advert_name,
            'active' => $advert->active,
            'published_from' => $advert->published,
            'published_to' => $advert->published_to,
            'price' => (float) $advert->price,
            'mark_id' => $advert->getMarks(),
            'model_id' => $advert->getModels(),
            'serie_id' => $advert->getSeries(),
            'modification_id' => $advert->getModifications(),
        ]);

        if ($contact = $advert->contact) {
            // регион
            /* @var $contact Contact */
            /* @var $region Region */
            $region = $contact->region;
            if ($region instanceof Region) {
                $model->setAttributes([
                    'region_id' => $region->id,
                    'region_name' => $region->site_name,
                ]);
            }

            /* @var $tradePoint TradePoint */
            $tradePoint = $contact->tradePoint;
            $partner = null;
            if ($tradePoint instanceof TradePoint) {
                /* @var $partner Partner */
                $partner = $tradePoint->partner;
            }
            if ($partner instanceof Partner) {
                // компания
                $model->setAttributes([
                    'seller_type' => $partner->company_type,
                    'company_name' => $partner->name,
                ]);
            }
            else {
                // частное лицо
                $model->setAttributes([
                    'seller_type' => 0,
                ]);
            }
        }

        if ($url = $advert->getPreviewUrl()) {
            // ссылка на предпросмотр
            $model->setAttributes([
                'preview_url' => $url,
            ]);
        }

        $model->validate();

        return $model;
    }
}