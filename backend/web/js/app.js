var app = angular.module('siteTrackApp', ['ngRoute', 'ngAnimate', 'ui.bootstrap', 'ngResource', 'appApp.services', 'ui.select', 'ngSanitize', 'ui.tree', 'angularFileUpload', 'angular-loading-bar']);

app.run(function($http, $window, $rootScope, $location, $route) {
    delete $window.sessionStorage.token;
    if(!$window.sessionStorage.token) {
        $http.get("/api/gettoken").success(function(data) {
          $window.sessionStorage.token = data.token; 
        });
    }
    
    $rootScope.searchTags = function(search) {
        if(search.length>2) {
            $location.path('/tags');
            $route.reload();
        }
    }
});

app.constant('tooltip', {
    'from_task_process': "This tooltip related to 'from task process'.",
    'to_task_process': "This tooltip related to 'to task process'.",
    'mandatory': "This tooltip related to 'mandatory'.",
    'email_notification': "This tooltip related to 'email notification'.",
    'allow_be': "This tooltip is related to 'Allow BE'.",
    'hierarchy_compulsory': "This tooltip is related to 'Hierarchy Compulsory'.",
});

app.constant('page_dropdown', {
    '10': "10",
    '20': "20",
    '30': "30",
    '40': "40",
    '50': "50"
});

app.constant('process_stage_type', {
    '1': "Checkbox options",
    '2': "Radio Options",
    '3': "Status (%)",
    '4': "Dropdown",
    '5': "Textbox"
});

app.config(['$locationProvider', '$routeProvider', '$httpProvider', function ($locationProvider, $routeProvider, $httpProvider) {

    var path = '/templates/';
    
    $routeProvider

        .when('/login', {
            templateUrl: SiteUrl+'/site/login',
            controller: 'SiteIndex',
        })
                
        .when('/', {
            templateUrl: path+'dashboard.html',
            controller: 'SiteIndex',
        })
        
        .when('/change-password', {
            templateUrl: path+'site/change-password.html',
            controller: 'ChangePassword',
        })
        
        .when('/labeltemplates', {
            templateUrl: path+'labeltemplates/manage.html',
            controller: 'ManageLabelTemplates',
        })
        
        .when('/roles', {
            templateUrl: path+'roles/index.html',
            controller: 'RolesIndex'
        })
        
        .when('/roles/create', {
            templateUrl: path+'roles/create.html',
            controller: 'RolesAdd'
        })
        .when('/roles/update/:id', {
            templateUrl: path+'roles/create.html',
            controller: 'RolesUpdate'
        })
        
        .when('/users', {
            templateUrl: path+'users/index.html',
            controller: 'UserIndex'
        })
        
        .when('/users/create', {
            templateUrl: path+'users/create.html',
            controller: 'UserCreate'
        })

	.when('/users/update/:id', {
            templateUrl: path+'users/update.html',
            controller: 'UserUpdate',
        })

        .when('/user-groups', {
            templateUrl: path+'user-group/index.html',
            controller: 'UserGroup'
        })
        
        .when('/projects', {
            templateUrl: path+'projects/index.html',
            controller: 'ProjectIndex'
        })
        
        .when('/projects/create', {
            templateUrl: path+'projects/form.html',
            controller: 'ProjectForm'
        })
        
        .when('/projects/update/:id', {
            templateUrl: path+'projects/form.html',
            controller: 'ProjectForm'
        })
        
        .when('/project-levels', {
            templateUrl: path+'projects/manage-level.html',
            controller: 'ProjectLevel'
        })
        
        .when('/user-groups', {
            templateUrl: path+'user-group/index.html',
            controller: 'UserGroup'
        })
        
        .when('/tagitems', {
            templateUrl: path+'tagitems/index.html',
            controller: 'TagItems'
        })
        
        .when('/tags', {
            templateUrl: path+'tags/index.html',
            controller: 'TagIndex'
        })
        
        .when('/tag-process-flow', {
            templateUrl: path+'projects/process-flow.html',
            controller: 'ProcessFlow'
        })
        
        .when('/tags/create', {
            templateUrl: path+'tags/create-simple-tag.html',
            controller: 'TagsCreate'
        })
        
        .when('/tags/update/:id/:project_id', {
            templateUrl: path+'tags/update-simple-tag.html',
            controller: 'TagsUpdate'
        })
        
        .when('/tags/createmaster', {
            templateUrl: path+'tags/create-master-tag.html',
            controller: 'TagsCreateMaster'
        })
        
        .when('/tags/updatemaster/:id/:project_id', {
            templateUrl: path+'tags/update-master-tag.html',
            controller: 'TagsUpdateMaster'
        })
        
        .when('/tags/:id', {
            templateUrl: path+'tags/view-tag.html',
            controller: 'TagsView'
        })
        
        .when('/r-:mod', {
            templateUrl: function(modattr){
                return path + 'mod/index' + '.html';
            },
            controller: 'Index'
        })

        .when('/r-:mod/create', {
            templateUrl: function(modattr){
                return path + 'mod/form.html';
            },
            controller: 'Create'
        })

        .when('/r-:mod/delete/:id', {
            templateUrl: function(modattr){
                return path + 'mod/index.html';
            },
            controller: 'Delete'
        })

        .when('/r-:mod/:id', {
            templateUrl: function(modattr){
                return path + 'mod/view.html';
            },
            controller: 'View'
        })

        .when('/r-:mod/update/:id', {
            templateUrl: function(modattr){
                return path + 'mod/form.html'
            },
            controller: 'Form'
        })

        .when('/site/error', {
            templateUrl: path + 'site/error.html',
            controller: 'error'
        })

        .otherwise({
            templateUrl: path + 'site/error.html',
            controller: 'error'
        });

    //$locationProvider.html5Mode(true).hashPrefix('!');
}]);

