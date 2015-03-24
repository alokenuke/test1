app.controller('RolesIndex', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown) {
        $scope.tagsNum = 1;
        rest.path = "roles";
        $scope.page_dropdown = page_dropdown;
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Manage Roles");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/roles", "Roles manage");
        
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
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Create New Role");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/roles", "Roles");
        breadcrumbsService.add("/#/roles/create", "Create New Role");
        $scope.moduleactions = {};
        
        $scope.role = {};
        
        $scope.addRoles = function(){
            $scope.role['type'] = 'Client';
            $scope.role['moduleactions'] = $scope.moduleactions;
            rest.postModel($scope.role).success(function(data) {
                $location.path("/roles");
            }).error(function(data) {
                alertService.clearAll();
                angular.forEach(data, function (v) {
                    alertService.add("error", v['message']);
                });
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
        breadcrumbsService.setTitle("Update Role");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/user/roles/update", "Update Role");
        $scope.moduleactions = {};
        
        $scope.role = {};
        
        $http.get("/roles/"+$routeParams.id+"?expand=roleSettings", {'id': $routeParams.id}).success(function(data) {
                $scope.role = data;
        });
        
        $scope.addRoles = function(){
            $scope.role['type'] = 'Client';
            $scope.role['moduleactions'] = $scope.moduleactions;
            rest.putModel($scope.role).success(function(data) {
                alertService.clearAll();
                alertService.add("success", "Role updated.");
                $location.path('/roles').replace();
            }).error(function(data) {
                alertService.clearAll();
                angular.forEach(data, function (v) {
                    alertService.add("error", v['message']);
                });
            });
        }
        
        $http.post("roles/loadactions", {'id': $routeParams.id}).success(function(data) {
            $scope.moduleactions = data;
        });
    }])

app.controller('ManageLabelTemplates', function($scope, rest, $location, alertService, $http, breadcrumbsService, $window, $upload, $route) {
    rest.path = "labeltemplates";
    
    breadcrumbsService.clearAll();
    breadcrumbsService.setTitle("Manage Label Templates");
    breadcrumbsService.add("/#/", "Home");
    breadcrumbsService.add("/#/labeltemplates", "Settings - Label Templates");
    $scope.paper_size = 'custom';
    $scope.label_template = {};
    
    $scope.checked_labels = [
        {
            name: 'tag_type',
            label: "Tag Type",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: 'tag_name',
            label: "Tag Name",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: 'uid',
            label: "UID",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: 'product_code',
            label: "Product Code",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: 'company_name',
            label: "Company Name",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: 'project_name',
            label: "Project Name",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: "tag_item",
            label: "Tag Items",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: "client_name",
            label: "Client Name",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: "project_address",
            label: "Project Address",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: "process",
            label: "Process",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: "client_location",
            label: "Client Location",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: "project_location",
            label: "Project Location",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: "main_contractor",
            label: "Main Contractor",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: "project_level",
            label: "Project Level",
            lineBreak: false,
            showLabel: true,
        },
        {
            name: "tag_description",
            label: "Tag Description",
            lineBreak: false,
            showLabel: true,
        }
    ];
        
    var errorCallback = function (data) {
        if(data.status!=401) {
            alertService.add('error', "Error in processing your request. Please try again.");
        }
    };
    
    $scope.addNewTemplate = function() {
        $scope.label_template.id = undefined;
        $scope.label_template.template_name = undefined;
        $scope.label_template.paper_size = "custom";
        $scope.label_template.num_label_horizontal = undefined;
        $scope.label_template.num_label_vertical = undefined;
        $scope.label_template.hor_label_spacing = undefined;
        $scope.label_template.ver_label_spacing = undefined;
        $scope.label_template.logo_width = undefined;
        
        $scope.label_template.checked_labels = $scope.checked_labels;
    }
    
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
            if(!$scope.label_template.id) {
                $scope.label_template.page_width = 0;
                $scope.label_template.page_height = 0;
            }
        }
    });
    
    $scope.selectTemplate = function() {
        $scope.paper_size=($scope.label_template.page_width==216 && $scope.label_template.page_height==279?'letter':($scope.label_template.page_width==210 && $scope.label_template.page_height==297?'a4':'custom'));
        
        var temp = [];
        
        angular.forEach($scope.checked_labels, function(defaultLabel) {
            var exists = false;
            angular.forEach($scope.label_template.checked_labels, function(label) {
                if(typeof label['name'] != 'undefined' && angular.equals(label['name'], defaultLabel['name'])) {
                    exists = true;
                    return;
                }
            });
            if(!exists)
                temp.push(defaultLabel);
        });
        
        if(temp.length > 0) {
            if(!angular.isArray($scope.label_template.checked_labels))
                $scope.label_template.checked_labels = [];
            
            angular.forEach(temp, function(val) {
                $scope.label_template.checked_labels.push(val);
            })
        }
    }
    
    $scope.deleteTemplate = function() {
        if(typeof $scope.label_template == 'undefined' || $scope.label_template.length <= 0) {
            alertService.clearAll();
            alertService.add('error', "No template selected.");
        }
        else {
            $scope.$apply(function(){
                var index = $scope.templates.indexOf($scope.label_template);
                $scope.templates.splice(index,1);
                alertService.clearAll();
                console.log($scope.label_template);
                rest.deleteById($scope.label_template).success(function() {
                    alertService.add('success', "Template ("+$scope.label_template.template_name+") removed.");
                    $scope.label_template = $scope.templates[0];
                });
            });
        }
    }
    
    $scope.previewTemplate = function() {
        var tabWindowId = window.open('about:blank', '_blank');

        $http.post("/reportsdownload/previewtemplate", $scope.label_template).success(function(response) {
            tabWindowId.location.href = response;
        }).error(errorCallback);
    }
    
    $scope.saveTemplate = function($e) {
        
        alertService.clearAll();
        if($scope.returnAction==false)
            $e.preventDefault();
        
        if($scope.label_template.id)
            $http.put("/labeltemplates/"+$scope.label_template.id, $scope.label_template).success(function(data) {
                alertService.add('success', "Template updated successfully.");
                $scope.label_template = data;
                $route.reload();
            }).error(errorCallback);        
        else
            rest.postModel($scope.label_template).success(function(data) {
                alertService.add('success', "Template created successfully.");
                $scope.label_template = data;
                $route.reload();
            }).error(errorCallback);
    }
    
    $scope.removePhoto = function() {
        $scope.label_template.logo = null;
    }
    
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
                    $scope.label_template.logo = data;
                });
            }
        };
    
    rest.models({}).success(function (data) {
        $scope.templates = data.items;
        $scope.label_template = $scope.templates[0];
        $scope.label_template.checked_labels = $scope.checked_labels;
    }).error(errorCallback);
    
})

