<?php 
    use \yii;  
?>
<script>
    var app = angular.module('siteTrackApp', ['ngRoute','ui.bootstrap', 'ngAnimate', 'ngResource', 'appApp.services', 'ui.select', 'ngSanitize', 'ui.tree', 'angularFileUpload', 'angular-loading-bar']);
    
    var routeForUnauthorizedAccess = '/noaccess';

<?php if(!Yii::$app->user->isGuest) { ?>
    app.run(function($http, $window, $rootScope, $location, $route) {
//        delete $window.sessionStorage.token;
//        if(!$window.sessionStorage.token) {
//            $http.get("/api/gettoken").success(function(data) {
//              $window.sessionStorage.token = data.token; 
//            });
//        }

        $rootScope.searchTags = function(search) {
            if(search.length>2) {
                $location.path('/tags');
                $route.reload();
            }
        }
    });
    
    app.constant('tooltip', {
        'from_task_process': "Select the specific Process flow level the user is assigned from.",
        'to_task_process': "Select the specific Process flow level the user is assigned up to.",
        'mandatory': "A Process Flow Level update from this individual is mandatory to move ahead to the next process level.",
        'email_notification': "Receive notification of the Tag updates as per the options set herewith.",
        'allow_be': "If a user is allowed BE (Backend Edit), they can make Tag updates/edits via website, else only from the mobile application.",
        'hierarchy_compulsory': "When Process Flow is made Hierarchy Compulsory, the Tag can be updated only in sequence of the flow from Top (First) to Bottom (Last).",
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

    <?php } ?>

    app.config(['$locationProvider', '$routeProvider', '$httpProvider', function ($locationProvider, $routeProvider, $httpProvider) {

        var path = '/templates/';

        $routeProvider

            .when('/login', {
                templateUrl: SiteUrl+'/site/login',
                controller: 'SiteIndex',
            })

        <?php if(!Yii::$app->user->isGuest) { ?>
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

            .when('/reporttemplates', {
                templateUrl: path+'reporttemplates/manage.html',
                controller: 'ManageReportTemplates',
            })

            .when('/roles', {
                templateUrl: path+'roles/index.html',
                controller: 'RolesIndex',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("roles", "list");
                    },
                }
            })

            .when('/roles/create', {
                templateUrl: path+'roles/create.html',
                controller: 'RolesAdd',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("roles", "create");
                    },
                }
            })
            .when('/roles/update/:id', {
                templateUrl: path+'roles/create.html',
                controller: 'RolesUpdate',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("roles", "update");
                    },
                }
            })

            .when('/users', {
                templateUrl: path+'users/index.html',
                controller: 'UserIndex',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("users", "list-all");
                    },
                }
            })

            .when('/reports', {
                templateUrl: path+'reports/index.html',
                controller: 'Reports'
            })

            .when('/reports/employee-logs', {
                templateUrl: path+'reports/employee-logs.html',
                controller: 'ReportsEmployeeLogs'
            })

            .when('/reports/timeattendance', {
                templateUrl: path+'reports/timeattendance-logs.html',
                controller: 'ReportsTimeattendanceLogs'
            })

            .when('/print-label', {
                templateUrl: path+'reports/printlabel.html',
                controller: 'PrintLabel'
            })

            .when('/timeattendance/print-label', {
                templateUrl: path+'reports/printtimeattendancelabel.html',
                controller: 'PrintTimeAttendanceLabel'
            })

            .when('/users/create', {
                templateUrl: path+'users/create.html',
                controller: 'UserCreate',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("users", "create");
                    },
                }
            })

            .when('/users/update/:id', {
                templateUrl: path+'users/update.html',
                controller: 'UserUpdate',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("users", "update");
                    },
                }
            })

            .when('/user-groups', {
                templateUrl: path+'user-group/index.html',
                controller: 'UserGroup'
            })

            .when('/projects', {
                templateUrl: path+'projects/index.html',
                controller: 'ProjectIndex',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("projects", "list-all");
                    },
                }
            })

            .when('/projects/create', {
                templateUrl: path+'projects/form.html',
                controller: 'ProjectForm',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("projects", "create");
                    },
                }
            })

            .when('/projects/update/:id', {
                templateUrl: path+'projects/form.html',
                controller: 'ProjectForm',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("projects", "update");
                    },
                }
            })

            .when('/project-levels', {
                templateUrl: path+'projects/manage-level.html',
                controller: 'ProjectLevel',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("projectlevel", "list-all");
                    },
                }
            })

            .when('/user-groups', {
                templateUrl: path+'user-group/index.html',
                controller: 'UserGroup',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("usergroups", "list-all");
                    },
                }
            })

            .when('/tagitems', {
                templateUrl: path+'tagitems/index.html',
                controller: 'TagItems',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("items", "list-all");
                    },
                }
            })

            .when('/timeattendance', {
                templateUrl: path+'timeattendance/index.html',
                controller: 'TimeAttendance',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("timeattendance", "list-all");
                    },
                }
            })

            .when('/timeattendance/create', {
                templateUrl: path+'timeattendance/form.html',
                controller: 'TimeAttendanceForm',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("timeattendance", "create");
                    },
                }
            })

            .when('/timeattendance/update/:id', {
                templateUrl: path+'timeattendance/form.html',
                controller: 'TimeAttendanceForm',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("timeattendance", "update");
                    },
                }
            })

            .when('/timeattendance/:id', {
                templateUrl: path+'timeattendance/view.html',
                controller: 'TimeAttendanceView',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("timeattendance", "view");
                    },
                }
            })

            .when('/tags', {
                templateUrl: path+'tags/index.html',
                controller: 'TagIndex',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("tags", "list");
                    },
                }
            })

            .when('/tag-process-flow', {
                templateUrl: path+'projects/process-flow.html',
                controller: 'ProcessFlow',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("tagprocess", "list-all");
                    },
                }
            })

            .when('/tags/create', {
                templateUrl: path+'tags/create-simple-tag.html',
                controller: 'TagsCreate',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("tags", "create-simple-tags");
                    },
                }
            })

            .when('/tags/update/:id/:project_id', {
                templateUrl: path+'tags/update-simple-tag.html',
                controller: 'TagsUpdate',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("tags", "updatesimpletags");
                    },
                }
            })

            .when('/tags/createmaster', {
                templateUrl: path+'tags/create-master-tag.html',
                controller: 'TagsCreateMaster',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("tags", "create-master-tags");
                    },
                }
            })

            .when('/tags/updatemaster/:id/:project_id', {
                templateUrl: path+'tags/update-master-tag.html',
                controller: 'TagsUpdateMaster',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("tags", "updatemastertags");
                    },
                }
            })

            .when('/tags/:id', {
                templateUrl: path+'tags/view-tag.html',
                controller: 'TagsView',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("tags", "view");
                    },
                }
            })

            .when('/site/error', {
                templateUrl: path + 'site/error.html',
                controller: 'error'
            })
            
            .when('/noaccess', {
                templateUrl: path + 'site/403.html',
                controller: 'error'
            })

            .when('/import/projects', {
                templateUrl: path + 'import/projects.html',
                controller: 'ImportsProjects',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("imports", "import-projects");
                    },
                }
            })

            .when('/import/users', {
                templateUrl: path + 'import/users.html',
                controller: 'ImportsUsers',
                resolve: { 
                    //Here we would use all the hardwork we have done 
                    //above and make call to the authorization Service 
                    //resolve is a great feature in angular, which ensures that a route 
                    //controller (in this case superUserController ) is invoked for a route 
                    //only after the promises mentioned under it are resolved.
                    permission: function(authorizationService, $route) {
                        return authorizationService.permissionCheck("imports", "import-users");
                    },
                }
            })

        <?php } ?>

            .otherwise({
                templateUrl: path + 'site/error.html',
                controller: 'error'
            });

        //$locationProvider.html5Mode(true).hashPrefix('!');
    }]);

    app.controller('SiteIndex', ['$scope', 'rest', 'breadcrumbsService', '$http', "$location", function ($scope, rest, breadcrumbsService, $http, $location) {

        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Site Track - Dashboard");
        breadcrumbsService.headTitle(" ");

        <?php if(!Yii::$app->user->isGuest) { ?>

        if($location.$$path!='/login') {
            if($location.$$path=='/') {
                $http.post("users/stats").success(function (data) {
                    $scope.stats = data;
                });
                $http.post("projects/getall?expand=stats").success(function (data) {
                    $scope.projects = data.items;
                });
                $http.post("usergroups/getall?expand=stats").success(function (data) {
                    $scope.usergroups = data.items;
                })

                $.getScript('/js/Chart.min.js', function()
                {
                    //$scope.loadChart({});
                });

                $scope.projectProgress = function(project) {

                    $scope.chartForProject = project;

                    angular.element("#graphPopupBox").modal("show");

                    //code before the pause
                    setTimeout(function(){
                        $scope.loadChart({'project': project});
                        //do what you need here
                    }, 500);
                }

                $scope.loadChart = function(params) {

                    if(typeof window.myBar != 'undefined') {
                        window.myBar.destroy();
                    }

                    $http.post("projects/getchartstats", params).success(function (data) {
                        var $completedTags = [];
                        var $totalTags = [];
                        var $labels = [];
                        var $i=0

                        if(typeof data['labels'] == 'undefined')
                            angular.forEach(data.items, function(val) {
                                //if(val['totalTags']>0) {
                                    $labels[$i] = val['project_name'];
                                    $completedTags[$i] = val['completedTags'];
                                    $totalTags[$i++] = val['totalTags'];
                                //}
                            });
                        else {
                            $labels = data['labels'];
                            $completedTags = data['completedTags'];
                            $totalTags = data['totalTags'];
                        }
                        if($labels.length>0) {
                            var barChartData = {
                                labels : $labels,
                                datasets : [
                                    {
                                        label: "Completed Tags",
                                        fillColor : "rgba(220,220,220,0.5)",
                                        strokeColor : "rgba(220,220,220,0.8)",
                                        highlightFill: "rgba(220,220,220,0.75)",
                                        highlightStroke: "rgba(220,220,220,1)",
                                        data : $completedTags
                                    },
                                    {
                                        label: "Total Tags",
                                        fillColor : "rgba(151,187,205,0.5)",
                                        strokeColor : "rgba(151,187,205,0.8)",
                                        highlightFill : "rgba(151,187,205,0.75)",
                                        highlightStroke : "rgba(151,187,205,1)",
                                        data : $totalTags
                                    }
                                ]
                            }
                            var canvas = angular.element("canvas");
                            var ctx = canvas[0].getContext("2d");
                            window.myBar = new Chart(ctx).Bar(barChartData, {
                                    responsive : true,
                                    multiTooltipTemplate: "<%if (datasetLabel ){%><%=datasetLabel %>: <%}%><%= value %>",
                                    //String - A legend template
                                    legendTemplate : "<ul id=\"chart_legend\" class=\"<%=name.toLowerCase()%>-legend nav navbar-nav mt-5 pl-10\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].fillColor%>;display: inline-block; width: 20px;\">&nbsp;</span> <%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>"
                            });

                            $("#chart_legend").remove();
                            var legend = window.myBar.generateLegend();
                            $('#canvas').before(legend);
                        } else {
                            $('#canvas').attr("height", "0");
                            $('#canvas').before("<h4>There is nothing to show.</h4>");
                        }
                    });
                }

            }
        }
        <?php } ?>
    }]);

