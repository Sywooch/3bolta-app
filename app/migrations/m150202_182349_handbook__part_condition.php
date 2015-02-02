<?php

use yii\db\Migration;

/**
 * Справочник - состояние запчастей
 */
class m150202_182349_handbook__part_condition extends Migration
{
    private $table = '{{%handbook}}';
    private $tableValues = '{{%handbook_value}}';

    private $handbooks = [
        [
            'code' => 'part_condition',
            'name' => 'Состояние запчасти',
            'values' => [
                [
                    'sort' => 1,
                    'name' => 'Б/У',
                ],
                [
                    'sort' => 2,
                    'name' => 'Новая',
                ],
                [
                    'sort' => 3,
                    'name' => 'Восстановленная',
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
