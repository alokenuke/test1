<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\ModulesActions */

$this->title = 'Create Modules Actions';
$this->params['breadcrumbs'][] = ['label' => 'Modules Actions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="modules-actions-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
