angular.module('e-homework').controller('UpdateDFController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
	//console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }
    $scope.ID = $routeParams.id;
    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session')); 
    $scope.$parent.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));      
    // console.log($scope.$parent.Menu);

    $scope.page_type = 'dairy-farming';
    $scope.getMenu = function(action, menu_type){
        var params = {'menu_type' : menu_type};
        HTTPService.clientRequest(action, params).then(function(result){
            console.log(result);
            $scope.MenuName = result.data.DATA.Menu;
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.getMenu('menu/get/type' ,$scope.page_type);
    
    $scope.loadParentList = function(action){
        var params = {'actives' : 'Y'};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.ParentList = result.data.DATA.List;

                if($scope.ID !== undefined && $scope.ID !== null){
                    $scope.loadData('dairy-farming/get', $scope.ID);
                }

                // console.log($scope.MasterGoalList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadData = function(action, id){
        var params = {
            'id' : id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Data = result.data.DATA.Data;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Data, AvgList){
        var params = {'Data' : Data, 'AvgList' : AvgList};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                // if($scope.ID !== undefined && $scope.ID !== null){
                    // window.location.href = '#/dairy-farming/update/' + result.data.DATA.id;
                    window.location.href = '#/dairy-farming';
                // }else{
                //     location.reload(); 
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/dairy-farming';
    }

    $scope.Data = {
        'id':''
        , 'dairy_farming_type':''
        , 'dairy_farming_name':''
        , 'parent_id':null
        , 'actives':'Y'
        , 'create_date':''
        , 'update_date':''
    };

    $scope.loadParentList('dairy-farming/list/parent');

});