<?php
namespace app\commands;

use XMLReader;
use Yii;
use yii\console\Controller;
use yii\db\Connection;

/**
 * Из БД ФИАС (http://fias.nalog.ru/) производит выгрузку в таблицу {{%region}}.
 * Помимо этого производит экспорт и импорт из этой таблицы в .sql - файл для переноса данных с площадки на площадку.
 */
class RegionsController extends Controller
{
    /**
     * @var array массив идентификатором типов объектов, которые интересуют (только регионы)
     */
    protected static $_aolevels = [1];

    /**
     * Файл для обмена регионами с площадки на площадку
     */
    const EXCHANGE_FILE = '@app/data/regions.csv';

    /**
     * Название таблицы
     */
    const TABLE = '{{%region}}';

    /**
     * Прочитать адрес объекта
     *
     * @param XMLReader $reader
     * @return array
     */
    protected function readObject(XMLReader $reader)
    {
        $object = [];

        while ($reader->moveToNextAttribute()){
            // здесь мы получаем атрибуты если они есть
            $object[$reader->localName] = $reader->value;
        }

        return $object;
    }

    /**
     * По массиву возвращает строку для вставки в CSV
     *
     * @param array $object
     * @return string
     */
    protected function getCsvRow($object)
    {
        return implode("\t", [
            $object['AOID'], $object['REGIONCODE'],
            $object['FORMALNAME'], $object['OFFNAME'],
            $object['SHORTNAME'],
            !empty($object['SITENAME']) ? $object['SITENAME'] : '',
            !empty($object['sort']) ? $object['sort'] : 100,
            !empty($object['as_default']) ? (int) $object['as_default'] : '',
        ]) . "\n";
    }

    /**
     * Обновить объект в БД.
     *
     * @param array $object
     */
    protected function consumeObject($object)
    {
        $this->stdout("Consume object: ");
        $this->stdout($this->getCsvRow($object));

        /* @var $db Connection */
        $db = Yii::$app->db;

        $row = $db->createCommand('SELECT * FROM ' . self::TABLE . ' WHERE external_id = :external_id AND region_code = :region_code', [
            ':external_id' => $object['AOID'],
            ':region_code' => $object['REGIONCODE'],
        ])->queryOne();

        $toUpdate = [
            'external_id' => $object['AOID'],
            'canonical_name' => $object['FORMALNAME'],
            'official_name' => $object['OFFNAME'],
            'short_name' => $object['SHORTNAME'],
            'region_code' => $object['REGIONCODE'],
            'site_name' => !empty($object['SITENAME']) ? $object['SITENAME'] : null,
        ];

        if (!empty($object['sort'])) {
            $toUpdate['sort'] = (int) $object['sort'];
        }

        if (isset($object['as_default'])) {
            $toUpdate['as_default'] = !empty($object['as_default']);
        }

        if (!empty($row) &&
                ($row['official_name'] != $object['OFFNAME'] ||
                    $row['short_name'] != $object['SHORTNAME'] ||
                    $row['region_code'] != $object['REGIONCODE'] ||
                    !empty($object['SITENAME']))
        ) {
            $db->createCommand()->update(self::TABLE, $toUpdate, 'id=:id', [
                ':id' => $row['id']
            ])->execute();
            $this->stdout("Updated\n");
        }
        else if (empty($row)) {
            $db->createCommand()->insert(self::TABLE, $toUpdate)->execute();
            $this->stdout("Created\n");
        }
    }

