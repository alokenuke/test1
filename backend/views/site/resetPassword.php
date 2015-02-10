<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

$this->title = 'Reset password';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <div class="tab-content">
        <div id="login" class="tab-pane active" style="max-width: 300px;margin:auto;">
            <div class="site-reset-password">
                <a target="_blank" href="<?php echo Yii::getAlias("@web"); ?>"><img title="Sitetrack" alt="Sitetrack" src="<?php echo Yii::getAlias("@web") ?>/img/logo_inner.png"></a>
                <h3><?= Html::encode($this->title) ?></h3>

                <?php
                foreach (\Yii::$app->session->getAllFlashes() as $key => $message) {
                    echo '<div class="alert alert-' . ($key == 'error' ? "danger" : "success") . '">' . $message . '</div>';
                }
                ?>
                
                <p>Please choose your new password:</p>

                <div class="row">
                    <div class="col-lg-12">
                        <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
                            <?= $form->field($model, 'password', ['options' => ['class' => 'input-block-level'], 'inputOptions' => ['placeholder' => 'New Password']])->passwordInput() ?>
                        <div class="form-group">
                        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
                        </div>
<?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
