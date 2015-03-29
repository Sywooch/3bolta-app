<?php
namespace partner\forms;

use yii\data\ActiveDataProvider;
use partner\models\TradePoint;

/**
 * Форма поиска торговых точек
 */
class TradePointSearch extends TradePoint
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'partner_id'], 'integer'],
            ['address', 'string', 'max' => 255],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = parent::find();

        if ($this->load($params) && $this->validate()) {
            $query->andFilterWhere([
                'id' => $this->id,
                'partner_id' => $this->partner_id,
            ])
            ->andFilterWhere(['like', 'address', $this->address]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }
}
