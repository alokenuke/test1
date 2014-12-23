<?php
namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use common\models\LoginForm;
use yii\filters\VerbFilter;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'dashboard'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
    
    public function actionDashboard()
    {
        return $this->renderPartial('dashboard');
    }
   
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            
            if (\yii::$app->request->getIsAjax()) {
                return $this->renderPartial('login', [
                    'model' => $model,
                ]);
            }
            else {
                return $this->render('login', [
                    'model' => $model,
                ]);
            }
                
        }
    }

    public function actionLogout()
    {
        $authKey = \Yii::$app->session->get('user.auth_key');
        $userId = \yii::$app->user->id;
        
        Yii::$app->user->logout();
        
        $authToken = \common\models\UserTokens::findOne(['token' => $authKey, 'user_id' => $userId, 'request_from' => 'webapp']);
        
        if($authToken) {
            $authToken->expiry_status = 1;
            $authToken->expire_on = time();
            $authToken->save();            
        }
        
        return $this->goHome();
    }
}
