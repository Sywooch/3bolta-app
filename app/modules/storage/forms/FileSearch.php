<?php

namespace storage\forms;

use yii\data\ActiveDataProvider;
use storage\models\File;

/**
 * Форма поиска файлов
 */
class FileSearch extends File
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'size'], 'integer'],
            [['real_name', 'uploader_addr', 'repository'], 'safe'],
            [['uploader_addr'], 'match', 'pattern' => '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$'],
            [['is_image'], 'boolean'],
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
                'size' => $this->size,
                'is_image' => $this->is_image,
                'repository' => $this->repository,
            ])
            ->andFilterWhere(['like', 'real_name', $this->real_name])
            ->andFilterWhere(['like', 'uploader_addr', $this->uploader_addr]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }
}
