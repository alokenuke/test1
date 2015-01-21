<?php

namespace backend\models;

use Yii;

/**
 * This is the model for sending mails.
 */
class SendMails
{
    public function send($template, $to, $subject, $data, $from="")
    {
        $mail = \Yii::$app->mailer->compose($template, ['data' => $data]);
        if(!$from)
            $mail->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot']);
        else
            $mail->setFrom ($from);
        $mail->setTo($to);
        $mail->setSubject($subject);
        $mail->send();
    }
}
