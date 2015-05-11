<?php

namespace backend\models;

use Yii;
use yii\web\ForbiddenHttpException;

/**
 * RoleAccess provides simple access control based on a set of rules.
 *
 * RoleAccess is an action filter. It will check its [[rules]] to find
 * the first rule that matches the current context variables (such as user IP address, user role).
 * The matching rule will dictate whether to allow or deny the access to the requested controller
 * action. If no rule matches, the access will be denied.
 *
 * To use RoleAccess, declare it in the `behaviors()` method of your controller class.
 * For example, the following declarations will allow authenticated users to access the "create"
 * and "update" actions and deny all other users from accessing these two actions.
 *
 */

class RoleAccess extends \yii\base\ActionFilter
{
    /**
     * @var User|string the user object representing the authentication status or the ID of the user application component.
     */
    public $user;
    /**
     * @var array a list of access rule objects or configuration arrays for creating the rule objects.
     * If a rule is specified via a configuration array, it will be merged with [[ruleConfig]] first
     * before it is used for creating the rule object.
     * @see ruleConfig
     */
    public $rules = [];
    
    /**
     * Initializes the [[rules]] array by instantiating rule objects from configurations.
     */
    public function init()
    {
        parent::init();
        $this->user = Yii::$app->user;
        
        foreach ($this->rules as $i => $rule) {
            if (is_array($rule)) {
                $this->rules[$i] = $rule;
            }
        }
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        $user = $this->user;
        $request = Yii::$app->getRequest();
        
        foreach ($this->rules as $rule) {
            if ($allow = $this->allows($action, $request, $rule)) {
                return true;
            } elseif ($allow === false) {
                if (isset($this->denyCallback)) {
                    call_user_func($this->denyCallback, $rule, $action);
                } else {
                    $this->denyAccess($user);
                }
                return false;
            }
        }
        if (isset($this->denyCallback)) {
            call_user_func($this->denyCallback, null, $action);
        } else {
            $this->denyAccess($user);
        }
        return false;
    }

    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     * @param User $user the current user
     * @throws ForbiddenHttpException if the user is already logged in.
     */
    protected function denyAccess($user)
    {
        if ($user->isGuest) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You login has expired. Please login agagin'));
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }
    
    public function allows($action, $request, $rule)
    {
        if ($this->matchRole($rule['roles']) && $this->matchAction($rule, $action) && $this->matchIP($rule, $request->getUserIP())) {
            return $rule['allow'] ? true : false;
        } else {
            return null;
        }
    }
    
    /**
     * @param Action $action the action
     * @return boolean whether the rule applies to the action
     */
    protected function matchAction($rule, $action)
    {
        return empty($rule['actions']) || in_array($action->id, $rule['actions'], true);
    }
    
    protected function matchRole($roles)
    {
        if (empty($roles)) {
            return true;
        }
        foreach ($roles as $role) {
            if ($role === '?') {
                if ($this->user->isGuest) {
                    return true;
                }
            } elseif ($role === '@') {
                if (!$this->user->isGuest) {
                    return true;
                }
            } elseif ($this->checkAccess($role)) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * @param string $ip the IP address
     * @return boolean whether the rule applies to the IP address
     */
    protected function matchIP($rule, $ip)
    {
        if (empty($rule['ips'])) {
            return true;
        }
        foreach ($rule['ips'] as $rule) {
            if ($rule === '*' || $rule === $ip || (($pos = strpos($rule, '*')) !== false && !strncmp($ip, $rule, $pos))) {
                return true;
            }
        }

        return false;
    }
    
    protected function checkAccess($role) {
        $roleDetails = $this->user->identity->role_details;
        
        if($role == $roleDetails->type && $roleDetails->isAdmin) {
            return true;
        }
        else if($role == $roleDetails->type) {
            $action = Yii::$app->controller->action->id;
            if($action == 'search' || $action == 'getall')
                $action = ['list', 'list-all'];
            $controller = Yii::$app->controller->id;
            $roleSettings = RoleSettings::find()->andWhere(['role_id' => $roleDetails->id, 'module' => $controller])->one();
            if($roleSettings) {
                $actions = json_decode($roleSettings->role_params);
                if(is_array($action)) {
                    foreach($action as $act) {
                        if(isset($actions->$act) && $actions->$act == 1)
                                return true;
                    }
                    return false;
                }
                else if(isset($actions->$action) && $actions->$action == 1) {
                    return true;
                }
                else {
                    $moduleAction = new ModulesActions();
                    if(!$moduleAction->findOne(['module_name' => $controller, 'action' => $action]))
                        return true;
                }
            }
            else {
                $moduleAction = new ModulesActions();
                if(!$moduleAction->findOne(['module_name' => $controller, 'action' => $action]))
                    return true;
            }
        }
        return false;
    }
}