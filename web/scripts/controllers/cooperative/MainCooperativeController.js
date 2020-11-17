angular.module('e-homework').controller('MainCooperativeController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
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

    $scope.page_type = 'cooperative';
    $scope.getMenu = function(action, menu_type){
        var params = {'menu_type' : menu_type};
        HTTPService.clientRequest(action, params).then(function(result){
            console.log(result);
            $scope.MenuName = result.data.DATA.Menu;
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.getMenu('menu/get/type' ,$scope.page_type);


    $scope.loadRegionList = function(){
        
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('cooperative/list/region', null).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.RegionList = result.data.DATA.List;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadList = function(action){
        var params = {'condition' : $scope.condition};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.List = result.data.DATA.List;
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
        window.location.href = '#/cooperative/update/' + id;
    }

    $scope.removeItem = function(id){
        
        $scope.alertMessage = 'ต้องการลบรายการนี้ ใช่หรือไม่ ?';
        var modalInstance = $uibModal.open({
            animation : true,
            templateUrl : 'views/dialog_confirm.html',
            size : 'sm',
            scope : $scope,
            backdrop : 'static',
            controller : 'ModalDialogCtrl',
            resolve : {
                params : function() {
                    return {};
                } 
            },
        });

        modalInstance.result.then(function (valResult) {
            IndexOverlayFactory.overlayShow();
            var params = {'id' : id};
            HTTPService.clientRequest('cooperative/delete', params).then(function(result){
                if(result.data.STATUS == 'OK'){
                    $scope.loadList('cooperative/list', '');
                }
                IndexOverlayFactory.overlayHide();
            });
        });
        
    }

    $scope.condition = {'region_id':null, 'cooperative_name' : ''};
    $scope.loadList('cooperative/list');
    $scope.loadRegionList();

});