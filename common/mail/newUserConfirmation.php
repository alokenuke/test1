<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

//$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<img src="https://admin.sitetrack-nfc.com/img/logo_inner.png" alt="Site Track NFC Logo" /><br />
<p>Dear <?php echo $data->attributes['first_name']?> <?php echo $data->attributes['last_name']?></p>

<h2>Welcome to SiteTrack!</h2>

<p>Your Support Admin account details are as follows:</p>
<table>
<tbody>
<tr>
<td style="width: 100px;">
<p>Username:</p>
</td>
<td><?php echo $data->attributes['username']?></td>
</tr>
<tr>
<td>Email:</td>
<td><?php echo $data->attributes['email']?> </td>
</tr>
<tr>
<td>Password:</td>
<td><?php echo $data->newPassword?></td>
</tr>
</tbody>
</table>

<p>You can login to the backend system from https://admin.sitetrack-nfc.com .</p>

<p>Please contact the SiteTrack admin who has created your account for more information on usage and your role.</p>

<p>In the event that you have not made any such request OR do not subscribe to any such role OR if you realize this email is not for you and is sent to you by mistake, please forward this email to support@sitetrack-nfc.com .</p>

<p>Great to have you on-board with us. </p>

<p>SiteTrack Admin</p>
<p>www.SiteTrack-NFC.com</p>
