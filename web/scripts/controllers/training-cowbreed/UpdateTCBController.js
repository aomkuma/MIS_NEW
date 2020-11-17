angular.module('e-homework').controller('UpdateTCBController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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

    $scope.page_type = 'training-cowbreed';
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
                    $scope.loadData('training-cowbreed/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadDairyFarming = function(type, parent_id){
        var params = {'type':type, 'parent_id' : parent_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/list/training-cowbreed', params).then(function(result){
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
        var params = {'actives':'Y', 'menu_type' : 'ฝึกอบรม'};
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
            'months' : $scope.Sperm.months
            ,'years' : $scope.Sperm.years
        };
        if(id != null){
            params = {'id':id};
        }else{
            params = {
                'months' : $scope.Sperm.months
                ,'years' : $scope.Sperm.years
            };
        }
        
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK' && result.data.DATA.Data != null){
                $scope.Sperm = result.data.DATA.Data;
                $scope.Sperm.cooperative_id = parseInt($scope.Sperm.cooperative_id);
                $scope.SpermDetailList = $scope.Sperm.training_cow_breed_detail;
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
            $scope.MonthName = '';
            $scope.YearName = '';
            // Get cooperative name
            
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
        HTTPService.clientRequest('training-cowbreed/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                // if($scope.ID !== undefined && $scope.ID !== null){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/training-cowbreed';///update/' + result.data.DATA.id;
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/training-cowbreed';
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
        $scope.loadData('training-cowbreed/get');
        $scope.$parent.getGoalByMenu('ฝึกอบรม', $scope.Sperm.years, $scope.Sperm.months);
        // $scope.SpermDetailList = [
        //     {
        //         'id':''
        //         ,'training-cowbreed_id':'1'
        //         ,'amount':'2'
        //         ,'price':'200'
        //         ,'values':'400'
        //     },
        //     {
        //         'id':''
        //         ,'training-cowbreed_id':'2'
        //         ,'amount':'4'
        //         ,'price':'400'
        //         ,'values':'1600'
        //     }
        // ];
    }

    $scope.addSpermDetail = function(){
        var detail = {
                'id':''
                ,'training_cowbreed_id':''
                ,'training_cowbreed_type_id':null
                ,'amount':''
                ,'values':''
                , 'create_date':''
                , 'update_date':''
            };

        $scope.SpermDetailList.push(detail);
    }

    $scope.addSpermItem = function(index){
        var item = {'id':''
                    , 'training-cowbreed_id':$scope.ID
                    , 'training-cowbreed_detail_id':''
                    , 'item_type':null
                    , 'item_amount':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID};

        $scope.SpermDetailList[index].training-cowbreed_item.push(item);
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
                HTTPService.clientRequest('training-cowbreed/delete/detail', params).then(function(result){
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
            $scope.SpermDetailList[parent_index].training-cowbreed_item.splice(child_index, 1);
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
                HTTPService.clientRequest('training-cowbreed/delete/item', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.SpermDetailList[parent_index].training-cowbreed_item.splice(child_index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.setSperm = function(){
        $scope.Sperm = {
            'id':''
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
            HTTPService.uploadRequest('training-cowbreed/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/training-cowbreed';///update/' + Data.id;
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
            HTTPService.uploadRequest('training-cowbreed/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/training-cowbreed';///update/' + Data.id;
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
        {'id':1, 'name':'หลังสูตรการเลียงโคนม'}
        ,{'id':2, 'name':'การใช้เครื่องรีดนม'}
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

    $scope.setSperm();
    $scope.loadData('training-cowbreed/get', $scope.ID);
    $scope.loadMasterGoalList();
    // $scope.loadCooperative();
    // $scope.loadDairyFarming('MAIN', '');
    // $scope.loadDairyFarming('CHILD', '');


});