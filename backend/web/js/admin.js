var app = angular.module('siteTrackApp', ['ngRoute','ngSanitize', 'ui.bootstrap', 'ngResource', 'appApp.services', 'angularFileUpload', 'angular-loading-bar']);

app.constant('page_dropdown', {
    '10': "10",
    '20': "20",
    '30': "30",
    '40': "40",
    '50': "50"
});
app.run(function($http, $window, $rootScope, $location, $route) {
    delete $window.sessionStorage.token;
    if(!$window.sessionStorage.token) {
        $http.get("/api/gettoken").success(function(data) {
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
            templateUrl: path+'admin/dashboard.html',
            controller: 'SiteIndex',
        })
        
        .when('/change-password', {
            templateUrl: path+'site/change-password.html',
            controller: 'ChangePassword',
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

        .when('/site/error', {
            templateUrl: path + 'site/error.html',
            controller: 'error'
        })
        
        .when('/sites-users', {
            templateUrl: path+'admin/users.html',
            controller: 'UserIndex'
        })
        
        .when('/create-user', {
            templateUrl: path+'admin/create-user.html',
            controller: 'UserCreate'
        })
        
        .when('/update/user/:id', {
            templateUrl: path+'admin/create-user.html',
            controller: 'UserCreate'
        })
        
        .when('/sites-roles', {
            templateUrl: path+'admin/roles.html',
            controller: 'RolesIndex'
        })
        
        .when('/add-role', {
            templateUrl: path+'admin/create-role.html',
            controller: 'RolesAdd'
        })
        
        .when('/roles/update/:id', {
            templateUrl: path + 'admin/create-role.html',
            controller: 'RolesUpdate'
        })
        
        .when('/companies', {
            templateUrl: path+'admin/companies.html',
            controller: 'Companies'
        })
        
        .when('/create-company', {
            templateUrl: path+'admin/create-company.html',
            controller: 'CreateCompany'
        })
        
        .when('/company/update/:id', {
            templateUrl: path+'admin/create-company.html',
            controller: 'CreateCompany',
        })
        
        .when('/sites-membership', {
            templateUrl: path+'admin/memberships.html',
            controller: 'MembershipIndex'
        })
        
        .when('/add-membership', {
            templateUrl: path+'admin/create-membership.html',
            controller: 'MembershipAdd'
        })
        
        .when('/membership/update/:id', {
            templateUrl: path + 'admin/create-membership.html',
            controller: 'MembershipAdd'
        })
        
        .otherwise({
            templateUrl: path + 'site/error.html',
            controller: 'error'
        });
        
    //$locationProvider.html5Mode(true).hashPrefix('!');
}]);

app.controller('SiteIndex', ['$scope', 'rest', 'breadcrumbsService', '$http', "$location", function ($scope, rest, breadcrumbsService, $http, $location) {
        
    breadcrumbsService.clearAll();
    breadcrumbsService.setTitle("SiteTrack - Owner Admin - Dashboard");    
    breadcrumbsService.headTitle(" ");
    
    if($location.$$path!='/login') {
        if($location.$$path=='/') {
            $http.post("company/stats").success(function (data) {
                $scope.stats = data;
            });
            $http.post("company/getall?expand=stats,membership").success(function (data) {
                $scope.companies = data.items;
            });
        }
    }
}]);


app.controller('ChangePassword', ['$scope', 'rest', 'breadcrumbsService', '$http', 'alertService', function ($scope, rest, breadcrumbsService, $http, alertService) {
        
    rest.path = "site";
    
    breadcrumbsService.clearAll();
    breadcrumbsService.setTitle("SiteTrack - Owner Admin - Change Password");
    breadcrumbsService.headTitle("Change Password");
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

app.controller('UserIndex', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown) {
      
        rest.path = "users";
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("SiteTrack - Owner Admin - Manage Users");
        breadcrumbsService.headTitle("Manage Users");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/users", "Manage - Users");
        
        $scope.page_dropdown = page_dropdown;
        $scope.$search = {};
        $scope.rec_noti = [
            {id: 'daily', name:'Daily'},
            {id: 'weekly', name:'Weekly'},
            {id: 'monthly', name:'Monthly'},
            {id: 'yearly', name:'Yearly'},
        ];
        
        $scope.sortUser = function(elm) {
            $scope.sortBy = elm;
            if(typeof $scope.sort !== 'undefined' && typeof $scope.sort[elm] !== 'undefined' && $scope.sort[elm].search("-up")!==-1) {
                $scope.sort[elm] = "-down";
                $scope.sortBy = "-"+elm;
            }
            else {
                $scope.sort = [];
                $scope.sort[elm] = "-up";
            }
            updateUserList();
        };
        
        $scope.removeUser =  function(model, $index) {
            rest.deleteById(model).success(function() {
                $scope.users.splice($index, 1);
            }).error(function(data) {errorCallback(data);});
        }
        
        $scope.pageChanged = function() {
            updateUserList();
        }
        
        $scope.searchUser = function(){
           updateUserList();
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                if(typeof data !== 'object')
                {
                    alertService.clearAll();
                    alertService.add("error", data);
                }
                else
                    alertService.add('error', "Error in processing your request. Please try again.");
            }
                
        };
        
        $scope.setPageLimit = function(){
            updateUserList();
        }
        var updateUserList = function() {
            var params = {'search': $scope.$search, 'sort': $scope.sortBy, 'page':$scope.currentPage, 'limit': $scope.numPerPage};
            rest.models(params).success(function (data) {
                $scope.users = data.items;
                $scope.totalCount = data._meta.totalCount;
                $scope.pageCount = data._meta.pageCount;
                $scope.currentPage = (data._meta.currentPage);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        
        $scope.sortUser("-id");
    }])

app.controller('UserCreate',
['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','$upload',
    function ($scope, rest, $location, $route, $rootScope, alertService, $http, breadcrumbsService,$upload) {
        
        rest.path = "users";
        $scope.user = {};
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("SiteTrack - Owner Admin - Create User");
        breadcrumbsService.headTitle("Create User");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/site-users", "Manage Users");
        breadcrumbsService.add("/#/create-user", "Create User");
            
       $scope.serverError = [];
        
        rest.setData("roles/getall", ['id', 'role_name'], {"company_id": 0}).success(function(data) {
            $scope.roles = data.items;
        });
        
        if($rootScope.id)
            rest.model({'id': $rootScope.id}).success(function(data) {
                $scope.user = data;
            });
        
        $scope.saveUser  = function(){
            if ($scope.user.id)
            {
                rest.putModel($scope.user).success(function (data) {
                    alertService.clearAll();
                    alertService.add("success", "User updated.");
                    $location.path('/sites-users').replace();
                }).error(function (data) {
                    alertService.clearAll();
                    alertService.add("error", "Validation Error");
                    angular.forEach(data, function (child_value, child_key) {
                        $scope.serverError[child_value['field']] = child_value['message'];
                    });
                });
            } else {
                rest.postModel($scope.user).success(function (data) {
                    alertService.clearAll();
                    alertService.add("success", "New user created successfully!!.");
                    $location.path('/sites-users').replace();
                }).error(function (data) {
                    alertService.clearAll();
                    alertService.add("error", "Validation Error");
                    angular.forEach(data, function (child_value, child_key) {
                        $scope.serverError[child_value['field']] = child_value['message'];
                    });
                });
            } 
        }
 }])
 
app.controller('Companies', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown) {
      
        rest.path = "company";
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("SiteTrack - Owner Admin - Manage Companies");
        breadcrumbsService.headTitle("Manage Companies");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/companies", "Manage - Companies");
        
        $scope.page_dropdown = page_dropdown;
        $scope.$search = {};
        
        $scope.searchCompany = function(){
           updateCompanyList();
        }
        
        
        $scope.sortCompany = function(elm) {
            $scope.sortBy = elm;
            if(typeof $scope.sort !== 'undefined' && typeof $scope.sort[elm] !== 'undefined' && $scope.sort[elm].search("-up")!==-1) {
                $scope.sort[elm] = "-down";
                $scope.sortBy = "-"+elm;
            }
            else {
                $scope.sort = [];
                $scope.sort[elm] = "-up";
            }
            updateCompanyList();
        };
               
        $scope.pageChanged = function() {
            updateCompanyList();
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                if(typeof data !== 'object')
                {
                    alertService.clearAll();
                    alertService.add("error", data);
                }
                else
                    alertService.add('error', "Error in processing your request. Please try again.");
            }
                
        };
        
        $scope.removeCompany =  function(model, $index) {
           
            rest.deleteById(model);
            
            $scope.companies.splice($index, 1);
        }
        
        $scope.setPageLimit = function(){
            updateCompanyList();
        }
        var updateCompanyList = function() {
            var params = {'search': $scope.$search, 'sort': $scope.sortBy, 'page':$scope.currentPage, 'limit': $scope.numPerPage};
            rest.customModelData("company/search?expand=membership", params).success(function (data) {
                $scope.companies = data.items;
                $scope.totalCount = data._meta.totalCount;
                $scope.pageCount = data._meta.pageCount;
                $scope.currentPage = (data._meta.currentPage);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        
        rest.setData("membership/getall", [], {'project_status': null}).success(function (data) {
            $scope.memberships = data.items;
        });
        
        $scope.sortCompany("-id");
    }])

app.controller('CreateCompany', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown','$upload', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown,$upload) {
      
        rest.path = "company";
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("SiteTrack - Owner Admin - Manage Companies");
        breadcrumbsService.headTitle("Manage Companies");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/create-company", "Create - Company");
        
        $scope.serverError = {};
        $scope.company = {};
        
        $scope.datepickers = {fromDate: false,toDate: false}
        $scope.openCalendar = function($event, which) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.datepickers[which]= true;
        };
        $scope.dateOptions = {formatYear: 'yy',startingDay: 1};
        $scope.formats = ['dd MMM yyyy'];
        $scope.format = $scope.formats[0];
        

        $scope.onFileSelect = function ($files) {
            //$files: an array of files selected, each file has name, size, and type.
            for (var i = 0; i < $files.length; i++) {
                var file = $files[i];
                $scope.upload = $upload.upload({
                    url: 'filemanager/upload', //upload.php script, node.js route, or servlet url
                    data: {myObj: $scope.myModelObj},
                    file: file,
                }).progress(function (evt) {
                    console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
                }).success(function (data, status, headers, config) {
                    // file is uploaded successfully
                    $scope.company.company_logo = data;
                });
            }
        };

        $scope.removePhoto = function () {
            $scope.company.company_logo = null;
        }

        $scope.saveCompany = function () {
            
            $scope.serverError = {};
            
            if(typeof $scope.company.expiry_date !== 'undefined') {
                $monthArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $expiryDate = $scope.company.expiry_date;
                if($expiryDate !=null && typeof $expiryDate === 'object') 
                    $scope.company.expiry_date = $expiryDate.getDate()+" "+$monthArr[$expiryDate.getMonth()]+" "+$expiryDate.getFullYear();
            }

            if ($scope.company.id)
            {
                $http.post('company/savecompany',{'company':$scope.company,'user':$scope.user}).success(function (data) {
                    alertService.clearAll();
                    alertService.add("success", "Project details updated.");
                    $location.path('/companies').replace();
                }).error(function (data) {
                    alertService.clearAll();
                    alertService.add("error", "Validation Error");
                    angular.forEach(data, function (v) {
                        $scope.serverError[v['field']] = v['message'];
                    });
                });
            }
            else {
                
                $http.post('company/savecompany',{'company':$scope.company,'user':$scope.user}).success(function (data) {
                    alertService.clearAll();
                    alertService.add("success", "New Company Created.");
                    $location.path('/companies').replace();
                }).error(function (data) {
                    alertService.clearAll();
                    alertService.add("error", "Validation Error");
                    angular.forEach(data, function (v) {
                        $scope.serverError[v['field']] = v['message'];
                    });
                });
            }
        }
        rest.setData("membership/getall", ['id', 'name'], {'project_status': null}).success(function (data) {
            $scope.memberships = data.items;
        });
        
        if ($routeParams.id) {
            $http.get("company/"+$routeParams.id+"?expand=user,membership").success(function (data) {
                $scope.company = data;
                $scope.membership = data.membership;
                $scope.user = data.user;
            });
        }
        
    }])

app.controller('RolesIndex', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown) {
        $scope.tagsNum = 1;
        rest.path = "roles";
        $scope.page_dropdown = page_dropdown;
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("SiteTrack - Owner Admin - Manage Roles");
        breadcrumbsService.headTitle("Manage Roles");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/sites-roles", "Roles manage");
        
        $scope.pageChanged = function() {
            updateUserList();
        }
        var errorCallback = function (data) {
            if(data.status!=401) {
                if(typeof data !== 'object')
                {
                    alertService.clearAll();
                    alertService.add("error", data);
                }
                else
                    alertService.add('error', "Error in processing your request. Please try again.");
            }
                
        };
        
        $scope.setPageLimit = function(){
            updateUserList();
        }
        
        
        var updateUserList = function() {
            var params = {'search': $scope.$search, 'sort': $scope.sortBy, 'page':$scope.currentPage, 'limit': $scope.numPerPage};
            rest.models(params).success(function (data) {
                $scope.roles = data.items;
                $scope.totalCount = data._meta.totalCount;
                $scope.pageCount = data._meta.pageCount;
                $scope.currentPage = (data._meta.currentPage+1);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        
        updateUserList();
        
        $scope.removeRole = function (model, $index) {

            rest.deleteById(model).success(function() {
                $scope.roles.splice($index, 1);
            }).error(function(data) {
                errorCallback(data);
            });
        }
        
    }])

app.controller('RolesAdd', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown','$location',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown,$location) {
        $scope.tagsNum = 1;
        rest.path = "roles";
        $scope.page_dropdown = page_dropdown;
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("SiteTrack - Owner Admin - Create New Role");
        breadcrumbsService.headTitle("Create New Role");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/sites-roles", "Roles");
        breadcrumbsService.add("/#/add-role", "Create New Role");
        $scope.moduleactions = {};
        
        $scope.role = {};
        
        $scope.addRoles = function(){
            $scope.serverError = {};
            $scope.role['type'] = 'Client';
            $scope.role['moduleactions'] = $scope.moduleactions;
            rest.postModel($scope.role).success(function(data) {
                $location.path("/sites-roles");
            });
        }
        
        $http.post("roles/loadactions").success(function(data) {
            $scope.moduleactions = data;
        });
    }])

app.controller('RolesUpdate', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown','$location', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown,$location) {
        $scope.tagsNum = 1;
        rest.path = "roles";
        $scope.page_dropdown = page_dropdown;
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("SiteTrack - Owner Admin - Update Role");
        breadcrumbsService.headTitle("Update Role");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/roles/update/"+$routeParams.id, "Update Role");
        $scope.moduleactions = {};
        
        $scope.role = {};
        
        $http.get("/roles/"+$routeParams.id+"?expand=roleSettings", {'id': $routeParams.id}).success(function(data) {
                $scope.role = data;
        });
        
        $scope.addRoles = function(){
            $scope.serverError = {};
            $scope.role['type'] = 'Client';
            $scope.role['moduleactions'] = $scope.moduleactions;
            rest.putModel($scope.role).success(function(data) {
                alertService.clearAll();
                alertService.add("success", "Role updated.");
                $location.path('/sites-roles').replace();
            }).error(function(data) { 
                alertService.clearAll();
                alertService.add("error", "Validation Error");
            });
        }
        
        $http.post("roles/loadactions", {'id': $routeParams.id}).success(function(data) {
            $scope.moduleactions = data;
        });
    }])

