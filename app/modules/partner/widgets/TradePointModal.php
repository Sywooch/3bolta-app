<?php
namespace partner\widgets;

use Yii;
use partner\models\TradePoint as TradePointModel;
use app\widgets\Modal;
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
            Url::toRoute(['/partner/partner/edit-trade-point', 'id' => $tradePointId]) :
            Url::toRoute(['/partner/partner/create-trade-point']);

        Modal::begin([
            'id' => $modalId,
            'title' => $title,
            'options' => [
                'class' => 'load-modal-ajax',
                'data-ajax-url' => $loadUrl,
            ]
        ]);
        Modal::end();
    }
}