app.controller('ProjectIndex', ['$scope', 'rest', '$location', '$route', '$routeParams', 'alertService', '$http', 'breadcrumbsService', 'page_dropdown', 'authorizationService', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, page_dropdown, authorizationService) {

        rest.path = "projects";
        $scope.page_dropdown = page_dropdown;
        $scope.permission = authorizationService.permissionModel.permission.projects;

        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("List Projects");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/projects", "Manage - Projects");

        var errorCallback = function (data) {
            if (data.status != 401) {
                alertService.add('error', data.name+": "+data.message);
            }
        };
        $scope.projects = [];
        $scope.countries = [];
        $scope.sort = {};
        $scope.showSearchBox = 1;

        $scope.deleteProject = function (id) {
            rest.deleteById({id: id}).success(function () {
                $location.path('/projects');
                $route.reload();
            }).error(errorCallback);
        }

        $scope.order = function (elm) {
            $scope.sortBy = elm;
            if ($scope.sort[elm] && $scope.sort[elm].search("-up") != -1) {
                $scope.sort[elm] = "-down";
                $scope.sortBy = "-" + elm;
            }
            else {
                $scope.sort = [];
                $scope.sort[elm] = "-up";
            }
            $scope.listProjects();
        };

        $scope.listProjects = function () {
            var params = {'search': $scope.search, 'sort': $scope.sortBy, 'limit': $scope.numPerPage, 'page': $scope.currentPage, };
            rest.models(params).success(function (data) {
                $scope.projects = data.items;

                $scope.totalCount = data._meta.totalCount;
                $scope.pageCount = data._meta.pageCount;
                $scope.currentPage = (data._meta.currentPage);
                $scope.numPerPage = data._meta.perPage;
            }).error(errorCallback);
        }

        $scope.listProjects();

        rest.setData("countries/getall", ['country_code', 'country_name'], {'project_status': null}).success(function (data) {
            $scope.countries = data.items;
        });


		$scope.downloadReport = function() {
            
           criteria = {'search': $scope.search,'da':'all'};
            
            rest.models(criteria).success(function (data) {
                
                $http.post("projects/export", data.items).success(function (data) {
                    var tabWindowId = window.open("_new");
                    tabWindowId.location.href = data;
                }).error(function (data) {
                    errorCallback(data)
                });

            }).error(errorCallback);
            
        }
    }])

app.controller('ProjectForm', ['$scope', 'rest', '$location', '$route', '$routeParams', 'alertService', '$http', 'breadcrumbsService', '$upload', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, $upload) {

        rest.path = "projects";

        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle(($routeParams.id ? "Update" : "Create New") + " Project");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/projects", "Projects");
        breadcrumbsService.add(null, ($routeParams.id ? "Update" : "Create New") + " Project");

        $scope.serverError = [];
        $scope.project = {};

        if ($routeParams.id) {
            rest.model({'id': $routeParams.id}).success(function (data) {
                $scope.project = data;
                if ($scope.project.project_logo)
                    $scope.project.project_logo = 'filemanager/getimage?type=cHJvamVjdF9pbWFnZQ==&file='+$scope.project.project_logo;
                if ($scope.project.project_image)
                    $scope.project.project_image = 'filemanager/getimage?type=cHJvamVjdF9pbWFnZQ==&file=' + $scope.project.project_image;
            });
        }

        $scope.onFileSelect = function ($files, modelName) {
            $scope.serverError.photo = "";
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
                    //console.log(data);
                    $scope.project[modelName] = data;
                }).error(function(data) {
                    $scope.serverError.photo = data;
                });
            }
        };

        $scope.removePhoto = function () {
            $scope.project.photo = null;
        }

        $scope.saveProject = function () {
            if ($scope.project.id)
            {
                rest.putModel($scope.project).success(function (data) {
                    alertService.clearAll();
                    alertService.add("success", "Project details updated.");
                    $location.path('/projects').replace();
                }).error(function (data) {
                    alertService.clearAll();
                    alertService.add("error", "Validation Error");
                    angular.forEach(data, function (v) {
                        $scope.serverError[v['field']] = v['message'];
                    });
                });
            }
            else {
                rest.postModel($scope.project).success(function (data) {
                    alertService.clearAll();
                    alertService.add("success", "New Project Created.");
                    $location.path('/projects').replace();
                }).error(function (data) {
                    alertService.clearAll();
                    alertService.add("error", "Validation Error");
                    angular.forEach(data, function (v) {
                        $scope.serverError[v['field']] = v['message'];
                    });
                });
            }
        }
        rest.setData("countries/getall", ['country_code', 'country_name'], {'project_status': null}).success(function (data) {
            $scope.countries = data.items;
        });
        
        rest.setData("timezones/getall", ['id', 'name'], {'status': 1}).success(function (data) {
            $scope.timezones = data.items;
        });
    }])

