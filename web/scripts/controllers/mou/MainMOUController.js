angular.module('e-homework').controller('MainMOUController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
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

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    

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
        var params = {'actives':'Y', 'RegionList' : $scope.$parent.PersonRegion, 'condition' : $scope.condition };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('cooperative/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Cooperative = result.data.DATA.List;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadList = function(action){
        var params = {'condition' : $scope.condition};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.List = result.data.DATA.DataList;
                console.log($scope.List);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    $scope.goUpdate = function(id){
        window.location.href = '#/mou/update/' + id;
    }

    $scope.goUpdateMOU = function(id){
        window.location.href = '#/mou/ex/update/' + id;
    }

    $scope.getRegionName = function(region_id){
        switch(region_id){
            case 1 : return 'อ.ส.ค. สำนักงานใหญ่ มวกเหล็ก';
            case 2 : return 'อ.ส.ค. สำนักงานกรุงเทพฯ Office';
            case 3 : return 'อ.ส.ค. สำนักงานภาคกลาง';
            case 4 : return 'อ.ส.ค. ภาคใต้ (ประจวบคีรีขันธ์)';
            case 5 : return 'อ.ส.ค. ภาคตะวันออกเฉียงเหนือ (ขอนแก่น)';
            case 6 : return 'อ.ส.ค. ภาคเหนือตอนล่าง (สุโขทัย)';
            case 7 : return 'อ.ส.ค. ภาคเหนือตอนบน (เชียงใหม่)';
            default : return '';
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
        return parseFloat(num).toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    $scope.YearList = getYearList(20);
    console.log($scope.YearList);
    $scope.condition = {'region_id' : null, 'years' : null};
    var curDate = new Date();
    var FiscalYear = curDate.getFullYear();
    var curMonth = curDate.getMonth() + 1;

    if(curMonth > 9){
        FiscalYear += 1;
    }
    console.log(FiscalYear);
    $scope.condition.years = FiscalYear;
    $scope.loadList('mou/list');

    
});