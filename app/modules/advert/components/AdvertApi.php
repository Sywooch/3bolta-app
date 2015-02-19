<?php
namespace advert\components;

use Yii;

use yii\base\Exception;
use advert\forms\Form;
use advert\models\Advert;

use yii\web\UploadedFile;

/**
 * Компонент для работы с объявлениями: публикация, просмотр, редактирование и т.п.
 */
class AdvertApi extends \yii\base\Component
{
    /**
     * Подтвердить публикацию объявления с кодом подтверждения $code.
     * Возвращает идентификатор объявления в случае успеха или null.
     *
     * @param type $code
     * @return int|null
     */
    public function confirmAdvert($code)
    {
        if (!$code) {
            return null;
        }

        $ret = null;

        $advert = Advert::find()->where(['confirmation' => $code])->one();

        if ($advert instanceof Advert) {
            $advert->setAttributes([
                'active' => true,
                'published' => date('Y-m-d H:i:s'),
                'confirmation' => null,
            ]);
            try {
                if ($advert->save(true, ['active', 'published', 'confirmation'])) {
                    $ret = $advert->id;
                }
            } catch (Exception $ex) {
                $ret = null;
            }
        }

        return $ret;
    }

    /**
     * Добавить новое объявление для неавторизованного пользователя.
     * На вход передается форма заполнения объявления.
     *
     * Если объявление было успешно создано - будет отправлено уведомление пользователю
     * с ссылкой для подтверждения. Пользователь должен пройти по этой ссылке
     * тем самым подтвердить, что объявление реально требует публикации.
     *
     * В случае успеха возвращает новую модель объявления.
     *
     * @param Form $form
     * @param [] $images массив загружаемых фотографий, каждый элемент - UploadedFile
     *
     * @return Advert|null
     */
    public function appendNotRegisterAdvert(Form $form, $images = [])
    {
        $ret = null;

        if ($form->validate()) {
            $transaction = Advert::getDb()->beginTransaction();

            try {
                $advert = new Advert();

                $advert->setAttributes([
                    'active' => false,
                    'advert_name' => $form->name,
                    'user_name' => $form->user_name,
                    'user_phone' => $form->user_phone,
                    'price' => $form->price,
                    'description' => $form->description,
                    'user_email' => $form->user_email,
                    'category_id' => $form->category_id,
                    'condition_id' => $form->condition_id,
                    'confirmation' => md5(uniqid() . $form->name . time()),
                ]);

                // привязать автомобили
                $advert->setMarks($form->getMark());
                $advert->setModels($form->getModel());
                $advert->setSeries($form->getSerie());
                $advert->setModifications($form->getModification());

                // привязать файлы
                $uploadImages = [];
                foreach ($images as $image) {
                    if ($image instanceof UploadedFile) {
                        $uploadImages[] = $image;
                    }
                }
                if (!empty($uploadImages)) {
                    $advert->setUploadImage($uploadImages);
                }

                if (!$advert->save()) {
                    throw new Exception();
                }

                $ret = $advert;

                $transaction->commit();
            } catch (Exception $ex) {
                $transaction->rollback();
                $ret = null;
            }
        }

        return $ret;
    }
}