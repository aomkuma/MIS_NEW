angular.module('e-homework').controller('MainAccController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
	//console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    

    $scope.page_type = 'account-permission';
    $scope.getMenu = function(action, menu_type){
        var params = {'menu_type' : menu_type};
        HTTPService.clientRequest(action, params).then(function(result){
            console.log(result);
            $scope.MenuName = result.data.DATA.Menu;
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.getMenu('menu/get/type' ,$scope.page_type);

    $scope.loadUserList = function(action, keyword){
        var params = {'Username' : keyword};
        IndexOverlayFactory.overlayShow();
        HTTPService.loginRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.UserList = result.data.DATA.MISUserList;
                // console.log($scope.UserList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDate(new Date(date));
    }

    $scope.goUpdate = function(id){
        window.location.href = '#/account-permission/update/' + id;
    }

    $scope.loadUserList('mis/list/user', '');


});