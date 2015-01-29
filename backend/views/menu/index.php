<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Menus';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Menu', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <div class="clearfix table-responsive table-responsive-cust">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'label',
            'url:url',
            [
                'attribute' => 'parent.label',
                'label' => 'Parent',
                'value' => function ($model) {
                    return $model['parent'] ? $model['parent']->label : '';
                },
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model['status'] ? "Enabled" : 'Disabled';
                },
            ],
            'position',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    </div>
</div>