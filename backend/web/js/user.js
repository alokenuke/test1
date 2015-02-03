app.controller('UserGroup', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService', 'page_dropdown', '$modal', '$log', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, page_dropdown, $modal, $log) {
        
    rest.path = "usergroups";

    breadcrumbsService.clearAll();
    breadcrumbsService.setTitle("Manage User Groups");
    breadcrumbsService.add("/#/", "Home");
    breadcrumbsService.add("/#/user-groups", "Manage - User Groups");

    $scope.list = [];
    $scope.selectedItem = {};
    $scope.page_dropdown = page_dropdown;
    
    $scope.options = {
    };
    
    $scope.updateItem = function(scope) {
        scope.editing = true; 
    }
    
    $scope.saveItem = function(obj, title) {
        var scope = obj.$modelValue;
        if(scope.user_group_id) {
            scope.level_name = title;
            if(scope.id)
                $http.put("/userlevels/"+scope.id, {'level_name': scope.level_name}).success(function(data) {
                    console.log("Level updated-"+data.id);
                });
            else
                $http.post("/userlevels", {'level_name': scope.level_name, 'user_group_id': scope.user_group_id}).success(function(data) {
                    scope.id = data.id;
                    console.log("Level updated-"+data.id);
                });
        }
        else {
            scope.group_name = title;
            if(scope.id)
                $http.put("/usergroups/"+scope.id, {'group_name': scope.group_name}).success(function(data) {
                    console.log("Group Updated-"+data.id);
                });
            else {
                $http.post("/usergroups", {'group_name': scope.group_name}).success(function(data) {
                    scope.id = data.id;
                    console.log("Group Updated-"+data.id);
                });
            }
        }
        scope.editing=false
        scope.editorEnabled = false;
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
    
    $scope.newSubItem = function(scope, type) {
        if(type=='top')
        {
            scope.unshift({
              id: null,
              group_name: "User Group " + (scope.length + 1),
              levels: [],
              editing: true
            });
        }
        else {
            scope.levels.unshift({
                id: null,
                level_name: 'level ' + (scope.levels.length + 1),
                user_group_id: scope.id,
                levels: [],
                editing: true,
            });
            scope.collapsed = true;
        }
    };
    
    $scope.sortUser = function(scope, elm) {
        scope.users_sortBy = elm;
        if(typeof scope.sort !== 'undefined' && typeof scope.sort[elm] !== 'undefined' && scope.sort[elm].search("-up")!==-1) {
            scope.sort[elm] = "-down";
            scope.users_sortBy = "-"+elm;
        }
        else {
            scope.sort = [];
            scope.sort[elm] = "-up";
        }
        $scope.searchUser(scope);
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
    
    $scope.loadUsers = function(scope) {
        if(!scope['users']) {
            scope.loading = true;
            rest.setData("users/levelusers/"+scope.id+"?expand=assignedProjects", null, {}).success(function(data) {
                scope['users'] = data.items;
                scope.users_totalCount = data._meta.totalCount;
                scope.users_pageCount = data._meta.pageCount;
                scope.users_currentPage = (data._meta.currentPage);
                scope.users_numPerPage = data._meta.perPage;
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
    
    $scope.assignUsersModal = function (scope) {
        var modalInstance = $modal.open({
          templateUrl: '/templates/user-group/assignUserModal.html',
          controller: 'AssignUserPopup',
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
    
    $scope.showProjectsModal = function (scope) {
        if(scope.user_group_id)
            return 0;
        var modalInstance = $modal.open({
          templateUrl: '/templates/user-group/showProjectsModal.html',
          controller: 'showProjectsPopup',
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
    
    $scope.unassignLevelUser = function(user, item, index) {
        $http.post('/userlevels/unassignusers/'+item.id, {'User':[user]}).success(function(data) {
            if(data>0)
                item.users.splice(index, 1);
        }).error(function(data) {
            console.log(data);
            alert("Invalid Request");
        });
    };
    
    var errorCallback = function (data) {
        if(data.status!=401) {
            alertService.add('error', "Error in processing your request. Please try again.");
        }
    };
    
    var params = {'search': $scope.$search, 'sort': $scope.sortBy, 'page':$scope.currentPage, 'limit': $scope.numPerPage};
    rest.models(params).success(function (data) {
        $scope.list = data.items;
        $scope.totalCount = data._meta.totalCount;
        $scope.pageCount = data._meta.pageCount;
        $scope.currentPage = (data._meta.currentPage);
        $scope.numPerPage = data._meta.perPage;
    }).error(errorCallback);
}])

app.controller('UserIndex', ['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','page_dropdown', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService,page_dropdown) {
      
        rest.path = "users";
        
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Manage Users");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/users", "Manage - Users");
        
        $scope.page_dropdown = page_dropdown;
        $scope.$search = {};
        $scope.rec_noti = [
            {id: 'daily', name:'Daily'},
            {id: 'weekly', name:'Weekly'},
            {id: 'monthly', name:'Monthly'},
            {id: 'yearly', name:'Yearly'},
        ];
        
        $scope.sortUser = function(elm) {
            $scope.sortBy = elm;
            if(typeof $scope.sort !== 'undefined' && typeof $scope.sort[elm] !== 'undefined' && $scope.sort[elm].search("-up")!==-1) {
                $scope.sort[elm] = "-down";
                $scope.sortBy = "-"+elm;
            }
            else {
                $scope.sort = [];
                $scope.sort[elm] = "-up";
            }
            updateUserList();
        };
        
        $scope.removeUser =  function(model, $index) {
            
            rest.deleteById(model);
            
            $scope.users.splice($index, 1);
        }
        
        $scope.pageChanged = function() {
            updateUserList();
        }
        
        rest.setData("usergroups/getall", ['user_groups.id', 'group_name'], {}).success(function(data) {$scope.usergroups = data.items;});
        
        $scope.searchUser = function(){
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
                $scope.users = data.items;
                $scope.totalCount = data._meta.totalCount;
                $scope.pageCount = data._meta.pageCount;
                $scope.currentPage = (data._meta.currentPage);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }
        
        $scope.sortUser("-id");
    }])

app.controller('UserCreate', 
['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','$upload', 'tooltip',
    function ($scope, rest, $location, $route, $rootScope, alertService, $http, breadcrumbsService,$upload, tooltip) {
        
        rest.path = "users";
        
        $scope.tooltip = tooltip;
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Create Users");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/users", "Users");
        breadcrumbsService.add("/#/users/create", "Create Users");
        
	$scope.EMAIL_REGEXP = /^[a-z0-9!#$%&'*+/=?^_`{|}~.-]+@[a-z0-9-]+(\.[a-z0-9-]+)*$/i;
            
        $scope.rec_noti = [
            {id: 'daily', name:'Daily'},
            {id: 'weekly', name:'Weekly'},
            {id: 'monthly', name:'Monthly'},
            {id: 'yearly', name:'Yearly'},
        ];
        
        rest.setData("roles/getall", ['id', 'role_name'], {}).success(function(data) {
            $scope.roles = data.items;
        });
        
         
        $scope.users = [];
        $scope.users.push({});
        $scope.serverError = [];
        $scope.userPhoto = [];
        $scope.copyUser =  function(fieldNum) {
            var totalFields = $scope.users.length+parseInt(fieldNum);
            if(totalFields >100) {
                alert("You can't create more than 100 users from this page. You have requested to generate fields for "+totalFields + " users.");
                return;
            }
            else
                for(var i=0;i<fieldNum;i++) {
                    $scope.users.push({});
                }
        }
        
        $scope.removeUser =  function($index) {
            $scope.users.splice($index, 1);
        }
        
        $scope.createUser = function(){
            $rootScope.alerts = [];
            $scope.serverError = [];
            alertService.clearAll();
            $http.post('/users/multiinsert', {'User':$scope.users}).success(function(data) {
                if(data=="Success") {
                    alertService.add("success", "Users created.");
                    alert("All users created.");
                    $location.path('/users').replace();
                }
            }).error(function(data) {
                alertService.clearAll();
                alertService.add("error", "Validation Error");
                angular.forEach(data.User, function(value, key) {
                    var pushValue = {};
                    if(value['id'])
                        $scope.users[key]['id'] = value['id'];
                    else {
                        angular.forEach(value, function(child_value, child_key) {
                            pushValue[child_key] = child_value[0];
                        });
                        $scope.serverError[key] = pushValue;
                    }
                })
            });
        }
            
        $scope.onFileSelect = function($files,index) {
            //$files: an array of files selected, each file has name, size, and type.
            for (var i = 0; i < $files.length; i++) {
                var file = $files[i];
                $scope.upload = $upload.upload({
                    url: 'fileupload/upload', //upload.php script, node.js route, or servlet url
                    data: {myObj: $scope.myModelObj},
                    file: file,
                }).progress(function(evt) {
                    console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
                }).success(function(data, status, headers, config) {
                    // file is uploaded successfully
                    //console.log(data);
                    $scope.users[index].photo = data;
                });
            }
        }; 

         $scope.removePhoto = function(index){

             $scope.users[index].photo = null;
         }
 }])

.controller('AssignUserPopup', function ($scope, $modalInstance, rest, $http, itemScope, page_dropdown) {
    
    rest.path = "users";
    $scope.search = {};
    $scope.popupUsers = [];
    $scope.temp = {};
    $scope.roles = {};
    $scope.page_dropdown = page_dropdown;
    
    $scope.getUsers = function(){
        var params = {'search': $scope.search, 'excludeUserIds': itemScope.availableUserIds, 'sort': $scope.sortBy, 'page':$scope.currentPage, 'limit': $scope.numPerPage};
        rest.models(params).success(function(data) {
            $scope.popupUsers = data.items;
            $scope.totalCount = data._meta.totalCount;
            $scope.pageCount = data._meta.pageCount;
            $scope.currentPage = (data._meta.currentPage);
            $scope.numPerPage = data._meta.perPage;
            $scope.allSelected = false;
            
            angular.forEach($scope.temp, function(value) {
                for(var j=0; j < Object.keys($scope.popupUsers).length; j++){
                    if($scope.popupUsers[j].id == value) {
                        $scope.popupUsers[j]['isSelected'] = true;
                        break;
                    }
                }
            });            
        });
    }
         
    $scope.sortUser = function(elm) {
        $scope.sortBy = elm;
        if(typeof $scope.sort !== 'undefined' && typeof $scope.sort[elm] !== 'undefined' && $scope.sort[elm].search("-up")!==-1) {
            $scope.sort[elm] = "-down";
            $scope.sortBy = "-"+elm;
        }
        else {
            $scope.sort = [];
            $scope.sort[elm] = "-up";
        }
        $scope.getUsers();
    };
     
    $scope.clearSearch = function() {
        $scope.search = {};
        $scope.getUsers();
    }
    
    $scope.selectUser = function(scope) {
        if (scope['isSelected']) {
            $scope.temp[""+scope.id] = scope.id;
        }
        else
            $scope.temp[""+scope.id] = undefined;
    }
    
    $scope.selectAllUsers = function(data, allSelected) {
        angular.forEach(data, function(v) {
            v['isSelected'] = allSelected;
            if (allSelected) {
                $scope.temp[""+v.id] = v.id;
            }
            else
                $scope.temp[""+v.id] = undefined;
        }); 
    }
     
    $scope.ok = function () {
        $http.post('/userlevels/assignusers/'+itemScope.id, {'User':$scope.temp, 'group_id': itemScope.user_group_id}).success(function(data) {
            console.log(data);
        }).error(function(data) {
            console.log(data);
        });
        $modalInstance.close($scope.temp);
    };
    
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };

   $scope.getUsers();
   rest.setData("roles/getall", ['id', 'role_name'], {}).success(function(data) {
        $scope.roles = data.items;
    });
                    
})

.controller('showProjectsPopup', function ($scope, $modalInstance, rest, $http, itemScope, page_dropdown) {
    
    rest.path = "projects";
    $scope.search = {};
    $scope.selectedProjects = [];
    $scope.allProjects = [];
    $scope.temp = {};
    $scope.page_dropdown = page_dropdown;
    
    $scope.listProjects = function() {
        $scope.list_loading = true;
        var params = {'page':$scope.list_currentPage, 'limit': $scope.list_numPerPage};
        rest.customModelData("projects/usergroup/"+itemScope.id, params).success(function(data) {
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
        $http.post('/usergroups/assignprojects/'+itemScope.id, {'Projects':$scope.temp}).success(function(data) {
            $scope.listProjects();
            $scope.temp = {};
        }).error(function(data) {
            console.log(data);
            alert("Error in assigning projects. Please try again later.");
        });
    };
    
    $scope.unassignProjects = function(project, index) {
        $http.post('/usergroups/unassignprojects/'+itemScope.id, {'Projects':[project]}).success(function(data) {
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

app.controller('UserUpdate',
['$scope', 'rest', '$location', '$route','$routeParams', 'alertService', '$http', 'breadcrumbsService','$upload', 'tooltip',
    function ($scope, rest, $location, $route, $rootScope, alertService, $http, breadcrumbsService,$upload, tooltip) {
        
        rest.path = "users";
        $scope.user = [];
        
        $scope.tooltip = tooltip;
        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Update User");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/users", "Manage Users");
        breadcrumbsService.add("/#/users/update", "Update User");
            
        $scope.rec_noti = [
            {id: 'daily', name:'Daily'},
            {id: 'weekly', name:'Weekly'},
            {id: 'monthly', name:'Monthly'},
            {id: 'yearly', name:'Yearly'},
        ];
        
        $scope.serverError = [];
        
        rest.setData("roles/getall", ['id', 'role_name'], {}).success(function(data) {
            $scope.roles = data.items;
        });
        
        rest.model({'id': $rootScope.id}).success(function(data) {
            $scope.user = data;
            if($scope.user.photo)
                $scope.user.photo = '/userUploads/'+$scope.user.company_id+'/userImages/'+$scope.user.photo;
        });
        
        $scope.onFileSelect = function($files) {
            //$files: an array of files selected, each file has name, size, and type.
            for (var i = 0; i < $files.length; i++) {
                var file = $files[i];
                $scope.upload = $upload.upload({
                    url: 'fileupload/upload', //upload.php script, node.js route, or servlet url
                    data: {myObj: $scope.myModelObj},
                    file: file,
                }).progress(function(evt) {
                    console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
                }).success(function(data, status, headers, config) {
                    // file is uploaded successfully
                    //console.log(data);
                    $scope.user.photo = data;
                });
            }
        }; 
        
        $scope.removePhoto = function(){
             $scope.user.photo = null;
         }
         
        $scope.updateUser  = function(){
            
            rest.putModel($scope.user).success(function(data) {
                alertService.clearAll();
                alertService.add("success", "User updated.");
                $location.path('/users').replace();
            }).error(function(data) { 
                alertService.clearAll();
                alertService.add("error", "Validation Error");
                angular.forEach(data, function(child_value, child_key) {
                    $scope.serverError[child_value['field']] = child_value['message'];
                });
            });
            
            
        }
 }]);