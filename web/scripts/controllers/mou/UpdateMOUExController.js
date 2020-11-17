angular.module('e-homework').controller('UpdateMOUExController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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
    console.log($scope.$parent.Menu);

    $scope.page_type = 'mou';
    $scope.getMenu = function(action, menu_type){
        var params = {'menu_type' : menu_type};
        HTTPService.clientRequest(action, params).then(function(result){
            console.log(result);
            $scope.MenuName = result.data.DATA.Menu;
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.getMenu('menu/get/type' ,$scope.page_type);   

    $scope.loadCooperative = function(){
        var params = {'actives':'Y'};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('cooperative/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Cooperative = result.data.DATA.List;
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
                $scope.Data.mou_amount = parseFloat($scope.Data.mou_amount);
                $scope.Data.mou_value = parseFloat($scope.Data.mou_value);
                $scope.Data.start_date = makeDate($scope.Data.start_date);
                $scope.Data.end_date = makeDate($scope.Data.end_date);

                $scope.HistoryList = $scope.Data.mou_histories;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Data){
        if(Data.start_date !== null){
            Data.start_date = makeSQLDate(Data.start_date);    
        }
        if(Data.end_date !== null){
            Data.end_date = makeSQLDate(Data.end_date);
        }
        
        
        var params = {'Data' : Data};
        // IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('mou/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                // alert('save success');
                // if($scope.ID !== undefined && $scope.ID !== null){

                    // window.location.href = '#/mou/update/' + result.data.DATA.id;
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/mou/';
                // }else{
                //     location.reload();    
                // }
                
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/mou';
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    $scope.getThaiDateTime = function(date){
        // console.log(date);
        return convertDateToFullThaiDate(new Date(date));
    }


    $scope.Data = {
        'id':''
        , 'cooperative_id':null
        , 'years':null
        , 'mou_amount':''
        , 'start_date':''
        , 'end_date':''
        , 'create_date':''
        , 'update_date':''
    };

    $scope.YearList = getYearList(20);

    $scope.popup1 = {
        opened: false
    };
    $scope.open1 = function() {
        $scope.popup1.opened = true;
    };

    $scope.popup2 = {
        opened: false
    };
    $scope.open2 = function() {
        $scope.popup2.opened = true;
    };

    $scope.popup3 = {
        opened: false
    };
    $scope.open3 = function() {
        $scope.popup3.opened = true;
    };

    $scope.loadCooperative();
    if($scope.ID !== undefined && $scope.ID !== null){
        $scope.loadData('mou/get', $scope.ID);
    }

});