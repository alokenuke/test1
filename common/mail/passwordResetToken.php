<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

    $resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>

<img src="https://admin.sitetrack-nfc.com/img/logo_inner.png" alt="Site Track NFC Logo" /><br />

Hello <?= Html::encode($user->first_name) ?> <?= Html::encode($user->last_name) ?>,<br /><br />

We have received a New Password request for your user account "<?= Html::encode($user->username) ?>".<br /><br />

Please <?= Html::a(Html::encode("CLICK HERE"), $resetLink) ?> to reset your password.<br /><br />
Or <br /><br />
Copy and Paste the following text in your browser address window and press ‘Enter’. <br />
<?= Html::a(Html::encode($resetLink), $resetLink) ?><br />
In the event that you have not made any such request, your old password is still functional for you to log-in. You also need to contact your primary SiteTrack account admin at <?= Html::encode($user->adminEmail) ?> for security reasons.<br /><br />
If you have any questions or concerns, please write to us at support@sitetrack-nfc.com indicating your Company Name and Username.<br /><br />
For your reference, The SiteTrack Backend login URL is https://admin.sitetrack-nfc.com . <br /><br />
Great to have you on-board with us. Do let us know if we could be of any further assistance.<br /><br />

SiteTrack Admin<br />
www.SiteTrack-NFC.com