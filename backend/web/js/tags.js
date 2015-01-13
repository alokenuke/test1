app.controller('TagIndex', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        $scope.tagsNum = 1;
        rest.path = "tags";
        
        breadcrumbsService.setTitle("Manage Tags");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
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
        
        $scope.delete = function (id) {
            rest.deleteById({id: id}).success(function () {
                $location.path('/post');
                $route.reload();
            }).error(errorCallback);
        }
        
        $scope.pageChanged = function() {
            $location.search("page", $scope.currentPage);
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
                    $http.post("usergroups/getall", {'search': {'project_id': projectId}, 'select': {'UserGroups': ['id', 'group_name']}}).success(function(data) {$scope.usergroups = data.items;});
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
            var params = {'search': $scope.search, 'sort': $scope.sortBy};
            rest.models(params).success(function (data) {
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
        rest.setData("usergroups/getall", {'UserGroups': ['id', 'group_name']}, {}).success(function(data) {$scope.usergroups = data.items;});
        
    }])

app.controller('TagsCreate', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'tooltip', '$modal', '$log',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, tooltip, $modal, $log) {
        
        rest.path = "tags";
        
        $scope.tooltip = tooltip;
        
        breadcrumbsService.setTitle("Create Simple Tag");        
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tags", "Create Tag");
        
        $scope.search = [];
        $scope.projectlevels = [];
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
        $scope.itemList = [];
        $scope.items = [];
        $scope.processes = [];
        $scope.tags = [{id:'',pre:'',tagName:'',post:'',productCode:'',tagDescription:''}];
        $scope.levels = [];
        $scope.process_stages = [];
        
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
               
        $scope.createSimilarTagModal = function() {
            
            var params = {'search': $scope.search, 'sort': $scope.sortBy};
            var temp = {};
            
            var modalInstance = $modal.open({
              templateUrl: '/templates/tags/add_more_tag.html',
              controller: 'createSimilarTagModalController',
              size: 'lg'
            });

            modalInstance.result.then(function (selectedItem) {
              $scope.selected = selectedItem;
            }, function () {
              $log.info('Modal dismissed at: ' + new Date());
            });
        }
        
        $scope.getUserLevel = function(){
            $http.post("/userlevels/getall",{group_id:$scope.search.usergroup.id}).success(function(data) {
                $scope.levels = data.items;
            });
        }
        
        $scope.copyTags =  function(tagsNum){
            var temp = [];
            temp.pre = $scope.tags[$scope.tags.length-1].pre;
            temp.tagName = $scope.tags[$scope.tags.length-1].tagName;
            temp.post = $scope.tags[$scope.tags.length-1].post;
            
            for(var i=0;i<tagsNum;i++) {
                $scope.tags.push({id:'',pre:parseInt(temp.pre)+i+1,tagName:temp.tagName,post:parseInt(temp.post)+i+1,productCode:'',tagDescription:''});
             }
        }
        
        $scope.updateSelectBox = function(variable, projectId, level, parent) {
            
            $scope.search.project_id = projectId;
            
            if(variable=="projectlevels") {
                
                $scope[variable].splice(level, ($scope[variable].length-level));
                
                $http.post("/projectlevel/getall", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });

                if(level==0) {
                    $scope.items = [];
                    $scope.processes = [];
                    rest.setData("usergroups/getall", {}, {}).success(function(data) {$scope.usergroups = data.items;});
                    rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0}).success(function(data) {$scope.items.push(data.items);});
                    rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0}).success(function(data) {$scope.processes.push(data.items);});
                }
            }
            else if(variable=='items') {
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("items/getall", ['id', 'item_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });
            }
            else if(variable=='processes') {
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0 && level <1)
                        $scope[variable].push(data.items);
                    else if(level==1) {
                        $scope.process_stages = data.items;
                    }
                });
            }
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        rest.setData("projects/getall", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
        
    }])

