angular.module('e-homework').controller('MainBEBController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
    //console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'industry';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    
    $scope.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));   

    $scope.page_type = 'begin-ending-balance';
    $scope.getMenu = function(action, menu_type){
        var params = {'menu_type' : menu_type};
        HTTPService.clientRequest(action, params).then(function(result){
            console.log(result);
            $scope.MenuName = result.data.DATA.Menu;
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.getMenu('menu/get/type' ,$scope.page_type);
     
    $scope.loadFactoryList = function(){
        
      var params = {'region' : $scope.PersonRegion};
        HTTPService.clientRequest('factory/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.FactoryList = result.data.DATA.DataList;
                // console.log($scope.List);
                // $scope.condition.Factory = $scope.FactoryList[0].id;
                $scope.loadList('begin-ending-balance/get');
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Data){
        var params = {'Data' : Data, 'condition' : $scope.condition};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('begin-ending-balance/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                alert('บันทึกสำเร็จ');
                location.reload();    
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.getMonthName = function(m){
        var monthTxt = '';
        switch(parseInt(m)){
            case 1 : monthTxt = 'มกราคม';break;
            case 2 : monthTxt = 'กุมภาพันธ์';break;
            case 3 : monthTxt = 'มีนาคม';break;
            case 4 : monthTxt = 'เมษายน';break;
            case 5 : monthTxt = 'พฤษภาคม';break;
            case 6 : monthTxt = 'มิถุนายน';break;
            case 7 : monthTxt = 'กรกฎาคม';break;
            case 8 : monthTxt = 'สิงหาคม';break;
            case 9 : monthTxt = 'กันยายน';break;
            case 10 : monthTxt = 'ตุลาคม';break;
            case 11 : monthTxt = 'พฤศจิกายน';break;
            case 12 : monthTxt = 'ธันวาคม';break;
        }
        return monthTxt;
    }

    $scope.getShortDateTime = function(d){
        return convertSQLDateTimeToReportDateTime(d);
    }

    $scope.loadList = function(action){
        var params = {
            'condition' : $scope.condition
            , 'region' : $scope.FactoryList
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Data = result.data.DATA;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    $scope.goUpdate = function(data){
        //window.location.href = '#/lost-in-process/update/' + id;
        $scope.EditData = true;
    }

    $scope.cancelUpdate = function(data){
        //window.location.href = '#/lost-in-process/update/' + id;
        $scope.EditData = false;
    }

    

    $scope.goSearch = function(){
        $scope.ViewType = 'MAIN';
        $scope.loadList('begin-ending-balance/get');
    }

    $scope.getFactoryName = function(factory){
        if($scope.FactoryList != null){
            for(var i=0; i < $scope.FactoryList.length; i++){
                if(factory == $scope.FactoryList[i].id){
                    return $scope.FactoryList[i].factory_name;
                }
            }
        }
    }

    $scope.numberFormat = function(num){
        if(num == null){
            return '';
        }
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    $scope.numberFormatComma = function(num){
        if(num == null){
            return '';
        }
        return num.toFixed(4).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    $scope.EditData = false;
    $scope.ViewType = 'MAIN';
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    var curDate = new Date();
    $scope.condition = {
                        'DisplayType':'monthly'
                        ,'months' : curDate.getMonth() + 1
                        ,'years': curDate.getFullYear()
                    };

    // $scope.loadList('lost-in-process/list', '');
    IndexOverlayFactory.overlayHide();

    $scope.loadFactoryList();
    

});