app.controller('ProjectIndex', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        
        rest.path = "projects";
        
        breadcrumbsService.setTitle("Manage Projects");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/projects", "Projects");
        breadcrumbsService.add("/#/projects", "List Projects");
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        $scope.predicate = "";
        
        $scope.projects = [];
                
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

app.controller('ProjectLevel', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        
        rest.path = "projects";
        
        breadcrumbsService.setTitle("Manage Project Levels");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/projects", "Projects");
        breadcrumbsService.add("/#/projects", "Levels");
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        $scope.list = [{
            "id": 1,
            "level": 0, 
            "title": "1. Area 1",
            "items": []
          }, {
            "id": 2,
            "level": 0,
            "title": "2. Area 2",
            "items": [{
              "id": 21,
              "level": 1,
              "title": "2.1. Floor 1",
              "items": [{
                "id": 211,
                "level": 2,
                "title": "2.1.1. Room 1",
                "items": []
              }, {
                "id": 212,
                "level": 2,
                "title": "2.1.2. Room 2",
                "items": []
              }],
            }, {
              "id": 22,
              "level": 1,
              "title": "2.2. Floor 2",
              "items": []
            }],
          }, {
            "id": 3,
            "level": 0,
            "title": "3. Area 3",
            "items": []
          }, {
            "id": 4,
            "level": 0,
            "title": "4. Area 4",
            "items": []
          }];

          $scope.selectedItem = {};

          $scope.options = {
          };

          $scope.remove = function(scope) {
            scope.remove();
          };

          $scope.toggle = function(scope) {
            scope.toggle();
          };

          $scope.newSubItem = function(scope) {
            var nodeData = scope.$modelValue;
            nodeData.items.push({
              id: nodeData.id * 10 + nodeData.items.length,
              title: nodeData.title + '.' + (nodeData.items.length + 1),
              items: []
            });
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