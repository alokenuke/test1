app.controller('PrintLabel', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', '$rootScope', 'page_dropdown',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, $rootScope, page_dropdown) {
        
        rest.path = "tags";
        $scope.page_dropdown = page_dropdown;
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Print NFC UIDs/QR Codes");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/print-label", "Print Label");
        
        $scope.datepickers = {fromDate: false,toDate: false}
        $scope.openCalendar = function($event, which) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.datepickers[which]= true;
        };
        $scope.dateOptions = {formatYear: 'yy',startingDay: 1};
        $scope.formats = ['dd MMM, yyyy', 'dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
        $scope.format = $scope.formats[0];
        
        $scope.search = {};
        $scope.sort = [];
        $scope.sortBy = "";
        $scope.projectlevels = [];
        $scope.projects = [];
        $scope.usergroups = [];
        $scope.items = [];
        $scope.processes = [];
        $scope.showSearchBox = true;
        $scope.select = {};
        $scope.temp = {};
        
        if(typeof $rootScope.globalSearch != 'undefined' && $rootScope.globalSearch.length > 2) {
            $scope.search.globalSearch = $rootScope.globalSearch;
            $scope.showSearchBox = false;
        }
        
        if($location.$$search.sort!=undefined) {
            $scope.predicate = $location.$$search.sort;
            $scope.reverse = ($scope.predicate.search("-")==-1?false:true);
        }
        
        $scope.viewLabels = function() {
            updateTagList();
        }
        
        $scope.clearSearch = function() {
            $scope.search = $scope.project = $scope.tag_process = $scope.tag_item = {};
            $scope.projectlevels = [];
            //$scope.projects = [];
            $scope.usergroups = [];
            $scope.items = [];
            $scope.processes = [];
            
            //rest.setData("projects/getall", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
            rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0}).success(function(data) {$scope.items.push(data.items);});
            rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0}).success(function(data) {$scope.processes.push(data.items);});
            
            updateTagList();
        }
        
        $scope.order = function(elm) {
            $scope.sortBy = elm;
            if($scope.sort[elm] && $scope.sort[elm].search("-up")!=-1) {
                $scope.sort[elm] = "-down";
                $scope.sortBy = "-"+elm;
            }
            else {
                $scope.sort = [];
                $scope.sort[elm] = "-up";    
            }
            updateTagList();
        };
        
        $scope.pageChanged = function() {
            $scope.allSelected = false;
            updateTagList();
        }
        
        $scope.setPageLimit = function(){
            updateTagList();
        }
                        
        $scope.updateSelectBox = function(variable, projectId, level, parent) {
            $scope.search.project_id = projectId;
            
            if(variable=="projectlevels") {
                if(level>0)
                    $scope.search.project_level_id = parent;
                
                $scope[variable].splice(level, ($scope[variable].length-level));
                
                $http.post("/projectlevel/getall", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });

                if(level==0) {
                    $http.post("usergroups/getall", {'search': {'project_id': projectId}}).success(function(data) {$scope.usergroups = data.items;});
                    rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0, 'tag_items_projects.project_id': projectId}).success(function(data) {
                        $scope['items'] = [];
                        if(data.items.length>0)
                            $scope['items'].push(data.items);
                    });
                    rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0, 'tag_process_projects.project_id': projectId}).success(function(data) {
                        $scope['processes'] = [];
                        if(data.items.length>0)
                            $scope['processes'].push(data.items);
                    });
                }
            }
            else if(variable=='items') {
                $scope.search.tag_item_id = parent;
                
                $scope[variable].splice(level+1, ($scope[variable].length-level-1));
                
                rest.setData("items/getall", ['id', 'item_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });
            }
            else if(variable=='processes') {
                $scope.search.tag_process_flow_id = parent;
                $scope[variable].splice(level+1, ($scope[variable].length-level-1));
                
                rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });
            }
        }
        
        $scope.printLabels = function() {
            if(parseInt($scope.label_template.id)>0) {
                if(  Object.keys($scope.temp).length>0) {
                    $http.post("/reportsdownload/printlabel", {'uid': $scope.temp, 'label_template': $scope.label_template.id}).success(function(response) {
                        var tabWindowId = window.open('about:blank', '_blank');
                        tabWindowId.location.href = response;
                    }).error(errorCallback);
                }
                else {
                    alert("Please select a label to print.");
                }
            }
            else
            {
                alert("Please select a label template.");
            }
        }
        
        $scope.selectTag = function(scope) {
            if (scope['isSelected']) {
                $scope.temp[""+scope.id] = scope.uid;
            }
            else
                $scope.temp[""+scope.id] = undefined;
        }

        $scope.selectAllTags = function(data, allSelected) {
            angular.forEach(data, function(v) {
                v['isSelected'] = allSelected;
                if (allSelected) {
                    $scope.temp[""+v.id] = v.uid;
                }
                else
                    $scope.temp[""+v.id] = undefined;
            });
        }
        
        var updateTagList = function() {
            if(typeof $scope.search.date_range !== 'undefined') {
                $monthArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $fromDate = $scope.search.date_range.from_date;
                if(typeof $fromDate === 'date')
                    $scope.search.date_range.from_date = $fromDate.getDate()+" "+$monthArr[$fromDate.getMonth()]+", "+$fromDate.getFullYear();
                
                $toDate = $scope.search.date_range.to_date;
                if(typeof $toDate === 'date')
                    $scope.search.date_range.to_date = $toDate.getDate()+" "+$monthArr[$toDate.getMonth()]+", "+$toDate.getFullYear();
            }
            var params = {'search': $scope.search, 'sort': $scope.sortBy, 'limit': $scope.numPerPage, 'page':$scope.currentPage, };
			rest.customModelData("tags/search?expand=tagActivityLog", params).success(function (data) {
                $scope.data = data.items; 
                
                if($scope.data.length > 0 && typeof $scope.select.tag_process != 'undefined' && $scope.select.tag_process[2] && $scope.select.tag_process[2].selected) {
                    $scope.allowLogActivity = true;
                }
                
                $scope.totalCount = data._meta.totalCount;
                $scope.pageCount = data._meta.pageCount;
                $scope.currentPage = (data._meta.currentPage);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
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
        
        $scope.logActivity = function () {
            
            var selectedTags = [];
            
            angular.forEach($scope.data, function(val) {
                if(val.isSelected)
                    selectedTags.push(val.id);
            });
            
            if(selectedTags.length <=0)
            {
                alert("Select at least one tag to log activity for the same.");
                return;
            }
            
            var modalInstance = $modal.open({
              templateUrl: '/templates/tags/logActivity.html',
              controller: 'MultipleTagLogActivityPopup',
              size: 'lg',
               resolve: {
                    itemScope: function () {
                        return selectedTags;
                    }
               }
            });

            modalInstance.result.then(function (selectedItem) {
                // Code to refresh the activity log list.
                updateTagList();
            }, function () {
                $log.info('Modal dismissed at: ' + new Date());
            });
        };
        
        rest.setData("projects/getall", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
        rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0}).success(function(data) {$scope.items.push(data.items);});
        rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0}).success(function(data) {$scope.processes.push(data.items);});
        rest.setData("usergroups/getall", {}, {}).success(function(data) {$scope.usergroups = data.items;});
        rest.customModelData("labeltemplates/getall", {'LabelTemplate': {'select': ['id', 'template_name']}}).success(function (data) {$scope.templates = data.items;$scope.print = {};$scope.label_template = $scope.templates[0];});
    }])

