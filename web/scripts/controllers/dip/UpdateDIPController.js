angular.module('e-homework').controller('UpdateDIPController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
    //console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'dip';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }
    $scope.ID = $routeParams.id;
    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    
    console.log($scope.$parent.Menu);

    $scope.page_type = 'dip';
    $scope.getMenu = function(action, menu_type){
        var params = {'menu_type' : menu_type};
        HTTPService.clientRequest(action, params).then(function(result){
            console.log(result);
            $scope.MenuName = result.data.DATA.Menu;
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.getMenu('menu/get/type' ,$scope.page_type);

    $scope.loadData = function(action, id){
        var params = {
            'id' : id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Data = result.data.DATA.Data;
                $scope.Data.join_date = makeDate($scope.Data.join_date);
                $scope.Data.pass_date = makeDate($scope.Data.pass_date);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Data){
        var params = {'Data' : Data};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dip/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                // if($scope.ID !== undefined && $scope.ID !== null){
                    window.location.href = '#/page/dip';
                // }else{
                //     location.reload(); 
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/page/dip';
    }

    $scope.popup1 = {
        opened: false
    };

    $scope.popup2 = {
        opened: false
    };

    $scope.open1 = function() {
        $scope.popup1.opened = true;
    };

    $scope.open2 = function() {
        $scope.popup2.opened = true;
    };

    $scope.Data = {
        'id':''
        , 'years' : null
        , 'dip_name':''
        , 'dip_status':null
        , 'create_date':''
        , 'update_date':''
        , 'create_by' : $scope.currentUser.Username
    };

    $scope.YearList = getYearList(20);

    if($scope.ID !== undefined && $scope.ID !== null){
        $scope.loadData('dip/get', $scope.ID);
    }

});