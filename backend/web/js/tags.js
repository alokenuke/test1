app.controller('TagsCreate', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        rest.path = "tags";
        $scope.tagsNum = 1;
        var ctrl = this;
        
        breadcrumbsService.setTitle("Site Track: Create simple tag");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/tags", "Tags");
        breadcrumbsService.add("/#/tags", "Create New Tag");
        
        ctrl.items = [];
        $scope.search = [];
        $scope.search.project = [];
        $scope.projectlevels = [];
        $scope.search.childlevels = [];
        $scope.search.group = [];
        $scope.itemList = [];
        
        $scope.updateLevel = function(projectId, level) {
            $scope.search.project_id = projectId;
            
            $scope.projectlevels.splice(level, ($scope.projectlevels.length-level));
            
            $http.post("/api/getall?mod=projectLevel", {'search': {'project_id': projectId, 'parent_id': 0}, 'select': ['id', 'level_name']}).success(function(data) {
                if(data.items.length>0)
                    $scope.projectlevels.push(data.items);
            });
            
            $http.post("/api/getall?mod=userGroups", {'search': {'project_id': projectId}, 'select': ['id', 'group_name']}).success(function(data) {
                $scope.usergroups = data.items;
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
        
        $scope.updateItems =  function(parentId, level){
           
           $scope.itemList.splice(level+1, ($scope.itemList.length-level+1));
            
            $http.post("/api/getall?mod=tagItems", {'parent_id': parentId}).success(function(data) {
               if(data.items.length>0)
                    $scope.itemList.push(data.items);
            });
        }
        
        $scope.getUserLevel = function(){
            $http.post("/api/getall?mod=userLevels",{group_id:$scope.search.usergroup.selected.id}).success(function(data) {
                $scope.levels = data.items;
            });
            //console.log($scope.levels);
        }
        
        $scope.tags = [
            {id:'',pre:'',tagName:'',post:'',productCode:'',tagDescription:''}
        ];
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
                
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
            
            $http.get("/api/getall?mod=tagItems", {'search': {'parent_id': 0}}).success(function(data) {
                $scope.itemList.push(data.items);
            });
                
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
        
    }])

app.controller('TagsView', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        rest.path = "tags";
        var ctrl = this;
        
        breadcrumbsService.setTitle("Site Track: Tag View");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/tags", "Manage");
        breadcrumbsService.add("/#/tags", "Tags");
        
        ctrl.items = [];
        $scope.search = [];
        $scope.search.project = [];
        $scope.projectlevels = [];
        $scope.search.childlevels = [];
        $scope.search.group = [];
        
        $scope.updateLevel = function(projectId, level) {
            $scope.search.project_id = projectId;
            
            $scope.projectlevels.splice(level, ($scope.projectlevels.length-level));
            
            $http.post("/api/getall?mod=projectLevel", {'search': {'project_id': projectId, 'parent_id': 0}, 'select': ['id', 'level_name']}).success(function(data) {
                if(data.items.length>0)
                    $scope.projectlevels.push(data.items);
            });
            
            $http.post("/api/getall?mod=userGroups", {'search': {'project_id': projectId}, 'select': ['id', 'group_name']}).success(function(data) {
                $scope.usergroups = data.items;
            });
        }
        
        $scope.fields = [
            {key: "id",label: "ID",},
            {key: "modified_date",label: "Modified",},
            {key: "project_name",label: "Project Name",},
            {key: "owner_name",label: "Owner",},
            {key: "address",label: "Address",},
            {key: "timezone",label: "Timezone",},
            {key: "project_status",label: "Status",},
        ];
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
                
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

app.controller('UserGroup', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 
                function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        rest.path = "userGroups";
        
        var ctrl = this;
        
        breadcrumbsService.setTitle("Site Track: Tag View");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/user-group", "Manage");
        breadcrumbsService.add("/#/user-group", "User Groups");
        breadcrumbsService.add("/#/user-group", "View Group");
        

                  $scope.list = [{
                    "id": 1,
                    "type": "item_group",
                    "title": "Construction Site",
                    "items": [{
                            "id": 1.1,
                            "type": "item_subtype",
                            "title": "Admin",
                             "items": [],
                          }, {
                            "id": 1.2,
                            "type": "item_subtype",
                            "title": "Site",
                          }, {
                            "id": 1.3,
                            "type": "item_subtype",
                            "title": "Personal",
                          },{
                            "id": 1.4,
                            "type": "item_subtype",
                            "title": "Consultant",
                          }],
                  }, {
                    "id": 2,
                    "type": "item_group",
                    "title": "Assets Tracking",
                    "items": [],
                  }, {
                    "id": 3,
                    "type": "item_group",
                    "title": "Test user group",
                    "items": []
                  },];

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
                  
                  $scope.addItemGroup = function() {
                    var nodeData = $scope.list;
                    nodeData.push({
                      id: null,
                      title: "Item Group " + (nodeData.length + 1),
                      type: "item_group",
                      items: []
                    });
                  };

                  $scope.newSubItem = function(scope, type) {
                    var nodeData = scope.$modelValue;
                    if(type)
                        type = (type=="item_group"?"item_type":"item_subtype");
                    else
                        type = "item_group";
                    
                    if(nodeData.items==undefined)
                        nodeData.items = [];
                    
                    nodeData.items.push({
                      id: nodeData.id * 10 + nodeData.items.length,
                      title: nodeData.title + '.' + (nodeData.items.length + 1),
                      type: type,
                      items: []
                    });
                  };
        
        ctrl.items = [];
        $scope.search = [];
        $scope.search.project = [];
        $scope.projectlevels = [];
        $scope.search.childlevels = [];
        $scope.search.group = [];
        
        $scope.updateLevel = function(projectId, level) {
            $scope.search.project_id = projectId;
            
            $scope.projectlevels.splice(level, ($scope.projectlevels.length-level));
            
            $http.post("/api/getall?mod=projectLevel", {'search': {'project_id': projectId, 'parent_id': 0}, 'select': ['id', 'level_name']}).success(function(data) {
                if(data.items.length>0)
                    $scope.projectlevels.push(data.items);
            });
            
            $http.post("/api/getall?mod=userGroups", {'search': {'project_id': projectId}, 'select': ['id', 'group_name']}).success(function(data) {
                $scope.usergroups = data.items;
            });
        }
        
        $scope.fields = [
            {key: "id",label: "ID",},
            {key: "modified_date",label: "Modified",},
            {key: "project_name",label: "Project Name",},
            {key: "owner_name",label: "Owner",},
            {key: "address",label: "Address",},
            {key: "timezone",label: "Timezone",},
            {key: "project_status",label: "Status",},
        ];
        
        var errorCallback = function (data) {
            if(data.status!=401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };
        
        $scope.predicate = "";
        $scope.projects = [];
        $scope.usergroups = [];
                
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



.controller('Index', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', function ($scope, rest, $location, $route, $routeParams, alertService) {
        
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
            $scope.currentPage = (data._meta.currentPage+1);
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

app.directive('ngConfirmClick', [
    function () {
        return {
            priority: 1,
            terminal: true,
            link: function (scope, element, attr) {
                var msg = attr.ngConfirmClick || "Are you sure?";
                var clickAction = attr.ngClick;
                element.bind('click', function (event) {
                    if (window.confirm(msg)) {
                        scope.$eval(clickAction)
                    }
                });
            }
        };
    }])

app.filter('titlecase', function() {
    return function(s) {
        s = ( s === undefined || s === null ) ? '' : s;
        return s.toString().toLowerCase().replace( /\b([a-z])/g, function(ch) {
            return ch.toUpperCase();
        });
    };
});