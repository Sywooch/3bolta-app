<?php
namespace app\commands;

use Yii;
use yii\console\Controller;

/**
 * Работа с крон-задачами, просмотр задач, необходимых для установки в crontab.
 */
class CronJobsController extends Controller
{
    /**
     * Просмотреть задачи, необходимые для установки в crontab.
     */
    public function actionView()
    {
        $list = !empty(Yii::$app->params['cron-jobs']) && is_array(Yii::$app->params['cron-jobs']) ? Yii::$app->params['cron-jobs'] : [];

        if (empty($list)) {
            return $this->stderr("Empty cron jobs list\n");
        }

        foreach ($list as $job => $time) {
            $this->stdout($time . ' ' . Yii::getAlias('@app/yii') . ' ' . $job . ' > /dev/null 2>&1 &' . "\n");
        }
    }
}