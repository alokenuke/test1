app.controller('UserGroup', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService) {
        
        rest.path = "userGroups";
        
        breadcrumbsService.setTitle("Manage User Groups");
        breadcrumbsService.clearAll();
        breadcrumbsService.add("", "Home");
        breadcrumbsService.add("/#/user-groups", "User Groups");
        
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