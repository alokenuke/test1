app.controller('TagsCreate', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        rest.path = "tags";
        
        var ctrl = this;
        
        breadcrumbsService.setTitle("Site Track: Create simple tag");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tags", "Create New Tag");
        
        ctrl.items = [];
        $scope.search = [];
        $scope.search.project = [];
        $scope.projectlevels = [];
        $scope.search.childlevels = [];
        $scope.search.group = [];
        
        $scope.updateLevel = function(projectId, level) {
            $scope.search.project_id = projectId;
            
            $scope.projectlevels.splice(level, ($scope.projectlevels.length-level));
            
            $http.post("/api/getall?mod=projectLevel", {'search': {'project_id': projectId, 'parent_id': 0}, 'select': ['id', 'level_name']}).success(function(data) {
                if(data.items.length>0)
                    $scope.projectlevels.push(data.items);
            });
            
            $http.post("/api/getall?mod=userGroups", {'search': {'project_id': projectId}, 'select': ['id', 'group_name']}).success(function(data) {
                $scope.usergroups = data.items;
            });
        }
        
        $scope.fields = [
            {key: "id",label: "ID",},
            {key: "modified_date",label: "Modified",},
            {key: "project_name",label: "Project Name",},
            {key: "owner_name",label: "Owner",},
            {key: "address",label: "Address",},
            {key: "timezone",label: "Timezone",},
            {key: "project_status",label: "Status",},
        ];
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
                
        if($location.$$search.sort!=undefined) {
            $scope.predicate = $location.$$search.sort;
            $scope.reverse = ($scope.predicate.search("-")==-1?false:true);
        }
        
        $scope.order = function(predicate, reverse) {
            $scope.predicate = (reverse?"-"+predicate:predicate);
            $scope.reverse = !reverse;
            $location.search("sort", $scope.predicate);
        };
        
        $scope.delete = function (id) {
            rest.deleteById({id: id}).success(function () {
                $location.path('/post');
                $route.reload();
            }).error(errorCallback);
        }
        
        $scope.pageChanged = function() {
            $location.search("page", $scope.currentPage);
        }
        
        $scope.status = {
            isopen: false
        };
        
        $scope.setSearchFields = function(obj, value) {
            $scope[obj] = value;
        }

        $scope.toggleDropdown = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.status.isopen = !$scope.status.isopen;
        };
                
        rest.models().success(function (data) {
            $scope.data = data.items;
                        
            $scope.totalCount = data._meta.totalCount;
            $scope.pageCount = data._meta.pageCount;
            $scope.currentPage = (data._meta.currentPage+1);
            $scope.numPerPage = data._meta.perPage;
            
            $http.get("/api/getall?mod=projects").success(function(data) {
                $scope.projects = data.items;
            });
            
            $http.get("/api/getall?mod=userGroups").success(function(data) {
                $scope.usergroups = data.items;
            });
            
        }).error(errorCallback);
        
    }])


app.controller('TagsView', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        rest.path = "tags";
        
        var ctrl = this;
        
        breadcrumbsService.setTitle("Site Track: Tag View");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/tags", "Manage");
        breadcrumbsService.add("/#/tags", "Tags");
        
        ctrl.items = [];
        $scope.search = [];
        $scope.search.project = [];
        $scope.projectlevels = [];
        $scope.search.childlevels = [];
        $scope.search.group = [];
        
        $scope.updateLevel = function(projectId, level) {
            $scope.search.project_id = projectId;
            
            $scope.projectlevels.splice(level, ($scope.projectlevels.length-level));
            
            $http.post("/api/getall?mod=projectLevel", {'search': {'project_id': projectId, 'parent_id': 0}, 'select': ['id', 'level_name']}).success(function(data) {
                if(data.items.length>0)
                    $scope.projectlevels.push(data.items);
            });
            
            $http.post("/api/getall?mod=userGroups", {'search': {'project_id': projectId}, 'select': ['id', 'group_name']}).success(function(data) {
                $scope.usergroups = data.items;
            });
        }
        
        $scope.fields = [
            {key: "id",label: "ID",},
            {key: "modified_date",label: "Modified",},
            {key: "project_name",label: "Project Name",},
            {key: "owner_name",label: "Owner",},
            {key: "address",label: "Address",},
            {key: "timezone",label: "Timezone",},
            {key: "project_status",label: "Status",},
        ];
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
                
        if($location.$$search.sort!=undefined) {
            $scope.predicate = $location.$$search.sort;
            $scope.reverse = ($scope.predicate.search("-")==-1?false:true);
        }
        
        $scope.order = function(predicate, reverse) {
            $scope.predicate = (reverse?"-"+predicate:predicate);
            $scope.reverse = !reverse;
            $location.search("sort", $scope.predicate);
        };
        
        $scope.delete = function (id) {
            rest.deleteById({id: id}).success(function () {
                $location.path('/post');
                $route.reload();
            }).error(errorCallback);
        }
        
        $scope.pageChanged = function() {
            $location.search("page", $scope.currentPage);
        }
        
        $scope.status = {
            isopen: false
        };
        
        $scope.setSearchFields = function(obj, value) {
            $scope[obj] = value;
        }

        $scope.toggleDropdown = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.status.isopen = !$scope.status.isopen;
        };
                
        rest.models().success(function (data) {
            $scope.data = data.items;
                        
            $scope.totalCount = data._meta.totalCount;
            $scope.pageCount = data._meta.pageCount;
            $scope.currentPage = (data._meta.currentPage+1);
            $scope.numPerPage = data._meta.perPage;
            
            $http.get("/api/getall?mod=projects").success(function(data) {
                $scope.projects = data.items;
            });
            
            $http.get("/api/getall?mod=userGroups").success(function(data) {
                $scope.usergroups = data.items;
            });
            
        }).error(errorCallback);
        
    }])


