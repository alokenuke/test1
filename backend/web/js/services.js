'use strict';

/* services.js */

// don't forget to declare this service module as a dependency in your main app constructor!
var appServices = angular.module('appApp.services', []);

appServices.factory('alertService', function($rootScope) {
    var alertService = {};

    // create an array of alerts available globally
    $rootScope.alerts = [];
    
    alertService.add = function(type, msg) {
        $rootScope.alerts.push({'type': type, 'msg': msg});
    };

    alertService.closeAlert = function(index) {
        $rootScope.alerts.splice(index, 1);
    };
    
	alertService.clearAll = function() {
        $rootScope.alerts = [];
    };    return alertService;
});

appServices.factory('breadcrumbsService', function($rootScope) {
    var breadcrumbsService = {};

    // create an array of alerts available globally
    $rootScope.breadcrumbs = [];
    
    $rootScope.range = function(n) {
        return new Array(n);
    }
    
    breadcrumbsService.setTitle = function(title) {
        $rootScope.page_title = title;
    }
    breadcrumbsService.headTitle = function(title) {
        $rootScope.head_title = title;
    }
    
    breadcrumbsService.add = function(link, label) {
        $rootScope.breadcrumbs.push({'link': link, 'label': label});
    };
    
    breadcrumbsService.clearAll = function() {
        $rootScope.breadcrumbs = [];
    };

    return breadcrumbsService;
});

appServices.directive('numbersOnly', function(){
   return {
     require: 'ngModel',
     link: function(scope, element, attrs, modelCtrl) {
       modelCtrl.$parsers.push(function (inputValue) {
           var max = parseInt(element.attr("max"));
           // this next if is necessary for when using ng-required on your input. 
           // In such cases, when a letter is typed first, this parser will be called
           // again, and the 2nd time, the value will be undefined
           if (inputValue == undefined) return '' 
           if(max) {
               var transformedInput = inputValue.replace(/[^0-9+.]/g, ''); 
               if(transformedInput.length>max)
                   transformedInput = (""+transformedInput).substring(0, max);
           }
           else
               var transformedInput = inputValue.replace(/[^0-9+.]/g, ''); 
           if (transformedInput!=inputValue) {
              modelCtrl.$setViewValue(transformedInput);
              modelCtrl.$render();
           }         

           return transformedInput;         
       });
     }
   };
});

appServices.directive('checkboxAll', function () {
  return function(scope, iElement, iAttrs) {
    var parts = iAttrs.checkboxAll;
    var selectedVar = "isSelected";
    iElement.attr('type','checkbox');
    iElement.on('change', function (evt) {
      scope.$apply(function () {
        var setValue = iElement.prop('checked');
        angular.forEach(scope.$eval(parts), function (v) {
          v[selectedVar] = setValue;
        });
      });
    });
    scope.$watch(parts, function (newVal) {
      var hasTrue=false, hasFalse=false;
      angular.forEach(newVal, function (v) {
        if (v[selectedVar]) {
          hasTrue = true;
        } else {
          hasFalse = true;
        }
      });
      if (hasTrue && hasFalse) {
        iElement.attr('checked', false);
      } else {
        iElement.attr('checked', hasTrue);
      }
    }, true);
  };
});
appServices.directive('ngConfirmClick', [
    function () {
        return {
            priority: 1,
            terminal: true,
            link: function (scope, element, attr, $dialogs) {
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

appServices.filter('titlecase', function() {
    return function(s) {
        s = ( s === undefined || s === null ) ? '' : s;
        return s.toString().toLowerCase().replace( /\b([a-z])/g, function(ch) {
            return ch.toUpperCase();
        });
    };
});
appServices.directive('openlightbox', 
   function() {
      var openLightBox = {
         link :   function(scope, element, attrs) {
             element.bind("click", function() {
                
                var title = $(this).attr("image-title");
                var imageUrl = $(this).attr("imageUrl");
                var imageType = $(this).attr("type");
                
                var element = angular.element('\
                    <div class="modal fade">\n\
                        <div class="modal-dialog" style="top: 10%;">\n\
                            <div class="modal-content">\n\
                                <div class="modal-header">\n\
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>\n\
                                    <h4 class="modal-title">Tag - '+title+'</h4>\n\
                                </div>\n\
                                <div class="modal-body text-center">\n\
                                    <img src="/userUploads/1/tagsImages/'+imageType+'/'+imageUrl+'" style="max-height: 200px;" />\n\
                                </div>\n\
                            </div>\n\
                        </div>\n\
                    </div>').on('hidden.bs.modal', function (e) {
                        element.remove();
                    });

                var body = $('body');
                body.append(element);
                element.modal('show');
            });
       }
   }
   return openLightBox;
});

appServices.directive("modalShow", function ($parse) {
    return {
        restrict: "A",
        link: function (scope, element, attrs) {

            //Hide or show the modal
            scope.showModal = function (visible, elem) {
                if (!elem)
                    elem = element;

                if (visible)
                    $(elem).modal("show");                     
                else
                    $(elem).modal("hide");
            }

            //Watch for changes to the modal-visible attribute
            scope.$watch(attrs.modalShow, function (newValue, oldValue) {
                scope.showModal(newValue, attrs.$$element);
            });

            //Update the visible value when the dialog is closed through UI actions (Ok, cancel, etc.)
            $(element).bind("hide.bs.modal", function () {
                $parse(attrs.modalShow).assign(scope, false);
                if (!scope.$$phase && !scope.$root.$$phase)
                    scope.$apply();
            });
        }

    };
});


appServices.directive('preloadable', function () {        
    return {
       link: function(scope, element) {
          element.addClass("empty");
          element.bind("load" , function(e){
             element.removeClass("empty");
          });
       }
    }
 });