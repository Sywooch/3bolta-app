<?php
namespace app\modules\handbook\forms;

use Yii;
use yii\data\ActiveDataProvider;
use app\modules\handbook\models\HandbookValue;
use app\modules\handbook\models\Handbook;

/**
 * Поиск значений справочников
 */
class HandbookValueSearch extends HandbookValue
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['handbook_code'], 'in', 'range' => Handbook::getAvailCode()],
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

        $query->andFilterWhere([
            'handbook_code' => $this->handbook_code,
        ]);

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
