angular.module('e-homework').controller('MenuController', function($scope, $compile, $cookies, $filter, $state, HTTPService, IndexOverlayFactory) {
    IndexOverlayFactory.overlayShow();
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }
    console.log('Hello ! Menu page');
	$scope.DEFAULT_LANGUAGE = 'TH';
    $scope.$parent.menu_selected = 'menu';

    $scope.load = function(action){
        HTTPService.clientRequest(action, null).then(function(result){
            //console.log(result);
            $scope.Menu = result.data.DATA.Menu;
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.edit = function(id){
        window.location.href = '#/menu-manage/update/' + id;
    }

    $scope.PAGE = 'MAIN';
    $scope.load('menu/list/manage');

});