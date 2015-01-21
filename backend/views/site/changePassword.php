<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

$this->title = 'Change password';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <div class="tab-content">
        <div id="login" class="tab-pane active" style="max-width: 300px;margin:auto;">
            <div class="site-request-password-reset">
                <div class="site-reset-password">
                    <h1><?= Html::encode($this->title) ?></h1>

                    <d>Please choose your new password:</p>

                    <div class="row">
                        <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
                        <?= $form->field($model, 'old_password', ['options' => ['class' => 'input-block-level'], 'inputOptions' => ['placeholder' => 'Old Password']])->passwordInput() ?>
                        <?= $form->field($model, 'new_password', ['options' => ['class' => 'input-block-level'], 'inputOptions' => ['placeholder' => 'New Password']])->passwordInput() ?>
                        <?= $form->field($model, 'repeat_password', ['options' => ['class' => 'input-block-level'], 'inputOptions' => ['placeholder' => 'Repeat Password']])->passwordInput() ?>
                        <div class="form-group mt-15">
                            <button type="submit" class="btn btn-large btn-primary btn-block">Change Password</button>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .control-label {display:none;}
    .help-block{display:none;}
    .has-error .help-block {display:block}
</style>