<?php
namespace app\widgets;

/**
 * Расширение \wh\widgets\MagicSuggest для условий по умолчанию
 * @inheritdoc
 */
class MagicSuggestDefaults extends \wh\widgets\MagicSuggest
{
    public function run()
    {
        if (!isset($this->clientOptions['noSuggestionText'])) {
            $this->clientOptions['noSuggestionText'] = \Yii::t('main', 'No suggestions');
        }
        if (!isset($this->clientOptions['placeholder'])) {
            $this->clientOptions['placeholder'] = \Yii::t('main', 'Type or click here');
        }
        if (isset($this->clientOptions['maxEntryRenderer']) && is_string($this->clientOptions['maxEntryRenderer'])) {
            $this->clientOptions['maxEntryRenderer'] = new \yii\web\JsExpression("
                function(v) {
                    var str = '" . $this->clientOptions['maxEntryRenderer'] . "';
                    return str.replace('{n}', v);
                }
            ");
        }
        if (isset($this->clientOptions['maxSelectionRenderer']) && is_string($this->clientOptions['maxSelectionRenderer'])) {
            $this->clientOptions['maxSelectionRenderer'] = new \yii\web\JsExpression("
                function(v) {
                    var str = '" . $this->clientOptions['maxSelectionRenderer'] . "';
                    return str.replace('{n}', v);
                }
            ");
        }
        if (isset($this->clientOptions['minCharsRenderer']) && is_string($this->clientOptions['minCharsRenderer'])) {
            $this->clientOptions['minCharsRenderer'] = new \yii\web\JsExpression("
                function(v) {
                    var str = '" . $this->clientOptions['minCharsRenderer'] . "';
                    return str.replace('{n}', v);
                }
            ");
        }
        return parent::run();
    }
}