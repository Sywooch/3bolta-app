<?php

namespace user\forms;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use user\models\User;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class UserSearch extends User
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['email', 'name'], 'string', 'max' => 100],
            ['type', 'in', 'range' => array_keys(self::getTypesList())],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = User::find();

        if ($this->load($params) && $this->validate()) {
            $query->andFilterWhere([
                'id' => $this->id,
                'status' => $this->status,
                'type' => $this->type,
            ])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['name', 'name', $this->name]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }
}
