angular.module('e-homework').controller('UpdateVTController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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
                    $scope.loadData('veterinary/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadDairyFarming = function(type, parent_id, index){
        var params = {'type':type, 'parent_id' : parent_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/list/veterinary', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                if(type == 'MAIN'){
                    $scope.DairyFarmingList[index] = result.data.DATA.List;
                }else{
                    $scope.SubDairyFarmingList[index] = result.data.DATA.List;
                }
                // $scope.Cooperative = result.data.DATA.List;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadData = function(action, id){

        
        
        var params = {
            'cooperative_id' : $scope.Veterinary.cooperative_id
            ,'months' : $scope.Veterinary.months
            ,'years' : $scope.Veterinary.years
        };
        if(id != null){
            params = {'id':id};
        }else{
            params = {
                'cooperative_id' : $scope.Veterinary.cooperative_id
                ,'months' : $scope.Veterinary.months
                ,'years' : $scope.Veterinary.years
            };
        }
        
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK' && result.data.DATA.Data != null){
                $scope.Veterinary = result.data.DATA.Data;
                $scope.Veterinary.cooperative_id = parseInt($scope.Veterinary.cooperative_id);
                $scope.VeterinaryDetailList = $scope.Veterinary.veterinary_detail;
                // load sub dar=iry farming
                for(var i =0; i < $scope.VeterinaryDetailList.length; i++){
                    $scope.loadDairyFarming('MAIN', '', i);
                    $scope.loadDairyFarming('CHILD', $scope.VeterinaryDetailList[i].dairy_farming_id, i);
                }
                IndexOverlayFactory.overlayHide();
            }else{
                if($scope.Veterinary.id != ''){
                    $scope.Veterinary.id = '';
                }
            }

            $scope.CooperativeName = '';
            $scope.MonthName = '';
            $scope.YearName = '';
            // Get cooperative name
            for(var i=0; i < $scope.Cooperative.length; i++){
                if($scope.Veterinary.cooperative_id == $scope.Cooperative[i].id){
                    $scope.CooperativeName = $scope.Cooperative[i].cooperative_name;
                }
            }

            for(var i=0; i < $scope.MonthList.length; i++){
                if($scope.Veterinary.months == $scope.MonthList[i].monthValue){
                    $scope.MonthName = $scope.MonthList[i].monthText;
                }
            }

            for(var i=0; i < $scope.YearList.length; i++){
                if($scope.Veterinary.years == $scope.YearList[i].yearText){
                    $scope.YearName = $scope.YearList[i].yearValue;
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Veterinary, VeterinaryDetailList){
        $scope.Saving = true;
        var params = {'Veterinary' : Veterinary, 'VeterinaryDetailList' : VeterinaryDetailList};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('veterinary/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                // if($scope.ID !== undefined && $scope.ID !== null){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/veterinary';///update/' + result.data.DATA.id;
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/veterinary';
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
        $scope.VeterinaryDetailList = [];
        // $scope.setVeterinary();
        $scope.loadData('veterinary/get');
        $scope.$parent.getGoalByMenu('บริการสัตวแพทย์', $scope.Veterinary.years, $scope.Veterinary.months);
        

    }

    $scope.addVeterinaryDetail = function(){
        var detail = {'id':''
                    , 'veterinary_id':$scope.ID
                    , 'dairy_farming_id':null
                    , 'sub_dairy_farming_id':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID
                    , 'veterinary_item':[{
                                            'id':''
                                            , 'veterinary_id':$scope.ID
                                            , 'veterinary_detail_id':''
                                            , 'item_type':null
                                            , 'item_amount':null
                                            , 'create_date':''
                                            , 'update_date':''
                                            , 'create_by':$scope.currentUser.UserID
                                            , 'update_by':$scope.currentUser.UserID
                                        }]
                    };

        $scope.VeterinaryDetailList.push(detail);

        $scope.DairyFarmingList.push({});
        $scope.SubDairyFarmingList.push({});
        var index = $scope.VeterinaryDetailList.length - 1;
        $scope.loadDairyFarming('MAIN', '', index);
        $scope.loadDairyFarming('CHILD', '', index);
    }

    $scope.addVeterinaryItem = function(index){
        var item = {'id':''
                    , 'veterinary_id':$scope.ID
                    , 'veterinary_detail_id':''
                    , 'item_type':null
                    , 'item_amount':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID};

        $scope.VeterinaryDetailList[index].veterinary_item.push(item);
    }

    $scope.removeDetail = function(id, index){
        if(id == ''){
            $scope.VeterinaryDetailList.splice(index, 1);
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
                HTTPService.clientRequest('veterinary/delete/detail', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.VeterinaryDetailList.splice(index, 1);
                        $scope.SubDairyFarmingList.splice(index, 1);
                        $scope.DairyFarmingList.splice(index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.removeItem = function(id, parent_index, child_index){
        if(id == ''){
            $scope.VeterinaryDetailList[parent_index].veterinary_item.splice(child_index, 1);
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
                HTTPService.clientRequest('veterinary/delete/item', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.VeterinaryDetailList[parent_index].veterinary_item.splice(child_index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.setVeterinary = function(){
        $scope.Veterinary = {
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
            HTTPService.uploadRequest('veterinary/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                     window.location.href = '#/veterinary';///update/' + Data.id;
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
            HTTPService.uploadRequest('veterinary/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/veterinary';///update/' + Data.id;
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
    $scope.SubDairyFarmingList = [{}];
    $scope.DairyFarmingList = [{}];
    $scope.VeterinaryDetailList = [];

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

    $scope.setVeterinary();
    $scope.loadCooperative();
    $scope.loadDairyFarming('MAIN', '', 0);
    $scope.loadDairyFarming('CHILD', '', 0);


});