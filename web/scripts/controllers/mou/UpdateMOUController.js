angular.module('e-homework').controller('UpdateMOUController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
	//console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session')); 

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

    $scope.loadCooperative = function(region_id){
        var params = {'actives':'Y', 'RegionList' : $scope.$parent.PersonRegion, 'condition' : {'region_id' : region_id} };
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

                $scope.avgList = $scope.Data.mou_avg;

                $scope.HistoryList = $scope.Data.mou_histories;

                for(var i = 0; i < $scope.avgList.length; i++){
                    $scope.avgIDList.push({'id':$scope.avgList[i].id});

                }
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
        
        
        var params = {'Data' : Data, 'avgList' : $scope.avgList};
        IndexOverlayFactory.overlayShow();
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

    $scope.avgData = function(Data){
        $scope.AVGAction = true;
        
        $scope.avgList = [];
        $scope.Data.mou_amount = 0;
        var avgAmount = parseFloat(Data.amount) / 12;
        avgAmount = parseFloat(avgAmount.toFixed(2));
        var month = 10;
        var year = parseInt(Data.years) - 1;
        for(var i = 0; i < 12; i++){

            // Create Date from years
            if(month > 12){
                month = 1;
                year += 1;
            }
            var dateStr = year + '-' + padLeft(""+(month), '00') + '-01';
            // console.log(dateStr);
            // var curDate = new Date(dateStr);
            
            var avgData = {
                'id':($scope.avgIDList[i] === undefined?'':$scope.avgIDList[i].id)
                , 'mou_id':''
                , 'amount' : null
                , 'avg_date':dateStr
            };

            $scope.avgList.push(avgData);
            month++;
            $scope.Data.mou_amount += avgAmount;
        }

        $scope.Data.mou_amount = 0;//parseFloat($scope.Data.mou_amount.toFixed(2));
        
        // console.log($scope.avgList);
    }

    $scope.reCalcAmount = function(){
        $scope.Data.mou_amount = 0;
        var loop = $scope.avgList.length;
        for(var i = 0; i < loop; i++){
            if($scope.avgList[i].amount != null){
                $scope.Data.mou_amount += parseFloat($scope.avgList[i].amount);
            }
        }
        // $scope.Data.mou_amount = ($scope.Data.mou_amount.toFixed(2));
        console.log('Total amount : ', $scope.Data.mou_amount);
    }

    $scope.getMonthYearText = function(dateStr){
        if(dateStr != null && dateStr != '' && dateStr != '0000-00-00'){
            return getMonthYearText(dateStr);
        }else{
            return '';
        }
        
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

    var curDate = new Date();

    $scope.Data = {
        'id':''
        , 'cooperative_id':null
        , 'years':curDate.getFullYear()
        , 'mou_amount':''
        , 'start_date':''
        , 'end_date':''
        , 'create_date':''
        , 'update_date':''
    };
    $scope.avgList = [];
    $scope.avgIDList = [];

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

    $scope.avgData($scope.Data);
    $scope.loadCooperative('');
    if($scope.ID !== undefined && $scope.ID !== null){
        $scope.loadData('mou/get', $scope.ID);
    }

});