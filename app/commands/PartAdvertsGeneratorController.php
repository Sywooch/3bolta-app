<?php
namespace app\commands;

use Yii;

use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use yii\console\Controller;
use advert\models\Part;
use advert\models\PartCategory;
use handbook\models\HandbookValue;
use auto\models\Mark;
use auto\models\Model;
use auto\models\Serie;
use auto\models\Modification;

/**
 * Генератор объявлений
 */
class PartAdvertsGeneratorController extends Controller
{
    protected $_generatorPhrases = [
        'Тормозные колодки',
        'Тормозной диск',
        'Крыло',
        'Дверь',
        'Двигатель',
        'Шаровая опора',
        'ШРУС',
        'Пыльник',
        'Рулевая рейка',
        'Рычаг подвески',
        'Свеча зажигания',
        'Выхлопная труба',
        'Тюнинговый обвес',
        'Воздушный обвес',
        'Карбюратор',
        'Хлам от старой машины',
    ];

    protected $_generatorNames = [
        'Вася', 'Петя', 'Иван', 'Саня', 'Вован',
        'Толян', 'Колян', 'Инокентий', 'Ибрагим', 'Зульфия',
        'Александр', 'Владимир', 'Петр', 'Алексей', 'Сергей',
        'Евгений', 'Илья', 'Павел', 'Мария', 'Елена',
    ];

    /**
     * Получить дерево автомобилей
     * @return array многоуровневый массив, ключ массива - идентификатор главного автомобиля и вниз по нарастающей
     */
    protected function getAutomobilesTree()
    {
        $names = [];
        $ret = [];

        $res = Mark::find();
        foreach ($res->each() as $mark) {
            $ret[$mark->id] = [];
            $names[$mark->id] = $mark->name;
        }

        $res = Model::find();
        foreach ($res->each() as $model) {
            if (isset($ret[$model->mark_id])) {
                $ret[$model->mark_id][$model->id] = [];
            }
        }

        foreach ($ret as $markId => $models) {
            foreach ($models as $modelId => $series) {
                $res = Serie::find()->andWhere(['model_id' => $modelId]);
                foreach ($res->each() as $serie) {
                    $ret[$markId][$modelId][$serie->id] = [];
                    $res2 = Modification::find()->andWhere(['serie_id' => $serie->id]);
                    foreach ($res2->each() as $modification) {
                        $ret[$markId][$modelId][$serie->id][] = $modification->id;
                    }
                }
            }
        }

        return [
            'ids' => $ret,
            'names' => $names,
        ];
    }

    /**
     * Возвращает по $cnt случайных автомобиля из каждой группы
     *
     * @param array $data массив, откуда брать данные (@see self::getAutomobilesTree())
     * @param int $cnt количество случайных записей
     * @return array ассоциативный массив для привязок к автомобилям
     */
    protected function getRandomAutomobiles($data, $cnt = 5)
    {
        $ret = [
            'mark' => [],
            'model' => [],
            'serie' => [],
            'modification' => [],
        ];

        for ($x = 0; $x < $cnt; $x++) {
            $markId = null;
            while (!$markId && !in_array($markId, $ret['mark'])) {
                $markId = array_rand($data);
            }
            $ret['mark'][] = $markId;
        }

        for ($x = 0; $x < $cnt; $x++) {
            $markId = array_rand($ret['mark']);
            $markId = $ret['mark'][$markId];
            $modelId = null;
            while (!$modelId && !in_array($modelId, $ret['model'])) {
                $modelId = array_rand($data[$markId]);
            }
            $ret['model'][] = $modelId;
        }

        for ($x = 0; $x < $cnt; $x++) {
            $serieId = null;
            $modificationId = null;
            foreach ($ret['mark'] as $m1) {
                foreach ($ret['model'] as $m2) {
                    if (isset($data[$m1][$m2])) {
                        $s1 = array_rand($data[$m1][$m2]);
                        if (!in_array($s1, $ret['serie'])) {
                            $serieId = $s1;
                            $modificationId = reset($data[$m1][$m2][$serieId]);
                            break;
                        }
                    }
                }
            }
            if ($serieId && $modificationId) {
                $ret['serie'][] = $serieId;
                $ret['modification'][] = $modificationId;
            }
        }

        return $ret;
    }

