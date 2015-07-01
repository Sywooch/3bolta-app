<?php
namespace app\commands;

use advert\models\AdvertImage;
use app\components\DaemonController;

/**
 * Демон по созданию сжатых изображений для объявлений.
 * Должен работать постоянно, добавить в крон задание на каждую минуту.
 */
class AdvertsImagesController extends DaemonController
{
    /**
     * Сжатие изображений. Для всех изображений объявлений, у которых еще не были
     * созданы сжатые аналоги - создает их.
     * В случае ошибки в каком-нибудь изображении - удаляет изображение из БД.
     *
     * Работает как демон.
     *
     * @param boolean $debug работать в режиме отладки
     */
    public function actionResize($debug = false)
    {
        $this->_debug = $debug;
        while (true) {
            $res = AdvertImage::find()
                ->andWhere('(thumb_id IS NULL OR preview_id IS NULL OR image_id IS NULL)')
                ->limit(100);
            foreach ($res->each() as $image) {
                /* @var $image AdvertImage */
                $this->stdout("Create previews for {$image->file_id}: ");
                try {
                    $image->createImage();
                    $image->createThumb();
                    $image->createPreview();

                    $this->stdout("done\n");
                }
                catch (\Exception $ex) {
                    throw $ex;
                    $this->stdout("error\n");
                }
            }
        }
    }
}