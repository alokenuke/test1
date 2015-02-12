app.controller('TagIndex', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'page_dropdown', '$rootScope',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, page_dropdown, $rootScope) {
        $scope.tagsNum = 1;
        rest.path = "tags";
        $scope.page_dropdown = page_dropdown;
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Manage Tags");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tags", "List Tags");
        
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
        
        if(typeof $rootScope.globalSearch != 'undefined' && $rootScope.globalSearch.length > 2) {
            $scope.search.globalSearch = $rootScope.globalSearch;
            $scope.showSearchBox = false;
        }
        
        if($location.$$search.sort!=undefined) {
            $scope.predicate = $location.$$search.sort;
            $scope.reverse = ($scope.predicate.search("-")==-1?false:true);
        }
        
        $scope.setSearch = function() {
            updateTagList();
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
                $location.path('/tags');
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
        
        var updateTagList = function() {
            var params = {'search': $scope.search, 'sort': $scope.sortBy, 'limit': $scope.numPerPage, 'page':$scope.currentPage, };
			rest.customModelData("tags/search?expand=tagActivityLog", params).success(function (data) {
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
        rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0}).success(function(data) {$scope.items.push(data.items);});
        rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0}).success(function(data) {$scope.processes.push(data.items);});
        rest.setData("usergroups/getall", {}, {}).success(function(data) {$scope.usergroups = data.items;});
        
    }])