app.controller('ProjectLevel', ['$scope', 'rest', '$location', '$route', '$routeParams', 'alertService', '$http', 'breadcrumbsService', "$modal", '$log', 'authorizationService', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, $modal, $log, authorizationService) {

        rest.path = "projectlevel";
        $scope.permission = authorizationService.permissionModel.permission.projectlevel;

        breadcrumbsService.clearAll();
        breadcrumbsService.setTitle("Manage Project Levels");
        breadcrumbsService.add("/#/", "Home");
        breadcrumbsService.add("/#/projects", "Projects");
        breadcrumbsService.add("/#/project-levels", "Manage Levels");

        $scope.flagUpdatedLevels = false;
        $scope.draggingInitiated = false;

        $scope.showProjectsModal = function (scope) {
            if (scope.parent_id > 0)
                return 0;
            var modalInstance = $modal.open({
                templateUrl: '/templates/user-group/showProjectsModal.html',
                controller: 'projectLevelProjectsPopup',
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
            if (data.status != 401) {
                alertService.add('error', "Error in processing your request. Please try again.");
            }
        };

        $scope.resetLevels = function () {
            $route.reload();
        }

        $scope.saveLevelPosition = function () {
            alertService.clearAll();
            $scope.loadingSaveLevels = true;
            $http.post("/projectlevel/savelevelpositions", {'ProjectLevels': $scope.list}).success(function (data) {
                alertService.add("success", "Project level saved successfully.");
                $scope.loadingSaveLevels = false;
                $scope.flagUpdatedLevels = false;
            }).error(function (data) {
                console.log(data);
                $scope.loadingSaveLevels = false;
                alertService.add("error", "Error in saving project levels. Please try again.");
            });
        }

        $scope.callbacks = {
            accept: function (sourceNodeScope, destNodesScope, destIndex) {
                var sourceLevel = sourceNodeScope.$modelValue;
                // Allow dragging only if element is not top level (group shouldn't be dragged).
                if (sourceLevel.parent_id > 0) {
                    if ((typeof destNodesScope.$modelValue[destIndex] == 'undefined') || (typeof destNodesScope.$modelValue[destIndex].parent_id == 'undefined') || (!destNodesScope.$modelValue[destIndex].parent_id)) {
                        return false;
                    }
                    $scope.draggingInitiated = true;
                    return true;
                }
                return false;
            },
            dropped: function (event) {
                if ($scope.draggingInitiated) {
                    $scope.flagUpdatedLevels = true;
                }
            },
        };

        $scope.removeItem = function(item) {
            var scope = item.$modelValue;
            $http.delete("/projectlevel/"+scope.id).success(function(data) {
                console.log("removed-"+scope.id);
            });
            item.remove();
        }

        $scope.toggle = function (scope) {
            scope.toggle();
        };

        $scope.newSubItem = function (scope, level) {
            if (level == 'top') {
                scope.push({
                    id: null,
                    level_name: 'Level ' + (scope.length + 1),
                    items: [],
                    parent_id: 0,
                    editing: true,
                });
            }
            else {
                var nodeData = scope.$modelValue;
                nodeData.levels.push({
                    id: null,
                    level_name: nodeData.level_name + '.' + (nodeData.levels.length + 1),
                    items: [],
                    parent_id: nodeData.id,
                    editing: true,
                });
                scope.expand();
            }
        };

        $scope.saveItem = function (obj, title) {
            var scope = obj.$modelValue;
            if (scope) {
                scope.level_name = title;
                if (scope.id)
                    $http.put("/projectlevel/" + scope.id, {'level_name': scope.level_name}).success(function (data) {
                        console.log("Level updated-" + data.id);
                    });
                else
                    $http.post("/projectlevel", {'level_name': scope.level_name, 'parent_id': scope.parent_id}).success(function (data) {
                        scope.id = data.id;
                        console.log("Level created - " + data.id);
                    });
            }
            scope.editing = false
            scope.editorEnabled = false;
        }

        var params = {'search': {'parent_id': 0}};
        rest.customModelData("projectlevel/getall?expand=levels", params).success(function (data) {
            $scope.list = data.items;
        }).error(errorCallback);
    }]);


app.controller('ProcessFlow', ['$scope', 'rest', '$location', '$route', '$routeParams', 'alertService', '$http', 'breadcrumbsService', "$modal", "$log", 'tooltip', 'authorizationService', function ($scope, rest, $location, $route, $routeParams, alertService, $http, breadcrumbsService, $modal, $log, tooltip, authorizationService) {
    
    rest.path = "tagprocess";
    $scope.tooltip = tooltip;
    $scope.permission = authorizationService.permissionModel.permission.tagprocess;

    alertService.clearAll();
    breadcrumbsService.clearAll();
    breadcrumbsService.setTitle("Manage Tag Process");
    breadcrumbsService.add("/#/", "Home");
    breadcrumbsService.add("/#/tag-process-flow", "Tag Process");

    $scope.flagUpdatedLevels = false;
    $scope.draggingInitiated = false;

    $scope.showProjectsModal = function (scope) {
        if (scope.parent_id > 0)
            return 0;
        var modalInstance = $modal.open({
            templateUrl: '/templates/user-group/showProjectsModal.html',
            controller: 'ProcessProjectsPopup',
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
        if (data.status != 401) {
            alertService.add('error', "Error in processing your request. Please try again.");
        }
    };
    
    $scope.setFlag = function(scope, type) {
        if(type=='default') {
            var item = scope.$parent.$parent.$parent.$modelValue;
            if(item.flagDefault != scope.$modelValue.id) {
                item.flagDefault = scope.$modelValue.id;
                $scope.flagUpdatedLevels = true;
            }
            else
                item.flagDefault = 0;
        }
        else if(type=='completion') {
            var item = scope.$parent.$parent.$parent.$modelValue;
            if(item.flagCompletion != scope.$modelValue.id) {
                item.flagCompletion = scope.$modelValue.id;
                $scope.flagUpdatedLevels = true;
            }
            else
                item.flagCompletion = 0;
        }
        else {
            scope.flagHierarchy = !scope.flagHierarchy;
            $scope.flagUpdatedLevels = true;
        }
    }
    
    $scope.getFlag = function(scope, type) {
        var item = scope.$parent.$parent.$parent.$modelValue;
        if(type=='default' && item.flagDefault == scope.$modelValue.id) {
            return item.flagDefault;
        }
        else if(type=='completion' && item.flagCompletion == scope.$modelValue.id) {
            return item.flagCompletion;
        }
    }
    
    $scope.resetLevels = function () {
        $route.reload();
    }

    $scope.saveProcessLevels = function () {
        alertService.clearAll();
        $scope.loadingSaveLevels = true;
        $http.post("/tagprocess/savepositionlevels", {'Process': $scope.list}).success(function (data) {
            alertService.add("success", "Project level saved successfully.");
            $scope.loadingSaveLevels = false;
            $scope.flagUpdatedLevels = false;
        }).error(function (data) {
            console.log(data);
            $scope.loadingSaveLevels = false;
            alertService.add("error", "Error in saving project levels. Please try again.");
        });
    }

    $scope.options = {
        accept: function (sourceNodeScope, destNodesScope, destIndex) {
            var sourceScope = sourceNodeScope.$modelValue;
            // Allow dragging only if element is not top level (group shouldn't be dragged).
            if (sourceScope.parent_id > 0) {
                if((destNodesScope.$modelValue.length > 0 && destNodesScope.$modelValue[0].parent_id == sourceScope.parent_id) || (destNodesScope.$modelValue.id == sourceScope.parent_id)) {
                    $scope.draggingInitiated = true;
                    return true;
                }
            }
            return false;
        },
        dropped: function (event) {
            if ($scope.draggingInitiated) {
                $scope.flagUpdatedLevels = true;
                $scope.draggingInitiated = false;
            }
        },
    };

    $scope.removeItem = function(item) {
        alertService.clearAll();
        var scope = item.$modelValue;
        $http.delete("/tagprocess/"+scope.id).success(function(data) {
            console.log("removed-"+scope.id);
            item.remove();
        }).error(function(data) {
            if(typeof data == 'object')
                angular.forEach(data, function(v) {
                    alertService.add("error", v['message']);
                });
            else
                alertService.add("error", data);
        });
    }

    $scope.toggle = function (scope) {
        scope.toggle();
    };

    $scope.newSubItem = function (scope, level) {
        if (level == 'top') {
            scope.push({
                id: null,
                process_name: 'Process ' + (scope.length + 1),
                tree: [],
                parent_id: 0,
                editing: true,
                type: 0
            });
        }
        else {
            var nodeData = scope.$modelValue;
            nodeData.tree.push({
                id: null,
                process_name: nodeData.process_name + '.' + (nodeData.tree.length + 1),
                tree: [],
                type: (nodeData.type+1),
                parent_id: nodeData.id,
                editing: true,
            });
            scope.expand();
        }
    };

    $scope.saveItem = function (obj, title) {
        var scope = obj.$modelValue;
        if (scope) {
            scope.process_name = title;
            if (scope.id)
                $http.put("/tagprocess/" + scope.id, {'process_name': scope.process_name, 'type': scope.type, 'option_type': scope.option_type}).success(function (data) {
                    console.log("Process updated-" + data.id);
                });
            else
                $http.post("/tagprocess", {'process_name': scope.process_name, 'parent_id': scope.parent_id, 'type': scope.type, 'option_type': scope.option_type})
                  .success(function (data) {
                    scope.id = data.id;
                    console.log("Process created - " + data.id);
                });
        }
        scope.editing = false
        scope.editorEnabled = false;
    }

    var params = {'search': {'parent_id': 0}};
    rest.customModelData("tagprocess/getall?expand=tree", params).success(function (data) {
        $scope.list = data.items;
    }).error(errorCallback);
}]);

app.controller('projectLevelProjectsPopup', function ($scope, $modalInstance, rest, $http, itemScope, page_dropdown) {

    rest.path = "projects";
    $scope.search = {};
    $scope.selectedProjects = [];
    $scope.allProjects = [];
    $scope.temp = {};
    $scope.page_dropdown = page_dropdown;

    $scope.listProjects = function () {
        $scope.list_loading = true;
        var params = {'page': $scope.list_currentPage, 'limit': $scope.list_numPerPage};
        rest.customModelData("projects/projectsbylevel/" + itemScope.id, params).success(function (data) {
            $scope.selectedProjects = data.items;
            $scope.list_totalCount = data._meta.totalCount;
            $scope.list_pageCount = data._meta.pageCount;
            $scope.list_currentPage = (data._meta.currentPage);
            $scope.list_numPerPage = data._meta.perPage;
            $scope.list_loading = false;
            $scope.getAllProjects();
        }).error(function () {
            $scope.list_loading = false;
        });
    }

    $scope.getAllProjects = function () {
        var params = {'search': $scope.search, 'excludeProjects': $scope.selectedProjects, 'page': $scope.currentPage, 'limit': $scope.numPerPage};
        rest.models(params).success(function (data) {
            $scope.allProjects = data.items;
            $scope.totalCount = data._meta.totalCount;
            $scope.pageCount = data._meta.pageCount;
            $scope.currentPage = (data._meta.currentPage);
            $scope.numPerPage = data._meta.perPage;

            angular.forEach($scope.temp, function (value) {
                for (var j = 0; j < Object.keys($scope.allProjects).length; j++) {
                    if ($scope.allProjects[j].id == value) {
                        $scope.allProjects[j]['isSelected'] = true;
                        break;
                    }
                }
            });
        });
    }

    $scope.clearSearch = function () {
        $scope.search = {};
        $scope.getAllProjects();
    }
    
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

    $scope.selectProject = function (scope) {
        if (scope['isSelected']) {
            $scope.temp["" + scope.id] = scope.id;
        }
        else
            $scope.temp["" + scope.id] = undefined;
    }

    $scope.assignProjects = function () {
        $http.post('/projectlevel/assignprojects/' + itemScope.id, {'Projects': $scope.temp}).success(function (data) {
            $scope.listProjects();
            $scope.temp = {};
        }).error(function (data) {
            console.log(data);
            alert("Error in assigning projects. Please try again later.");
        });
    };

    $scope.unassignProjects = function (project, index) {
        $http.post('/projectlevel/unassignprojects/' + itemScope.id, {'Projects': [project.id]}).success(function (data) {
            if (data > 0)
                $scope.selectedProjects.splice(index, 1);
            $scope.allProjects.push(project);
        }).error(function (data) {
            console.log(data);
            alert("Invalid Request");
        });
    };

    $scope.close = function () {
        $modalInstance.dismiss('cancel');
    };

    $scope.listProjects();
});

app.controller('ProcessProjectsPopup', function ($scope, $modalInstance, rest, $http, itemScope, page_dropdown) {

    rest.path = "projects";
    $scope.search = {};
    $scope.selectedProjects = [];
    $scope.allProjects = [];
    $scope.temp = {};
    $scope.page_dropdown = page_dropdown;

    $scope.listProjects = function () {
        $scope.list_loading = true;
        var params = {'page': $scope.list_currentPage, 'limit': $scope.list_numPerPage};
        rest.customModelData("projects/projectsbyprocess/" + itemScope.id, params).success(function (data) {
            $scope.selectedProjects = data.items;
            $scope.list_totalCount = data._meta.totalCount;
            $scope.list_pageCount = data._meta.pageCount;
            $scope.list_currentPage = (data._meta.currentPage);
            $scope.list_numPerPage = data._meta.perPage;
            $scope.list_loading = false;
            $scope.getAllProjects();
        }).error(function () {
            $scope.list_loading = false;
        });
    }

    $scope.getAllProjects = function () {
        var params = {'search': $scope.search, 'excludeProjects': $scope.selectedProjects, 'page': $scope.currentPage, 'limit': $scope.numPerPage};
        rest.models(params).success(function (data) {
            $scope.allProjects = data.items;
            $scope.totalCount = data._meta.totalCount;
            $scope.pageCount = data._meta.pageCount;
            $scope.currentPage = (data._meta.currentPage);
            $scope.numPerPage = data._meta.perPage;

            angular.forEach($scope.temp, function (value) {
                for (var j = 0; j < Object.keys($scope.allProjects).length; j++) {
                    if ($scope.allProjects[j].id == value) {
                        $scope.allProjects[j]['isSelected'] = true;
                        break;
                    }
                }
            });
        });
    }

    $scope.clearSearch = function () {
        $scope.search = {};
        $scope.getAllProjects();
    }
    
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

    $scope.selectProject = function (scope) {
        if (scope['isSelected']) {
            $scope.temp["" + scope.id] = scope.id;
        }
        else
            $scope.temp["" + scope.id] = undefined;
    }

    $scope.assignProjects = function () {
        $http.post('/tagprocess/assignprojects/' + itemScope.id, {'Projects': $scope.temp}).success(function (data) {
            $scope.listProjects();
            $scope.temp = {};
        }).error(function (data) {
            console.log(data);
            alert("Error in assigning projects. Please try again later.");
        });
    };

    $scope.unassignProjects = function (project, index) {
        $http.post('/tagprocess/unassignprojects/' + itemScope.id, {'Projects': [project.id]}).success(function (data) {
            if (data > 0)
                $scope.selectedProjects.splice(index, 1);
            $scope.allProjects.push(project);
        }).error(function (data) {
            console.log(data);
            alert("Invalid Request");
        });
    };

    $scope.close = function () {
        $modalInstance.dismiss('cancel');
    };

    $scope.listProjects();
});