.controller('Index', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', function ($scope, rest, $location, $route, $routeParams, alertService) {
        
        rest.path = $routeParams.mod;
        $scope.mod = $routeParams.mod;
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        $scope.predicate = "";
                
        if($location.$$search.sort!=undefined) {
            $scope.predicate = $location.$$search.sort;
            $scope.reverse = ($scope.predicate.search("-")==-1?false:true);
        }
        
        $scope.order = function(predicate, reverse) {
            $scope.predicate = (reverse?"-"+predicate:predicate);
            $scope.reverse = !reverse;
            $location.search("sort", $scope.predicate);
        };
        
        $scope.delete = function (id) {
            rest.deleteById({id: id}).success(function () {
                $location.path('/post');
                $route.reload();
            }).error(errorCallback);
        }
        
        $scope.pageChanged = function() {
            $location.search("page", $scope.currentPage);
        }
                
        rest.models().success(function (data) {
            
            $scope.fields = data._fields;
                        
            $scope.data = data.items;
                        
            $scope.totalCount = data._meta.totalCount;
            $scope.pageCount = data._meta.pageCount;
            $scope.currentPage = (data._meta.currentPage+1);
            $scope.numPerPage = data._meta.perPage;
            
        }).error(errorCallback);
    }])

    .controller('View', ['$scope', 'rest', '$routeParams', function ($scope, rest, $routeParams) {
            
        rest.path = $routeParams.mod;
        $scope.mod = $routeParams.mod;
        
        var errorCallback = function (data) {
            console.log(data.message);
        };
        rest.model().success(function (data) {
            $scope.data = data;
            
            $scope.fields = data._fields;
            
        }).error(errorCallback);
    }])
    .controller('Form', ['$scope', 'rest', '$location', '$routeParams', 'alertService', function ($scope, rest, $location, $routeParams, alertService) {
        rest.path = $routeParams.mod;
        $scope.mod = $routeParams.mod;

        $scope.save = function () {
            rest.putModel($scope.data).success(function () {
                $location.path($scope.mod + '/' + $scope.data.id);
            }).error(errorCallback);
        };

        rest.model().success(function (data) {
            $scope.data = data;
            $scope.fields = data._fields;
        }).error(errorCallback);

        var errorCallback = function (data) {
            data.map(function (el) {
                alertService.add('error', el.message);
            })
        };
    }])
    .controller('Create', ['$scope', 'rest', '$location', '$routeParams', 'alertService', function ($scope, rest, $location, $routeParams, alertService) {
        rest.path = $routeParams.mod;
        $scope.mod = $routeParams.mod;

        $scope.data = {};
        
        rest.getFields().success(function (data) {
            $scope.fields = data._fields;
        }).error(errorCallback);
        
        $scope.save = function () {
            rest.postModel($scope.data).success(function (data) {
                $location.path($routeParams.mod+'/' + data.id);
            }).error(errorCallback);
        };

        var errorCallback = function (data) {
            if(data.statusCode==422) {
                angular.forEach(data, function(value, key) {
                    $(".field-"+$scope.mod+"-"+value['field']).parent().addClass("has-error")
                    $(".field-"+$scope.mod+"-"+value['field']+" .help-block").text(value['message']);
                });
            }
        };
    }])
    .controller('Delete', ['$scope', 'rest', '$location', '$routeParams', 'alertService', function ($scope, rest, $location, $routeParams, alertService) {
        rest.path = $routeParams.mod;
        $scope.mod = $routeParams.mod;

        rest.deleteModel($scope.data).success(function () {
            $location.path('/'+$scope.mod);
        }).error(errorCallback);

        var errorCallback = function (data) {
            data.map(function (el) {
                alertService.add('error', el.message);
            })
        };
    }])

app.directive('ngConfirmClick', [
    function () {
        return {
            priority: 1,
            terminal: true,
            link: function (scope, element, attr) {
                var msg = attr.ngConfirmClick || "Are you sure?";
                var clickAction = attr.ngClick;
                element.bind('click', function (event) {
                    if (window.confirm(msg)) {
                        scope.$eval(clickAction)
                    }
                });
            }
        };
    }])

app.filter('titlecase', function() {
    return function(s) {
        s = ( s === undefined || s === null ) ? '' : s;
        return s.toString().toLowerCase().replace( /\b([a-z])/g, function(ch) {
            return ch.toUpperCase();
        });
    };
});