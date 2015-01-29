<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Menu */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="menu-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'label')->textInput(['maxlength' => 128]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => 256]) ?>

    <?= $form->field($model, 'parent_id')->dropDownList(yii\helpers\ArrayHelper::map(\backend\models\Menu::find()->all(), "id", "label"), ['prompt'=>'- Parent-']) ?>

    <?= $form->field($model, 'status')->checkbox() ?>
    
    <?= $form->field($model, 'position')->input("number") ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