app.controller('ManageReportTemplates', function($scope, rest, $location, alertService, $http, breadcrumbsService, $window, $upload, $route) {
    rest.path = "reporttemplates";
    
    breadcrumbsService.clearAll();
    breadcrumbsService.setTitle("Manage Report Templates");
    breadcrumbsService.add("/#/", "Home");
    breadcrumbsService.add("/#/reporttemplates", "Settings - Report Templates");
    $scope.paper_size = 'custom';
    $scope.report_template = {};
    
    var errorCallback = function (data) {
        if(data.status!=401) {
            alertService.add('error', "Error in processing your request. Please try again.");
        }
    };
    
    $scope.$watch('paper_size', function(newValue) {
        if(newValue=='letter')
        {
            $scope.report_template.page_width = 216;
            $scope.report_template.page_height = 279;
        }
        else if(newValue=='a4')
        {
            $scope.report_template.page_width = 210;
            $scope.report_template.page_height = 297;
        }
        else {
            $scope.report_template.page_width = 0;
            $scope.report_template.page_height = 0;
        }
    });
    
    $scope.deleteTemplate = function() {
        if(typeof $scope.report_template == 'undefined' || $scope.report_template.length <= 0) {
            alertService.clearAll();
            alertService.add('error', "No template selected.");
        }
        else {
            $scope.$apply(function(){
                var index = $scope.templates.indexOf($scope.report_template);
                $scope.templates.splice(index,1);
                alertService.clearAll();
                console.log($scope.report_template);
                rest.deleteById($scope.report_template).success(function() {
                    alertService.add('success', "Template ("+$scope.report_template.template_name+") removed.");
                    $scope.report_template = $scope.templates[0];
                });
            });
        }
    }
    
    $scope.previewTemplate = function() {
        var tabWindowId = window.open('about:blank', '_blank');

        $http.post("/reportsdownload/previewtemplate", $scope.report_template).success(function(response) {
            tabWindowId.location.href = response;
        }).error(errorCallback);
    }
    
    $scope.saveTemplate = function($e) {
        alertService.clearAll();
        if($scope.returnAction==false)
            $e.preventDefault();
        if($scope.report_template.id)
            $http.put("/reporttemplates/"+$scope.report_template.id, $scope.report_template).success(function(data) {
                alertService.add('success', "Template updated successfully.");
                $scope.report_template = data;
                $route.reload();
            }).error(errorCallback);        
        else
            rest.postModel($scope.report_template).success(function(data) {
                alertService.add('success', "Template created successfully.");
                $scope.report_template = data;
                $route.reload();
            }).error(errorCallback);
    }
    
    $scope.removePhoto = function() {
        $scope.report_template.logo = null;
    }
    
    rest.models({}).success(function (data) {
        $scope.templates = data.items;
        if(typeof $scope.templates[0] != 'undefined')
            $scope.report_template = $scope.templates[0];
    }).error(errorCallback);
    
})