<?php
namespace app\helpers;

use DateTime;
use IntlDateFormatter;
use Yii;

/**
 * Хелпер для работы с датами.
 */
class Date
{
    /**
     * Форматирует дату и возвращает следующее:
     * - сегодня, 00:00;
     * - вчера, 00:00;
     * - завтра, 00:00;
     * - 23 февраля, 00:00;
     * - 22 февраля 2014 г., 00:00;
     *
     * В зависимости от текущей даты.
     *
     * Возможные варианты переменной $date:
     * - string, в формате Y-m-d H:i:s;
     * - DateTime;
     * - integer.
     *
     * @param mixed $date
     * @param boolean $allowShort разрешать приставки "сегодня", "завтра" и т.п.
     */
    public static function formatDate($date, $allowShort = true)
    {
        if (is_numeric($date) || is_string($date)) {
            $date = new DateTime($date);
        }

        $currentDate = new DateTime(date('Y-m-d 00:00:00'));

        $diff = $currentDate->diff($date, false);

        if ($diff->y === 0 && $diff->m === 0 && $diff->d === 0 && $allowShort) {
            return Yii::t('main', 'today') . ', ' . $date->format('H:i');
        }
        else if ($diff->y === 0 && $diff->m === 0 && $diff->d === -1 && $allowShort) {
            return Yii::t('main', 'yesterday') . ', ' . $date->format('H:i');
        }
        else if ($diff->y === 0 && $diff->m === 0 && $diff->d === 1 && $allowShort) {
            return Yii::t('main', 'tomorrow') . ', ' . $date->format('H:i');
        }
        else {
            $formatter = new IntlDateFormatter(
                Yii::$app->language, IntlDateFormatter::SHORT,
                IntlDateFormatter::MEDIUM
            );
            if ($diff->y === 0) {
                $formatter->setPattern('d MMMM, h:mm');
            }
            else {
                $formatter->setPattern('d MMMM Yг., h:mm');
            }
            return $formatter->format($date);
        }
    }
}