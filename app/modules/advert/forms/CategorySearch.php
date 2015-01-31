<?php

namespace app\modules\advert\forms;

use Yii;
use yii\data\ActiveDataProvider;
use app\modules\advert\models\Category;

/**
 * Search model of Category
 */
class CategorySearch extends Category
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'safe'],
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
            ])
            ->andFilterWhere(['like', 'name', $this->name]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }
}