app.controller('PrintTimeAttendanceLabel', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', '$rootScope',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, $rootScope) {
        
        rest.path = "tags";
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Print Time Attendance Label");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/print-label", "Print Time Attendance Labels");
        
        $scope.datepickers = {fromDate: false,toDate: false}
        $scope.openCalendar = function($event, which) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.datepickers[which]= true;
        };
        $scope.dateOptions = {formatYear: 'yy',startingDay: 1};
        $scope.formats = ['dd MMM, yyyy', 'dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
        $scope.format = $scope.formats[0];
        
        $scope.temp = {};
        $scope.search = {};
        $scope.projectlevels = [];
        $scope.projects = null;
        $scope.usergroups = [];
        $scope.items = [];
        $scope.processes = [];
        $scope.print = {};
        
        $scope.printLabels = function() {
            if(parseInt($scope.label_template.id)>0) {
                if(  Object.keys($scope.temp).length>0) {
                    $http.post("/reportsdownload/printtimeattendancelabel", {'uid': $scope.temp, 'label_template': $scope.label_template.id}).success(function(response) {
                        var tabWindowId = window.open('about:blank', '_blank');
                        tabWindowId.location.href = response;
                    }).error(errorCallback);
                }
                else {
                    alert("Please select a label to print.");
                }
            }
            else
            {
                alert("Please select a label template.");
            }
        }
        
        $scope.order = function(elm) {
            $scope.sortBy = elm;
            if($scope.sort[elm] && $scope.sort[elm].search("-up")!=-1) {
                $scope.sort[elm] = "-down";
                $scope.sortBy = "-"+elm;
            }
            else {
                $scope.sort = [];
                $scope.sort[elm] = "-up";    
            }
            viewLabels();
        };
        
        $scope.pageChanged = function() {
            viewLabels();
        }
        
        $scope.setPageLimit = function(){
            viewLabels();
        }
        
        $scope.setSearch = function() {
            viewLabels();
        }
        
        $scope.clearSearch = function() {
            $scope.search = $scope.project = $scope.tag_process = $scope.tag_item = {};
            $scope.projectlevels = [];
            $scope.projects = [];
            $scope.usergroups = [];
            $scope.items = [];
            $scope.processes = [];
            
            rest.setData("projects/getall", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
            
            updateTagList();
        }
        
        $scope.updateSelectBox = function(variable, projectId, level, parent) {
            $scope.search.project_id = projectId;
            
            if(variable=="projectlevels") {
                if(level>0)
                    $scope.search.project_level_id = parent;
                
                $scope[variable].splice(level, ($scope[variable].length-level));
                
                $http.post("/projectlevel/getall", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });

                if(level==0) {
                    $http.post("usergroups/getall", {'search': {'project_id': projectId}}).success(function(data) {$scope.usergroups = data.items;});
                }
            }
        }
        
        $scope.printLabel = function() {
            if(parseInt($scope.print.label_template.id)>0) {
                if(  Object.keys($scope.print.labels).length>0) {
                    var tabWindowId = window.open('about:blank', '_blank');
                    
                    $http.post("/reportsdownload/printtimeattendancelabel", {'print': $scope.print, 'print_type': $scope.print_type}).success(function(response) {
                        tabWindowId.location.href = response;
                    }).error(errorCallback);
                }
                else {
                    alert("Please select a label to print.");
                }
            }
            else
            {
                alert("Please select a label template.");
            }
        }
        
        $scope.selectTag = function(scope) {
            if (scope['isSelected']) {
                $scope.temp[""+scope.id] = scope.id;
            }
            else
                $scope.temp[""+scope.id] = undefined;
        }

        $scope.selectAllTags = function(data, allSelected) {
            alert(allSelected);
            angular.forEach(data, function(v) {
                v['isSelected'] = allSelected;
                if (allSelected) {
                    $scope.temp[""+v.id] = v.id;
                }
                else
                    $scope.temp[""+v.id] = undefined;
            });
        }
        
        $scope.viewLabels = function() {
            if(typeof $scope.search.date_range !== 'undefined') {
                $monthArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $fromDate = $scope.search.date_range.from_date;
                if(typeof $fromDate === 'date')
                    $scope.search.date_range.from_date = $fromDate.getDate()+" "+$monthArr[$fromDate.getMonth()]+", "+$fromDate.getFullYear();
                
                $toDate = $scope.search.date_range.to_date;
                if(typeof $toDate === 'date')
                    $scope.search.date_range.to_date = $toDate.getDate()+" "+$monthArr[$toDate.getMonth()]+", "+$toDate.getFullYear();
            }
            var params = {'search': $scope.search, 'sort': $scope.sortBy, 'limit': $scope.numPerPage, 'page':$scope.currentPage, };
            rest.customModelData("timeattendance/search?expand=attendanceLog", params).success(function (data) {
                $scope.data = data.items;
                $scope.totalCount = data._meta.totalCount;
                $scope.pageCount = data._meta.pageCount;
                $scope.currentPage = (data._meta.currentPage);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        rest.customModelData("projects/search", {}).success(function(data) {$scope.projects = data.items;});
        rest.setData("usergroups/getall", {}, {}).success(function(data) {$scope.usergroups = data.items;});
        rest.setData("company/default", {}, {}).success(function (data) {$scope.company = data;});
        rest.customModelData("labeltemplates/getall", {}).success(function (data) {$scope.templates = data.items;$scope.label_template = $scope.templates[0];});
    }])

app.controller('Reports', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'page_dropdown', '$rootScope',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, page_dropdown, $rootScope) {
        
        $scope.tagsNum = 1;
        rest.path = "tags";
        $scope.page_dropdown = page_dropdown;
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Generate Tag Reports");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/reports", "Generate Tag Reports");
        
        $scope.datepickers = {fromDate: false,toDate: false}
        $scope.openCalendar = function($event, which) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.datepickers[which]= true;
        };
        $scope.dateOptions = {formatYear: 'yy',startingDay: 1};
        $scope.formats = ['dd MMM, yyyy', 'dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
        $scope.format = $scope.formats[0];
        
        $scope.search = {};
        $scope.sort = [];
        $scope.sortBy = "";
        $scope.projectlevels = [];
        $scope.projects = null;
        $scope.usergroups = [];
        $scope.items = [];
        $scope.processes = [];
        
        $scope.clearSearch = function() {
            $scope.search = $scope.project = $scope.tag_process = $scope.tag_item = {};
            $scope.projectlevels = [];
            $scope.projects = [];
            $scope.usergroups = [];
            $scope.items = [];
            $scope.processes = [];
            
            rest.setData("projects/getall", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
            rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0}).success(function(data) {$scope.items.push(data.items);});
            rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0}).success(function(data) {$scope.processes.push(data.items);});
        }
                
        $scope.updateSelectBox = function(variable, projectId, level, parent) {
            $scope.search.project_id = projectId;
            
            if(variable=="projectlevels") {
                if(level>0)
                    $scope.search.project_level_id = parent;
                
                $scope[variable].splice(level, ($scope[variable].length-level));
                
                $http.post("/projectlevel/getall", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });

                if(level==0) {
                    $http.post("usergroups/getall", {'search': {'project_id': projectId}}).success(function(data) {$scope.usergroups = data.items;});
                    rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0, 'tag_items_projects.project_id': projectId}).success(function(data) {
                        $scope['items'] = [];
                        if(data.items.length>0)
                            $scope['items'].push(data.items);
                    });
                    rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0, 'tag_process_projects.project_id': projectId}).success(function(data) {
                        $scope['processes'] = [];
                        if(data.items.length>0)
                            $scope['processes'].push(data.items);
                    });
                }
            }
            else if(variable=='items') {
                $scope.search.tag_item_id = parent;
                
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("items/getall", ['id', 'item_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });
            }
            else if(variable=='processes') {
                $scope.search.tag_process_flow_id = parent;
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });
            }
        }
        
        $scope.generateReports = function() {
            if(typeof $scope.search.project_id == 'undefined' || $scope.search.project_id <= 0) {
                alertService.clearAll();
                alertService.add("error", " You must have a project selected before moving ahead!!");
                return;
            }
            if(typeof $scope.search.date_range !== 'undefined') {
                $monthArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $fromDate = $scope.search.date_range.from_date;
                if(typeof $fromDate === 'date')
                    $scope.search.date_range.from_date = $fromDate.getDate()+" "+$monthArr[$fromDate.getMonth()]+", "+$fromDate.getFullYear();
                
                $toDate = $scope.search.date_range.to_date;
                if(typeof $toDate === 'date')
                    $scope.search.date_range.to_date = $toDate.getDate()+" "+$monthArr[$toDate.getMonth()]+", "+$toDate.getFullYear();
            }
            var params = {'search': $scope.search, 'filter': $scope.filter, 'print_type': $scope.print_type};
            rest.customModelData("reports/generate-tag-reports", params).success(function (data) {
                var tabWindowId = window.open("_new");
                tabWindowId.location.href = data;
            }).error(errorCallback);
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        rest.setData("projects/getall", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
        rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0}).success(function(data) {$scope.items.push(data.items);});
        rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0}).success(function(data) {$scope.processes.push(data.items);});
        rest.setData("usergroups/getall", {}, {}).success(function(data) {$scope.usergroups = data.items;});
        
    }])

