<?php
namespace advert\models;

use app\helpers\Date as DateHelper;
use auto\models\Mark;
use auto\models\Model as Model2;
use auto\models\Modification;
use auto\models\Serie;
use DateTime;
use geo\models\Region;
use partner\forms\TradePoint;
use partner\models\Partner;
use sammaye\solr\SolrDocumentInterface;
use Solarium\QueryType\Select\Result\Document;
use Yii;
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
    public $name = '';

    /**
     * @var boolean активность
     */
    public $active = true;

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
    public $region_name = '';

    /**
     * @var int идентификатор региона
     */
    public $region_id = 0;

    /**
     * @var float цена
     */
    public $price = 0;

    /**
     * @var int идентификатор типа продавца
     */
    public $seller_type = 0;

    /**
     * @var string название компании
     */
    public $company_name = '';

    /**
     * @var string путь к превью
     */
    public $preview_url = '';

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
     * @var string номер по каталогу
     */
    public $catalogue_number = '';

    /**
     * @var int идентификатор категории
     */
    public $category_id = 0;

    /**
     * @var int идентификатор состояния запчасти
     */
    public $condition_id = 0;

    /**
     * @var string дерево привязанных автомобилей, результат работы методы PartsSearchApi::getAutomobilesTree
     */
    public $automobiles_tree = '';

    /**
     * Правила валидации
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'region_id', 'seller_type', 'category_id', 'condition_id'], 'integer'],
            [['name', 'region_name', 'company_name', 'preview_url', 'catalogue_number'], 'string'],
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
                /* @var $date DateTime */
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $data);
                if ($date === false) {
                    $data = str_replace(['T', 'Z'], [' ', ''], $data);
                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $data);
                    if ($date === false) {
                        return null;
                    }
                    return $date->format('Y-m-d H:i:s');
                }
                return $date->format('Y-m-d') . 'T' . $date->format('H:i:s') . 'Z';
            }],
            ['automobiles_tree', 'filter', 'filter' => function($data) {
                $value = is_string($data) ? \yii\helpers\Json::decode($data) : (is_array($data) ? $data : []);
                return \yii\helpers\Json::encode(is_array($value) ? $value : []);
            }],
        ];
    }

    /**
     * Установить данные из индекса Solr
     *
     * @param Document $doc
     * @return \self
     */
    public static function populateFromSolr($doc)
    {
        /* @var $doc Document */
        $ret = new self();
        $ret->setAttributes($doc->getFields());
        $ret->validate();
        return $ret;
    }

    /**
     * Установить данные из параметров запчасти
     *
     * @param PartParam $param
     */
    protected function loadDataFromParam(PartParam $param)
    {
        $this->setAttributes([
            'catalogue_number' => $param->catalogue_number,
            'category_id' => (int) $param->category_id,
            'condition_id' => (int) $param->condition_id,
        ]);
    }

    /**
     * Установить данные из модели контактов
     *
     * @param Contact $contact
     */
    protected function loadDataFromContact(Contact $contact)
    {
        // регион
        /* @var $contact Contact */
        /* @var $region Region */
        $region = $contact->region;
        if ($region instanceof Region) {
            $this->setAttributes([
                'region_id' => $region->id,
                'region_name' => $region->site_name,
            ]);
        }

        // определить тип продавца
        /* @var $tradePoint TradePoint */
        $tradePoint = $contact->tradePoint;
        $partner = null;
        if ($tradePoint instanceof TradePoint) {
            /* @var $partner Partner */
            $partner = $tradePoint->partner;
        }
        if ($partner instanceof Partner) {
            // компания
            $this->setAttributes([
                'seller_type' => $partner->company_type,
                'company_name' => $partner->name,
            ]);
        }
        else {
            // частное лицо
            $this->setAttributes([
                'seller_type' => 0,
            ]);
        }
    }

    /**
     * Создать объект на основе объявления
     *
     * @param Part $advert
     * @return \self
     */
    public static function populateFromAdvert(Part $advert)
    {
        $model = new self();

        /* @var $searchApi \advert\components\PartsSearchApi */
        $searchApi = \Yii::$app->getModule('advert')->partsSearch;

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
            'automobiles_tree' => $searchApi->getAdvertAutomobileTree($advert),
        ]);

        if ($contact = $advert->contact) {
            // контакты
            $model->loadDataFromContact($contact);
        }

        if ($param = $advert->partParam) {
            // параметры запчасти
            $model->loadDataFromParam($param);
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

    /**
     * Получить оригинальную модель запчасти
     *
     * @return Part
     */
    public function getOriginal()
    {
        return Part::find()->andWhere(['id' => (int) $this->id])->one();
    }

    /**
     * Возвращает отформатированную дату публикации
     * @return string
     */
    public function getPublishedFormatted()
    {
        $ret = '';
        if ($this->published_from) {
            $ret = DateHelper::formatDate($this->published_from);
        }
        return $ret;
    }

    /**
     * Возвращает отформатированную цену
     * @return string
     */
    public function getPriceFormated()
    {
        $price = (float) $this->price;
        $decimals = 2;
        if (round($price, 0) == $price) {
            $decimals = 0;
        }
        return number_format($price, $decimals, ',', ' ');
    }

    /**
     * Получить продавца:
     * 1) если компания - возвращает название партнера;
     * 2) иначе - "Частное лицо".
     *
     * @return string
     */
    public function getSeller()
    {
        if ($this->seller_type == 0) {
            return Yii::t('frontend/advert', 'private person');
        }

        return $this->company_name;
    }

    /**
     * Получить массив привязанных марок
     * @return array
     */
    public function getMark()
    {
        $res = !empty($this->mark_id) ? Mark::find()->andWhere(['id' => $this->mark_id])->all() : [];
        return !empty($res) ? $res : [];
    }

    /**
     * Получить массив привязанных моделей
     * @return array
     */
    public function getModel()
    {
        $res = !empty($this->model_id) ? Model2::find()->andWhere(['id' => $this->model_id])->all() : [];
        return !empty($res) ? $res : [];
    }

    /**
     * Получить массив привязанных серий
     * @return array
     */
    public function getSerie()
    {
        $res = !empty($this->serie_id) ? Serie::find()->andWhere(['id' => $this->serie_id])->all() : [];
        return !empty($res) ? $res : [];
    }

    /**
     * Получить массив привязанных модификаций
     * @return array
     */
    public function getModification()
    {
        $res = !empty($this->modification_id) ? Modification::find()->andWhere(['id' => $this->modification_id])->all() : [];
        return !empty($res) ? $res : [];
    }

    /**
     * Вернуть значение automobiles_tree в виде массива
     * @return array
     */
    public function getAutomobilesTree()
    {
        $ret = is_array($this->automobiles_tree) ? $this->automobiles_tree : \yii\helpers\Json::decode($this->automobiles_tree);
        return is_array($ret) ? $ret : [];
    }
}