app.controller('TagsCreate', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'tooltip', '$modal', '$log', 'alertService',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, tooltip, $modal, $log, alertService) {
        
        rest.path = "tags";
        
        $scope.tooltip = tooltip;
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Create Simple Tag");
        breadcrumbsService.headTitle("Create Tag - <span class='sT'>sT</span>");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tags/create", "Create Tag");
        
        $scope.search = {};
        $scope.projectlevels = [];
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
        $scope.itemList = [];
        $scope.items = [];
        $scope.processes = [];
        $scope.tagDetails = {};
        $scope.tagDetails.tags = [{id:'',pre:'',tagName:'',post:'',productCode:'',tagDescription:''}];
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
        
        $scope.noti_status = [];
        
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
        
        $scope.copyLastTag = function() {
            if(!$scope.tagDetails.project_id)
            {
                alert("Please select a project first.");
                return false;
            }
            $http.post("/tags/get-last-tag", {'search': {'project_id': $scope.tagDetails.project_id}}).success(function(data) {
                if(data>0) {
                    $scope.tagLoading = true;
                    $scope.loadTagById(data);
                }
                else
                    alert("No last tag found or Error in loading last tag.");
            });
        }
               
        $scope.createSimilarTagModal = function() {
            
            if(!$scope.tagDetails.project_id)
            {
                alert("Please select a project first.");
                return false;
            }
            
            var modalInstance = $modal.open({
                templateUrl: '/templates/tags/create_similar_tag.html',
                controller: 'createSimilarTagModalController',
                size: 'lg',
                resolve: {
                    itemScope: function () {
                        return {'project_id': $scope.tagDetails.project_id};
                    }
                }
            });

            modalInstance.result.then(function (selectedItem) {
                
                $scope.tagLoading = true;
                
                $scope.loadTagById(selectedItem);
                
            }, function () {
                $log.info('Modal dismissed at: ' + new Date());
            });
        }
        
        $scope.loadTagById = function(selectedItem) {
            
            $http.get("/tags/"+selectedItem+"?expand=projectLevelObj,itemObj,processObj,userGroup,tagAssignmentObj").success(function(data) {
                $scope.tagDetails.tag_item_id = data.tag_item_id;
                $scope.search.item = data.itemObj;
                $scope.items.splice(1, ($scope.items.length-1));
                angular.forEach(data.itemObj, function(v, k){
                    if(k!=0) { 
                       rest.setData("items/getall", ['id', 'item_name'], {'parent_id': v['parent_id'], 'project_id': $scope.tagDetails.project_id}).success(function(data) {
                            if(data.items.length)
                                $scope.items.push(data.items);
                            else
                                $scope.items.push([v]);
                        });
                    }
                });

                $scope.tagDetails.tag_process_flow_id = data.tag_process_flow_id;
                $scope.search.process = data.processObj;
                $scope.processes.splice(1, ($scope.processes.length-1));
                angular.forEach(data.processObj, function(v, k){
                    if(k!=0) {
                        rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': v['parent_id'], 'project_id': $scope.tagDetails.project_id}).success(function(data) {
                            if(data.items.length)
                                $scope.processes.push(data.items);
                            else
                                $scope.processes.push([v]);
                        });
                    }
                });

                rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': $scope.tagDetails.tag_process_flow_id}).success(function(data) {
                    $scope.process_stages = data.items;
                });

                $scope.tagDetails.project_level_id = data.project_level_id;
                $scope.search.childlevels = data.projectLevelObj;
                $scope.projectlevels.splice(1, ($scope.projectlevels.length-1));
                angular.forEach(data.projectLevelObj, function(v, k){
                    if(k!=0) {
                        $http.post("/projectlevel/getall", {'search': {'project_id': $scope.tagDetails.project_id, 'parent_id': v['parent_id']}, 'select': ['id', 'level_name']}).success(function(data) {
                            if(data.items.length>0)
                                $scope.projectlevels.push(data.items);
                            else
                                $scope.projectlevels.push([v]);
                        });
                    }
                });

                $scope.tagDetails.user_group_id = data.user_group_id;
                $scope.search.usergroup = data.userGroup;
                $scope.getUserLevel();
                $scope.tagDetails.tagAssignment = data.tagAssignmentObj;
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
            else if(!$scope.tagDetails['tag_item_id'])
            {
                alert("Please select an item type before moving ahead.");
                $scope.search.usergroup = null;
                return;
            }
            else if(!$scope.tagDetails['tag_process_flow_id'])
            {
                alert("Please select a process flow before moving ahead.");
                $scope.search.usergroup = null;
                return;
            }
            
            $scope.tagDetails['user_group_id'] = $scope.search.usergroup.id;
            $http.post("/userlevels/getall", {'search': {user_group_id:$scope.search.usergroup.id}}).success(function(data) {
                $scope.levels = data.items;
            });
        }
        
        $scope.copyTags =  function(tagsNum){
            var temp = [];
            temp.pre = $scope.tagDetails.tags[$scope.tagDetails.tags.length-1].pre;
            temp.tagName = $scope.tagDetails.tags[$scope.tagDetails.tags.length-1].tagName;
            temp.post = $scope.tagDetails.tags[$scope.tagDetails.tags.length-1].post;
            
            for(var i=0;i<tagsNum;i++) {
                $scope.tagDetails.tags.push({id:'',pre:parseInt(temp.pre)+i+1,tagName:temp.tagName,post:parseInt(temp.post)+i+1,productCode:'',tagDescription:''});
             }
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
                    $scope.items = [];
                    $scope.processes = [];
                    var itemLoading = rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0, 'project_id': projectId}).success(function(data) {
                        if(data.items.length) {
                            $scope.items.push(data.items);
                            $scope.search.item = [];
                            if(data.items.length==1) {
                                $scope.search.item[0] = data.items[0];
                                $scope.tagDetails.tag_item_id = data.items[0].id;
                                return $scope.updateSelectBox('items', projectId, level+1, $scope.search.item[0].id);
                            }
                            else
                                return true;
                        }
                    });
                    var processLoading = rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0, 'project_id': projectId}).success(function(data) {
                        if(data.items.length)
                            $scope.processes.push(data.items);
                            $scope.search.process = [];
                            if(data.items.length==1) {
                                $scope.search.process[0] = data.items[0];
                                $scope.tagDetails.tag_process_flow_id = data.items[0].id;
                                return $scope.updateSelectBox('processes', projectId, level+1, $scope.search.process[0].id);
                            }
                            else
                                return true;
                    });
                    
                    if(itemLoading && processLoading)
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
            else if(variable=='items') {
                if(level > 0)
                    $scope.tagDetails['tag_item_id'] = parent;
                
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("items/getall", ['id', 'item_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0) {
                        $scope.items.push(data.items);
                        if(data.items.length==1) {
                            $scope.search.item[level] = data.items[0];
                            $scope.updateSelectBox('items', projectId, level, $scope.search.item[level].id);
                        }
                    }
                });
                
                if($scope.search['process'] && $scope.search.process[0] && level==1) {
                    $scope['processes'].splice(0, ($scope['processes'].length-1));

                    rest.setData("items/getrelatedprocess/"+parent, ['id', 'process_name'], {'process_parent_id': $scope.search.process[0], 'tag_item_id': $scope.search.item[1]}).success(function(data) {
                        if(data.items.length>0) {
                            $scope['processes'].push(data.items);
                            
                            if(data.items.length==1) {
                                $scope.search.process[level] = data.items[0];
                                $scope.updateSelectBox('processes', projectId, level, $scope.search.process[level].id);
                            }
                        }
                    });
                }
            }
            else if(variable=='processes') {
                
                if(level==1)
                    $scope.tagDetails['tag_process_flow_id'] = parent;
                
                if($scope.search['item'] && $scope.search.item[1]) {
                    $scope[variable].splice(level, ($scope[variable].length-level-1));
                    
                    if(level < 1) {   
                        rest.setData("items/getrelatedprocess/"+$scope.search.item[1].id, ['id', 'process_name'], {'process_parent_id': parent}).success(function(data) {
                            if(data.items.length>0) {
                                $scope[variable].push(data.items);
                                if(data.items.length==1) {
                                    $scope.search.process[$scope[variable].length] = data.items[0];
                                    $scope.updateSelectBox('processes', projectId, level+1, $scope.search.process[level].id);
                                }
                            }
                        });
                    }
                    else if(level==1) {
                        rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': parent}).success(function(data) {
                            $scope.process_stages = data.items;
                        });
                    }
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
                        $scope.tagDetails.tag_assignment.push({'user_id': value['user_id'], 'process_stage_from': $scope.search.stage_from[value['user_id']], 'process_stage_to': $scope.search.stage_to[value['user_id']], 'mandatory': $scope.search.mandatory[value['user_id']], 'notification_status': $scope.search.notification_status[value['user_id']], 'notification_frequency': $scope.search.notification_frequency[value['user_id']]});
                    }
                });
            });
            
            $http.post("/tags/create-simple-tags",{'tagDetails': $scope.tagDetails}).success(function(data) {
                alertService.clearAll();
                if(data=='Success') {
                    alertService.add('success', "Simple Tag(s) Created Successfully.");
                    $location.path('/tags').replace();
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
            $scope.search.project = data.items[0];
            $scope.updateSelectBox('projectlevels', $scope.search.project.id, 0, 0)
        });
        
        $scope.$watch('process_stages', function (newVal) {
            $scope.noti_status = [
                {id: 'all', name:'All process'},
                {id: 'assigned', name:'Assigned process'},
            ];
            angular.forEach($scope.process_stages, function(v) {
                $scope.noti_status.push({'id': v['id'], 'name': v['process_name']});
            });
        }, true);
        
    }])

