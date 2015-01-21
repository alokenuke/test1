app.controller('RolesIndex', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown) {
        $scope.tagsNum = 1;
        rest.path = "roles";
        $scope.page_dropdown = page_dropdown;
        breadcrumbsService.setTitle("Manage Roles");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/user/roles", "Roles manage");
        
        $scope.pageChanged = function() {
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
                $scope.roles = data.items;
                $scope.totalCount = data._meta.totalCount;
                $scope.pageCount = data._meta.pageCount;
                $scope.currentPage = (data._meta.currentPage+1);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        
        updateUserList();
        
		$scope.removeRole =  function(model, $index) {
            
            rest.deleteById(model);
            
            $scope.roles.splice($index, 1);
        }
        
    }])

app.controller('RolesAdd', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown','$location',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown,$location) {
        $scope.tagsNum = 1;
        rest.path = "roles";
        $scope.page_dropdown = page_dropdown;
        breadcrumbsService.setTitle("Manage Roles");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/user/roles/create", "Add Roles");
        
        $scope.role = {};
        
        $scope.addRoles = function(){ 
            
            $scope.role['type'] = 'Client';    
            rest.postModel($scope.role).success(function(data) {
                $location.path("/user/roles");
            });
        }
        
    }])

app.controller('RolesUpdate', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown','$location',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown,$location) {
        $scope.tagsNum = 1;
        rest.path = "roles";
        $scope.page_dropdown = page_dropdown;
        breadcrumbsService.setTitle("Manage Roles");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/user/roles/update", "Update Roles");
        
        $scope.role = {};
        
        rest.model({'id': $routeParams.id}).success(function(data) {
                $scope.role = data;
        });
        
        $scope.addRoles = function(){ 
            
            $scope.role['type'] = 'Client';
            rest.putModel($scope.role).success(function(data) {
                alertService.clearAll();
                alertService.add("success", "Role updated.");
                $location.path('/roles').replace();
            }).error(function(data) { 
                alertService.clearAll();
                alertService.add("error", "Validation Error");
            });
            
        }
        
        
    }])

app.controller('ManageLabelTemplates', function($scope, rest, $location, alertService, $http, breadcrumbsService, $window) {
    rest.path = "labeltemplates";
    breadcrumbsService.setTitle("Manage Label Templates");
    breadcrumbsService.clearAll();
    breadcrumbsService.add("", "Home");
    breadcrumbsService.add("/#/settings", "Settings");
    breadcrumbsService.add("/#/labeltemplates", "Label Templates");
    $scope.paper_size = 'custom';
    $scope.label_template = {};
    
    var errorCallback = function (data) {
        if(data.status!=401) {
            alertService.add('error', "Error in processing your request. Please try again.");
        }
    };
    
    $scope.range = function(min, max, step){
        step = step || 1;
        var input = [];
        for (var i = min; i <= max; i += step) input.push(i);
        return input;
    };
    
    $scope.calculateLabelHeightWidth = function() {
        if ($scope.label_template.num_label_vertical != 0 && $scope.label_template.num_label_horizontal != 0)
        {
            $scope.label_template.cal_label_height = (($scope.label_template.page_height - $scope.label_template.top_margin - $scope.label_template.bottom_margin - (($scope.label_template.num_label_vertical - 1) * $scope.label_template.ver_label_spacing))) / parseInt($scope.label_template.num_label_vertical);
                        
            $scope.label_template.cal_label_width = (($scope.label_template.page_width - $scope.label_template.left_margin - $scope.label_template.right_margin - (($scope.label_template.num_label_horizontal - 1) * $scope.label_template.hor_label_spacing)) / parseInt($scope.label_template.num_label_horizontal));
        }
    }
        
    $scope.$watch('paper_size', function(newValue) {
        if(!$scope.label_template.id) {
            if(newValue=='letter')
            {
                $scope.label_template.page_width = 216;
                $scope.label_template.page_height = 279;
            }
            else if(newValue=='a4')
            {
                $scope.label_template.page_width = 210;
                $scope.label_template.page_height = 297;
            }
            else {
                $scope.label_template.page_width = 0;
                $scope.label_template.page_height = 0;
            }
        }
    });
    
    $scope.previewTemplate = function() {        
        $http.post("/labeltemplates/preview", $scope.label_template).success(function(data) {
            
        }).error(errorCallback);
    }
    
    $scope.saveTemplate = function() {
        if($scope.label_template.id)
            $http.put("/labeltemplates/"+$scope.label_template.id, $scope.label_template).success(function() {
                alertService.add('success', "Template updated successfully.");
            }).error(errorCallback);        
        else
            rest.postModel($scope.label_template).success(function(data) {
                alertService.add('success', "Template created successfully.");
            }).error(errorCallback);
    }
    
    rest.models({}).success(function (data) {
        $scope.templates = data.items;
        $scope.label_template = $scope.templates[0];
    }).error(errorCallback);
    
})