<?php
namespace partner\components;

use partner\forms\Partner as PartnerForm;
use partner\forms\TradePoint as TradePointForm;
use partner\models\Partner;
use partner\models\Specialization;
use partner\models\TradePoint;
use user\forms\Register;
use user\models\User;
use yii\base\Component;
use Exception;
use partner\exception\PartnersApiException;

/**
 * API для работы с партнерами
 */
class PartnersApi extends Component
{
    /**
     * Редактирование торговой точки $tradePoint из формы $form.
     * В случае успеха возвращает true.
     *
     * @param TradePointForm $form форма редактирования
     * @param TradePoint $tradePoint торговая точка
     * @return boolean
     * @throws PartnersApiException
     */
    public function updateTradePoint(TradePointForm $form, TradePoint $tradePoint)
    {
        $ret = false;

        $tradePoint->setAttributes([
            'address' => $form->address,
            'latitude' => $form->latitude,
            'longitude' => $form->longitude,
            'phone' => $form->phone,
            'phone_from_profile' => (boolean) $form->phone_from_profile,
            'region_id' => (int) $form->region_id,
        ]);

        $transaction = $tradePoint->getDb()->beginTransaction();

        try {
            if (!$tradePoint->save()) {
                throw new PartnersApiException('', PartnersApiException::VALIDATION_ERROR);
            }

            $transaction->commit();

            $ret = true;;
        } catch (Exception $ex) {
            $transaction->rollBack();
            $ret = false;
            PartnersApiException::throwUp($ex);
        }

        return $ret;
    }

    /**
     * Создать торговую точку на основе данных из формы
     *
     * @param TradePointForm $form форма создания торговой точки
     * @param Partner $partner данные партнера
     * @return TradePoint|null
     * @throws PartnersApiException
     */
    public function createTradePoint(TradePointForm $form, Partner $partner)
    {
        $ret = null;

        $tradePoint = new TradePoint();
        $tradePoint->setAttributes([
            'address' => $form->address,
            'latitude' => $form->latitude,
            'longitude' => $form->longitude,
            'phone' => $form->phone,
            'phone_from_profile' => (boolean) $form->phone_from_profile,
            'region_id' => (int) $form->region_id,
            'partner_id' => $partner->id,
        ]);

        $transaction = $tradePoint->getDb()->beginTransaction();

        try {
            if (!$tradePoint->save()) {
                throw new PartnersApiException('', PartnersApiException::VALIDATION_ERROR);
            }

            $transaction->commit();

            $ret = $tradePoint;
        } catch (Exception $ex) {
            $transaction->rollBack();
            $ret = null;
            PartnersApiException::throwUp($ex);
        }

        return $ret;
    }

    /**
     * Редактирование данных о партнере из формы редактирования.
     * Если к пользователю еще не привязан партнерский аккаунт - создает его.
     * В случае успеха возвращает модель Partner.
     *
     * @param PartnerForm $form форма редактирования партнера
     * @param User $user модель пользователя, к которому необходимо привязать партнера
     * @return Partner|null
     * @throws PartnersApiException
     */
    public function updatePartnerData(PartnerForm $form, User $user)
    {
        $ret = null;

        if ($user->type != User::TYPE_LEGAL_PERSON) {
            // редактировать может только пользователь с соотв. типом
            throw new PartnersApiException('', PartnersApiException::USER_TYPE_ERROR);
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

        $partner->setMark($form->getSpecializationArray());

        $transaction = $partner->getDb()->beginTransaction();

        try {
            if (!$partner->save()) {
                throw new PartnersApiException('', PartnersApiException::VALIDATION_ERROR);
            }

            $transaction->commit();

            $ret = $partner;
        } catch (Exception $ex) {
            $transaction->rollBack();
            $ret = null;
            PartnersApiException::throwUp($ex);
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
     * @throws PartnersApiException
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
            // создать привязки к специализациям
            $partner->setMark($form->getPartnerSpecializationArray());
            if (!$partner->save()) {
                throw new PartnersApiException('', PartnersApiException::VALIDATION_ERROR);
            }

            $transaction->commit();

            $ret = $partner;
        } catch (Exception $ex) {
            $transaction->rollBack();
            $ret = null;
            PartnersApiException::throwUp($ex);
        }

        return $ret;
    }
}