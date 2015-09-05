<?php
use yii\db\Migration;

/**
 * Недостающие индексы
 */
class m150905_124852_advert__contact_indexes extends Migration
{
    private $table = '{{%advert_contact}}';

    public function safeUp()
    {
        $this->createIndex('advert_contact_user_phone_canonical_index', $this->table, 'user_phone_canonical', false);
        $this->createIndex('advert_contact_user_email_index', $this->table, 'user_email', false);
    }

    public function safeDown()
    {
        $this->dropIndex('advert_contact_user_phone_canonical_index', $this->table);
        $this->dropIndex('advert_contact_user_email_index', $this->table);
    }
}