    /**
     * Для всех регионов обновляет координаты центра, используя Google Geocode.
     */
    public function actionUpdateCoordinates()
    {
        /* @var $db Connection */
        $db = Yii::$app->db;

        static $baseUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address=';

        // не более 5 - запросов подряд, а потом - ожидание 1 секунду
        $cnt = 5;

        $res = $db->createCommand('SELECT * FROM ' . self::TABLE . ' WHERE center_lat IS NULL and center_lng IS NULL')->query();

        while ($row = $res->read()) {
            if ($cnt == 0) {
                $cnt = 5;
                sleep(1);
            }
            $query = $row['site_name'];
            if ($row['short_name'] == 'г') {
                $query = 'город ' . $row['official_name'];
            }
            else if ($row['short_name'] == 'АО') {
                $query = $row['official_name'] . ' Автономный Округ';
            }
            else if ($row['short_name'] == 'Респ') {
                $query = 'Республика ' . $row['official_name'];
            }
            $json = file_get_contents($baseUrl . urlencode($query));
            try {
                $result = \yii\helpers\Json::decode($json);
                if (!empty($result['status']) && $result['status'] == 'OK' && !empty($result['results'])) {
                    $result = reset($result['results']);
                    if (
                            !empty($result['geometry']['location']) &&
                            !empty($result['address_components'][0]) &&
                            (
                                in_array('administrative_area_level_1', $result['address_components'][0]['types']) ||
                                in_array('political', $result['address_components'][0]['types'])
                            )
                    ) {
                        $db->createCommand()->update(self::TABLE, [
                            'center_lat' => $result['geometry']['location']['lat'],
                            'center_lng' => $result['geometry']['location']['lng'],
                        ], 'id=:id', [
                            ':id' => $row['id'],
                        ])->execute();
                        $this->stdout("region {$row['id']} updated\n");
                    }
                }
            }
            catch (\Exception $ex) { }
            $cnt--;
        }
    }

    /**
     * Экспортировать регионы из БД в файл @app/data/regions.csv в формате csv.
     */
    public function actionExport()
    {
        /* @var $db Connection */
        $db = Yii::$app->db;

        $fileToExport = Yii::getAlias(self::EXCHANGE_FILE);
        if (is_file($fileToExport)) {
            unlink($fileToExport);
        }

        $res = $db->createCommand('SELECT * FROM ' . self::TABLE)->query();

        while ($row = $res->read()) {
            $object = [
                'AOID' => $row['external_id'],
                'REGIONCODE' => $row['region_code'],
                'FORMALNAME' => $row['canonical_name'],
                'OFFNAME' => $row['official_name'],
                'SHORTNAME' => $row['short_name'],
                'SITENAME' => $row['site_name'],
                'as_default' => $row['as_default'],
                'sort' => $row['sort'],
            ];
            file_put_contents($fileToExport, $this->getCsvRow($object), FILE_APPEND);
        }

        $this->stdout("done\n");
    }

    /**
     * Импортировать регионы из файла  @app/data/regions.csv в БД.
     */
    public function actionImport()
    {
        $fileToImport = Yii::getAlias(self::EXCHANGE_FILE);

        if (!is_file($fileToImport)) {
            $this->stderr("Not such CSV in $fileToImport\n");
            return 1;
        }

        $file = fopen($fileToImport, 'r');
        while ($row = fgetcsv($file, 0, "\t")) {
            if (count($row) < 6) {
                continue;
            }
            $object = array_combine(
                array('AOID', 'REGIONCODE', 'FORMALNAME', 'OFFNAME', 'SHORTNAME', 'SITENAME'),
                $row
            );
            $this->consumeObject($object);
        }
        fclose($file);
    }

    /**
     * Чтение регионов из файла $addrPath.
     * $addrPath - это путь к XML-файлу из БД ФИАС с кодом ADDROBJ.
     * Можно передать абсолютный путь, либо алиас пути.
     *
     * После чтение каждого региона - они заносятся по-новой, либо редактируются в таблице {{%region}}.
     *
     * @param string $addrPath
     */
    public function actionReadFias($addrPath)
    {
        $addrPath = Yii::getAlias($addrPath);

        if (!is_file($addrPath)) {
            $this->stderr("File $addrPath does not exists\n");
            return 1;
        }

        $xmlReader = new XMLReader();
        $xmlReader->open($addrPath);

        $beginObjects = false;
        $beginObject = false;
        $object = [];

        while ($xmlReader->read()) {
            if ($xmlReader->nodeType == XMLReader::ELEMENT && $xmlReader->localName == 'AddressObjects') {
                // начало массива
                $beginObjects = true;
            }
            else if ($xmlReader->nodeType == XMLReader::ELEMENT && $beginObjects && $xmlReader->localName == 'Object') {
                // начало объекта
                $object = $this->readObject($xmlReader);
                if ($object['ACTSTATUS'] == 1 && in_array($object['AOLEVEL'], self::$_aolevels)) {
                    $this->consumeObject($object);
                }
            }
        }
    }
}