app.controller('MembershipIndex', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown) {
        $scope.tagsNum = 1;
        rest.path = "membership";
        $scope.page_dropdown = page_dropdown;
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("SiteTrack - Owner Admin - Manage Membership");
        breadcrumbsService.headTitle("Manage Membership");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/sites-membership", "Manage Membership");
        
        $scope.pageChanged = function() {
            updateMembership();
        }
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        $scope.setPageLimit = function(){
            updateMembership();
        }
        
        
        var updateMembership = function() {
            var params = {'search': $scope.$search, 'sort': $scope.sortBy, 'page':$scope.currentPage, 'limit': $scope.numPerPage};
            rest.models(params).success(function (data) {
                $scope.memberships = data.items;
                $scope.totalCount = data._meta.totalCount;
                $scope.pageCount = data._meta.pageCount;
                $scope.currentPage = (data._meta.currentPage+1);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        
        updateMembership();
        
        $scope.removeMembership = function (model, $index) {

            rest.deleteById(model);
            $scope.memberships.splice($index, 1);
        }
        
    }])

app.controller('MembershipAdd', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown','$location',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown,$location) {
        
        rest.path = "membership";
        $scope.page_dropdown = page_dropdown;
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("SiteTrack - Owner Admin - Membership");
        breadcrumbsService.headTitle("Create / Update Membership");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/sites-membership", "Membership");
        breadcrumbsService.add("/#/add-membership", "Create New Membership");
        $scope.moduleactions = {};
        
        $scope.serverError = {};
        
        if ($routeParams.id) {
            $http.get("membership/"+$routeParams.id).success(function (data) {
                $scope.membership = data;
            });
        }
        
        $scope.addMembership = function(){
            $scope.serverError = {};
             if ($scope.membership.id)
            {
                rest.putModel($scope.membership).success(function (data) {
                    alertService.clearAll();
                    alertService.add("success", "Membership updated.");
                    $location.path('/sites-membership').replace();
                }).error(function (data) {
                    alertService.clearAll();
                    alertService.add("error", "Validation Error");
                    angular.forEach(data, function (v) {
                        $scope.serverError[v['field']] = v['message'];
                    });
                });
            }
            else {
                rest.postModel($scope.membership).success(function (data) {
                    $location.path("/sites-membership");
                }).error(function (data) {
                    alertService.clearAll();
                    alertService.add("error", "Validation Error");
                    angular.forEach(data, function (v) {
                        $scope.serverError[v['field']] = v['message'];
                    });
                });
            }
        }
        
    }])


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
    };
}])
.config(['$httpProvider',function($httpProvider) {
    //Http Intercpetor to check auth failures for xhr requests
    $httpProvider.interceptors.push('authHttpResponseInterceptor');
    
    $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';    
}]);