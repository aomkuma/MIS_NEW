angular.module('e-homework').controller('MainCMController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
    //console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'dairyfarming';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    
    $scope.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));   
    console.log($scope.$parent.Menu);
    $scope.page_type = 'cooperative-milk';
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
        HTTPService.clientRequest('cooperative-milk/list/approve', null).then(function(result){
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
        var params = {
            'condition' : $scope.condition
            , 'region' : $scope.PersonRegion
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.List = result.data.DATA.DataList;

                $scope.SummaryPerson = 0;
                $scope.SummaryPersonSent = 0;
                $scope.SummaryCow = 0;
                $scope.SummaryCowBeeb = 0;
                $scope.SummaryMilkAmount = 0;
                $scope.SummaryValues = 0;

                for(var i = 0; i < $scope.List.length; i++){
                    $scope.SummaryPerson += parseFloat($scope.List[i].TotalPerson); 
                    $scope.SummaryPersonSent += parseFloat($scope.List[i].TotalPersonSent); 
                    $scope.SummaryCow += parseFloat($scope.List[i].TotalCow); 
                    $scope.SummaryCowBeeb += parseFloat($scope.List[i].TotalCowBeeb); 
                    $scope.SummaryMilkAmount += parseFloat($scope.List[i].TotalMilkAmount); 
                    $scope.SummaryValues += parseFloat($scope.List[i].TotalValues); 
                }

                $scope.SummaryPerson = $scope.SummaryPerson.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ","); 
                $scope.SummaryPersonSent = $scope.SummaryPersonSent.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ","); 
                $scope.SummaryCow = $scope.SummaryCow.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ","); 
                $scope.SummaryCowBeeb = $scope.SummaryCowBeeb.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ","); 
                $scope.SummaryMilkAmount = $scope.SummaryMilkAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","); 
                $scope.SummaryValues = $scope.SummaryValues.toFixed(4);//.replace(/\B(?=(\d{3})+(?!\d))/g, ","); 

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
        window.location.href = '#/cooperative-milk/update/' + id;
    }

    $scope.goSearch = function(){
        $scope.ViewType = 'MAIN';
        $scope.loadList('cooperative-milk/list/main');
    }


    $scope.viewDetail = function(){
        $scope.ViewType = 'DETAIL';
        console.log($scope.DetailList);
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
        return num.toFixed(4).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    $scope.Header = [];
    $scope.ViewType = 'MAIN';
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    var curDate = new Date();
    $scope.condition = {
                        'Region':$scope.PersonRegion[0].RegionID
                        , 'DisplayType':'monthly'
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

    // $scope.loadList('cooperative-milk/list', '');
    IndexOverlayFactory.overlayHide();

    // Dummy Data
    $scope.List = [
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์'
            ,'TotalMilk':3000
            ,'TotalMilkSent':3000000
            ,'TotalMilkBeeb':2970
            ,'TotalMilkBeebSent':2920000
            ,'TotalMilkAmount':30
            ,'TotalValues':2.1
            ,'AverageValues':900000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'ฟาร์มโคมน 1962'
            ,'TotalMilk':3000
            ,'TotalMilkSent':3000000
            ,'TotalMilkBeeb':2970
            ,'TotalMilkBeebSent':2920000
            ,'TotalMilkAmount':30
            ,'TotalValues':2.1
            ,'AverageValues':900000
            ,'DiffBahtPercentage':2
        }
    ];

    $scope.DetailList = [
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'MemberName':'สหกรณ์ไทย-เดนมาร์ค มิตภาพ จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'MemberName':'สหกรณ์ไทย-เดนมาร์ค ปากช่อง จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'MemberName':'สหกรณ์ไทย-เดนมาร์ค ซับกระดาน จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'MemberName':'สหกรณ์ไทย-เดนมาร์ค พระพุทธบาท จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'MemberName':'สหกรณ์ไทย-เดนมาร์ค ลำพญากลาง จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        }
    ];

    $scope.DetailSummary = [
        {'values':'4000'}
        ,{'values':'9600'}
        ,{'values':'3932'}
        ,{'values':'36000'}
        ,{'values':'3099'}
        ,{'values':'7200'}
        ,{'values':'151500'}
    ];
    $scope.loadList('cooperative-milk/list/main');

});