<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Periodo */

$this->title = $model->codigo;
?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1><?= Html::encode($this->title) ?></h1>
    <ol class="breadcrumb">
        <li><a href="?r=periodo/index"><i class="fa fa-calendar"></i> Período</a></li>
        <li class="active"><a href="?r=periodo/view">Visualizar</a></li>
    </ol>
</section>
<section class="content">
<div class="box box-success"> 
<div class="periodo-view box-body">

    <p>
        <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Excluir', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Você deseja deletar este item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'codigo',
            [
            'attribute' => 'dtInicio',
            'format' => ['date', 'php:d/m/Y']
            ],
            [
            'attribute' => 'dtTermino',
            'format' => ['date', 'php:d/m/Y']
            ],
        ],
    ]) ?>

</div>
</div>
</section>