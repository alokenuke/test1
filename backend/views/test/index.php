<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Modules Actions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="modules-actions-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Modules Actions', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<div class="table-responsive table-responsive-cust">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'company_id',
            'module_name',
            'action',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
</div>