    protected function getCategories()
    {
        $ret = [];

        $res = PartCategory::find()->all();
        foreach ($res as $i) {
            $ret[] = $i->id;
        }

        return $ret;
    }

    protected function getConditions()
    {
        $ret = [];

        $res = HandbookValue::find()->andWhere(['handbook_code' => 'part_condition'])->all();
        foreach ($res as $i) {
            $ret[] = $i->id;
        }

        return $ret;
    }

    protected function getRegions()
    {
        $ret = [];

        $res = \geo\models\Region::find()->all();
        foreach ($res as $i) {
            $ret[] = $i->id;
        }

        return $ret;
    }

    /**
     * Генератор позиций
     *
     * @param int $cnt количество позиций (по умолчанию - 10)
     * @param string $imagesPath папка, откуда брать изображения (по умолчанию - data/advert-generator-images)
     */
    public function actionIndex($cnt = 100, $imagesPath = '@app/data/advert-generator-images')
    {
        $images = [];
        $dir = new \DirectoryIterator(Yii::getAlias($imagesPath));
        foreach ($dir as $file) {
            /* @var $file DirectoryIterator */
            if ($file->isFile() && !$file->isDot() && !$file->isDir()) {
                $images[] = $file->getPathname();
            }
        }

        $categories = $this->getCategories();
        $conditions = $this->getConditions();
        $auto = $this->getAutomobilesTree();
        $regions = $this->getRegions();

        for ($x = 0; $x < $cnt; $x++) {
            $publishedDate = new \DateTime();
            $publishedToDate = new \DateTime();
            $publishedToDate->add(new \DateInterval('P30D'));

            $autoXref = $this->getRandomAutomobiles($auto['ids']);
            $name = $this->_generatorPhrases[array_rand($this->_generatorPhrases)] . ' ' . $auto['names'][reset($autoXref['mark'])];
            $price = rand(10000, 2000000) / 100;
            $userName = $this->_generatorNames[array_rand($this->_generatorNames)];
            $userPhone = '+7 (' . rand(111, 999) . ') ' . rand(111, 999) . '-' . rand(11, 99) . '-' . rand(11, 99);
            $userEmail = 'generator-' . uniqid() . '@3bolta.com';
            $category = $categories[array_rand($categories)];
            $condition = $conditions[array_rand($conditions)];
            $region = $regions[array_rand($regions)];

            $transaction = Part::getDb()->beginTransaction();

            try {
                $this->stdout('Create advert ' . $x . ': ');

                $advert = new Part();

                $advert->setAttributes([
                    'active' => true,
                    'published' => $publishedDate->format('Y-m-d H:i:s'),
                    'published_to' => $publishedToDate->format('Y-m-d H:i:s'),
                    'advert_name' => $name,
                    'price' => $price,
                ]);

                $advert->setMarks($autoXref['mark']);
                $advert->setModels($autoXref['model']);
                $advert->setSeries($autoXref['serie']);
                $advert->setModifications($autoXref['modification']);

                $randImage = rand(10, 1000);

                if ($randImage > 500) {
                    $path = $images[array_rand($images)];

                    $info = pathinfo($path);

                    $image = new UploadedFile();
                    $image->name = $info['basename'];
                    $image->tempName = $path;
                    $image->type = FileHelper::getMimeType($path);
                    $image->size = filesize($path);
                    $image->error = 0;

                    $advert->setUploadImage([$image]);
                }

                if (!$advert->save()) {
                    throw new \yii\base\Exception();
                }

                $advert->updateAutomobiles();

                $contacts = new \advert\models\Contact();
                $contacts->setAttributes([
                    'user_name' => $userName,
                    'user_phone' => $userPhone,
                    'user_email' => $userEmail,
                    'region_id' => $region,
                    'advert_id' => $advert->id,
                ]);
                if (!$contacts->save()) {
                    throw new \yii\base\Exception();
                }

                $params = new \advert\models\PartParam();
                $params->setAttributes([
                    'category_id' => $category,
                    'condition_id' => $condition,
                    'advert_id' => $advert->id,
                ]);
                if (!$params->save()) {
                    throw new \yii\base\Exception();
                }

                $transaction->commit();

                $this->stdout('done...' . "\n");
            }
            catch (\yii\base\Exception $ex) {
                $transaction->rollBack();
                throw $ex;
            }
        }
    }
}
