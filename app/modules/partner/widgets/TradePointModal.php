<?php
namespace partner\widgets;

use Yii;
use partner\models\TradePoint as TradePointModel;
use yii\bootstrap\Modal;
use yii\helpers\Url;

/**
 * Модальное окно создания/редактирования торговой точки.
 * Содержимое модального окна подгружается только в момент вызова самого модального окна
 * через контроллер UserTradePointController
 */
class TradePointModal extends \yii\bootstrap\Widget
{
    /**
     * @var \partner\models\TradePoint модель, которую необходимо редактировать
     */
    public $tradePoint;

    public function run()
    {
        $tradePointId = $this->tradePoint instanceof TradePointModel ?
            $this->tradePoint->id :
            null;

        $modalId = $tradePointId ? 'tradePointModal' . $tradePointId : 'newTradePointModal';
        $title = $tradePointId ?
            Yii::t('frontend/partner', 'Update trade point') :
            Yii::t('frontend/partner', 'Create trade point');
        $loadUrl = $tradePointId ?
            Url::toRoute(['/partner/user-trade-point/edit', 'id' => $tradePointId]) :
            Url::toRoute(['/partner/user-trade-point/create']);

        Modal::begin([
            'id' => $modalId,
            'header' => '<h2>' . $title . '</h2>',
            'toggleButton' => false,
            'options' => [
                'class' => 'load-modal-ajax',
                'data-ajax-url' => $loadUrl,
            ]
        ]);
        Modal::end();
    }
}