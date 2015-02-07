<?php
namespace advert\forms;

use yii\data\ActiveDataProvider;
use advert\models\Advert;

/**
 * Поиск объявлений
 */
class AdvertSearch extends Advert
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'condition_id', 'user_id'], 'integer'],
            [['advert_name', 'user_name', 'user_email', 'user_id'], 'safe'],
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
                'user_id' => $this->user_id,
                'condition_id' => $this->condition_id,
                'category_id' => $this->category_id,
            ])
            ->andFilterWhere(['like', 'user_name', $this->user_name])
            ->andFilterWhere(['like', 'user_email', $this->user_email]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }
}