app.controller('ReportsEmployeeLogs', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'page_dropdown', '$rootScope',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, page_dropdown, $rootScope) {
        
        $scope.tagsNum = 1;
        rest.path = "reports";
        $scope.page_dropdown = page_dropdown;
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Employee Logs");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/reports/employee-logs", "Employee Logs");
        
        $scope.datepickers = {fromDate: false,toDate: false}
        $scope.openCalendar = function($event, which) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.datepickers[which]= true;
        };
        $scope.dateOptions = {formatYear: 'yy',startingDay: 1};
        $scope.formats = ['dd MMM, yyyy', 'dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
        $scope.format = $scope.formats[0];
        
        $scope.search = {};
        $scope.sort = [];
        $scope.sortBy = "";
        $scope.showSearchBox = true;
        $scope.time = Date.now() / 1000;
                
        $scope.setSearch = function() {
            updateList();
        }
        
        $scope.clearSearch = function() {
            $scope.search = {};
            updateList();
        }
        
        $scope.pageChanged = function() {
            updateList();
        }
        
        $scope.setPageLimit = function(){
            updateList();
        }
       
        var updateList = function() {
            if(typeof $scope.search.date_range !== 'undefined') {
                $monthArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $fromDate = $scope.search.date_range.from_date;
                if(typeof $fromDate === 'date')
                    $scope.search.date_range.from_date = $fromDate.getDate()+" "+$monthArr[$fromDate.getMonth()]+", "+$fromDate.getFullYear();
                
                $toDate = $scope.search.date_range.to_date;
                if(typeof $toDate === 'date')
                    $scope.search.date_range.to_date = $toDate.getDate()+" "+$monthArr[$toDate.getMonth()]+", "+$toDate.getFullYear();
            }
            var params = {'search': $scope.search, 'sort': $scope.sortBy, 'limit': $scope.numPerPage, 'page':$scope.currentPage, };
            rest.customModelData("reports/employee-logs?field=login_location,login_ip,created_on,request_from,expire_on,expiry_status&expand=user", params).success(function (data) {
                $scope.data = data.items;
                $scope.totalCount = data._meta.totalCount;
                $scope.pageCount = data._meta.pageCount;
                $scope.currentPage = (data._meta.currentPage);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        updateList();
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
		$scope.downloadReport = function() {
            
            criteria = {'search': $scope.search,'da':'all'};
            
            rest.customModelData("reports/employee-logs?field=login_location,login_ip,created_on,request_from,expire_on,expiry_status&expand=user", criteria).success(function (data) {
                
                $http.post("exports/generate-employee-logs-reports", data.items).success(function (data) {
                    var tabWindowId = window.open("_new");
                    tabWindowId.location.href = data;
                }).error(function (data) {
                    errorCallback(data)
                });


            }).error(errorCallback);
        }

    }])

app.controller('ReportsTimeattendanceLogs', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'page_dropdown', '$rootScope',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, page_dropdown, $rootScope) {
        
        $scope.tagsNum = 1;
        rest.path = "time-attendance-log";
        $scope.page_dropdown = page_dropdown;
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Time Attendance Logs");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/reports/timeattendance", "Time Attendance Logs");
        
        $scope.datepickers = {fromDate: false,toDate: false}
        $scope.openCalendar = function($event, which) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.datepickers[which]= true;
        };
        $scope.dateOptions = {formatYear: 'yy',startingDay: 1};
        $scope.formats = ['dd MMM, yyyy', 'dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
        $scope.format = $scope.formats[0];
        
        $scope.search = {};
        $scope.sort = [];
        $scope.sortBy = "";
        $scope.showSearchBox = true;
        $scope.time = Date.now() / 1000;
        $scope.projectlevels = [];
        $scope.projects = [];
        $scope.usergroups = [];
                
        $scope.setSearch = function() {
            updateList();
        }
        
        $scope.clearSearch = function() {
            $scope.search = {};
            updateList();
        }
        
        $scope.pageChanged = function() {
            updateList();
        }
        
        $scope.setPageLimit = function(){
            updateList();
        }
       
        var updateList = function() {
            var params = {'search': $scope.search, 'sort': $scope.sortBy, 'limit': $scope.numPerPage, 'page':$scope.currentPage, };
            rest.customModelData("time-attendance-log/search?expand=user,timeattendance,project_level", params).success(function (data) {
                $scope.data = data.items;
                $scope.totalCount = data._meta.totalCount;
                $scope.pageCount = data._meta.pageCount;
                $scope.currentPage = (data._meta.currentPage);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        updateList();
        
        $scope.updateSelectBox = function(variable, projectId, level, parent) {
            $scope.search.project_id = projectId;
            
            if(variable=="projectlevels") {
                if(level>0)
                    $scope.search.project_level_id = parent;
                
                $scope[variable].splice(level, ($scope[variable].length-level));
                
                $http.post("/projectlevel/getall", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });

                if(level==0) {
                    $http.post("usergroups/getall", {'search': {'project_id': projectId}}).success(function(data) {$scope.usergroups = data.items;});
                }
            }
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        $scope.downloadReport = function() {
            if(typeof $scope.search.date_range !== 'undefined') {
                $monthArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $fromDate = $scope.search.date_range.from_date;
                if(typeof $fromDate === 'date')
                    $scope.search.date_range.from_date = $fromDate.getDate()+" "+$monthArr[$fromDate.getMonth()]+", "+$fromDate.getFullYear();
                
                $toDate = $scope.search.date_range.to_date;
                if(typeof $toDate === 'date')
                    $scope.search.date_range.to_date = $toDate.getDate()+" "+$monthArr[$toDate.getMonth()]+", "+$toDate.getFullYear();
            }
            var params = {'search': $scope.search, 'sort': $scope.sortBy, 'limit': $scope.numPerPage, 'page':$scope.currentPage, };
            $http.post("reports/generate-time-attendance-reports", params).success(function(data) {
                var tabWindowId = window.open("_new");
                tabWindowId.location.href = data;
            }).error(function(data) {errorCallback(data)});
        }
        
        
        rest.setData("projects/getall", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
        rest.setData("usergroups/getall", {}, {}).success(function(data) {$scope.usergroups = data.items;});
    }])
app.controller('ImportsProjects', ['$scope', 'rest', '$location', '$route', '$routeParams', 'alertService', '$http', 'breadcrumbsService', '$upload', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, $upload) {

        rest.path = "imports";
        $scope.files ={};
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Import Projects");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/import/projects", "Import Projects");
        
        $scope.onFileSelect = function ($files, modelName) {
            //$files: an array of files selected, each file has name, size, and type.
            for (var i = 0; i < $files.length; i++) {
                var file = $files[i];
                $scope.upload = $upload.upload({
                    url: 'filemanager/uploaddoc', //upload.php script, node.js route, or servlet url
                    data: {myObj: $scope.myModelObj},
                    file: file,
                }).progress(function (evt) {
                    console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
                }).success(function (data, status, headers, config) {
                    // file is uploaded successfully
                    //console.log(data);
                    $scope.files[modelName] = data;
                });
            }
        };
        
        $scope.saveProjects = function () {
            
            $http.post("/imports/import-projects",{'data':$scope.files}).success(function (data) {
                alertService.clearAll();
                alertService.add("success", data);
                return;
            }).error(function (data) {
                alertService.clearAll();
                alertService.add("error", "Error in project import!!");
                return;
            });
        }
        
        $scope.downloadTemplate = function () {
            
            $http.post("/exports/download-project-template").success(function(data) {
                var tabWindowId = window.open("_new");
                tabWindowId.location.href = data;
            }).error(function(data) {errorCallback(data)});
        }
        
}])

