app.controller('UserGroup', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'page_dropdown', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, page_dropdown) {
        
    rest.path = "usergroups";

    breadcrumbsService.setTitle("Manage User Groups");
    breadcrumbsService.clearAll();
    breadcrumbsService.add("", "Home");
    breadcrumbsService.add("/#/user-groups", "User Groups");

    $scope.list = [];
    $scope.selectedItem = {};
    $scope.page_dropdown = page_dropdown;

    $scope.options = {
    };

    $scope.remove = function(scope) {
      scope.remove();
    };

    $scope.updateItem = function(scope) {
        scope.editing = true;
    }
    
    $scope.saveItem = function(scope, title) {
        scope.title = title;
        scope.editing=false
    }
    
    $scope.addItemGroup = function() {
      var nodeData = $scope.list;
      nodeData.push({
        id: null,
        group_name: "User Group " + (nodeData.length + 1),
        levels: []
      });
    };
    
    $scope.sortUser = function(scope, elm) {
        scope.sortBy = elm;
        if(typeof(scope.sort[elm]) !== 'undefined' && scope.sort[elm].search("-up")!==-1) {
            scope.sort[elm] = "-down";
            scope.sortBy = "-"+elm;
        }
        else {
            scope.sort = [];
            scope.sort[elm] = "-up";
        }
        searchUser(scope);
    };
    
    $scope.searchUser = function(scope) {
        scope.loading = true;
        var params = {'search': scope.search, 'sort': scope.users_sortBy, 'page':scope.users_currentPage, 'limit': scope.users_numPerPage};
        rest.customModelData("users/levelusers/"+scope.id, params).success(function (data) {
            scope['users'] = data.items;
            scope.users_totalCount = data._meta.totalCount;
            scope.users_pageCount = data._meta.pageCount;
            scope.users_currentPage = (data._meta.currentPage+1);
            scope.users_numPerPage = data._meta.perPage;
            scope.loading = false;
        }).error(function() {
            errorCallback();
            scope.loading = false;
        });
    }
    
    $scope.loadUsers = function(scope) {
        if(!scope['users']) {
            scope.loading = true;
            rest.setData("users/levelusers/"+scope.id, ['users'], {}).success(function(data) {
                scope['users'] = data.items;
                scope.users_totalCount = data._meta.totalCount;
                scope.users_pageCount = data._meta.pageCount;
                scope.users_currentPage = (data._meta.currentPage+1);
                scope.users_numPerPage = data._meta.perPage;
                scope.loading = false;
                scope.collapsed = !scope.collapsed;
            }).error(function() {
                errorCallback();
                scope.loading = false;
            });
        }
        else
            scope.collapsed = !scope.collapsed;
    }

    $scope.newSubItem = function(scope, type) {
      var nodeData = scope.$modelValue;
      
      nodeData.levels.push({
        id: nodeData.id * 10 + nodeData.levels.length,
        level_name: 'level ' + (nodeData.levels.length + 1),
        levels: []
      });
    };
    
    var errorCallback = function (data) {
        if(data.status!=401) {
            alertService.add('error', "Error in processing your request. Please try again.");
        }
    };
    
    var params = {'search': $scope.$search, 'sort': $scope.sortBy, 'page':$scope.currentPage, 'limit': $scope.numPerPage};
    rest.models(params).success(function (data) {
        $scope.list = data.items;
        $scope.totalCount = data._meta.totalCount;
        $scope.pageCount = data._meta.pageCount;
        $scope.currentPage = (data._meta.currentPage+1);
        $scope.numPerPage = data._meta.perPage;
    }).error(errorCallback);
    
}])

app.controller('UserIndex', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown) {
      
        rest.path = "users";
        
        breadcrumbsService.setTitle("Manage User Groups");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/user-groups", "User Groups");
        
        $scope.page_dropdown = page_dropdown;
        $scope.$search = {};
        $scope.rec_noti = [
            {id: 'daily', name:'Daily'},
            {id: 'weekly', name:'Weekly'},
            {id: 'monthly', name:'Monthly'},
            {id: 'yearly', name:'Yearly'},
        ];
        
        $scope.pageChanged = function() {
            updateUserList();
        }
        
        rest.setData("usergroups/getall", ['user_groups.id', 'group_name'], {}).success(function(data) {$scope.usergroups = data.items;});
        
        $scope.searchUser = function(){
           updateUserList();
        }
        
         var errorCallback = function (data) {
            if(data.status!=401) {
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
                $scope.currentPage = (data._meta.currentPage+1);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        
        updateUserList();
        
    }])

app.controller('UserCreate', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', function ($scope, rest, $location, $route, $rootScope, alertService, $http, breadcrumbsService) {
        
        rest.path = "users";
        
        breadcrumbsService.setTitle("Create New Users");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("", "Manage");
        breadcrumbsService.add("", "Users");
        breadcrumbsService.add("/#/users/create", "Create");
        
	$scope.EMAIL_REGEXP = /^[a-z0-9!#$%&'*+/=?^_`{|}~.-]+@[a-z0-9-]+(\.[a-z0-9-]+)*$/i;
            
        $scope.rec_noti = [
            {id: 'daily', name:'Daily'},
            {id: 'weekly', name:'Weekly'},
            {id: 'monthly', name:'Monthly'},
            {id: 'yearly', name:'Yearly'},
        ];
        
        rest.setData("roles/getall", ['id', 'role_name'], {}).success(function(data) {
            $scope.roles = data.items;
        });
        
         
        $scope.users = [];
        $scope.users.push({});
            
        $scope.copyUser =  function(fieldNum) {
            angular.element("<div>{fieldNum}</div>").error.required = "hello";
            for(var i=0;i<fieldNum;i++) {
                $scope.users.push({});
            }
        }
        
        $scope.createUser = function(){
            $rootScope.alerts = [];
            $http.post('/users/multiinsert', {'User':$scope.users}).success(function(data) {
                if(data=="Success")
                    alertService.add("success", "Users created.");
            }).error(function(data) {
                alertService.add("error", "Validation Error");
                angular.forEach(data.User, function(value, key) {
                    angular.forEach(value, function(child_value, child_key) {
                        
                    });
                })
            });
        }
    }])