app.controller('TagsUpdate', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'tooltip', '$modal', '$log', 'alertService',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, tooltip, $modal, $log, alertService) {
        
        rest.path = "tags";
        
        $scope.tooltip = tooltip;
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Update Tag - sT");
        breadcrumbsService.headTitle("Update Tag - <span class='sT'>sT</span>");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tags/update/"+$routeParams.id+"/"+$routeParams.project_id, "Update Tag");
        
        alertService.clearAll();
        
        $scope.search = {};
        $scope.projectlevels = [];
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
        $scope.itemList = [];
        $scope.items = [];
        $scope.processes = [];
        $scope.tagDetails = {};
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
        
        $scope.noti_status = [];
        
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
        
        $scope.loadTagById = function(selectedItem) {
            $scope.tagLoading = true;
            $http.get("/tags/"+selectedItem+"?expand=projectLevelObj,itemObj,processObj,userGroup,tagAssignmentObj").success(function(data) {
                
                $scope.search.project = {'id': $routeParams.project_id, 'project_name': data.project_name};
                
                $scope.tagDetails.tag_name = data.tag_name;
                $scope.tagDetails.pre = data.pre;
                $scope.tagDetails.post = data.post;
                $scope.tagDetails.tag_description = data.tag_description;
                $scope.tagDetails.product_code = data.product_code;
                
                $scope.tagDetails.tag_item_id = data.tag_item_id;
                $scope.search.item = data.itemObj;
                $scope.items.splice(1, ($scope.items.length-1));
                angular.forEach(data.itemObj, function(v, k){
                    if(k!=0)
                        $scope.items.push([v]);
                });

                $scope.tagDetails.tag_process_flow_id = data.tag_process_flow_id;
                $scope.search.process = data.processObj;
                $scope.processes.splice(1, ($scope.processes.length-1));
                angular.forEach(data.processObj, function(v, k){
                    if(k!=0)
                        $scope.processes.push([v]);
                });

                rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': $scope.tagDetails.tag_process_flow_id}).success(function(data) {
                        $scope.process_stages = data.items;
                });

                $scope.tagDetails.project_level_id = data.project_level_id;
                $scope.search.childlevels = data.projectLevelObj;
                $scope.projectlevels.splice(1, ($scope.projectlevels.length-1));
                angular.forEach(data.projectLevelObj, function(v, k){
                    $scope.projectlevels.push([v]);
                });

                $scope.tagDetails.user_group_id = data.user_group_id;
                $scope.search.usergroup = data.userGroup;
                $scope.getUserLevel();
                $scope.tagDetails.tagAssignment = data.tagAssignmentObj;
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
            else if(!$scope.tagDetails['tag_item_id'])
            {
                alert("Please select an item type before moving ahead.");
                $scope.search.usergroup = null;
                return;
            }
            else if(!$scope.tagDetails['tag_process_flow_id'])
            {
                alert("Please select a process flow before moving ahead.");
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
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });

                if(level==0) {
                    $scope.items = [];
                    $scope.processes = [];
                    rest.setData("usergroups/getall", [], {'project_id': projectId}).success(function(data) {
                        if(data.items.length)
                            $scope.usergroups = data.items;
                    });
                    rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0, 'project_id': projectId}).success(function(data) {
                        if(data.items.length)
                            $scope.items.push(data.items);
                    });
                    rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0, 'project_id': projectId}).success(function(data) {
                        if(data.items.length)
                            $scope.processes.push(data.items);
                        
                        $scope.loadTagById($routeParams.id);
                    });
                }
                else
                {
                    $scope.tagDetails['project_level_id'] = parent;
                }
            }
            else if(variable=='items') {
                if(level > 0)
                    $scope.tagDetails['tag_item_id'] = parent;
                
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("items/getall", ['id', 'item_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });
                
                if($scope.search['process'] && $scope.search.process[0] && level==1) {
                    $scope['processes'].splice(0, ($scope['processes'].length-1));

                    rest.setData("items/getrelatedprocess/"+parent, ['id', 'process_name'], {'process_parent_id': $scope.search.process[0], 'tag_item_id': $scope.search.item[1]}).success(function(data) {
                        if(data.items.length>0)
                            $scope['processes'].push(data.items);
                    });
                }
            }
            else if(variable=='processes') {
                
                if(level==1)
                    $scope.tagDetails['tag_process_flow_id'] = parent;
                
                if($scope.search['item'] && $scope.search.item[1]) {
                    $scope[variable].splice(level, ($scope[variable].length-level-1));
                    
                    if(level < 1) {   
                        rest.setData("items/getrelatedprocess/"+$scope.search.item[1].id, ['id', 'process_name'], {'process_parent_id': parent}).success(function(data) {
                            if(data.items.length>0)
                                $scope[variable].push(data.items);
                        });
                    }
                    else if(level==1) {
                        rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': parent}).success(function(data) {
                            $scope.process_stages = data.items;
                        });
                    }
                }
            }
        }
        
        $scope.updateSelectBox("projectlevels", $routeParams.project_id, 0, 0);
        
        $scope.saveTagDetails = function() {
            $scope.working = true;
            $scope.tagDetails.tag_assignment = [];
            $scope.tagDetails.tagAssignment = null;
            
            angular.forEach($scope.levels, function(level) {
                angular.forEach(level.relateUsers, function(value) {
                    if(value['isSelected']) {
                        $scope.tagDetails.tag_assignment.push({'user_id': value['user_id'], 'process_stage_from': $scope.search.stage_from[value['user_id']], 'process_stage_to': $scope.search.stage_to[value['user_id']], 'mandatory': $scope.search.mandatory[value['user_id']], 'notification_status': $scope.search.notification_status[value['user_id']], 'notification_frequency': $scope.search.notification_frequency[value['user_id']]});
                    }
                });
            });
            
            $http.post("/tags/updatesimpletags/"+$routeParams.id,{'tagDetails': $scope.tagDetails}).success(function(data) {
                alertService.clearAll();
                if(data=='Success') {
                    alertService.add('success', "Simple Tag Updated Successfully.");
                    $location.path('/tags').replace();
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
        
        $scope.$watch('process_stages', function (newVal) {
            $scope.noti_status = [
                {id: 'all', name:'All process'},
                {id: 'assigned', name:'Assigned process'},
            ];
            angular.forEach($scope.process_stages, function(v) {
                $scope.noti_status.push({'id': v['id'], 'name': v['process_name']});
            });
        }, true);
        
    }])

app.controller('TagsCreateMaster', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'tooltip','$modal','$log',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, tooltip,$modal,$log) {
        
        rest.path = "tags";
        $scope.tooltip = tooltip;
        
        alertService.clearAll();
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Create Master Tag");      
        breadcrumbsService.headTitle("Create Tag - <span class='mT'>mT</span>");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tags/createmaster", "Create Master Tag");
        
        $scope.search = {};
        $scope.projectlevels = [];
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
        $scope.itemList = [];
        $scope.items = [];
        $scope.processes = [];
        $scope.tagDetails = {};
        $scope.tagDetails.tags = [];
        $scope.tagDetails.tagAssignment = {};
        $scope.levels = [];
        $scope.process_stages = [];
        $scope.working = false;
        $scope.selectedTags = [];        
        $scope.noti_frequency = [
            {id: 'onupdate', name:'On update'},
            {id: 'daily', name:'Daily'},
            {id: 'weekly', name:'Weekly'},
            {id: 'monthly', name:'Monthly'},
            {id: 'yearly', name:'Yearly'},
        ];
        
        $scope.noti_status = [];
        
        $scope.$delItems  = [];  
        
        $scope.deleteTags = function() {
            
            $scope.$delItems = $scope.$delItems.filter(function(e){return e});

            for(var i = 0; i < $scope.selectedTags.length; i++) {
                 var obj = $scope.selectedTags[i];

                 if($scope.$delItems.indexOf(obj.id) !== -1) {
                     $scope.selectedTags.splice(i, 1);
                     i--;
                 }
             }
             $scope.$delItems  = [];
             $scope.selectedTags = $scope.selectedTags.filter(function(e){return e});
        }
        
        $scope.openModel = function () {
        
            var modalInstance = $modal.open({
              templateUrl: '/templates/tags/add_more_tag.html',
              controller: 'SelectTagsPopup',
              size: 'lg',
               resolve: {
                  items: function () {
                    return $scope.selectedTags;
                   }
               }
            });

          modalInstance.result.then(function (selectedItem) {
            
            if(selectedItem != 'cancel'){
                    selectedItem.filter(function (e) {
                         $scope.selectedTags.push(e);
                    });
            }
            
          }, function () {
          $log.info('Modal dismissed at: ' + new Date());
          });
        };
        
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
          
        $scope.getUserLevel = function() {
            
            if(!$scope.tagDetails['project_level_id'])
            {
                alert("Please select a project level before moving ahead.");
                $scope.search.usergroup = null;
                return;
            }
            else if(!$scope.tagDetails['tag_item_id'])
            {
                alert("Please select an item type before moving ahead.");
                $scope.search.usergroup = null;
                return;
            }
            else if(!$scope.tagDetails['tag_process_flow_id'])
            {
                alert("Please select a process flow before moving ahead.");
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
                    $scope.items = [];
                    $scope.processes = [];
                    var itemLoading = rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0, 'project_id': projectId}).success(function(data) {
                        if(data.items.length) {
                            $scope.items.push(data.items);
                            $scope.search.item = [];
                            if(data.items.length==1) {
                                $scope.search.item[0] = data.items[0];
                                $scope.tagDetails.tag_item_id = data.items[0].id;
                                return $scope.updateSelectBox('items', projectId, level+1, $scope.search.item[0].id);
                            }
                            else
                                return true;
                        }
                    });
                    var processLoading = rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0, 'project_id': projectId}).success(function(data) {
                        if(data.items.length)
                            $scope.processes.push(data.items);
                            $scope.search.process = [];
                            if(data.items.length==1) {
                                $scope.search.process[0] = data.items[0];
                                $scope.tagDetails.tag_process_flow_id = data.items[0].id;
                                return $scope.updateSelectBox('processes', projectId, level+1, $scope.search.process[0].id);
                            }
                            else
                                return true;
                    });
                    
                    if(itemLoading && processLoading)
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
            else if(variable=='items') {
                if(level > 0)
                    $scope.tagDetails['tag_item_id'] = parent;
                
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("items/getall", ['id', 'item_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0) {
                        $scope.items.push(data.items);
                        if(data.items.length==1) {
                            $scope.search.item[level] = data.items[0];
                            $scope.updateSelectBox('items', projectId, level, $scope.search.item[level].id);
                        }
                    }
                });
                
                if($scope.search['process'] && $scope.search.process[0] && level==1) {
                    $scope['processes'].splice(0, ($scope['processes'].length-1));

                    rest.setData("items/getrelatedprocess/"+parent, ['id', 'process_name'], {'process_parent_id': $scope.search.process[0], 'tag_item_id': $scope.search.item[1]}).success(function(data) {
                        if(data.items.length>0) {
                            $scope['processes'].push(data.items);
                            
                            if(data.items.length==1) {
                                $scope.search.process[level] = data.items[0];
                                $scope.updateSelectBox('processes', projectId, level, $scope.search.process[level].id);
                            }
                        }
                    });
                }
            }
            else if(variable=='processes') {
                
                if(level==1)
                    $scope.tagDetails['tag_process_flow_id'] = parent;
                
                if($scope.search['item'] && $scope.search.item[1]) {
                    $scope[variable].splice(level, ($scope[variable].length-level-1));
                    
                    if(level < 1) {   
                        rest.setData("items/getrelatedprocess/"+$scope.search.item[1].id, ['id', 'process_name'], {'process_parent_id': parent}).success(function(data) {
                            if(data.items.length>0) {
                                $scope[variable].push(data.items);
                                if(data.items.length==1) {
                                    $scope.search.process[$scope[variable].length] = data.items[0];
                                    $scope.updateSelectBox('processes', projectId, level+1, $scope.search.process[level].id);
                                }
                            }
                        });
                    }
                    else if(level==1) {
                        rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': parent}).success(function(data) {
                            $scope.process_stages = data.items;
                        });
                    }
                }
            }
        }

        $scope.saveTagDetails = function() {
            
            if($scope.$selectedTags.length == 0){
                alertService.clearAll();
                alert("Please select related tags.");
                alertService.add('error', 'Please select related tags.');
                return;
            }
            
            $scope.working = true;
            $scope.tagDetails.tag_assignment = [];
            $scope.tagDetails.tagAssignment = undefined;
            
            angular.forEach($scope.levels, function(level) {
                
                angular.forEach(level.relateUsers, function(value) {
                    if(value['isSelected']) {
                        $scope.tagDetails.tag_assignment.push({'user_id': value['user_id'], 'process_stage_from': $scope.search.stage_from[value['user_id']], 'process_stage_to': $scope.search.stage_to[value['user_id']], 'mandatory': $scope.search.mandatory[value['user_id']], 'notification_status': $scope.search.notification_status[value['user_id']], 'notification_frequency': $scope.search.notification_frequency[value['user_id']]});
                    }
                });
            });
            
            $http.post("/tags/create-master-tags",{'tagDetails': $scope.tagDetails,'relatedTags':$scope.selectedTags}).success(function(data) {
                alertService.clearAll();
                if(data=='Success') {
                    alertService.add('success', "Master Tag Created Successfully.");
                    $location.path('/tags').replace();
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
            $scope.search.project = data.items[0];
            $scope.updateSelectBox('projectlevels', $scope.search.project.id, 0, 0)
        });
        
        $scope.$watch('process_stages', function (newVal) {
            $scope.noti_status = [
                {id: 'all', name:'All process'},
                {id: 'assigned', name:'Assigned process'},
            ];
            angular.forEach($scope.process_stages, function(v) {
                $scope.noti_status.push({'id': v['id'], 'name': v['process_name']});
            });
        }, true);
    }])

