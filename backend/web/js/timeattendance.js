app.controller('TimeAttendance', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'page_dropdown', '$rootScope',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, page_dropdown, $rootScope) {
        $scope.tagsNum = 1;
        rest.path = "timeattendance";
        $scope.page_dropdown = page_dropdown;
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Manage Attendance Tags");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/timeattendance", "Attendance Tags");
        breadcrumbsService.add("/#/timeattendance", "List Tags");
        
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

        $scope.setSearch = function() {
            updateTagList();
        }
        
        $scope.clearSearch = function() {
            $scope.search = $scope.project = {};
            $scope.projectlevels = [];
            $scope.projects = [];
            $scope.usergroups = [];
            
            rest.setData("projects/getall", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
            
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
        
        $scope.deleteTag = function (id) {
            rest.deleteById({id: id}).success(function () {
                $location.path('/timeattendance');
                $route.reload();
            }).error(errorCallback);
        }
        
        $scope.pageChanged = function() {
            updateTagList();
        }
        
        $scope.setPageLimit = function(){
            updateTagList();
        }
        
        $scope.removeUser =  function(model, $index) {
            rest.deleteById(model);
            $scope.data.splice($index, 1);
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
        
        updateTagList();
        
        rest.setData("projects/getall", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
        rest.setData("usergroups/getall", {}, {}).success(function(data) {$scope.usergroups = data.items;});
        
    }])

app.controller('TimeAttendanceForm', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'tooltip', '$modal', '$log', 'alertService',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, tooltip, $modal, $log, alertService) {
        
        rest.path = "timeattendance";
        
        $scope.tooltip = tooltip;
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Create Attendance Tag");
        breadcrumbsService.headTitle("Create Attendance Tag");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/timeattendance", "Attendance Tags");
        breadcrumbsService.add("/#/timeattendance/create", "Create Tag");
        
        $scope.search = {};
        $scope.projectlevels = [];
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
        $scope.tagDetails = {};
        $scope.tagDetails.tag = {};
        $scope.tagDetails.tagAssignment = {};
        $scope.levels = [];
        $scope.process_stages = [];
        $scope.working = false;
        
        $scope.noti_frequency = [
            {id: 'onupdate', name:'On update'},
            {id: 'daily', name:'Daily'},
            {id: 'weekly', name:'Weekly'},
            {id: 'monthly', name:'Monthly'},
            {id: 'yearly', name:'Yearly'},
        ];
                
        $scope.selectAllLevels = function($event) {
            var parts = "levels";
            var selectedVar = "isSelected";
            var element = angular.element($event.currentTarget);
            var setValue = $scope.selectAllLevelUsers;
            angular.forEach($scope.$eval(parts), function (v) {
                v[selectedVar] = setValue;
                angular.forEach(v.relateUsers, function (v) {
                    v[selectedVar] = setValue;
                });
            });
        }
        
        if($routeParams.id) {
            var selectedItem = $routeParams.id;
            $scope.tagLoading = true;
            $http.get("/timeattendance/"+selectedItem+"?expand=projectLevelObj,userGroup,timeAttendanceAssignmentObj").success(function(data) {
                
                $scope.search.project = {'id': data.project_id, 'project_name': data.project_name};
                $scope.tagDetails.project_id = $scope.search.project.id;
                $scope.updateSelectBox("projectlevels", $scope.tagDetails.project_id, 0, 0);
                
                $scope.tagDetails.tag.id = selectedItem;
                $scope.tagDetails.tag.tag_name = data.tag_name;
                $scope.tagDetails.tag.tag_description = data.tag_description;
                $scope.tagDetails.project_level_id = data.project_level_id;
                $scope.search.childlevels = data.projectLevelObj;
                $scope.projectlevels.splice(1, ($scope.projectlevels.length-1));
                angular.forEach(data.projectLevelObj, function(v, k){
                    $scope.projectlevels.push([v]);
                });

                $scope.tagDetails.user_group_id = data.user_group_id;
                $scope.search.usergroup = data.userGroup;
                $scope.getUserLevel();
                $scope.tagDetails.tagAssignment = data.timeAttendanceAssignmentObj;
                $scope.tagLoading = false;
            }).error(function(data) {
                errorCallback();
                $scope.tagLoading = false;
            });
        }
        
        $scope.getUserLevel = function() {
            
            if(!$scope.tagDetails['project_level_id'])
            {
                alert("Please select a project level before moving ahead.");
                $scope.search.usergroup = null;
                return;
            }
            $scope.tagDetails['user_group_id'] = $scope.search.usergroup.id;
            $http.post("/userlevels/getall", {'search': {user_group_id:$scope.search.usergroup.id}}).success(function(data) {
                $scope.levels = data.items;
            });
        }
        
        $scope.updateSelectBox = function(variable, projectId, level, parent) {
            
            $scope.tagDetails['project_id'] = projectId;
            
            if(variable=="projectlevels") {
                
                $scope[variable].splice(level, ($scope[variable].length-level));
                
                $http.post("/projectlevel/getall", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                    if(data.items.length>0) {
                        $scope[variable].push(data.items);
                        if(data.items.length==1 && level<=1) {
                            if(typeof $scope.search.childlevels == 'undefined') {
                                $scope.search.childlevels = [];
                            }
                            $scope.tagDetails.project_level_id = data.items[0].id;
                            $scope.search.childlevels[level] = data.items[0];
                            $scope.updateSelectBox('projectlevels', projectId, ($scope.search.childlevels.length+1), $scope.search.childlevels[level].id)
                        }
                    }
                });

                if(level==0) {
                      rest.setData("usergroups/getall", [], {'project_id': projectId}).success(function(data) {
                        if(data.items.length) {
                            $scope.usergroups = data.items;
                            if(data.items.length==1) {
                                $scope.search.usergroup = data.items[0];
                                $scope.tagDetails.user_group_id = data.items[0].id;
                                $scope.getUserLevel();
                            }
                        }
                    });
                }
                else
                {
                    $scope.tagDetails['project_level_id'] = parent;
                }
            }
        }
        
        $scope.saveTagDetails = function() {
            $scope.working = true;
            $scope.tagDetails.tag_assignment = [];
            $scope.tagDetails.tagAssignment = null;
            
            angular.forEach($scope.levels, function(level) {
                angular.forEach(level.relateUsers, function(value) {
                    if(value['isSelected']) {
                        $scope.tagDetails.tag_assignment.push({'user_id': value['user_id']});
                    }
                });
            });
            
            $http.post("/timeattendance/save",{'tagDetails': $scope.tagDetails}).success(function(data) {
                alertService.clearAll();
                if(data=='Success') {
                    alertService.add('success', "Simple Tag(s) Created Successfully.");
                    $location.path('/timeattendance').replace();
                }
                $scope.working = false;
            }).error(function(data) {
                alertService.clearAll();
                alert("Error in tag creation");
                alertService.add('error', data);
                $scope.working = false;
            });
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        rest.setData("projects/getall", ['id', 'project_name'], {'project_status': null}).success(function(data) {
            $scope.projects = data.items;
            if(data.items.length==1) {
                $scope.search.project = data.items[0];
                $scope.updateSelectBox('projectlevels', $scope.search.project.id, 0, 0)
            }
        });
    }])

