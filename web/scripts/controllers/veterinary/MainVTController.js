angular.module('e-homework').controller('MainVTController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
	console.log('Hello veterinary !');
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

    $scope.page_type = 'veterinary';
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
        HTTPService.clientRequest('veterinary/list/approve', null).then(function(result){
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
        $scope.CurYear = $scope.condition.YearTo + 543;
        $scope.LastYear = $scope.CurYear - 1;
        var params = {
            'condition' : $scope.condition
            , 'region' : $scope.PersonRegion
        };
      //  IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.List = result.data.DATA.DataList;
                $scope.SummaryData = result.data.DATA.Summary;
                console.log($scope.List);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadListDetail = function(action, description){
        $scope.data_description = description;
        var params = {
            'condition' : $scope.condition
            , 'region' : $scope.PersonRegion
            , 'description' : description
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.DetailList = result.data.DATA.DetailList;
                $scope.CooperativeList = result.data.DATA.CooperativeList;
                // $scope.SummaryData = result.data.DATA.Summary;
                 console.log($scope.DetailList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadListSubDetail = function(action, cooperative_id, description){
        var params = {
            'condition' : $scope.condition
            , 'region' : $scope.PersonRegion
            , 'description' : description
            , 'cooperative_id' : cooperative_id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.DetailList = result.data.DATA.SubDetailList;
                $scope.Cooperative = result.data.DATA.Cooperative;
                $scope.Header =  result.data.DATA.MonthNameList;
                // $scope.SummaryData = result.data.DATA.Summary;
                // console.log($scope.List);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    $scope.goUpdate = function(id){
        window.location.href = '#/veterinary/update/' + id;
    }

    $scope.goApprove = function(id, month, year){
        window.location.href = '#/veterinary/update/' + id + '/' + month + '/' + year;
    }

    $scope.goSearch = function(){
        $scope.ViewType = 'MAIN';
        $scope.loadList('veterinary/list/main');
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

    $scope.viewDetail2 = function(cooperative_id, description){
        $scope.ViewType = 'DETAIL2';
        // console.log($scope.condition.DisplayType);
        if($scope.condition.DisplayType == 'quarter'){
            $scope.Header = [
                            {'month':'มกราคม'}
                            ,{'month':'กุมภาพันธ์'}
                            ,{'month':'มีนาคม'}
                            ];

            $scope.DetailList = [
            {'DairyFarmingName':'การรักษาโคป่วย'
            ,'BGColor':'#B6CCFF'
            ,'Data':[
               {
                    'ItemName' : 'สมาชิก '
                    ,'Unit':'คน'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'โคนม '
                    ,'Unit':'ตัว'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าเวชภัณฑ์ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าบริการ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                } 
            ]
        },
        {'DairyFarmingName':'การควบคุมโรค'
        ,'BGColor':'#B6CCFF'
        },
        {'DairyFarmingName':'วัณโรค/โรคแท้งติดต่อ'
            ,'BGColor':'#BBECA9'
            ,'Data':[
               {
                    'ItemName' : 'สมาชิก '
                    ,'Unit':'คน'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'โคนม '
                    ,'Unit':'ตัว'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าเวชภัณฑ์ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าบริการ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                } 
            ]
        },
        {'DairyFarmingName':'ปากเท้าเปื่อย'
            ,'BGColor':'#BBECA9'
            ,'Data':[
               {
                    'ItemName' : 'สมาชิก '
                    ,'Unit':'คน'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'โคนม '
                    ,'Unit':'ตัว'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าเวชภัณฑ์ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าบริการ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ]
                    ,'Summary':1245
                } 
            ]
        }
        ];
        }else if($scope.condition.DisplayType == 'monthly'){

            $scope.Header = [
                            {'month':'มกราคม'}
                            ];

            $scope.DetailList = [
            {'DairyFarmingName':'การรักษาโคป่วย'
            ,'BGColor':'#B6CCFF'
            ,'Data':[
               {
                    'ItemName' : 'สมาชิก '
                    ,'Unit':'คน'
                    ,'Dataset':[
                                {'Amount':150}
            ]}]}];

        }else if($scope.condition.DisplayType == 'annually'){
            $scope.Header = [
                            {'month':'มกราคม'}
                            ,{'month':'กุมภาพันธ์'}
                            ,{'month':'มีนาคม'}
                            ,{'month':'เมษายน'}
                            ,{'month':'พฤษภาคม'}
                            ,{'month':'มิถุนายน'}
                            ,{'month':'กรกฎาคม'}
                            ,{'month':'สิงหาคม'}
                            ,{'month':'กันยายน'}
                            ,{'month':'ตุลาคม'}
                            ,{'month':'พฤศจิกายน'}
                            ,{'month':'ธันวาคม'}
                            ];

            $scope.DetailList = [
            {'DairyFarmingName':'การรักษาโคป่วย'
            ,'BGColor':'#B6CCFF'
            ,'Data':[
               {
                    'ItemName' : 'สมาชิก '
                    ,'Unit':'คน'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'โคนม '
                    ,'Unit':'ตัว'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าเวชภัณฑ์ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าบริการ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                } 
            ]
        },
        {'DairyFarmingName':'การควบคุมโรค'
        ,'BGColor':'#B6CCFF'
        },
        {'DairyFarmingName':'วัณโรค/โรคแท้งติดต่อ'
            ,'BGColor':'#BBECA9'
            ,'Data':[
               {
                    'ItemName' : 'สมาชิก '
                    ,'Unit':'คน'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'โคนม '
                    ,'Unit':'ตัว'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าเวชภัณฑ์ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าบริการ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                } 
            ]
        },
        {'DairyFarmingName':'ปากเท้าเปื่อย'
            ,'BGColor':'#BBECA9'
            ,'Data':[
               {
                    'ItemName' : 'สมาชิก '
                    ,'Unit':'คน'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'โคนม '
                    ,'Unit':'ตัว'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าเวชภัณฑ์ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                },
                {
                    'ItemName' : 'ค่าบริการ '
                    ,'Unit':'บาท'
                    ,'Dataset':[
                                {'Amount':150}
                                ,{'Amount':200}
                                ,{'Amount':110}
                                ,{'Amount':97}
                                ,{'Amount':52}
                                ,{'Amount':121}
                                ,{'Amount':23}
                                ,{'Amount':48}
                                ,{'Amount':90}
                                ,{'Amount':87}
                                ,{'Amount':65}
                                ,{'Amount':117}
                                ]
                    ,'Summary':1245
                } 
            ]
        }
        ];
        }

        $scope.loadListSubDetail('veterinary/list/subdetail', cooperative_id, description);
    }

    $scope.viewDetail = function(region, description){
        $scope.RegionName = region;
        $scope.ViewType = 'DETAIL';
        $scope.description = description;
        $scope.loadListDetail('veterinary/list/detail', description);
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

    $scope.exportReport = function(DetailList, CooperativeList){
        console.log(DetailList, CooperativeList, $scope.data_description);
        // return;
        IndexOverlayFactory.overlayHide();
        $scope.condition['region_name'] = $scope.getRegionName($scope.data_description.region_id);
        var params = {

            'DetailList' : DetailList
            , 'CooperativeList' : CooperativeList
            , 'data_description' : $scope.data_description
            , 'condition' : $scope.condition
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('veterinary/report', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                window.location.href="../" + result.data.DATA;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.Header = [];
    $scope.ViewType = 'MAIN';
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    var curDate = new Date();
    $scope.condition = {
                        'DisplayType':'monthly'
                        ,'MonthFrom' : curDate.getMonth() + 1
                        ,'YearFrom': curDate.getFullYear()
                        ,'MonthTo' : curDate.getMonth() + 1
                        ,'YearTo': curDate.getFullYear()
                        ,'QuarterFrom':'1'
                        ,'QuarterTo':'4'
                    };

    $scope.SummaryData = {
                    'SummaryCurrentCow':''
                    ,'SummaryCurrentService':''
                    ,'SummaryCowPercentage':''
                    ,'SummaryServicePercentage':''
                    };

    $scope.ResultYearList = [
                {'years' : (curDate.getFullYear() + 543)}
                ,{'years' : (curDate.getFullYear() + 543) - 1}
            ];

    // $scope.loadList('veterinary/list', '');
    IndexOverlayFactory.overlayHide();
    console.log($scope.condition);
    setTimeout(function(){
        $scope.loadList('veterinary/list/main');
        
    },200);
    

});