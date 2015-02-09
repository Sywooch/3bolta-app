<?php
use yii\db\Migration;

/**
 * Удалить таблицу advert_photo
 */
class m150209_204933_advert__drop_advert_photo extends Migration
{
    public function safeUp()
    {
        try {
            $this->dropTable('{{%advert_photo}}');
        } catch (\yii\db\Exception $ex) {

        }
    }
}