app.controller('SiteIndex', ['$scope', 'rest', 'breadcrumbsService', '$http', "$location", function ($scope, rest, breadcrumbsService, $http, $location) {
        
    rest.path = "projects";
    breadcrumbsService.clearAll();
    breadcrumbsService.setTitle("Site Track - Dashboard");
    breadcrumbsService.headTitle(" ");
    
    if($location.$$path!='/login') {
        rest.models().success(function (data) {
            $scope.projects = data.items;
        })
    }
}]);


app.controller('ChangePassword', ['$scope', 'rest', 'breadcrumbsService', '$http', 'alertService', function ($scope, rest, breadcrumbsService, $http, alertService) {
        
    rest.path = "site";
    
    breadcrumbsService.clearAll();
    breadcrumbsService.setTitle("Change Password");
    breadcrumbsService.add("/#/", "Home");
    breadcrumbsService.add("/#/change-password", "Settings - Change Password");
    
    $scope.model = {};
    $scope.serverError = {};
    $scope.change = function(data){
        $http.post('/users/change-password',{ChangePassword:$scope.model}).success(function() {
            alertService.clearAll();
            alertService.add("success", "Password changed successfully.");
        }).error(function(data){ 
            alertService.clearAll();
            angular.forEach(data, function(value) {
                $scope.serverError[value.field] = value.message;
            });
        });
    }
    
}]);

app.controller('error', ['$scope', function ($scope) {
    $scope.error = {
        code: 404,
        message: "The above error occurred while the Web server was processing your request."
    };
}]);

app.service('rest', function ($http, $location, $routeParams) {
    return {
        baseUrl: '/',
        path: "",
        
        models: function (params) {
            return $http.post(this.baseUrl + this.path + "/search", params);
        },
        customModelData: function (path, params) {
            return $http.post(this.baseUrl + path , params);
        },
        getModels: function(mod, select, cond) {
            return $http.post("/"+ mod, {'search': cond, 'select': select});;
        },
        model: function () {
            if ($routeParams.expand != null) {
                return $http.get(this.baseUrl + this.path + "/" + $routeParams.id + '&expand=' + $routeParams.expand);
            }
            return $http.get(this.baseUrl + this.path + "/" + $routeParams.id);
        },
        getFields:function() {
            return $http.get(this.baseUrl+ this.path + "/fields");
        },

        get: function () {
            return $http.get(this.baseUrl + this.path);
        },

        postModel: function (model) {
            return $http.post(this.baseUrl + this.path, model);
        },

        putModel: function (model) {
            return $http.put(this.baseUrl+ this.path + "/" + $routeParams.id, model);
        },
        deleteModel: function (model) {
            return $http.delete(this.baseUrl+ this.path + "/" + $routeParams.id, model);
        },
        deleteById: function (model) {
            return $http.delete(this.baseUrl+ this.path + "/" + model.id, model);
        },
        setData: function(mod, select, cond) {
            return $http.post("/"+ mod, {'search': cond, 'select': select});;
        }
    };
});

app.factory('Security', ['$http', function ($http) {

        var token;

        function login(email, password) {
            return $http.post('/auth/login', {email: email, password: password})
                .then(function (response) {

                    if (response.data.token) {
                        token=response.data.token;
                    }
                });
        }

        function getToken(){
            return token;
        }

        return {
            login:login,
            token:getToken
        };     
}]);

app.factory('authHttpResponseInterceptor',['$q','$location', '$window',function($q,$location, $window) {
    return {
      response: function(response){
          if (response.status === 401) {
              $location.path('/login').replace();
          }

          return response || $q.when(response);
      },
      responseError: function(rejection) {
          if (rejection.status === 401) {
              $location.path('/login').replace();
          }
          return $q.reject(rejection);
      },
       request: function (config) {
            config.headers = config.headers || {};
            
            if ($window.sessionStorage.token) {
                if(config.url.search("/api") != -1 && config.url.search("/api/gettoken") == -1)
                    config.url = config.url+"&access-token="+$window.sessionStorage.token;
            }
            return config;
      },
    };
}])
.config(['$httpProvider',function($httpProvider) {
    //Http Intercpetor to check auth failures for xhr requests
    $httpProvider.interceptors.push('authHttpResponseInterceptor');
    
    $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';    
}]);