app.controller('TagsUpdateMaster', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'tooltip', '$modal', '$log', 'alertService',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, tooltip, $modal, $log, alertService) {
        
        rest.path = "tags";
        
         rest.path = "tags";
        
        $scope.tooltip = tooltip;
        
        alertService.clearAll();
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Update Master Tag");
        breadcrumbsService.headTitle("Update Tag - <span class='mT'>mT</span>");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tags/updatemaster/"+$routeParams.id+"/"+$routeParams.project_id, "Update Master Tag");
        
        alertService.clearAll();
        
        $scope.search = {};
        $scope.projectlevels = [];
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
        $scope.itemList = [];
        $scope.items = [];
        $scope.processes = [];
        $scope.tagDetails = {};
        $scope.tagDetails.tagAssignment = {};
        $scope.levels = [];
        $scope.process_stages = [];
        $scope.working = false;
        $scope.tagError = [];
        $scope.noti_frequency = [
            {id: 'onupdate', name:'On update'},
            {id: 'daily', name:'Daily'},
            {id: 'weekly', name:'Weekly'},
            {id: 'monthly', name:'Monthly'},
            {id: 'yearly', name:'Yearly'},
        ];
        
        $scope.noti_status = [];
        $scope.$delItems  = []; 
                            
        $scope.deleteTags = function () {

            $scope.$delItems = $scope.$delItems.filter(function (e) {
                return e
            });

            for (var i = 0; i < $scope.tagDetails.relatedTags.length; i++) {
                var obj = $scope.tagDetails.relatedTags[i];

                if ($scope.$delItems.indexOf(obj.id) !== -1) {
                    $scope.tagDetails.relatedTags.splice(i, 1);
                    i--;
                }
            }
            $scope.$delItems = [];
            $scope.tagDetails.relatedTags = $scope.tagDetails.relatedTags.filter(function (e) {
                return e
            });
        }                    
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
        
        $scope.loadTagById = function(selectedItem) {
            $scope.tagLoading = true;
            $http.get("/tags/"+selectedItem+"?expand=projectLevelObj,itemObj,processObj,userGroup,tagAssignmentObj,relatedTags").success(function(data) {
                
                $scope.search.project = {'id': $routeParams.project_id, 'project_name': data.project_name};
                
                $scope.tagDetails.tag_name = data.tag_name;
                $scope.tagDetails.pre = data.pre;
                $scope.tagDetails.post = data.post;
                $scope.tagDetails.tag_description = data.tag_description;
                $scope.tagDetails.product_code = data.product_code;
                $scope.tagDetails.relatedTags = data.relatedTags;
                
                $scope.tagDetails.tag_item_id = data.tag_item_id;
                $scope.search.item = data.itemObj;
                $scope.items.splice(1, ($scope.items.length-1));
                angular.forEach(data.itemObj, function(v, k){
                    if(k!=0)
                        $scope.items.push([v]);
                });

                $scope.tagDetails.tag_process_flow_id = data.tag_process_flow_id;
                $scope.search.process = data.processObj;
                $scope.processes.splice(1, ($scope.processes.length-1));
                angular.forEach(data.processObj, function(v, k){
                    if(k!=0)
                        $scope.processes.push([v]);
                });

                rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': $scope.tagDetails.tag_process_flow_id}).success(function(data) {
                        $scope.process_stages = data.items;
                });

                $scope.tagDetails.project_level_id = data.project_level_id;
                $scope.search.childlevels = data.projectLevelObj;
                $scope.projectlevels.splice(1, ($scope.projectlevels.length-1));
                angular.forEach(data.projectLevelObj, function(v, k){
                    $scope.projectlevels.push([v]);
                });

                $scope.tagDetails.user_group_id = data.user_group_id;
                $scope.search.usergroup = data.userGroup;
                $scope.getUserLevel();
                $scope.tagDetails.tagAssignment = data.tagAssignmentObj;
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
            else if(!$scope.tagDetails['tag_item_id'])
            {
                alert("Please select an item type before moving ahead.");
                $scope.search.usergroup = null;
                return;
            }
            else if(!$scope.tagDetails['tag_process_flow_id'])
            {
                alert("Please select a process flow before moving ahead.");
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
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });

                if(level==0) {
                    $scope.items = [];
                    $scope.processes = [];
                    rest.setData("usergroups/getall", [], {'project_id': projectId}).success(function(data) {
                        if(data.items.length)
                            $scope.usergroups = data.items;
                    });
                    rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0, 'project_id': projectId}).success(function(data) {
                        if(data.items.length)
                            $scope.items.push(data.items);
                    });
                    rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0, 'project_id': projectId}).success(function(data) {
                        if(data.items.length)
                            $scope.processes.push(data.items);
                        
                        $scope.loadTagById($routeParams.id);
                    });
                }
                else
                {
                    $scope.tagDetails['project_level_id'] = parent;
                }
            }
            else if(variable=='items') {
                if(level > 0)
                    $scope.tagDetails['tag_item_id'] = parent;
                
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("items/getall", ['id', 'item_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });
                
                if($scope.search['process'] && $scope.search.process[0] && level==1) {
                    $scope['processes'].splice(0, ($scope['processes'].length-1));

                    rest.setData("items/getrelatedprocess/"+parent, ['id', 'process_name'], {'process_parent_id': $scope.search.process[0], 'tag_item_id': $scope.search.item[1]}).success(function(data) {
                        if(data.items.length>0)
                            $scope['processes'].push(data.items);
                    });
                }
            }
            else if(variable=='processes') {
                
                if(level==1)
                    $scope.tagDetails['tag_process_flow_id'] = parent;
                
                if($scope.search['item'] && $scope.search.item[1]) {
                    $scope[variable].splice(level, ($scope[variable].length-level-1));
                    
                    if(level < 1) {   
                        rest.setData("items/getrelatedprocess/"+$scope.search.item[1].id, ['id', 'process_name'], {'process_parent_id': parent}).success(function(data) {
                            if(data.items.length>0)
                                $scope[variable].push(data.items);
                        });
                    }
                    else if(level==1) {
                        rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': parent}).success(function(data) {
                            $scope.process_stages = data.items;
                        });
                    }
                }
            }
        }
        
        $scope.openModel = function () {
        
            var modalInstance = $modal.open({
              templateUrl: '/templates/tags/add_more_tag.html',
              controller: 'SelectTagsPopup',
              size: 'lg',
               resolve: {
                  items: function () {
                    return $scope.tagDetails.relatedTags;
                   }
               }
            });

          modalInstance.result.then(function (selectedItem) {
                if (selectedItem != 'cancel') {
                    selectedItem.filter(function (e) {
                        $scope.tagDetails.relatedTags.push(e);
                    });
                }
          }, function () {
          $log.info('Modal dismissed at: ' + new Date());
          });
        };
        
        $scope.updateSelectBox("projectlevels", $routeParams.project_id, 0, 0);
        
        
        $scope.saveTagDetails = function() {
            
            if($scope.tagDetails.relatedTags.length == 0){   
                alertService.clearAll();
                alert("Please select related tags.");
                alertService.add('error', 'Please select related tags.');
                return;
            }
            
            $scope.working = true;
            $scope.tagDetails.tag_assignment = [];
            $scope.tagDetails.tagAssignment = null;
            
            angular.forEach($scope.levels, function(level) {
                angular.forEach(level.relateUsers, function(value) {
                    if(value['isSelected']) {
                        $scope.tagDetails.tag_assignment.push({'user_id': value['user_id'], 'process_stage_from': $scope.search.stage_from[value['user_id']], 'process_stage_to': $scope.search.stage_to[value['user_id']], 'mandatory': $scope.search.mandatory[value['user_id']], 'notification_status': $scope.search.notification_status[value['user_id']], 'notification_frequency': $scope.search.notification_frequency[value['user_id']]});
                    }
                });
            });
            
            $http.post("/tags/updatemastertags/"+$routeParams.id,{'tagDetails': $scope.tagDetails}).success(function(data) {
                alertService.clearAll();
                if(data=='Success') {
                    alertService.add('success', "Master Tag Updated Successfully.");
                    $location.path('/tags').replace();
                }
                $scope.working = false;
            }).error(function(data) {
                alertService.clearAll();
                alert("Error in tag creation");
                alertService.add('error', data);
                $scope.tagError.name = data['tag_name'][0];
                $scope.working = false;
            });
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        $scope.$watch('process_stages', function (newVal) {
            $scope.noti_status = [
                {id: 'all', name:'All process'},
                {id: 'assigned', name:'Assigned process'},
            ];
            angular.forEach($scope.process_stages, function(v) {
                $scope.noti_status.push({'id': v['id'], 'name': v['process_name']});
            });
        }, true);
        
    }])