<?php if(!Yii::$app->user->isGuest) { ?>
    app.controller('ChangePassword', ['$scope', 'rest', 'breadcrumbsService', '$http', 'alertService', function ($scope, rest, breadcrumbsService, $http, alertService) {

        rest.path = "site";

        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Change Password");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/change-password", "Settings - Change Password");

        $scope.model = {};
        $scope.serverError = {};
        $scope.change = function(data){
            $scope.serverError = {};
            $http.post('/users/change-password',{ChangePassword:$scope.model}).success(function() {
                alertService.clearAll();
                alertService.add("success", "Password changed successfully.");
                $scope.model = {};
            }).error(function(data){ 
                alertService.clearAll();
                angular.forEach(data, function(value) {
                    $scope.serverError[value.field] = value.message;
                });
            });
        }

    }]);
<?php } ?>
    app.controller('error', ['$scope', function ($scope) {
        $scope.error = {
            code: 404,
            message: "The above error occurred while the Web server was processing your request."
        };
    }]);

<?php if(!Yii::$app->user->isGuest) { ?>
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
<?php } ?>
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
                else if (rejection.status === 404) {
                    $location.path('/error').replace();
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

    app.factory('authorizationService', function ($resource, $q, $rootScope, $location) {
        return {
            // We would cache the permission for the session,
            //to avoid roundtrip to server
            //for subsequent requests

            permissionModel: {
                permission: {},
                isPermissionLoaded: false
            },

            permissionCheck: function ($module, $action) {
                
                if(typeof this.permissionModel.permission[$module] != 'undefined' && this.permissionModel.permission[$module].length > 0)
                    this.permissionModel.isPermissionLoaded = true;
                else
                    this.permissionModel.isPermissionLoaded = false;

                // we will return a promise .
                var deferred = $q.defer();

                //this is just to keep a pointer to parent scope from within promise scope.
                var parentPointer = this;

                //Checking if permission object(list of roles for logged in user) 
                //is already filled from service
                if (this.permissionModel.isPermissionLoaded) {
                    //Check if the current user has required role to access the route
                    this.getPermission(this.permissionModel, $module, $action, deferred);
                } else {
                    //if permission is not obtained yet, we will get it from  server.
                    // 'api/permissionService' is the path of server web service , used for this example.

                    $resource('/roles/get-permission').get({"modules": $module}).$promise.then(function (response) {
                        //when server service responds then we will fill the permission object
                        parentPointer.permissionModel.permission = response;

                        //Indicator is set to true that permission object is filled and 
                        //can be re-used for subsequent route request for the session of the user
                        parentPointer.permissionModel.isPermissionLoaded = true;

                        //Check if the current user has required role to access the route
                        parentPointer.getPermission(parentPointer.permissionModel, $module, $action, deferred);
                    });
                }
                return deferred.promise;
            },

            //Method to check if the current user has required role to access the route
            //'permissionModel' has permission information obtained from server for current user
            //'$module' is the list of modules which are available for root
            //'deferred' is the object through which we shall resolve promise
            getPermission: function (permissionModel, $module, $action, deferred) {
                var ifPermissionPassed = false;
                
                if(typeof permissionModel.permission[$module] != 'undefined' && typeof permissionModel.permission[$module][$action] != "undefined")
                    ifPermissionPassed = true;
                
                if (!ifPermissionPassed) {
                    //If user does not have required access, 
                    //we will route the user to unauthorized access page
                    $location.path(routeForUnauthorizedAccess);
                    //As there could be some delay when location change event happens, 
                    //we will keep a watch on $locationChangeSuccess event
                    // and would resolve promise when this event occurs.
                    $rootScope.$on('$locationChangeSuccess', function (next, current) {
                        deferred.resolve();
                    });
                } else {
                    deferred.resolve();
                }
            }
        };
    });

</script>