app.controller('TagsCreateMaster', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'tooltip','$modal','$log',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, tooltip,$modal,$log) {
        
        rest.path = "tags";
        
        $scope.tooltip = tooltip;
        
        breadcrumbsService.setTitle("Create Simple Tag");        
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tags", "Create Tag");
        
        $scope.search = [];
        $scope.projectlevels = [];
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
        $scope.itemList = [];
        $scope.items = [];
        $scope.processes = [];
        $scope.tags = [{id:'',pre:'',tagName:'',post:'',productCode:'',tagDescription:''}];
        $scope.levels = [];
        $scope.process_stages = [];
        $scope.selectedTags = [];
        $scope.$delItems  = [];  
        
        $scope.deleteTags = function() {
            
               $scope.$delItems = $scope.$delItems.filter(function(e){return e});
               
               console.log($scope.$delItems);
               for(var i = 0; i < $scope.selectedTags.length; i++) {
                    var obj = $scope.selectedTags[i];

                    if($scope.$delItems.indexOf(obj.id) !== -1) {
                        $scope.selectedTags.splice(i, 1);
                        i--;
                    }
                }
                $scope.$delItems  = [];
                $scope.selectedTags = $scope.selectedTags.filter(function(e){return e});
                console.log($scope.selectedTags);

        }
        
        
        $scope.openModel = function () {
        
            var modalInstance = $modal.open({
              templateUrl: '/templates/tags/add_more_tag.html',
              controller: 'SelectTagsPopup',
              size: 'lg',
               resolve: {
                  items: function () {
                    return $scope.tags;
                   }
               }
            });

          modalInstance.result.then(function (selectedItem) {
            $scope.selectedTags = selectedItem.filter(function(e){return e});
          }, function () {
          $log.info('Modal dismissed at: ' + new Date());
          });
        };
        
        $scope.getUserLevel = function(){
            $http.post("/userlevels/getall",{group_id:$scope.search.usergroup.id}).success(function(data) {
                $scope.levels = data.items;
            });
        }
        
        $scope.copyTags =  function(tagsNum){
            var temp = [];
            temp.pre = $scope.tags[$scope.tags.length-1].pre;
            temp.tagName = $scope.tags[$scope.tags.length-1].tagName;
            temp.post = $scope.tags[$scope.tags.length-1].post;
            
            for(var i=0;i<tagsNum;i++) {
                $scope.tags.push({id:'',pre:parseInt(temp.pre)+i+1,tagName:temp.tagName,post:parseInt(temp.post)+i+1,productCode:'',tagDescription:''});
             }
        }
        
        $scope.updateSelectBox = function(variable, projectId, level, parent) {
            
            $scope.search.project_id = projectId;
            
            if(variable=="projectlevels") {
                
                $scope[variable].splice(level, ($scope[variable].length-level));
                
                $http.post("/projectlevel/getall", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });

                if(level==0) {
                    $scope.items = [];
                    $scope.processes = [];
                    rest.setData("usergroups/getall", {'UserGroups': ['user_group_id', 'group_name']}, {}).success(function(data) {$scope.usergroups = data.items;});
                    rest.setData("items/getall", ['id', 'item_name'], {'parent_id': 0}).success(function(data) {$scope.items.push(data.items);});
                    rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': 0}).success(function(data) {$scope.processes.push(data.items);});
                }
            }
            else if(variable=='items') {
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("items/getall", ['id', 'item_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });
            }
            else if(variable=='processes') {
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("tagprocess/getall", ['id', 'process_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0 && level <1)
                        $scope[variable].push(data.items);
                    else if(level==1) {
                        $scope.process_stages = data.items;
                    }
                });
            }
        }
        
        var updateTagList = function() {
            var params = {'search': $scope.search, 'sort': $scope.sortBy};
            rest.models(params).success(function (data) {
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
        
        updateTagList([]);
        
        rest.setData("projects/getall", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
    }])

app.controller('TagsView', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        
        rest.path = "tags";
        
        breadcrumbsService.setTitle("Tag Analytics: Construction work Tag");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tags", "Tag Analytics");
        
        $scope.search = [];
        $scope.projectlevels = [];
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
        
        $scope.updateLevel = function(projectId, level, parent) {
            $scope.search.project_id = projectId;
            
            $scope.projectlevels.splice(level, ($scope.projectlevels.length-level));
            
            $http.post("/projectlevel/getall", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                if(data.items.length>0)
                    $scope.projectlevels.push(data.items);
            });
            
            if(level==0)
                $http.post("usergroups/getall", {'search': {'project_id': projectId}, 'select': {'UserGroups': ['id', 'group_name']}}).success(function(data) {
                    $scope.usergroups = data.items;
                });
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
                
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
        
        $scope.toggleDropdown = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.status.isopen = !$scope.status.isopen;
        };
                
        rest.models().success(function (data) {
            $scope.data = data.items;
                        
            $scope.totalCount = data._meta.totalCount;
            $scope.pageCount = data._meta.pageCount;
            $scope.currentPage = (data._meta.currentPage);
            $scope.numPerPage = data._meta.perPage;
            
            $http.get("/projects/getall").success(function(data) {
                $scope.projects = data.items;
            });
            
            $http.get("/usergroups/getall").success(function(data) {
                $scope.usergroups = data.items;
            });
            
        }).error(errorCallback);
    }])

