<?php
use yii\db\Migration;

/**
 * Cправочник - тип компании
 */
class m150312_181034_handbook__company_type extends Migration
{
    private $table = '{{%handbook}}';
    private $tableValues = '{{%handbook_value}}';

    private $handbooks = [
        [
            'code' => 'company_type',
            'name' => 'Тип компании',
            'values' => [
                [
                    'sort' => 1,
                    'name' => 'Магазин',
                ],
                [
                    'sort' => 2,
                    'name' => 'Сеть магазинов',
                ],
                [
                    'sort' => 3,
                    'name' => 'Разборка',
                ]
            ],
        ],
    ];

    public function safeUp()
    {
        foreach ($this->handbooks as $handbook) {
            $this->insert($this->table, [
                'code' => $handbook['code'],
                'name' => $handbook['name'],
            ]);
            foreach ($handbook['values'] as $value) {
                $value['handbook_code'] = $handbook['code'];
                $this->insert($this->tableValues, $value);
            }
        }
    }

    public function safeDown()
    {
        foreach ($this->handbooks as $handbook) {
            $this->delete($this->table, 'code=:code', [
                ':code' => $handbook['code']
            ]);
        }
    }
}
