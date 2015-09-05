<?php
namespace advert\components;

use advert\forms\PartForm;
use advert\models\Advert;
use advert\models\Contact;
use advert\models\PartParam;
use advert\models\Part;
use DateInterval;
use DateTime;
use user\models\User;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * Компонент для работы с запчастями: публикация, просмотр, редактирование и т.п.
 */
class PartsApi extends Component
{
    /**
     * Прикрепить все неавторизованные объявления к пользователю $user.
     * Прикрепление происходит по e-mail.
     * Если у не авторизованных объявлений совпадает e-mail с пользователем - выполняется привязка,
     * иначе - нет.
     *
     * @todo Крайне непроизводительный подход: сначала берутся все контакты объявлений,
     *  потом их объявления обновляются, потом обновляются сами контакты.
     *  Желательно сделать так, чтобы обновление происходило в один запрос.
     *
     * @param User $user пользователь, к которому требуется прикрепить объявления
     * @return int количество привязанных объявлений
     * @throws Exception
     */
    public function attachNotAuthAdvertsToUser(User $user)
    {
        $cnt = 0;

        $res = Contact::find()->andWhere([
            'user_email' => $user->email
        ]);
        foreach ($res->each() as $contact) {
            /* @var $contact Contact */
            $transaction = Contact::getDb()->beginTransaction();
            try {
                // привязать объявление к пользователю
                Advert::getDb()->createCommand()->update(Advert::tableName(), [
                    'user_id' => $user->id,
                ], "id=:id", [
                    ':id' => $contact->advert_id,
                ])
                ->execute();

                // очистить контакты объявления
                $contact->setAttributes([
                    'user_name' => null,
                    'user_phone' => null,
                    'user_email' => null,
                ]);
                if (!$contact->save()) {
                    throw new Exception();
                }

                $cnt++;

                $transaction->commit();
            }
            catch (Exception $ex) {
                $transaction->rollBack();
                throw $ex;
            }
        }

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
                ->setTo($advert->getUserEmail())
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
                        '/advert/part-advert/confirm', 'code' => $advert->confirmation
                    ], true)
                ])
                ->setTo($advert->getUserEmail())
                ->setSubject(Yii::t('mail_subjects', 'Publish advert'))
                ->send();
        }
        return false;
    }

    /**
     * Создать или получить модель контактов на основе формы редактирования объявления и
     * уже созданного объявления
     *
     * @param PartForm $form
     * @param Advert $advert
     * @return Contact
     */
    protected function getContactsFromForm(PartForm $form, Advert $advert)
    {
        // получить модель контактов из объявления или создать новую
        /* @var $contacts Contact */
        $contacts = $advert->contact instanceof Contact ? $advert->contact : new Contact();
        if ($contacts->isNewRecord && !$advert->isNewRecord) {
            // новую модель прикрепляем к объявлению сразу же
            $contacts->advert_id = $advert->id;
        }

        if (!$form->getUserId()) {
            // если добавляется от неавторизованного пользователя
            $contacts->setAttributes([
                'user_name' => $form->user_name,
                'user_phone' => $form->user_phone,
                'user_email' => $form->user_email,
            ]);
        }
        else {
            // если от авторизованного пользователя - устанавливаем торговую точку
            $contacts->setAttributes([
                'trade_point_id' => $form->trade_point_id,
            ]);
        }

        $contacts->setAttributes([
            'region_id' => $form->region_id,
        ]);

        // обновить привязку контактов у объявления
        unset ($advert->contact);

        return $contacts;
    }

    /**
     * Создать или получить модель параметров запчасти из формы редактирования
     *
     * @param PartForm $form
     * @param Part $advert
     * @return AdvertPartParams
     */
    protected function getPartParamsFromForm(PartForm $form, Part $advert)
    {
        /* @var $params PartParam */
        $params = $advert->partParam instanceof PartParam ? $advert->partParam : new PartParam();
        if ($params->isNewRecord && !$advert->isNewRecord) {
            // новую модель прикрепляем к объявлению
            $params->advert_id = $advert->id;
        }

        $params->setAttributes([
            'catalogue_number' => $form->catalogue_number,
            'category_id' => $form->category_id,
            'condition_id' => $form->condition_id,
        ]);

        // обновить привязку параметров у объявления
        unset ($advert->partParam);

        return $params;
    }

    /**
     * Установить данные из формы в объявление
     *
     * @param PartForm $form
     * @param Advert $advert
     */
    protected function setDataFromForm(PartForm $form, Advert $advert)
    {
        $advert->setAttributes([
            'advert_name' => $form->name,
            'price' => $form->price,
            'description' => $form->description,
            'confirmation' => md5(uniqid() . $form->name . time()),
            'allow_questions' => (boolean) $form->allow_questions,
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
     * @param PartForm $form
     *
     * @return Advert|null
     */
    public function appendRegisterAdvert(PartForm $form)
    {
        $ret = null;

        if ($form->validate()) {
            $transaction = Part::getDb()->beginTransaction();

            try {
                $advert = new Part();

                $advert->user_id = $form->getUserId();
                $advert->active = true;

                // установить данные из формы
                $this->setDataFromForm($form, $advert);

                if (!$advert->save()) {
                    throw new Exception();
                }

                // привязать контакты
                /* @var $contacts Contact */
                $contacts = $this->getContactsFromForm($form, $advert);
                if (!$contacts->save()) {
                    throw new Exception();
                }

                // привязать параметры
                /* @var $params PartParam */
                $params = $this->getPartParamsFromForm($form, $advert);
                if (!$params->save()) {
                    throw new Exception();
                }

                // обновить автомобили
                $advert->updateAutomobiles();
                // обновить публикацию
                $this->updatePublication($advert);

                $ret = $advert;

                $transaction->commit();
            } catch (Exception $ex) {
                $transaction->rollback();
                $ret = null;

                throw $ex;
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
     * @param PartForm $form
     *
     * @return Advert|null
     */
    public function appendNotRegisterAdvert(PartForm $form)
    {
        $ret = null;

        if ($form->validate()) {
            $transaction = Part::getDb()->beginTransaction();

            try {
                $advert = new Part();

                // неавторизованные объявления первым делом создаются неактивными
                $advert->active = false;

                // установить данные из формы
                $this->setDataFromForm($form, $advert);

                if (!$advert->save()) {
                    throw new Exception();
                }

                // привязать контакты
                $contacts = $this->getContactsFromForm($form, $advert);
                if (!$contacts->save()) {
                    throw new Exception();
                }

                // привязать параметры
                /* @var $params PartParam */
                $params = $this->getPartParamsFromForm($form, $advert);
                if (!$params->save()) {
                    throw new Exception();
                }

                // обновить автомобили
                $advert->updateAutomobiles();

                $transaction->commit();

                // отправить e-mail о необходимости активировать объявление
                $this->sendPublishConfirmation($advert);

                $ret = $advert;

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
     * @param PartForm $form
     * @return boolean true в случае успеха
     */
    public function updateAdvert(PartForm $form)
    {
        $ret = false;

        /* @var $advert Part */
        $advert = $form->getExists();
        if (!($advert instanceof Part) || !$form->validate()) {
            return $ret;
        }

        $transaction = Part::getDb()->beginTransaction();

        try {
            // установить данные из формы
            $this->setDataFromForm($form, $advert);
            if (!$advert->save()) {
                throw new Exception();
            }

            // обновить контакты
            $contacts = $this->getContactsFromForm($form, $advert);
            if (!$contacts->save()) {
                throw new Exception();
            }

            // обновить параметры
            /* @var $params PartParam */
            $params = $this->getPartParamsFromForm($form, $advert);
            if (!$params->save()) {
                throw new Exception();
            }

            // обновить автомобили
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