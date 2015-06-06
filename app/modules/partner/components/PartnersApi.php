<?php
namespace partner\components;

use user\forms\Register;
use user\models\User;
use partner\models\Partner;
use partner\forms\Partner as PartnerForm;
use yii\base\Exception;

/**
 * API для работы с партнерами
 */
class PartnersApi extends \yii\base\Component
{
    /**
     * Редактирование данных о партнере из формы редактирования.
     * Если к пользователю еще не привязан партнерский аккаунт - создает его.
     * В случае успеха возвращает модель Partner.
     *
     * @param PartnerForm $form форма редактирования партнера
     * @param User $user модель пользователя, к которому необходимо привязать партнера
     * @return Partner|null
     * @throws Exception
     */
    public function updatePartnerData(PartnerForm $form, User $user)
    {
        $ret = null;

        if ($user->type != User::TYPE_LEGAL_PERSON) {
            // редактировать может только пользователь с соотв. типом
            return $ret;
        }

        $partner = null;

        if ($user->partner instanceof Partner) {
            // к пользователю уже привязан партнерский аккаунт
            $partner = $user->partner;
        }
        else {
            // партнерского аккаунта еще нет
            $partner = new Partner();
            $partner->user_id = $user->id;
        }

        $partner->setAttributes([
            'name' => $form->name,
            'company_type' => $form->type,
        ]);

        $transaction = $partner->getDb()->beginTransaction();

        try {
            if (!$partner->save()) {
                throw new Exception();
            }

            $transaction->commit();

            $ret = $partner;
        } catch (Exception $ex) {
            $transaction->rollBack();
            $ret = null;
        }

        return $ret;
    }

    /**
     * Регистрация партнера из формы регистрации пользвоателя.
     * В случае успеха возвращает модель зарегистрированного партнера.
     * Необходимо передать модель формы регистрации и модель уже созданного пользователя.
     *
     * @param Register $form
     * @param User $user
     * @return Partner|null
     * @throws Exception
     */
    public function registerPartner(Register $form, User $user)
    {
        $ret = null;

        $partner = new Partner();

        $partner->setAttributes([
            'name' => $form->partnerName,
            'company_type' => $form->partnerType,
            'user_id' => $user->id,
        ]);

        $transaction = $partner->getDb()->beginTransaction();

        try {
            if (!$partner->save()) {
                throw new Exception();
            }

            $transaction->commit();

            $ret = $partner;
        } catch (Exception $ex) {
            $transaction->rollBack();
            $ret = null;
        }

        return $ret;
    }
}