app.controller('TagsView', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        
        rest.path = "tag-activity-log";
        
        alertService.clearAll();
        breadcrumbsService.clearAll();
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tags", "Tag Analytics");
        
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
		
        $http.post("/tag-activity-log/search?expand=attachments,user",{'search': {'tag_id':$routeParams.id}}).success(function(data) {
            $scope.activity = data.items;
        });
        
        $http.get("/tags/"+$routeParams.id+"?expand=projectLevelObj,userGroup,processObj,itemObj,tagAssignmentObj,relatedTags").success(function(data) {
            $scope.tagAnalytic = data;
            breadcrumbsService.setTitle("Tag Analytics: "+data.tag_name);
            breadcrumbsService.headTitle("Tag Analytics - <span class='"+data.type+"'>"+data.type+"</span>");
        });
    }])

app.controller('TagItems', function($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, page_dropdown, $modal, $log) {
    
    rest.path = "items";
    
    alertService.clearAll();
    breadcrumbsService.clearAll();
    breadcrumbsService.setTitle("Manage Items");
    breadcrumbsService.add("/#/", "Home");
    breadcrumbsService.add("/#/tagitems", "Manage tag Items");
    
    $scope.list = null;
    $scope.selectedItem = {};
    $scope.page_dropdown = page_dropdown;
    $scope.$search = {};

    $scope.options = {
    };

    $scope.updateItem = function(scope) {
        scope.editing = true;
    }
    
    $scope.saveItem = function(scope, title) {
        scope.item_name = title;
        if(scope.id)
            $http.put("/items/"+scope.id, {'item_name': scope.item_name}).success(function(data) {
                console.log("Item Updated-"+data.id);
            });
        else {
            $http.post("/items", {'item_name': scope.item_name, 'parent_id': scope.parent_id}).success(function(data) {
                scope.id = data.id;
                console.log("Group Added-"+data.id);
            });
        }
        scope.editing=false
    }
    
    $scope.linkProcess = function (flow) {
        var modalInstance = $modal.open({
          templateUrl: '/templates/tagitems/relatedProcessFlowModal.html',
          controller: 'RelatedProcessFlow',
          size: 'lg',
           resolve: {
              itemScope: function () {
                return flow;
               }
           }
        });

        modalInstance.result.then(function (selectedItem) {
            $scope.searchUser(scope);
        }, function () {
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
    
    $scope.removeItem = function(item) {
        var scope = item.$modelValue;
        $http.delete("/items/"+scope.id).success(function(data) {
            console.log("Removed -"+scope.id);
        });
        item.remove();
    }
    
    $scope.cancelEditing = function(item) {
        var scope = item.$modelValue;
        scope.tempTitle=scope.group_name;
        scope.editing=false;
        if(scope.id==null)
            item.remove();
    }
    
    $scope.newSubItem = function(scope, type) {
        if(type=='top')
        {
            scope.push({
              id: null,
              item_name: "Item Group " + (scope.length + 1),
              items: [],
              editing: true,
              parent_id: 0
            });
        }
        else {
            
            if(typeof scope.items === 'undefined')
                scope.items = [];
            
            scope.items.push({
                id: null,
                item_name: 'Item ' + (scope.items.length + 1),
                parent_id: scope.id,
                items: [],
                editing: true,
            });
        }
    };
    
    $scope.searchUser = function(scope) {
        scope.loading = true;
        var params = {'search': scope.search, 'sort': scope.users_sortBy, 'page':scope.users_currentPage, 'limit': scope.users_numPerPage};
        rest.customModelData("users/levelusers/"+scope.id, params).success(function (data) {
            scope['users'] = data.items;
            scope.users_totalCount = data._meta.totalCount;
            scope.users_pageCount = data._meta.pageCount;
            scope.users_currentPage = (data._meta.currentPage);
            scope.users_numPerPage = data._meta.perPage;
            scope.loading = false;
        }).error(function() {
            errorCallback();
            scope.loading = false;
        });
    }
    
    $scope.loadItems = function(scope) {
        
        rest.path = "items";
        
        if(!scope['items']) {
            var parent_id = parseInt(scope.id);
            scope.loading = true;

            var params = {'search': {'parent_id': parent_id}, 'page':$scope.currentPage, 'limit': $scope.numPerPage};
            rest.models(params).success(function (data) {
                scope.items = data.items;
                scope.totalCount = data._meta.totalCount;
                scope.pageCount = data._meta.pageCount;
                scope.currentPage = (data._meta.currentPage);
                scope.numPerPage = data._meta.perPage;
                scope.loading = false;
                scope.collapsed = true;
            }).error(function() {
                errorCallback();
                scope.loading = false;
            });
        }
        else
            scope.collapsed = !scope.collapsed;
    }
    
    $scope.showProjectsModal = function (scope) {
        if(scope.user_group_id)
            return 0;
        var modalInstance = $modal.open({
          templateUrl: '/templates/user-group/showProjectsModal.html',
          controller: 'TagItemProjectsPopup',
          size: 'lg',
           resolve: {
              itemScope: function () {
                return scope;
               }
           }
        });

        modalInstance.result.then(function (selectedItem) {
            $scope.searchUser(scope);
        }, function () {
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
    
    var errorCallback = function (data) {
        alertService.clearAll();
        alertService.add('error', "Error in processing your request. Please try again.");
    };
    
    $scope.$search['parent_id']= 0;
    
    $scope.loadItemGroup = function() {
        var params = {'search': $scope.$search, 'sort': $scope.sortBy, 'page':$scope.currentPage, 'limit': $scope.numPerPage};
        rest.models(params).success(function (data) {
            $scope.list = data.items;
            $scope.totalCount = data._meta.totalCount;
            $scope.pageCount = data._meta.pageCount;
            $scope.currentPage = (data._meta.currentPage);
            $scope.numPerPage = data._meta.perPage;
        }).error(errorCallback);
    }
    
    $scope.loadItemGroup();
});

app.controller('createSimilarTagModalController', function ($scope, $modalInstance, $http, itemScope) {

    $scope.search = {};
    $scope.popupTags = {};
    $scope.temp = [];
    $scope.popupTags.selectedTagId = 0;
    $scope.search.project_id = itemScope.project_id;
    
    $scope.searchTags = function(){
        $http.post("/tags/search",{search:$scope.search}).success(function(data) {
            $scope.popupTags = data.items;
            $scope.totalCount = data._meta.totalCount;
            $scope.pageCount = data._meta.pageCount;
            $scope.currentPage = (data._meta.currentPage);
            $scope.numPerPage = data._meta.perPage;
        });
    }
     
    $scope.clearSearch = function() {
        $scope.search = {};
        $scope.searchTags();
    }

    $scope.$watch("popupTags", function (newVal) {
        $scope.temp = [];
        angular.forEach(newVal, function (v) {
            if (v['isSelected']) {
                $scope.temp.push(v);
            }
        });
    }, true);

    $scope.ok = function () {
        if(!$scope.popupTags.selectedTagId)
        {
            alert("No tag selected.");
            return false;
        }
        $modalInstance.close($scope.popupTags.selectedTagId);
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
    
    $scope.searchTags();
    
});

app.controller('SelectTagsPopup', function ($scope, $modalInstance, $http, items) {
         
    $scope.search = {};
    $scope.popupTags = {};
    $scope.temp = [];
    
    $scope.searchTags = function(){
        $http.post("/tags/search",{search:$scope.search, 'excludeTags': items}).success(function(data) {
            $scope.popupTags = data.items;
        });
     }
     
    $scope.clearSearch = function() {
        $scope.search = {};
        $scope.searchTags();
    }
     
    $scope.$watch("popupTags", function (newVal) {
      var hasTrue=false, hasFalse=false;
      $scope.temp = [];
      angular.forEach(newVal, function (v) {
        if (v['isSelected']) {
            $scope.temp.push(v);
        }
      });
    }, true);
     
    $scope.ok = function () {
        $modalInstance.close($scope.temp);
    };
    
    $scope.close = function () {
        $modalInstance.close('cancel');
    };


   $scope.searchTags();
                    
})

.controller('TagItemProjectsPopup', function ($scope, $modalInstance, rest, $http, itemScope, page_dropdown) {
    
    rest.path = "projects";
    $scope.search = {};
    $scope.selectedProjects = [];
    $scope.allProjects = [];
    $scope.temp = {};
    $scope.page_dropdown = page_dropdown;
    
    $scope.selectAllProjects = function(data, allSelected) {
        angular.forEach(data, function(v) {
            v['isSelected'] = allSelected;
            if (allSelected) {
                $scope.temp[""+v.id] = v.id;
            }
            else
                $scope.temp[""+v.id] = undefined;
        });
    }
    
    $scope.listProjects = function() {
        $scope.list_loading = true;
        var params = {'page':$scope.list_currentPage, 'limit': $scope.list_numPerPage};
        rest.customModelData("projects/tagitems/"+itemScope.id, params).success(function(data) {
            $scope.selectedProjects = data.items;
            $scope.list_totalCount = data._meta.totalCount;
            $scope.list_pageCount = data._meta.pageCount;
            $scope.list_currentPage = (data._meta.currentPage);
            $scope.list_numPerPage = data._meta.perPage;
            $scope.list_loading = false;
            $scope.getAllProjects();
        }).error(function() {
            $scope.list_loading = false;
        });
    }
    
    $scope.getAllProjects = function(){
        var params = {'search': $scope.search, 'excludeProjects': $scope.selectedProjects, 'page':$scope.currentPage, 'limit': $scope.numPerPage};
        rest.models(params).success(function(data) {
            $scope.allProjects = data.items;
            $scope.totalCount = data._meta.totalCount;
            $scope.pageCount = data._meta.pageCount;
            $scope.currentPage = (data._meta.currentPage);
            $scope.numPerPage = data._meta.perPage;
            
            angular.forEach($scope.temp, function(value) {
                for(var j=0; j < Object.keys($scope.allProjects).length; j++){
                    if($scope.allProjects[j].id == value) {
                        $scope.allProjects[j]['isSelected'] = true;
                        break;
                    }
                }
            });            
        });
    }
    
    $scope.clearSearch = function() {
        $scope.search = {};
        $scope.getAllProjects();
    }
    
    $scope.selectProject = function(scope) {
        if (scope['isSelected']) {
            $scope.temp[""+scope.id] = scope.id;
        }
        else
            $scope.temp[""+scope.id] = undefined;
    }
    
    $scope.assignProjects = function () {
        $http.post('/items/assignprojects/'+itemScope.id, {'Projects':$scope.temp}).success(function(data) {
            $scope.listProjects();
            $scope.temp = {};
        }).error(function(data) {
            console.log(data);
            alert("Error in assigning projects. Please try again later.");
        });
    };
    
    $scope.unassignProjects = function(project, index) {
        $http.post('/items/unassignprojects/'+itemScope.id, {'Projects':[project]}).success(function(data) {
            if(data>0)
                $scope.selectedProjects.splice(index, 1);
        }).error(function(data) {
            console.log(data);
            alert("Invalid Request");
        });
    };
    
    $scope.close = function () {
        $modalInstance.dismiss('cancel');
    };

    $scope.listProjects();                
})

.controller('RelatedProcessFlow', function ($scope, $modalInstance, rest, $http, itemScope, page_dropdown) {
    
    $scope.itemScope = itemScope;
    $scope.serverError = "";
    
    
    rest.setData("tagprocess/getall", ['id','type', 'process_name'], {'parent_id': 0}).success(function(data) {
        $scope.process_type = data.items;
    });
    
    var getRelatedProcess = function() {
        $http.post("items/getrelatedprocess/"+$scope.itemScope.id, {'expand': 'parentProcess'}).success(function(data) {
            $scope.processes = data.items;
        });
    }
    
    $scope.getProcessFlow = function(){
        if($scope.item_type_id) {
            rest.setData("tagprocess/getall", ['id','type', 'process_name'], {'parent_id': $scope.item_type_id}).success(function(data) {
                $scope.process_flow = data.items;
            });
        }
    }
    
    $scope.assignItems = function(){
        
        $scope.process.item_type_id = $scope.itemScope.id;
        $scope.serverError = "";
        
        $http.post('items/assignprocess',{process:$scope.process}).success(function(data){
                $scope.process = null;
                getRelatedProcess();
        }).error(function(data){
            $scope.serverError = "This process flow is already assigned.";
        });
    }
    
    $scope.unassignProcess = function(item_type_id,process_flow_id){
        $http.post('items/unassignprocess',{item_type_id:item_type_id,process_flow_id:process_flow_id}).success(function(data){
            getRelatedProcess();
            $scope.serverError = null;
        }).error(function(data){
            //$scope.serverError2 = "Unable to delete process flow.";
        });
    }
    
    $scope.close = function () {
        $modalInstance.dismiss('cancel');
    };
    
    
    getRelatedProcess();
    
    
})