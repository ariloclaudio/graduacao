<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Atividade;

/**
 * AtividadeSearch represents the model behind the search form about `app\models\Atividade`.
 */
class AtividadeSearch extends Atividade
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'max_horas', 'curso_id', 'grupo_id'], 'integer'],
            [['codigo', 'nome'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Atividade::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'max_horas' => $this->max_horas,
            'curso_id' => $this->curso_id,
            'grupo_id' => $this->grupo_id,
        ]);

        $query->andFilterWhere(['like', 'codigo', $this->codigo])
            ->andFilterWhere(['like', 'nome', $this->nome]);

        return $dataProvider;
    }
}