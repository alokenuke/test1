<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\models;

use Yii;
use yii\web\ServerErrorHttpException;

/**
 * DeleteAction implements the API endpoint for deleting a model.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DeleteAction extends \yii\rest\DeleteAction
{
    /**
     * Deletes a model.
     * @param mixed $id id of the model to be deleted.
     * @throws ServerErrorHttpException on failure.
     */
    public function run($id)
    {
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }
        
        $result = $model->actDelete();
        
        if ($result === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }
        else if($result !== true) {
            Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            return $result;
        }
        else if($model->getErrors()) {
            return $model;
        }
        Yii::$app->getResponse()->setStatusCode(204);
    }
}
