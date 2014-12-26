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
    
    return alertService;
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
           // this next if is necessary for when using ng-required on your input. 
           // In such cases, when a letter is typed first, this parser will be called
           // again, and the 2nd time, the value will be undefined
           if (inputValue == undefined) return '' 
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