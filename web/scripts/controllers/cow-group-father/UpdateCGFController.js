angular.module('e-homework').controller('UpdateCGFController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
    //console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'dairyfarming';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }
    $scope.ID = $routeParams.id;
    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));  
    $scope.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));      
    // console.log($scope.$parent.Menu);

    $scope.page_type = 'cow-group-father';
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
                    if($scope.UserRole[i].role == '1' && $scope.UserRole[i].actives == 'Y'){
                        $scope.Maker = true;
                    }
                }
                // console.log($scope.MasterGoalList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.Maker = false;
    $scope.getUserRole();
    
    $scope.loadCooperative = function(){
        var params = {'actives':'Y', 'RegionList':$scope.PersonRegion};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('cooperative/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Cooperative = result.data.DATA.List;
                if($scope.ID !== undefined && $scope.ID !== null){
                    $scope.loadData('cow-group-father/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadDairyFarming = function(type, parent_id){
        var params = {'type':type, 'parent_id' : parent_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/list/cow-group-father', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                if(type == 'MAIN'){
                    $scope.DairyFarmingList = result.data.DATA.List;
                }else{
                    $scope.SubDairyFarmingList = result.data.DATA.List;
                }
                // $scope.Cooperative = result.data.DATA.List;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadMasterGoalList = function(){
        var params = {'actives':'Y', 'menu_type' : 'ข้อมูลฝูงโคพ่อพันธุ์'};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('master-goal/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.MasterGoalList = result.data.DATA.List;
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.loadData = function(action, id){
        
        
        var params = {
            'cooperative_id' : $scope.Sperm.cooperative_id
            ,'months' : $scope.Sperm.months
            ,'years' : $scope.Sperm.years
        };
        if(id != null){
            params = {'id':id};
        }else{
            params = {
                'cooperative_id' : $scope.Sperm.cooperative_id
                ,'months' : $scope.Sperm.months
                ,'years' : $scope.Sperm.years
                ,'cow_group_name' : $scope.Sperm.cow_group_name
            };
        }
        
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK' && result.data.DATA.Data != null){
                $scope.Sperm = result.data.DATA.Data;
                $scope.Sperm.cooperative_id = parseInt($scope.Sperm.cooperative_id);
                $scope.SpermDetailList = $scope.Sperm.cowgroup_father_detail;

                console.log($scope.SpermDetailList);
                // load sub dar=iry farming
                // for(var i =0; i < $scope.SpermDetailList.length; i++){
                //     $scope.loadDairyFarming('CHILD', $scope.SpermDetailList[i].dairy_farming_id);
                // }
                IndexOverlayFactory.overlayHide();
            }else{
                if($scope.Sperm.id != ''){
                    $scope.Sperm.id = '';
                }
            }

            $scope.CowGroupName = $scope.Sperm.cow_group_name;
            $scope.CooperativeName = '';
            $scope.MonthName = '';
            $scope.YearName = '';
            // Get cooperative name
            for(var i=0; i < $scope.Cooperative.length; i++){
                if($scope.Sperm.cooperative_id == $scope.Cooperative[i].id){
                    $scope.CooperativeName = $scope.Cooperative[i].cooperative_name;
                }
            }

            for(var i=0; i < $scope.MonthList.length; i++){
                if($scope.Sperm.months == $scope.MonthList[i].monthValue){
                    $scope.MonthName = $scope.MonthList[i].monthText;
                }
            }

            for(var i=0; i < $scope.YearList.length; i++){
                if($scope.Sperm.years == $scope.YearList[i].yearText){
                    $scope.YearName = $scope.YearList[i].yearValue;
                }
            }
            
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Sperm, SpermDetailList){
        $scope.Saving = true;
        var params = {'Data' : Sperm, 'Detail' : SpermDetailList};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('cow-group-father/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                // if($scope.ID !== undefined && $scope.ID !== null){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/cow-group-father';///update/' + result.data.DATA.id;
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/cow-group-father';
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    $scope.getThaiDateTime = function(date){
        // console.log(date);
        return convertDateToFullThaiDate(new Date(date));
    }

    $scope.getThaiDateTimeFromString = function(date){
        console.log(date);
        if(date != undefined && date != null && date != ''){
            return convertSQLDateTimeToReportDateTime(date);
        }
    }

    $scope.goSearch = function(){
        $scope.Search = true;
        $scope.SpermDetailList = [];
        // $scope.setSperm();
        $scope.loadData('cow-group-father/get');
        $scope.$parent.getGoalByMenu('ข้อมูลฝูงโคพ่อพันธุ์', $scope.Sperm.years, $scope.Sperm.months);
        // $scope.SpermDetailList = [
        //     {
        //         'id':''
        //         ,'cow_group_id':1
        //         ,'cow_type_id':1
        //         ,'cow_item_id':1
        //         ,'beginning_period':17
        //         ,'beginning_period_total_values':1500000
        //         ,'total_born':1
        //         ,'total_born_values':10000
        //         ,'total_movein':1
        //         ,'total_movein_values':10000
        //         ,'total_buy':1
        //         ,'total_buy_values':10000
        //         ,'total_die':1
        //         ,'total_die_values':10000
        //         ,'total_sell':1
        //         ,'total_sell_values':10000
        //         ,'total_sell_carcass':1
        //         ,'total_sell_carcass_values':10000
        //         ,'total_moveout':1
        //         ,'total_moveout_values':10000
        //         ,'total_cutout':1
        //         ,'total_cutout_values':10000
        //         ,'last_period':13
        //         ,'last_period_total_values':980000
        //     }
        // ];
    }

    $scope.addSpermDetail = function(){
        var detail =
            {
                'id':''
                ,'cow_group_name':null
                ,'cow_type_id':null
                ,'cow_item_id':null
                ,'beginning_period':''
                ,'beginning_period_total_values':''
                ,'total_born':''
                ,'total_born_values':''
                ,'total_movein':''
                ,'total_movein_values':''
                ,'total_buy':''
                ,'total_buy_values':''
                ,'total_die':''
                ,'total_die_values':''
                ,'total_sell':''
                ,'total_sell_values':''
                ,'total_sell_carcass':''
                ,'total_sell_carcass_values':''
                ,'total_moveout':''
                ,'total_moveout_values':''
                ,'total_cutout':''
                ,'total_cutout_values':''
                ,'last_period':''
                ,'last_period_total_values':''
            }
        ;

        $scope.SpermDetailList.push(detail);
    }

    $scope.addSpermItem = function(index){
        var item = {'id':''
                    , 'cow-group-father_id':$scope.ID
                    , 'cow-group-father_detail_id':''
                    , 'item_type':null
                    , 'item_amount':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID};

        $scope.SpermDetailList[index].cow-group-father_item.push(item);
    }

    $scope.removeDetail = function(id, index){
        if(id == ''){
            $scope.SpermDetailList.splice(index, 1);
        }else{
        $scope.alertMessage = 'ข้อมูลจะถูกลบจากระบบทันที<br>ต้องการลบรายการนี้ ใช่หรือไม่ ?';
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
                HTTPService.clientRequest('cow-group-father/delete/detail', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.SpermDetailList.splice(index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.removeItem = function(id, parent_index, child_index){
        if(id == ''){
            $scope.SpermDetailList[parent_index].cow-group-father_item.splice(child_index, 1);
        }else{

            $scope.alertMessage = 'ข้อมูลจะถูกลบจากระบบทันที<br>ต้องการลบรายการนี้ ใช่หรือไม่ ?';
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
                HTTPService.clientRequest('cow-group-father/delete/item', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.SpermDetailList[parent_index].cow-group-father_item.splice(child_index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.setSperm = function(){
        $scope.Sperm = {
            'id':''
            , 'cow_group_name' : 'ฝูงโคพ่อพันธุ์'
            , 'cooperative_id':null
            , 'region_id':null
            , 'months':curDate.getMonth() + 1
            , 'years':curDate.getFullYear()
            , 'create_date':''
            , 'update_date':''
        };    
    }

    $scope.approve = function(Data, OrgType){
        $scope.alertMessage = 'ต้องการอนุมัติข้อมูลนี้ ใช่หรือไม่ ?';
        var modalInstance = $uibModal.open({
            animation : false,
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

            var params = {'id' : Data.id, 'OrgType' : OrgType, 'ApproveStatus' : 'approve'};
            HTTPService.uploadRequest('cow-group-father/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/cow-group-father';///update/' + Data.id;
                }else{
                    alert(result.data.DATA);
                }
                IndexOverlayFactory.overlayHide();
            });
        });
    }

    $scope.ApproveComment = '';
    $scope.reject = function(Data, OrgType){
        $scope.alertMessage = 'ไม่ต้องการอนุมัติข้อมูลนี้ ใช่หรือไม่ ?';
        $scope.ApproveComment = '';
        var modalInstance = $uibModal.open({
            animation : false,
            templateUrl : 'reject_dialog.html',
            size : 'md',
            scope : $scope,
            backdrop : 'static',
            controller : 'ModalDialogReturnFromOKBtnCtrl',
            resolve : {
                params : function() {
                    return {};
                } 
            },
        });

        modalInstance.result.then(function (valResult) {
            console.log(valResult);
            var params = {'id' : Data.id, 'OrgType' : OrgType, 'ApproveStatus' : 'reject', 'ApproveComment' : valResult};
            HTTPService.uploadRequest('cow-group-father/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/cow-group-father';///update/' + Data.id;
                }else{
                    alert(result.data.DATA);
                }
                IndexOverlayFactory.overlayHide();
            });
        });
    }

    var curDate = new Date();
    
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    $scope.Search = false;
    $scope.SubDairyFarmingList = [];
    $scope.DairyFarmingList = [];
    $scope.SpermDetailList = [];
    $scope.SpermList = [
        {'id':1, 'name':'ฝูงโคต้นงวด'}
        ,{'id':2, 'name':'โคเพิ่ม'}
        ,{'id':3, 'name':'โคลด'}
        ,{'id':4, 'name':'ฝูงโคปลายงวด'}
        ,{'id':5, 'name':'การจำหน่ายโค'}
    ];
    $scope.CowGroupList = [
            {'id':1, 'name':'ฝูงโค 1'}
            ,{'id':2, 'name':'ฝูงโค 2'}
            ];
    $scope.CowTypeList = [
            {'id':1, 'name':'ประเภท 1'}
            ,{'id':2, 'name':'ประเภท 2'}
            ];
    $scope.CowItemList = [
            {'id':1, 'name':'โคอายุ 1-12 เดือน'}
            ,{'id':2, 'name':'ประเภท 2'}
            ];

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

    $scope.calcHeadValues = function(weight, price, values){
        values = weight * price;
        return parseFloat(values);
    }

    $scope.Sperm = {'cow_group_name' : 'ฝูงโคพ่อพันธุ์'};

    $scope.setSperm();
    $scope.loadCooperative();
    $scope.loadMasterGoalList();
    // $scope.loadDairyFarming('MAIN', '');
    // $scope.loadDairyFarming('CHILD', '');


});