app.controller('ProcessFlow', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        rest.path = "TagProcessType";
        
        var ctrl = this;
        
        breadcrumbsService.setTitle("Manage Tag Process");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tag-process-flow", "Tag Process");
        
        $scope.list = [{
            "id": 1,
            "type": "process_type",
            "title": "Construction Item Process",
            "items": []
          }, {
            "id": 2,
            "type": "process_type",
            "title": "2. Assets Tracking",
            "items": [{
              "id": 21,
              "type": "process_flow",
              "title": "2.1. Item Delivered",
            }, {
              "id": 22,
              "type": "process_flow",
              "title": "2.2. Item Installed",
              "items": [{
                    "id": 211,
                    "type": "process_stage",
                    "title": "2.1.2 Item Checked - (Dropdown)",
                  }, {
                    "id": 212,
                    "type": "process_stage",
                    "title": "2.2.2 Item Installed - (Checkbox)",
                  }, {
                    "id": 213,
                    "type": "process_stage",
                    "title": "2.2.2 Item Installed Approved - (Radio)",
                  }],
            }],
          }, {
            "id": 3,
            "type": "process_type",
            "title": "3. Area 3",
            "items": []
          }, {
            "id": 4,
            "type": "process_type",
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

          $scope.updateItem = function(scope) {
              scope.editing = true;
          }

          $scope.addProcessType = function() {
            var nodeData = $scope.list;
            nodeData.push({
              id: null,
              title: "Process Type " + (nodeData.length + 1),
              type: "process_type",
              items: []
            });
          };

          $scope.newSubItem = function(scope, type) {
            var nodeData = scope.$modelValue;
            if(type)
                type = (type=="process_type"?"process_flow":"process_stage");
            else
                type = "process_type";

            if(nodeData.items==undefined)
                nodeData.items = [];

            nodeData.items.push({
              id: nodeData.id * 10 + nodeData.items.length,
              title: nodeData.title + '.' + (nodeData.items.length + 1),
              type: type,
              items: []
            });
          };
                
        rest.models().success(function (data) {
            $scope.data = data.items;
                        
            $scope.totalCount = data._meta.totalCount;
            $scope.pageCount = data._meta.pageCount;
            $scope.currentPage = (data._meta.currentPage);
            $scope.numPerPage = data._meta.perPage;
            
            $http.get("/projects/getall").success(function(data) {
                $scope.projects = data.items;
            });
            
            $http.get("/usergroups/getall").success(function(data) {
                $scope.usergroups = data.items;
            });
            
        });
        
    }]);

app.controller('TagItems', function($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, page_dropdown, $modal, $log) {
    
    rest.path = "items";
        
    breadcrumbsService.setTitle("Manage Items");
    breadcrumbsService.clearAll();
    breadcrumbsService.add("", "Home");
    breadcrumbsService.add("", "Manage");
    breadcrumbsService.add("", "Tag");
    breadcrumbsService.add("/#/tagitems", "Items");
    
    $scope.list = [];
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
    
    $scope.removeItem = function(item) {
        var scope = item.$modelValue;
        if(scope.user_group_id) {
            $http.delete("/userlevels/"+scope.id).success(function(data) {
                console.log("Level removed-"+scope.id);
            });
        }
        else {
            $http.delete("/usergroups/"+scope.id).success(function(data) {
                console.log("Group removed-"+scope.id);
            });
        }
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
            scope.unshift({
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
            
            scope.items.unshift({
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
                scope.collapsed = !scope.collapsed;
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

app.controller('createSimilarTagModalController', function ($scope, $modalInstance, $http) {

  $scope.search = {};
    $scope.popupTags = {};
    $scope.temp = [];
    
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
        $modalInstance.close($scope.temp);
    };

    $http.post("/tags/getall").success(function(data) {
        $scope.popupTags = data.items;
    });

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
});

app.controller('SelectTagsPopup', function ($scope, $modalInstance, $http, items) {
         
    $scope.search = {};
    $scope.popupTags = {};
    $scope.temp = [];
    
    $scope.searchTags = function(){
        $http.post("/tags/getall",{search:$scope.search}).success(function(data) {
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

   $http.post("/tags/getall").success(function(data) {
       $scope.popupTags = data.items;
   });
                    
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