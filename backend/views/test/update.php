<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ModulesActions */

$this->title = 'Update Modules Actions: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Modules Actions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="modules-actions-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
