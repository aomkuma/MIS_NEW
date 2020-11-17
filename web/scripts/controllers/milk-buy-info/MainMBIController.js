angular.module('e-homework').controller('MainMBIController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
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

    $scope.page_type = 'milk-buy-info';
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
        HTTPService.clientRequest('travel/list/approve', null).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.ApproveList = result.data.DATA.DataList;
                // console.log($scope.List);
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

        if($scope.condition.DisplayType == 'monthly'){
            action = 'mbi/list/milk-buy-info/month';
        }else if($scope.condition.DisplayType == 'quarter'){
            action = 'mbi/list/milk-buy-info/quarter';
        }else if($scope.condition.DisplayType == 'annually'){
            action = 'mbi/list/milk-buy-info/year';
        }
        $scope.CurYear = $scope.condition.YearTo + 543;
        $scope.LastYear = $scope.CurYear - 1;
        console.log($scope.condition.DisplayType, $scope.CurYear, $scope.LastYear);
        var params = {
            'condition' : $scope.condition
            , 'region' : $scope.PersonRegion
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.List = result.data.DATA.List;
                $scope.SummaryData = result.data.DATA.Summary;
                console.log($scope.List);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadListDetail = function(action, data){
        $scope.CurYear = $scope.condition.YearTo + 543;
        $scope.LastYear = $scope.CurYear - 1;
        var params = {
            'condition' : data
            , 'region' : $scope.PersonRegion
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.DetailList = result.data.DATA.List;
                $scope.DetailSummary =  result.data.DATA.Summary;
                console.log($scope.DetailList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadListMOU = function(action){

        // if($scope.condition.DisplayType == 'monthly'){
        //     action = 'mbi/list/milk-buy-info/mou/month';
        // }else if($scope.condition.DisplayType == 'quarter'){
        //     action = 'mbi/list/milk-buy-info/mou/month/quarter';
        // }else if($scope.condition.DisplayType == 'annually'){
        //     action = 'mbi/list/milk-buy-info/mou/month/year';
        // }

        $scope.CurYear = $scope.condition.YearTo + 543;
        $scope.LastYear = $scope.CurYear - 1;
        var params = {
            'condition' : $scope.condition
            , 'region' : $scope.PersonRegion
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.MOUList = result.data.DATA.List;
                console.log($scope.DetailList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadListMOUDetail = function(action, condition){
        $scope.CurYear = $scope.condition.YearTo + 543;
        $scope.LastYear = $scope.CurYear - 1;
        var params = {
            'condition' : condition
            , 'region' : $scope.PersonRegion
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.MOUDetailList = result.data.DATA.List;
                
                console.log($scope.MOUDetailList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    $scope.getMonthName = function(month){
        return getThaiMonthInt(month);
    }

    $scope.goUpdate = function(id){
        window.location.href = '#/travel/update/' + id;
    }

    $scope.goSearch = function(){
        $scope.ViewType = 'MAIN';
         $scope.loadList('mbi/list/milk-buy-info');
    }


    $scope.viewDetail = function(RegionName, data){
        $scope.RegionName = RegionName;
        console.log(data);
        $scope.ViewType = 'DETAIL';
        $scope.loadListDetail('mbi/list/milk-buy-info/detail', data);
        // console.log($scope.DetailList);
    }

    $scope.viewMOU = function(RegionName){
        $scope.RegionName = RegionName;
        $scope.ViewType = 'MOU';
        $scope.loadListMOU('mbi/list/milk-buy-info/mou');
    }

    $scope.viewMOUDetail = function(condition, RegionName){
        $scope.RegionName = RegionName;
        $scope.ViewType = 'MOU-DETAIL';
        $scope.loadListMOUDetail('mbi/list/milk-buy-info/mou/detail', condition);
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
        HTTPService.clientRequest('travel/report', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                window.location.href="../" + result.data.DATA;
            }
            IndexOverlayFactory.overlayHide();
        });
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
        return num.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    $scope.Header = [];
    $scope.ViewType = 'MAIN';
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    var curDate = new Date();
    $scope.condition = {
                        'Region':null
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

    // $scope.loadList('travel/list', '');
    IndexOverlayFactory.overlayHide();

    // Dummy Data
    $scope.List = [
        {
            'RegionName':'ฝ.สส'
            ,'SpermName':'บุคคลทั่วไป (ผู้ใหญ่)'
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
            ,'SpermName':'บุคคลทั่วไป (เด็ก)'
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
            ,'SpermName':'นักศึกษา '
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
            'label':'จำนวนผู้เข้าชม (คน)'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                    ,{'label':''}
                ]
        }
        ,{
            'label':'ราคาต่อหน่วย (บาท)'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                    ,{'label':''}
                ]
        }
        ,{
            'label':'รวม (บาท)'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                    ,{'label':''}
                ]
        }
        ,{
            'label':'จำนวนผู้เข้าชมที่ยกเว้น'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                    ,{'label':''}
                ]
        }
    ];

    $scope.ItemUnit = [
        {'label':'ผู้ใหญ่'}
        ,{'label':'เด็ก'}
        ,{'label':'นักศึกษา'}
        ,{'label':'ผู้ใหญ่'}
        ,{'label':'เด็ก'}
        ,{'label':'นักศึกษา'}
        ,{'label':'ผู้ใหญ่'}
        ,{'label':'เด็ก'}
        ,{'label':'นักศึกษา'}
        ,{'label':'ผู้ใหญ่'}
        ,{'label':'เด็ก'}
        ,{'label':'นักศึกษา'}
        
    ];

    $scope.DetailList = [
        {
            'TravelDate':'2018-09-11'
            ,'Organization':'โรงเรียนสวนกุหลาบ'
            ,'Discount':'0'
            ,'ValueList':[
                {'values':'0'}
                ,{'values':'0'}
                ,{'values':'1'}
                ,{'values':'12000'}
                ,{'values':'10'}
                ,{'values':'2400'}
                ,{'values':'50500'}
                ,{'values':'0'}
                ,{'values':'0'}
                ,{'values':'0'}
                ,{'values':'0'}
                ,{'values':'0'}
            ]
        },
        {
            'TravelDate':'2018-09-11'
            ,'Organization':'โรงเรียนสวนกุหลาบ'
            ,'Discount':'0'
            ,'ValueList':[
                {'values':'2'}
                ,{'values':'4800'}
                ,{'values':'1'}
                ,{'values':'12000'}
                ,{'values':'10'}
                ,{'values':'2400'}
                ,{'values':'50500'}
                ,{'values':'0'}
                ,{'values':'0'}
                ,{'values':'0'}
                ,{'values':'0'}
                ,{'values':'0'}
            ]
        }
        ,
        {
            'TravelDate':'2018-09-11'
            ,'Organization':'โรงเรียนสวนกุหลาบ'
            ,'Discount':'0'
            ,'ValueList':[
                {'values':'2'}
                ,{'values':'4800'}
                ,{'values':'1'}
                ,{'values':'12000'}
                ,{'values':'10'}
                ,{'values':'2400'}
                ,{'values':'50500'}
                ,{'values':'0'}
                ,{'values':'0'}
                ,{'values':'0'}
                ,{'values':'0'}
                ,{'values':'0'}
            ]
        }
    ];

    $scope.DetailSummary = [
        {'values':''}
        ,{'values':'2'}
        ,{'values':'3'}
        ,{'values':'36000'}
        ,{'values':'30'}
        ,{'values':'7200'}
        ,{'values':'151500'}
        ,{'values':'0'}
        ,{'values':'0'}
        ,{'values':'0'}
        ,{'values':'0'}
        ,{'values':'0'}
        ,{'values':'0'}
        ,{'values':'0'}
    ];
    $scope.loadList('mbi/list/milk-buy-info');

});