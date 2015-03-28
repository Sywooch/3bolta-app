<?php
namespace partner\forms;

use yii\data\ActiveDataProvider;
use partner\models\Partner;

/**
 * Форма поиска партнеров
 */
class PartnerSearch extends Partner
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['name', 'string', 'max' => 100],
            ['company_type', 'in', 'range' => array_keys(self::getCompanyTypes()), 'skipOnEmpty' => true],
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
                'company_type' => $this->company_type,
            ])
            ->andFilterWhere(['like', 'name', $this->name]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }
}
