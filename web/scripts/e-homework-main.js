var serviceUrl = '../services/public/';
var serviceLoginUrl = 'https://dportal.dpo.go.th/dpo/public/';//'http://172.23.10.224/dpo/public/';//'http://127.0.0.1/dportal/dpo/public/';

var app = angular.module('e-homework', ['ui.bootstrap' , 'ngRoute' , 'ngAnimate', 'ngCookies', 'ui.router', 'oc.lazyLoad', 'ngFileUpload', 'angular-bind-html-compile']);

app.config(function($controllerProvider, $compileProvider, $filterProvider, $provide) {
  app.register = {
    controller: $controllerProvider.register,
    directive: $compileProvider.directive,
    filter: $filterProvider.register,
    factory: $provide.factory,
    service: $provide.service
  };
});

angular.module('e-homework').controller('AppController', ['$cookies','$scope', '$filter', '$uibModal','IndexOverlayFactory', 'HTTPService', function($cookies, $scope, $filter, $uibModal, IndexOverlayFactory, HTTPService) {
	$scope.overlay = IndexOverlayFactory;
	$scope.overlayShow = false;
	$scope.currentUser = null;
    $scope.TotalLogin = 0;
    $scope.menu_selected = '';

    
    $scope.logout = function(){
        sessionStorage.setItem('user_session', null);
        sessionStorage.removeItem('user_session');
        $scope.currentUser = null;
        console.log(sessionStorage.getItem('user_session'));
        setTimeout(function(){
            window.location.replace('#/guest/logon');    
        },500);
        
    }

    $scope.getGoalByMenu = function(menu_type, years, months){
        var params = {'menu_type' : menu_type, 'years': years, 'months': months};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('goal-mission/menu', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Goal = result.data.DATA;
                // console.log($scope.UserList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

}]);
