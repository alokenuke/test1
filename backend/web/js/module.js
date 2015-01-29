app.controller('Index', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', function ($scope, rest, $location, $route, $routeParams, alertService) {
        
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
            $scope.currentPage = (data._meta.currentPage);
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