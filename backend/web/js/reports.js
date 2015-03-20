app.controller('PrintLabel', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', '$rootScope',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, $rootScope) {
        
        rest.path = "tags";
        
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
        $scope.projectlevels = [];
        $scope.projects = null;
        $scope.usergroups = [];
        $scope.items = [];
        $scope.processes = [];
        $scope.print = {};
        $scope.print.labels = {};
        $scope.basePrintType = {
            'bar_code': 'YmFyY29kZQ==',
            'qr_code': 'cXJjb2Rl',
            'nfc_code': 'bmZjY29kZQ=='
        };
        
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
            rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0}).success(function(data) {$scope.items.push(data.items);});
            rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0}).success(function(data) {$scope.processes.push(data.items);});
            
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
        
        $scope.printLabel = function() {
            if(parseInt($scope.print.label_template.id)>0) {
                if(  Object.keys($scope.print.labels).length>0) {
                    var tabWindowId = window.open('about:blank', '_blank');
                    
                    $http.post("/reportsdownload/printlabel", {'print': $scope.print, 'print_type': $scope.print.label_template.print_type}).success(function(response) {
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
        
        $scope.checkLabel = function(label) {
            if(label.isSelected) {
                $scope.print.labels[label['id']] = {};
                $scope.print.labels[label['id']]['uid'] = label['uid'];
                if($scope.print.label_template.checked_labels.company_name) $scope.print.labels[label['id']]['company_name'] = $scope.company.company_name;
                if($scope.print.label_template.checked_labels.client_name) $scope.print.labels[label['id']]['client_name'] = $scope.search.project.client_name;
                if($scope.print.label_template.checked_labels.client_location) $scope.print.labels[label['id']]['client_location'] = $scope.search.project.client_address+" "+$scope.search.project.client_city;
                if($scope.print.label_template.checked_labels.main_contractor) $scope.print.labels[label['id']]['main_contractor'] = $scope.search.project.main_contractor;
                if($scope.print.label_template.checked_labels.project_name) $scope.print.labels[label['id']]['project_name'] = $scope.search.project.project_name;
                if($scope.print.label_template.checked_labels.project_location) $scope.print.labels[label['id']]['area'] = $scope.search.project.project_location;
                if($scope.print.label_template.checked_labels.project_level) {
                    $scope.print.labels[label['id']]['project_level'] = "";
                    var index = 0;
                    angular.forEach(label.project_level, function(val) {
                        if(index!=0)
                            $scope.print.labels[label['id']]['project_level'] += " > ";
                        $scope.print.labels[label['id']]['project_level'] += val;
                        index++;
                    });
                }
                if($scope.print.label_template.checked_labels.tag_item) {
                    $scope.print.labels[label['id']]['item'] = "";
                    angular.forEach(label.itemObj, function(val, key) {
                        if(key!=0)
                            $scope.print.labels[label['id']]['item'] += " > ";
                        $scope.print.labels[label['id']]['item'] += val['item_name'];
                    });
                }
                if($scope.print.label_template.checked_labels.process) {
                    $scope.print.labels[label['id']]['process'] = "";
                    angular.forEach(label.processObj, function(val, key) {
                        if(key!=0)
                            $scope.print.labels[label['id']]['process'] += " > ";
                        $scope.print.labels[label['id']]['process'] += val['process_name'];
                    });
                }
                $scope.print.labels[label['id']]['uid'] = label.uid;
                if($scope.print.label_template.checked_labels.tag_description) $scope.print.labels[label['id']]['task_summary'] = label.tag_description;
            }
            else {
                $scope.print.labels[label['id']] = undefined;
            }
        }
        
        $scope.checkAll = function(checkAll) {
            angular.forEach($scope.labels, function(val) {
                val['isSelected'] = checkAll;
                if(checkAll) {
                    $scope.checkLabel(val);
                }
            });
            if(!checkAll) 
                $scope.print.labels = {};
        }
                
        $scope.viewLabels = function() {
            $(".has-error").removeClass("has-error");
            if(typeof $scope.search.project_id == 'undefined' || $scope.search.project_id <= 0) {
                alertService.clearAll();
                alertService.add("error", " You must have a project selected before moving ahead!!");
                $("#projects").addClass("has-error");
                return;
            }
            if(typeof $scope.print.label_template == 'undefined' || $scope.print.label_template.id <= 0) {
                alertService.clearAll();
                alertService.add("error", " Please select a template to print!!");
                $("#label_template").addClass("has-error");
                return;
            }
            var params = {'search': $scope.search, 'filter': $scope.filter, 'print_type': $scope.print_type};
            rest.customModelData("reports/labels", params).success(function (data) {
                $scope.labels = data.items;                
            }).error(errorCallback);
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        rest.customModelData("projects/search", {}).success(function(data) {$scope.projects = data.items;});
        rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0}).success(function(data) {$scope.items.push(data.items);});
        rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0}).success(function(data) {$scope.processes.push(data.items);});
        rest.setData("usergroups/getall", {}, {}).success(function(data) {$scope.usergroups = data.items;});
        rest.setData("company/default", {}, {}).success(function (data) {$scope.company = data;});
        rest.customModelData("labeltemplates/getall", {}).success(function (data) {$scope.templates = data.items;$scope.print.label_template = $scope.templates[0];});
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
        
        $scope.search = {};
        $scope.projectlevels = [];
        $scope.projects = null;
        $scope.usergroups = [];
        $scope.items = [];
        $scope.processes = [];
        $scope.print = {};
        $scope.print.labels = {};
        $scope.basePrintType = {
            'bar_code': 'QXR0ZW5kYW5jZWJhcmNvZGU=',
            'qr_code': 'QXR0ZW5kYW5jZXFyY29kZQ==',
            'nfc_code': 'bmZjY29kZQ=='
        };
        
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
        
        $scope.checkLabel = function(label) {
            if(label.isSelected) {
                $scope.print.labels[label['id']] = {};
                $scope.print.labels[label['id']]['uid'] = label['uid'];
                if($scope.print.label_template.checked_labels.company_name) $scope.print.labels[label['id']]['company_name'] = $scope.company.company_name;
                if($scope.print.label_template.checked_labels.client_name) $scope.print.labels[label['id']]['client_name'] = $scope.search.project.client_name;
                if($scope.print.label_template.checked_labels.client_location) $scope.print.labels[label['id']]['client_location'] = $scope.search.project.client_address+" "+$scope.search.project.client_city;
                if($scope.print.label_template.checked_labels.main_contractor) $scope.print.labels[label['id']]['main_contractor'] = $scope.search.project.main_contractor;
                if($scope.print.label_template.checked_labels.project_name) $scope.print.labels[label['id']]['project_name'] = $scope.search.project.project_name;
                if($scope.print.label_template.checked_labels.project_location) $scope.print.labels[label['id']]['area'] = $scope.search.project.project_location;
                if($scope.print.label_template.checked_labels.project_level) {
                    $scope.print.labels[label['id']]['project_level'] = "";
                    var index = 0;
                    angular.forEach(label.project_level, function(val) {
                        if(index!=0)
                            $scope.print.labels[label['id']]['project_level'] += " > ";
                        $scope.print.labels[label['id']]['project_level'] += val;
                        index++;
                    });
                }
                $scope.print.labels[label['id']]['uid'] = label.uid;
                if($scope.print.label_template.checked_labels.tag_description) $scope.print.labels[label['id']]['task_summary'] = label.tag_description;
            }
            else {
                $scope.print.labels[label['id']] = undefined;
            }
        }
        
        $scope.checkAll = function(checkAll) {
            angular.forEach($scope.labels, function(val) {
                val['isSelected'] = checkAll;
                if(checkAll) {
                    $scope.checkLabel(val);
                }
            });
            if(!checkAll) 
                $scope.print.labels = {};
        }
                
        $scope.viewLabels = function() {
            if(typeof $scope.search.project_id == 'undefined' || $scope.search.project_id <= 0) {
                alertService.clearAll();
                alertService.add("error", " You must have a project selected before moving ahead!!");
                $("#projects").addClass("has-error");
                return;
            }
            if(typeof $scope.print.label_template == 'undefined' || $scope.print.label_template.id <= 0) {
                alertService.clearAll();
                alertService.add("error", " Please select a template to print!!");
                $("#label_template").addClass("has-error");
                return;
            }
            var params = {'search': $scope.search, 'filter': $scope.filter, 'print_type': $scope.print_type};
            rest.customModelData("reports/timeattendancelabels", params).success(function (data) {
                $scope.labels = data.items;                
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
        rest.customModelData("labeltemplates/getall", {}).success(function (data) {$scope.templates = data.items;$scope.print.label_template = $scope.templates[0];});
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