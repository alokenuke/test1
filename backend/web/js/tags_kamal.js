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
        
        $scope.search = [];
        $scope.sort = [];
        $scope.sortBy = "";
        $scope.projectlevels = [];
        $scope.projects = [];
        $scope.usergroups = [];
        $scope.items = [];
        $scope.processes = [];
        $scope.itemList = [];
        
        if($location.$$search.sort!=undefined) {
            $scope.predicate = $location.$$search.sort;
            $scope.reverse = ($scope.predicate.search("-")==-1?false:true);
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
        
        $scope.setSearchFields = function(obj, value) {
            $scope[obj] = value;
        }
        
        $scope.updateSelectBox = function(variable, projectId, level, parent) {
            
            $scope.search.project_id = projectId;
            
            if(variable=="projectlevels") {
                
                $scope[variable].splice(level, ($scope[variable].length-level));
                
                $http.post("/api/getall?mod=projectLevel", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });

                if(level==0)
                    $http.post("/api/getall?mod=userGroups", {'search': {'project_id': projectId}, 'select': ['user_groups.id', 'group_name']}).success(function(data) {
                        $scope.usergroups = data.items;
                    });
            }
            else if(variable=='items') {
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("items", ['id', 'item_name'], {'parent_id': parent, 'project_id': projectId}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });
            }
            else if(variable=='processes') {
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("tagProcess", ['id', 'process_name'], {'parent_id': parent, 'project_id': projectId}).success(function(data) {
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
                $scope.currentPage = (data._meta.currentPage+1);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        updateTagList([]);
        
        rest.setData("projects", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
        rest.setData("items", ['id', 'item_name'], {'parent_id': 0}).success(function(data) {$scope.items.push(data.items);});
        rest.setData("tagProcess", ['id', 'process_name'], {'parent_id': 0}).success(function(data) {$scope.processes.push(data.items);});
        rest.setData("userGroups", ['user_groups.id', 'group_name'], {}).success(function(data) {$scope.usergroups = data.items;});
        
    }])

app.controller('TagsPopUp', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'tooltip', '$modal', '$log',
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, tooltip, $modal, $log) {
                    
                    $http.post("/api/getall?mod=tags").success(function(data) {
                        $scope.tags = data.items;
                    });
                    
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
        
        
        
        $scope.createSimilarTagModal = function() {
            
            var params = {'search': $scope.search, 'sort': $scope.sortBy};
            var temp = {};

            rest.getModels("tags", [], {'project_id': $scope.projects.id}).success(function(data) {
                  temp.tags = data.items;
                  temp.totalCount = data._meta.totalCount;
                  temp.pageCount = data._meta.pageCount;
                  temp.currentPage = (data._meta.currentPage+1);
                  temp.numPerPage = data._meta.perPage;
                  
                  var modalInstance = $modal.open({
                    templateUrl: 'createSimilarTagModal.html',
                    controller: 'createSimilarTagModalController',
                    size: 'lg',
                    resolve: {
                      data: function () {
                          return temp;
                      }
                    }
                  });

                  modalInstance.result.then(function (selectedItem) {
                    $scope.selected = selectedItem;
                  }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                  });
                  
            });
        }
        
        $scope.getUserLevel = function(){
            $http.post("/api/getall?mod=userLevels",{group_id:$scope.search.usergroup.id}).success(function(data) {
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
                
                $http.post("/api/getall?mod=projectLevel", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });

                if(level==0) {
                    $scope.items = [];
                    $scope.processes = [];
                    rest.setData("userGroups", ['user_groups.id', 'group_name'], {'project_id': projectId}).success(function(data) {$scope.usergroups= data.items;});
                    rest.setData("items", ['id', 'item_name'], {'project_id': projectId, 'parent_id': 0}).success(function(data) {$scope.items = [];if(data.items.length)$scope.items.push(data.items);});
                    rest.setData("tagProcess", ['id', 'process_name'], {'project_id': projectId, 'parent_id': 0}).success(function(data) {$scope.processes = [];if(data.items.length) $scope.processes.push(data.items);});
                }
            }
            else if(variable=='items') {
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("items", ['id', 'item_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });
            }
            else if(variable=='processes') {
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("tagProcess", ['id', 'process_name'], {'parent_id': parent}).success(function(data) {
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
                $scope.currentPage = (data._meta.currentPage+1);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        updateTagList([]);
        
        rest.setData("projects", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
        
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
        
        $scope.openModel = function () {

            var modalInstance = $modal.open({
              templateUrl: '/templates/tags/add_more_tag.html',
              controller: 'TagsPopUp',
              size: 'lg',
               resolve: {
                  items: function () {
                    return $scope.tags;
                   }
               }
            });

          modalInstance.result.then(function (selectedItem) {
          $scope.selected = selectedItem;
          }, function () {
          $log.info('Modal dismissed at: ' + new Date());
          });
        };
        
        $scope.getUserLevel = function(){
            $http.post("/api/getall?mod=userLevels",{group_id:$scope.search.usergroup.id}).success(function(data) {
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
                
                $http.post("/api/getall?mod=projectLevel", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });

                if(level==0) {
                    $scope.items = [];
                    $scope.processes = [];
                    rest.setData("userGroups", ['user_groups.id', 'group_name'], {'project_id': projectId}).success(function(data) {$scope.usergroups= data.items;});
                    rest.setData("items", ['id', 'item_name'], {'project_id': projectId, 'parent_id': 0}).success(function(data) {$scope.items = [];if(data.items.length)$scope.items.push(data.items);});
                    rest.setData("tagProcess", ['id', 'process_name'], {'project_id': projectId, 'parent_id': 0}).success(function(data) {$scope.processes = [];if(data.items.length) $scope.processes.push(data.items);});
                }
            }
            else if(variable=='items') {
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("items", ['id', 'item_name'], {'parent_id': parent}).success(function(data) {
                    if(data.items.length>0)
                        $scope[variable].push(data.items);
                });
            }
            else if(variable=='processes') {
                $scope[variable].splice(level, ($scope[variable].length-level-1));
                
                rest.setData("tagProcess", ['id', 'process_name'], {'parent_id': parent}).success(function(data) {
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
                $scope.currentPage = (data._meta.currentPage+1);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        updateTagList([]);
        
        rest.setData("projects", ['id', 'project_name'], {'project_status': null}).success(function(data) {$scope.projects = data.items;});
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
            
            $http.post("/api/getall?mod=projectLevel", {'search': {'project_id': projectId, 'parent_id': parent}, 'select': ['id', 'level_name']}).success(function(data) {
                if(data.items.length>0)
                    $scope.projectlevels.push(data.items);
            });
            
            if(level==0)
                $http.post("/api/getall?mod=userGroups", {'search': {'project_id': projectId}, 'select': ['user_groups.id', 'group_name']}).success(function(data) {
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
            $scope.currentPage = (data._meta.currentPage+1);
            $scope.numPerPage = data._meta.perPage;
            
            $http.get("/api/getall?mod=projects").success(function(data) {
                $scope.projects = data.items;
            });
            
            $http.get("/api/getall?mod=userGroups").success(function(data) {
                $scope.usergroups = data.items;
            });
            
        });
        
    }]);

app.controller('createSimilarTagModalController', function ($scope, $modalInstance, data) {

  $scope.data = data;
  
  $scope.selectedTags = [];
  
  $scope.selected = {
    item: $scope.data.tags[0]
  };

  $scope.ok = function () {
    $modalInstance.close($scope.selectedTags);
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
});