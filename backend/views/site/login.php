<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <div class="tab-content">
        <div id="login" class="tab-pane active" style="max-width: 300px;margin:auto;">
            <center style="padding-bottom: 20px;">
                <a target="_blank" href="<?php echo Yii::getAlias("@web");?>"><img title="Sitetrack" alt="Sitetrack" src="<?php echo Yii::getAlias("@web")?>/img/logo_inner.png"></a>
            </center>
            
            <?php
                foreach (\Yii::$app->session->getAllFlashes() as $key => $message) {
                    echo '<div class="alert alert-' . ($key=='error'?"danger":"success") . '">' . $message . '</div>';
                }
            ?>

            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                <?php $form->errorSummary($model);?>
                <?= $form->field($model, 'username', ['options' => ['class' => 'input-block-level'], 'inputOptions' => ['placeholder' => 'Username']]) ?>
                <?= $form->field($model, 'password', ['options' => ['class' => 'input-block-level'], 'inputOptions' => ['placeholder' => 'Password']])->passwordInput() ?>
                <?= $form->field($model, 'rememberMe')->checkbox() ?>
                <div class="form-group">
                    <?= Html::submitButton('Sign In', ['class' => 'btn btn-large btn-primary btn-block', 'name' => 'login-button']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="text-center">
        <ul class="list-inline">
            <li><a class="text-muted" href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['site/request-password-reset'])?>" data-toggle="tab">Forgot Password</a></li>
        </ul>
    </div>
</div>
<style>
    .control-label {display:none;}
    .help-block{display:none;}
    .has-error .help-block {display:block}
</style>