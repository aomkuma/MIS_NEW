angular.module('e-homework').controller('UpdateCMController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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
                    $scope.loadData('cooperative-milk/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadDairyFarming = function(type, parent_id){
        var params = {'type':type, 'parent_id' : parent_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/list/cooperative-milk', params).then(function(result){
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

    $scope.loadData = function(action, id){

        
        
        var params = {
            'region_id' : $scope.Sperm.region_id
            ,'months' : $scope.Sperm.months
            ,'years' : $scope.Sperm.years
        };
        if(id != null){
            params = {'id':id};
        }else{
            params = {
                'region_id' : $scope.Sperm.region_id
                ,'months' : $scope.Sperm.months
                ,'years' : $scope.Sperm.years
            };
        }
        
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK' && result.data.DATA.Data != null){
                $scope.Sperm = result.data.DATA.Data;
                $scope.Sperm.region_id = parseInt($scope.Sperm.region_id);
                $scope.SpermDetailList = $scope.Sperm.cooperative_milk_detail;
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
            $scope.CooperativeName = '';
            $scope.MonthName = '';
            $scope.YearName = '';
            // Get cooperative name
            for(var i=0; i < $scope.PersonRegion.length; i++){
                if($scope.Sperm.region_id == $scope.PersonRegion[i].RegionID){
                    $scope.CooperativeName = $scope.PersonRegion[i].RegionName;
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
        HTTPService.clientRequest('cooperative-milk/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                // if($scope.ID !== undefined && $scope.ID !== null){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/cooperative-milk';///update/' + result.data.DATA.id;
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/cooperative-milk';
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
        $scope.loadData('cooperative-milk/get');
        $scope.$parent.getGoalByMenu('สหกรณ์และปริมาณน้ำนม', $scope.Sperm.years, $scope.Sperm.months);
        // $scope.SpermDetailList = [
        //     {
        //         'id':''
        //         ,'cooperative_id':1
        //         ,'member_id':2
        //         ,'total_person':'200'
        //         ,'total_person_sent':'150'
        //         ,'total_cow':'400'
        //         ,'total_cow_beeb':230
        //         ,'milk_amount':230
        //         ,'total_values':230
        //         ,'average_values':230
        //     }
        // ];
    }

    $scope.addSpermDetail = function(){
        var detail =
            {
                'id':''
                ,'cooperative_id':null
                ,'member_id':''
                ,'total_person':''
                ,'total_person_sent':''
                ,'total_cow':''
                ,'total_cow_beeb':''
                ,'milk_amount':''
                ,'total_values':''
                ,'average_values':''
            }
        ;

        $scope.SpermDetailList.push(detail);
    }

    $scope.addSpermItem = function(index){
        var item = {'id':''
                    , 'cooperative-milk_id':$scope.ID
                    , 'cooperative-milk_detail_id':''
                    , 'item_type':null
                    , 'item_amount':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID};

        $scope.SpermDetailList[index].cooperative-milk_item.push(item);
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
                HTTPService.clientRequest('cooperative-milk/delete/detail', params).then(function(result){
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
            $scope.SpermDetailList[parent_index].cooperative-milk_item.splice(child_index, 1);
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
                HTTPService.clientRequest('cooperative-milk/delete/item', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.SpermDetailList[parent_index].cooperative-milk_item.splice(child_index, 1);
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
            , 'months':($scope.currentDay.getMonth() + 1)
            , 'years':$scope.currentDay.getFullYear()
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
            HTTPService.uploadRequest('cooperative-milk/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/cooperative-milk';//    /update/' + Data.id;
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
            HTTPService.uploadRequest('cooperative-milk/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/cooperative-milk';///update/' + Data.id;
                }else{
                    alert(result.data.DATA);
                }
                IndexOverlayFactory.overlayHide();
            });
        });
    }

    $scope.checkDuplicateCo = function(spermDetail, index){
        if(spermDetail.cooperative_id != null && spermDetail.cooperative_id != ''){
            for(var i = 0; i < $scope.SpermDetailList.length; i++){
                spermDetail.cooperative_id == $scope.SpermDetailList[i].cooperative_id
                if(spermDetail.cooperative_id == $scope.SpermDetailList[i].cooperative_id && index != i){
                    alert('สหกรณ์ดังกล่าวได้ถูกเลือกไว้แล้ว');
                    spermDetail.cooperative_id = null;
                }
            }
        }
    }
    
    $scope.currentDay = new Date();
    
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    $scope.Search = false;
    $scope.SubDairyFarmingList = [];
    $scope.DairyFarmingList = [];
    $scope.SpermDetailList = [];
    $scope.SpermList = [
        {'id':'1', 'name':'หญ้าแห้ง'}
        ,{'id':'2', 'name':'อาหาร TMR'}
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
    $scope.loadCooperative();
    // $scope.loadDairyFarming('MAIN', '');
    // $scope.loadDairyFarming('CHILD', '');


});