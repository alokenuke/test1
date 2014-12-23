var app = angular.module('siteTrackApp', ['ngRoute', 'ngAnimate', 'ui.bootstrap', 'ngResource', 'appApp.services', 'ui.select', 'ngSanitize']);

app.run(function($http, $window) {
    delete $window.sessionStorage.token;
    if(!$window.sessionStorage.token) {
        $http.get("/api/gettoken?mod=user").success(function(data) {
          $window.sessionStorage.token = data.token; 
        });
    }
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

        .when('/site/index', {
            templateUrl: path+'site/main.html',
            controller: 'SiteIndex'
        })
        
        .when('/projects', {
            templateUrl: path+'project/tabs.html',
            controller: 'ProjectIndex'
        })
        
        .when('/projects/create', {
            templateUrl: path+'project/create.html',
            controller: 'ProjectCreate'
        })
        
        .when('/projects/:id', {
            templateUrl: path+'project/view.html',
            controller: 'ProjectView'
        })
        
        .when('/projects/update/:id', {
            templateUrl: path+'project/form.html',
            controller: 'ProjectUpdate'
        })
        
        .when('/tags', {
            templateUrl: path+'tags/index.html',
            controller: 'TagIndex'
        })
        
        .when('/tag/create', {
            templateUrl: path+'tags/create-simple-tag.html',
            controller: 'TagsCreate'
        })
        
        .when('/tags/:id', {
            templateUrl: path+'tag/view.html',
            controller: 'TagView'
        })
        
        .when('/tag/update/:id', {
            templateUrl: path+'tag/form.html',
            controller: 'TagUpdate'
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

app.controller('SiteIndex', ['$scope', 'rest', 'breadcrumbsService', '$http', function ($scope, rest, breadcrumbsService, $http) {
        
    rest.path = "projects";
    
    rest.models().success(function (data) {
        $scope.projects = data.items;

        $scope.totalCount = data._meta.totalCount;
        $scope.pageCount = data._meta.pageCount;
        $scope.currentPage = (data._meta.currentPage+1);
        $scope.numPerPage = data._meta.perPage;

        $http.get("/api/getall?mod=projects").success(function(data) {
            $scope.projects = data.items;
        });
    })
}]);

app.controller('error', ['$scope', function ($scope) {
    $scope.error = {
        code: 404,
        message: "The above error occurred while the Web server was processing your request."
    };
}]);

app.service('rest', function ($http, $location, $routeParams) {
    return {
        baseUrl: '/api',
        path: "",
        
        models: function () {
            if (Object.keys($routeParams).length > 0) {
                var param = "";
                $.each($routeParams, function(key, val) {
                    if(key=="mod")
                        return;
                    param = (param==""?"":"&")+key+"=" + val;
                });
                return $http.get(this.baseUrl + "?mod=" + this.path +'&' + param);
            }
            return $http.get(this.baseUrl + "?mod=" + this.path + location.search);
        },

        model: function () {
            if ($routeParams.expand != null) {
                return $http.get(this.baseUrl + "/" + $routeParams.id + "?mod=" + this.path +'&expand=' + $routeParams.expand);
            }
            return $http.get(this.baseUrl + "/" + $routeParams.id + "?mod=" + this.path);
        },
        
        getFields:function() {
            return $http.get(this.baseUrl + "/fields" + "?mod=" + this.path);
        },

        get: function () {
            return $http.get(this.baseUrl + "?mod=" + this.path);
        },

        postModel: function (model) {
            return $http.post(this.baseUrl + "?mod=" + this.path, model);
        },

        putModel: function (model) {
            return $http.put(this.baseUrl + "/" + $routeParams.id + "?mod=" + this.path, model);
        },
        deleteModel: function (model) {
            return $http.delete(this.baseUrl + "/" + $routeParams.id + "?mod=" + this.path, model);
        },
        deleteById: function (model) {
            return $http.delete(this.baseUrl + "/" + model.id + "?mod=" + this.path, model);
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