app.controller('TimeAttendanceView', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        
        rest.path = "timeattendance-log";
        
        alertService.clearAll();
        breadcrumbsService.clearAll();
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/timeattendance", "Attendance Tags");
        breadcrumbsService.add("/#/timeattendance", "Attendance Tag Activity Logs");
        
        $scope.search = [];
        $scope.projectlevels = [];
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
        $scope.activity = [];
        
        $scope.updateLevel = function(projectId, level, parent) {
            $scope.search.project_id = projectId;
            
            $scope.projectlevels.splice(level, ($scope.projectlevels.length-level));
            
            $http.post("/projectlevel/getall", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                if(data.items.length>0)
                    $scope.projectlevels.push(data.items);
            });
            
            if(level==0)
                $http.post("usergroups/getall", {'search': {'project_id': projectId}}).success(function(data) {
                    $scope.usergroups = data.items;
                });
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
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
		
        $http.post("/timeattendance-log/search?expand=loggedBy",{'search': {'tag_id':$routeParams.id}}).success(function(data) {
            $scope.activity = data.items;
        });
        
        $http.get("/timeattendance/"+$routeParams.id+"?expand=projectLevelObj,userGroup,timeattendanceAssignmentObj").success(function(data) {
            $scope.tagAnalytic = data;
            breadcrumbsService.setTitle("Time Attendance Logs: "+data.tag_name);
        });
    }])