app.controller('ImportsUsers', ['$scope', 'rest', '$location', '$route', '$routeParams', 'alertService', '$http', 'breadcrumbsService', '$upload', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, $upload) {

        rest.path = "imports";
        $scope.files ={};
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Import Users");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/import/users", "Import Users");
        
        $scope.onFileSelect = function ($files, modelName) {
            //$files: an array of files selected, each file has name, size, and type.
            for (var i = 0; i < $files.length; i++) {
                var file = $files[i];
                $scope.upload = $upload.upload({
                    url: 'filemanager/uploaddoc', //upload.php script, node.js route, or servlet url
                    data: {myObj: $scope.myModelObj},
                    file: file,
                }).progress(function (evt) {
                    console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
                }).success(function (data, status, headers, config) {
                    // file is uploaded successfully
                    //console.log(data);
                    $scope.files[modelName] = data;
                });
            }
        };
        
        $scope.saveUsers = function () {
            
            $http.post("/imports/import-users",{'data':$scope.files}).success(function (data) {
                alertService.clearAll();
                alertService.add("success", data);
                return;
            }).error(function (data) {
                $scope.error = data;
                return;
            });
        }
        
        $scope.downloadTemplate = function () {
            
            $http.post("/exports/download-user-template").success(function(data) {
                var tabWindowId = window.open("_new");
                tabWindowId.location.href = data;
            }).error(function(data) {errorCallback(data)});
        }
        
}])