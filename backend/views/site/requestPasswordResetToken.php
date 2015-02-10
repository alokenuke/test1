<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\PasswordResetRequestForm */

$this->title = 'Request password reset';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <div class="tab-content">
        <div id="login" class="tab-pane active" style="max-width: 300px;margin:auto;">
            <div class="site-request-password-reset">
                <a target="_blank" href="<?php echo Yii::getAlias("@web");?>"><img title="Sitetrack" alt="Sitetrack" src="<?php echo Yii::getAlias("@web")?>/img/logo_inner.png"></a>
                <h3><?= Html::encode($this->title) ?></h3>

                <?php
                    foreach (\Yii::$app->session->getAllFlashes() as $key => $message) {
                        echo '<div class="alert alert-' . ($key=='error'?"danger":"success") . '">' . $message . '</div>';
                    }
                ?>
                <p>Please fill out your email. A link to reset password will be sent there.</p>

                <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
                    <?= $form->field($model, 'email', ['options' => ['class' => 'input-block-level'], 'inputOptions' => ['placeholder' => 'Email']]) ?>
                    <div class="form-group mt-15">
                        <?= Html::submitButton('Send', ['class' => 'btn btn-large btn-primary btn-block']) ?>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
    <div class="text-center">
        <ul class="list-inline">
            <li><a class="text-muted" href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['site/login'])?>" data-toggle="tab">Return to Login</a></li>
        </ul>
    </div>
</div>
<style>
    .control-label {display:none;}
    .help-block{display:none;}
    .has-error .help-block {display:block}
</style>