<?php

namespace backend\controllers;

class ReportsdownloadController extends \yii\web\Controller
{
    public function actionPreviewtemplate()
    {
        return $this->renderPartial('_previewtemplate', [
            'template_param' => \Yii::$app->request->post(),
        ]);
    }

}
