angular.module('e-homework').controller('MainLWSController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
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

    $scope.page_type = 'lost-wait-sale';
    $scope.getMenu = function(action, menu_type){
        var params = {'menu_type' : menu_type};
        HTTPService.clientRequest(action, params).then(function(result){
            console.log(result);
            $scope.MenuName = result.data.DATA.Menu;
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.getMenu('menu/get/type' ,$scope.page_type); 

    $scope.getUserRole = function(){
        var params = {'UserID' : $scope.currentUser.UserID};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('account-permission/get', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.UserRole = result.data.DATA.Role;
                for(var i =0; i < $scope.UserRole.length; i++){
                    if($scope.UserRole[i].role == '2' && $scope.UserRole[i].actives == 'Y'){
                        $scope.Approver = true;
                        $scope.loadApproveList();
                    }
                }
                // console.log($scope.MasterGoalList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.Approver = false;
    $scope.getUserRole();

    $scope.loadApproveList = function(){
        
      //  IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('lost-wait-sale/list/approve', null).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.ApproveList = result.data.DATA.DataList;
                // console.log($scope.List);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadFactoryList = function(){
        
      var params = {'region' : $scope.PersonRegion};
        HTTPService.clientRequest('factory/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.FactoryList = result.data.DATA.DataList;
                // console.log($scope.List);
                $scope.condition.Factory = $scope.FactoryList[0].id;
                $scope.loadList('lost-wait-sale/list/main');
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.showApproveList = function(){
        var modalInstance = $uibModal.open({
            animation : false,
            templateUrl : 'approve_list_dialog.html',
            size : 'lg',
            scope : $scope,
            backdrop : 'static',
            controller : 'ModalDialogReturnFromOKBtnCtrl',
            resolve : {
                params : function() {
                    return {};
                } 
            },
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
        $scope.CurYear = $scope.condition.YearTo + 543;
        $scope.LastYear = $scope.CurYear - 1;
        var params = {
            'condition' : $scope.condition
            , 'region' : $scope.PersonRegion
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.List = result.data.DATA.DataList;
                $scope.SummaryData = result.data.DATA.Summary;
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
        window.location.href = '#/lost-wait-sale/update/' + id;
    }

    $scope.goSearch = function(){
        $scope.ViewType = 'MAIN';
        $scope.loadList('lost-wait-sale/list/main');
    }


    $scope.viewDetail = function(){
        $scope.ViewType = 'DETAIL';
        console.log($scope.DetailList);
    }
    $scope.exportReport = function(data,condition){
       // console.log(DetailList, $scope.data_description);
        // return;
        IndexOverlayFactory.overlayHide();
        var params = {
            'data' : data
           , 'condition' : condition
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('lost-wait-sale/report', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                window.location.href="../" + result.data.DATA;
            }
            IndexOverlayFactory.overlayHide();
        });
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

    $scope.Header = [];
    $scope.ViewType = 'MAIN';
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    var curDate = new Date();
    $scope.condition = {
                        'Factory':''
                        ,'DisplayType':'monthly'
                        ,'MonthFrom' : curDate.getMonth() + 1
                        ,'YearFrom': curDate.getFullYear()
                        ,'MonthTo' : curDate.getMonth() + 1
                        ,'YearTo': curDate.getFullYear()
                        ,'QuarterFrom':'1'
                        ,'QuarterTo':'4'
                    };

    $scope.SummaryData = {
                    'SummaryCurrentMineralAmount':'240000'
                    ,'SummaryCurrentMineralIncome':'10245000'
                    ,'SummaryMineralAmountPercentage':'15'
                    ,'SummaryMineralIncomePercentage':'11.21'
                    };

    $scope.ResultYearList = [
                {'years' : (curDate.getFullYear() + 543)}
                ,{'years' : (curDate.getFullYear() + 543) - 1}
            ];

    // $scope.loadList('lost-wait-sale/list', '');
    IndexOverlayFactory.overlayHide();

    // Dummy Data
    $scope.List = [
        {
            'RegionName':'ฝ.สส'
            ,'SpermName':'หญ้าแห้ง'
            ,'CurrentAmount':2000
            ,'CurrentBaht':2000000
            ,'BeforeAmount':1980
            ,'BeforeBaht':1920000
            ,'DiffAmount':20
            ,'DiffAmountPercentage':2
            ,'DiffBaht':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'สภต.'
            ,'SpermName':'หญ้าสด'
            ,'CurrentAmount':3000
            ,'CurrentBaht':3000000
            ,'BeforeAmount':2970
            ,'BeforeBaht':2920000
            ,'DiffAmount':30
            ,'DiffAmountPercentage':2.1
            ,'DiffBaht':900000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'สภต.'
            ,'SpermName':'อาหาร TMR '
            ,'CurrentAmount':3000
            ,'CurrentBaht':3000000
            ,'BeforeAmount':2970
            ,'BeforeBaht':2920000
            ,'DiffAmount':30
            ,'DiffAmountPercentage':2.1
            ,'DiffBaht':900000
            ,'DiffBahtPercentage':2
        }
    ];

    $scope.Item = [
        {
            'label':'จำหน่ายน้ำแช่แข็งผ่านกระบวนการ'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                ]
        }
        ,{
            'label':'จำหน่ายน้ำแช่แข็งไม่ผ่านกระบวนการ'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                ]
        }
        ,{
            'label':'จำหน่ายไนโตรเจนเหลว'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                ]
        }
        ,{
            'label':'จำหน่ายวัสดุผสมเทียมและอื่นๆ'
            ,'unit' : [
                    {'label':''}
                ]
        }
    ];

    $scope.ItemUnit = [
        {'label':'หลอด'}
        ,{'label':'มูลค่า (บาท)'}
        ,{'label':'หลอด'}
        ,{'label':'มูลค่า (บาท)'}
        ,{'label':'หลอด'}
        ,{'label':'มูลค่า (บาท)'}
        ,{'label':'บาท'}
        
    ];

    $scope.DetailList = [
        {
            'RegionName':'มิตรภาพ'
            ,'ValueList':[
                {'values':'0'}
                ,{'values':'0'}
                ,{'values':'1'}
                ,{'values':'12000'}
                ,{'values':'10'}
                ,{'values':'2400'}
                ,{'values':'50500'}
            ]
        },
        {
            'RegionName':'บกระดาน'
            ,'ValueList':[
                {'values':'2'}
                ,{'values':'4800'}
                ,{'values':'1'}
                ,{'values':'12000'}
                ,{'values':'10'}
                ,{'values':'2400'}
                ,{'values':'50500'}
            ]
        }
        ,
        {
            'RegionName':'ลำพญาลาง'
            ,'ValueList':[
                {'values':'2'}
                ,{'values':'4800'}
                ,{'values':'1'}
                ,{'values':'12000'}
                ,{'values':'10'}
                ,{'values':'2400'}
                ,{'values':'50500'}
            ]
        }
    ];

    $scope.DetailSummary = [
        {'values':'4'}
        ,{'values':'9600'}
        ,{'values':'3'}
        ,{'values':'36000'}
        ,{'values':'30'}
        ,{'values':'7200'}
        ,{'values':'151500'}
    ];

    $scope.loadFactoryList();
    

});