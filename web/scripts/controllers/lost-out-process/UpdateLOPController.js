angular.module('e-homework').controller('UpdateLOPController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
    //console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'industry';
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

    $scope.page_type = 'lost-out-process';
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

    $scope.loadFactoryList = function(){
        
      var params = {'region' : $scope.PersonRegion};
        HTTPService.clientRequest('factory/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.FactoryList = result.data.DATA.DataList;
                // console.log($scope.List);
                // $scope.condition.Factory = $scope.FactoryList[0].id;
                if($scope.ID !== undefined && $scope.ID !== null){
                    $scope.loadData('lost-out-process/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadDairyFarming = function(type, parent_id){
        var params = {'type':type, 'parent_id' : parent_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/list/lost-out-process', params).then(function(result){
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
        var params = {'actives':'Y', 'menu_type' : 'การสูญเสียหลังกระบวนการ'};
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
            'factory_id' : $scope.Data.factory_id
            ,'months' : $scope.Data.months
            ,'years' : $scope.Data.years
        };
        if(id != null){
            params = {'id':id};
        }
        
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK' && result.data.DATA.Data != null){
                $scope.Data = result.data.DATA.Data;
                $scope.Data.factory_id = parseInt($scope.Data.factory_id);
                $scope.DataDetailList = $scope.Data.lost_out_process_detail;
                $scope.getUploadLogList();
                // load sub dar=iry farming
                // for(var i =0; i < $scope.DataDetailList.length; i++){
                //     $scope.loadDairyFarming('CHILD', $scope.DataDetailList[i].dairy_farming_id);
                // }
                IndexOverlayFactory.overlayHide();
            }else{
                if($scope.Data.id != ''){
                    $scope.Data.id = '';
                }

                for(var i = 0; i < $scope.MasterGoalList.length; i++){
                    $scope.DataDetailList.push({
                            'id':''
                            ,'lost_out_process_id':''
                            ,'lost_out_process_type':$scope.MasterGoalList[i].id
                            ,'amount':''
                            ,'value':''
                        });
                    }
            }
            $scope.FactoryName = '';
            $scope.MonthName = '';
            $scope.YearName = '';
            // Get cooperative name
            for(var i=0; i < $scope.FactoryList.length; i++){
                if($scope.Data.factory_id == $scope.FactoryList[i].id){
                    $scope.FactoryName = $scope.FactoryList[i].factory_name;
                }
            }

            for(var i=0; i < $scope.MonthList.length; i++){
                if($scope.Data.months == $scope.MonthList[i].monthValue){
                    $scope.MonthName = $scope.MonthList[i].monthText;
                }
            }

            for(var i=0; i < $scope.YearList.length; i++){
                if($scope.Data.years == $scope.YearList[i].yearText){
                    $scope.YearName = $scope.YearList[i].yearValue;
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Data, DataDetailList){
        $scope.Saving = true;
        var params = {'Data' : Data, 'Detail' : DataDetailList};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('lost-out-process/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                // if($scope.ID !== undefined && $scope.ID !== null){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/lost-out-process/update/' + result.data.DATA.id;
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/lost-out-process';
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
        $scope.DataDetailList = [];
        // $scope.setData();
        $scope.loadData('lost-out-process/get');
        
        // $scope.DataDetailList = [
        //     {
        //         'id':''
        //         ,'lost-out-process_id':'1'
        //         ,'amount':'2'
        //         ,'price':'200'
        //         ,'values':'400'
        //     },
        //     {
        //         'id':''
        //         ,'lost-out-process_id':'2'
        //         ,'amount':'4'
        //         ,'price':'400'
        //         ,'values':'1600'
        //     }
        // ];
    }

    $scope.addDataDetail = function(){
        var detail = {
                'id':''
                ,'lost_out_process_id':''
                ,'lost_out_process_type':''
                ,'amount':''
                ,'value':''
            };

        $scope.DataDetailList.push(detail);
    }

    $scope.removeDetail = function(id, index){
        if(id == ''){
            $scope.DataDetailList.splice(index, 1);
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
                HTTPService.clientRequest('lost-out-process/delete/detail', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.DataDetailList.splice(index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.setData = function(){
        $scope.Data = {
            'id':''
            , 'cooperative_id':null
            , 'region_id':$scope.PersonRegion[0].RegionID
            , 'factory_id' : $scope.PersonRegion[0].FactoryID
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
            HTTPService.uploadRequest('lost-out-process/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/lost-out-process';///update/' + Data.id;
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
            HTTPService.uploadRequest('lost-out-process/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/lost-out-process';///update/' + Data.id;
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
    $scope.DataDetailList = [];
    $scope.DataList = [
        {'id':1, 'name':'หญ้าแห้ง'}
        ,{'id':2, 'name':'อาหาร TMR'}
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

    
    $scope.switchDetailType = function(){
        if($scope.DETAIL_TYPE == 'MANUAL'){
            $scope.DETAIL_TYPE = 'UPLOAD';
            $scope.AttachFile = null;
            $scope.getUploadLogList();
        }else{
            $scope.DETAIL_TYPE = 'MANUAL';
        }
    }
    $scope.DETAIL_TYPE = 'UPLOAD';

    $scope.getUploadLogList = function(){
        var params = {'id' : $scope.Data.id, 'menu_type' : 'lost-out-process'};
        HTTPService.clientRequest('upload-log/list', params).then(function(result){
            console.log(result);
            $scope.UploadLogList = result.data.DATA.List;
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.uploadFile = function(Data, AttachFile ){
        // var FileDate = '';
        if($scope.FileDate != null && $scope.FileDate != undefined && $scope.FileDate != ''){
            $scope.FileDate = makeSQLDate($scope.FileDate);
        }
        IndexOverlayFactory.overlayShow();
        var params = {'Data' : Data, 'AttachFile' : AttachFile, 'menu_type' : 'lost-out-process', 'FileDate' : $scope.FileDate};
            HTTPService.uploadRequest('lost-out-process/upload', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('อัพโหลดสำเร็จ');
                    window.location.href = '#/lost-out-process';///update/' + Data.id;
                }else{
                    alert(result.data.DATA);
                }
                IndexOverlayFactory.overlayHide();
            });
    }

    $scope.exportTemplate = function(){
       // console.log(DetailList, $scope.data_description);
        // return;
        IndexOverlayFactory.overlayHide();
        var params = {
            'factory_id' : $scope.Data.factory_id
           , 'years' : $scope.Data.years
           , 'months' : $scope.Data.months
        }; 
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('lost-out-process/load/template', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                window.location.href="../" + result.data.DATA;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.setData();
    $scope.loadFactoryList();
    $scope.loadMasterGoalList();
    $scope.getUploadLogList();
    // $scope.loadDairyFarming('MAIN', '');
    // $scope.loadDairyFarming('CHILD', '');


});