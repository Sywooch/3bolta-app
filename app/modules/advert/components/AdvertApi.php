<?php
namespace advert\components;

use Yii;

use DateInterval;
use DateTime;
use yii\helpers\Url;
use yii\base\Exception;
use advert\forms\Form;
use advert\models\Advert;
use user\models\User;

use yii\web\UploadedFile;

/**
 * Компонент для работы с объявлениями: публикация, просмотр, редактирование и т.п.
 */
class AdvertApi extends \yii\base\Component
{
    /**
     * Прикрепить все неавторизованные объявления к пользователю $user.
     * Прикрепление происходит по e-mail.
     * Если у не авторизованных объявлений совпадает e-mail с пользователем - выполняется привязка,
     * иначе - нет.
     *
     * @param User $user пользователь, к которому требуется прикрепить объявления
     * @return int количество привязанных объявлений
     */
    public function attachNotAuthAdvertsToUser(User $user)
    {
        $cnt = Advert::getDb()->createCommand()
            ->update(Advert::tableName(), [
                'user_id' => $user->id,
                'user_name' => null,
                'user_phone' => null,
                'user_email' => null,
            ], "user_id IS NULL AND user_email=:user_email", [
                ':user_email' => $user->email,
            ])
            ->execute();
        return $cnt;
    }

    /**
     * Остановить публикацию объявления текущей датой
     *
     * @param Advert $advert
     * @return boolean true, в случае успеха
     */
    public function stopPublication(Advert $advert)
    {
        $ret = false;

        if ($advert->active && $advert->published && strtotime($advert->published_to) > time()) {
            try {
                $advert->published_to = date('Y-m-d H:i:s');
                $ret = $advert->save();
            }
            catch (Exception $ex) {
                $ret = false;
            }
        }

        return $ret;
    }

    /**
     * Обновить дату публикации объявления на DEFAULT_PUBLISH_DAYS дней.
     *
     * @param Advert $advert
     * @return boolean true в случае успеха
     */
    public function updatePublication(Advert $advert)
    {
        $ret = false;

        if ($advert->active && strtotime($advert->published_to) < time()) {
            try {
                $date = new DateTime();
                $date->add(new DateInterval('P' . Advert::DEFAULT_PUBLISH_DAYS . 'D'));
                $advert->published = date('Y-m-d H:i:s');
                $advert->published_to = $date->format('Y-m-d H:i:s');
                if ($advert->save(true, ['published', 'published_to'])) {
                    $ret = true;
                }
            }
            catch (Exception $ex) {
                $ret = false;
            }
        }

        return $ret;
    }
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

        if ($advert instanceof Advert && !$advert->active && !$advert->published) {
            $dateFrom = new DateTime();
            $dateTo = new DateTime();
            $dateTo->add(new DateInterval('P' . Advert::DEFAULT_PUBLISH_DAYS . 'D'));
            $advert->setAttributes([
                'active' => true,
                'published' => $dateFrom->format('Y-m-d H:i:s'),
                'published_to' => $dateTo->format('Y-m-d 23:59:59'),
                'confirmation' => null,
            ]);
            try {
                if ($advert->save(true, [
                    'active', 'published', 'confirmation', 'published_to'
                ])) {
                    $ret = $advert->id;
                }
            } catch (Exception $ex) {
                $ret = null;
            }
        }

        return $ret;
    }

    /**
     * Отправить уведомление о прекращении публикации объявления по причине
     * истечения срока публикации
     *
     * @param Advert $advert
     * @return boolean
     */
    public function sendExpiredConfirmation(Advert $advert)
    {
        if ($advert->active && $advert->published) {
            try {
                return Yii::$app->mailer->compose('expiredPublishAdvert', [
                    'advert' => $advert
                ])
                ->setTo($advert->user_email)
                ->setSubject(Yii::t('mail_subjects', 'Publish advert expired'))
                ->send();
            }
            catch (Exception $ex) { }
        }
        return false;
    }

    /**
     * Отправить уведомление о публикации объявления
     *
     * @param Advert $advert
     * @return boolean
     * @throws Exception
     */
    public function sendPublishConfirmation(Advert $advert)
    {
        if (!$advert->active && !$advert->published && !$advert->user_id) {
            return Yii::$app->mailer->compose('publishNotAuthAdvert', [
                    'advert' => $advert,
                    'confirmationLink' => Url::toRoute([
                        '/advert/advert/confirm', 'code' => $advert->confirmation
                    ], true)
                ])
                ->setTo($advert->user_email)
                ->setSubject(Yii::t('mail_subjects', 'Publish advert'))
                ->send();
        }
        return false;
    }

    /**
     * Установить данные из формы в объявление
     *
     * @param Form $form
     * @param Advert $advert
     */
    protected function setDataFromForm(Form $form, Advert $advert)
    {
        $advert->setAttributes([
            'advert_name' => $form->name,
            'price' => $form->price,
            'description' => $form->description,
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
        foreach ($form->getImages() as $image) {
            if ($image instanceof UploadedFile) {
                $uploadImages[] = $image;
            }
        }
        if (!empty($uploadImages)) {
            $advert->setUploadImage($uploadImages);
        }
    }

    /**
     * Добавить новое объявление для авторизованного пользователя.
     * На вход передается форма заполнения объявления.
     * В форме уже должен быть установлен идентификатор пользователя.
     *
     * Если объявление было успешно создано - объявление сразу же публикуется.
     *
     * В случае успеха возвращает новую модель объявления.
     *
     * @param Form $form
     *
     * @return Advert|null
     */
    public function appendRegisterAdvert(Form $form)
    {
        $ret = null;

        if ($form->validate()) {
            $transaction = Advert::getDb()->beginTransaction();

            try {
                $advert = new Advert();

                $advert->user_id = $form->getUserId();
                $advert->active = true;

                $this->setDataFromForm($form, $advert);

                if (!$advert->save()) {
                    throw new Exception();
                }

                $advert->updateAutomobiles();

                $this->updatePublication($advert);

                $ret = $advert;

                $transaction->commit();
            } catch (Exception $ex) {
                $transaction->rollback();
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
     *
     * @return Advert|null
     */
    public function appendNotRegisterAdvert(Form $form)
    {
        $ret = null;

        if ($form->validate()) {
            $transaction = Advert::getDb()->beginTransaction();

            try {
                $advert = new Advert();

                $advert->active = false;

                $advert->setAttributes([
                    'user_email' => $form->user_email,
                    'user_name' => $form->user_name,
                    'user_phone' => $form->user_phone,
                ]);

                $this->setDataFromForm($form, $advert);

                if (!$advert->save()) {
                    throw new Exception();
                }

                $advert->updateAutomobiles();

                $this->sendPublishConfirmation($advert);

                $ret = $advert;

                $transaction->commit();
            } catch (Exception $ex) {
                $transaction->rollback();
                $ret = null;
            }
        }

        return $ret;
    }

    /**
     * Обновить объявление из формы.
     * На вход передается форма, в ней уже должно быть зашито объявление
     *
     * @param Form $form
     * @return boolean true в случае успеха
     */
    public function updateAdvert(Form $form)
    {
        $ret = false;

        $advert = $form->getExists();
        if (!($advert instanceof Advert) || !$form->validate()) {
            return $ret;
        }

        $transaction = Advert::getDb()->beginTransaction();

        try {
            $this->setDataFromForm($form, $advert);
            if (!$advert->save()) {
                throw new Exception();
            }

            $advert->updateAutomobiles();

            $transaction->commit();

            $ret = true;
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            $ret = false;
        }

        return $ret;
    }
}