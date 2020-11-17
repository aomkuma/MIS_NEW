angular.module('e-homework').controller('UpdateGMController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
	//console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }
    $scope.ID = $routeParams.id;
    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session')); 
    $scope.$parent.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));      
    // console.log($scope.$parent.Menu);
    console.log($scope.$parent.Menu);

    $scope.page_type = 'goal-mission';
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

    $scope.loadMasterGoalList = function(action, menu_type, factory_id){
        if($scope.Data.goal_type == 'DBI'){
            factory_id = '';
        }
        var params = {'actives' : 'Y', 'menu_type' : menu_type, 'factory_id' : factory_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.MasterGoalList = result.data.DATA.List;

                if($scope.ID !== undefined && $scope.ID !== null){
                    // $scope.loadData('goal-mission/get', $scope.ID);
                }

                // console.log($scope.MasterGoalList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadData = function(action, id){
        var params = {
            'id' : id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Data = result.data.DATA.Data;
                $scope.avgList = $scope.Data.goal_mission_avg;

                $scope.loadMasterGoalList('master-goal/list', $scope.Data.menu_type);

                $scope.totalAmount = 0;
                $scope.totalAddonAmount = 0;
                $scope.totalPriceValue = 0;
                for(var i = 0; i < $scope.avgList.length; i++){
                    $scope.avgIDList.push({'id':$scope.avgList[i].id});
                    $scope.avgList[i].amount = parseFloat($scope.avgList[i].amount);
                    $scope.avgList[i].addon_amount = parseFloat($scope.avgList[i].addon_amount);
                    $scope.avgList[i].price_value = parseFloat($scope.avgList[i].price_value);
                    $scope.totalAmount += $scope.avgList[i].amount;
                    $scope.totalAddonAmount += $scope.avgList[i].addon_amount;
                    $scope.totalPriceValue += $scope.avgList[i].price_value;
                }
                $scope.totalAmount = parseFloat($scope.totalAmount.toFixed(2));
                $scope.totalAddonAmount = parseFloat($scope.totalAddonAmount.toFixed(2));
                $scope.totalPriceValue = parseFloat($scope.totalPriceValue.toFixed(2));
                
                $scope.historyList = $scope.Data.goal_mission_history;

                $scope.Data.amount = parseFloat($scope.Data.amount);
                $scope.Data.addon_amount = parseFloat($scope.Data.addon_amount);
                $scope.Data.price_value = parseFloat($scope.Data.price_value);
                
                $scope.changeGoalType();

                $scope.checkMenu();
                //find goal type
                // $scope.findGoalType($scope.Data.goal_id);
            }
            IndexOverlayFactory.overlayHide();
        });

    }

    $scope.save = function(Data, AvgList, SaveStatus){
        if($scope.totalAmount != $scope.Data.amount){
            alert('ผลรวมของจำนวนเฉลี่ยไม่เท่ากับจำนวนเป้ารายปี กรุณาตรวจสอบข้อมูล');
            return false;
        }
        if($scope.Data.price_value != null && $scope.totalPriceValue != $scope.Data.price_value){
            alert('ผลรวมของมูลค่าเฉลี่ยไม่เท่ากับมูลค่าเป้ารายปี กรุณาตรวจสอบข้อมูล');
            return false;
        }
        var params = {'Data' : Data, 'AvgList' : AvgList, 'SaveStatus' : SaveStatus};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('goal-mission/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                // if($scope.ID !== undefined && $scope.ID !== null){
                    // window.location.href = '#/goal-mission/update/' + result.data.DATA.id;
                    alert('บันทึกข้อมูลสำเร็จ');
                    window.location.href = '#/goal-mission/';
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }else{
                alert(result.data.DATA);
                IndexOverlayFactory.overlayHide();
            }
        });
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
            HTTPService.uploadRequest('goal-mission/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/goal-mission/';
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
            HTTPService.uploadRequest('goal-mission/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/goal-mission/';
                }else{
                    alert(result.data.DATA);
                }
                IndexOverlayFactory.overlayHide();
            });
        });
    }

    $scope.findGoalType = function(goal_id){
        for(var i = 0; i < $scope.MasterGoalList.length; i++){
            if($scope.MasterGoalList[i].id == goal_id){
                $scope.goal_type = $scope.MasterGoalList[i].goal_type;
                break;
            }
        }
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/goal-mission';
    }

    $scope.setGoalType = function(data){
        console.log(data);
        $scope.goal_type = data.goal_type;
    }

    $scope.reCalcAmount = function(){
        $scope.totalAmount = 0;
        var loop = $scope.avgList.length;
        for(var i = 0; i < loop; i++){
            if($scope.avgList[i].amount != null){
                $scope.totalAmount += $scope.avgList[i].amount;
            }else{
                $scope.avgList[i].amount = 0;
            }
        }
        $scope.totalAmount = parseFloat($scope.totalAmount.toFixed(2));
        console.log('Total amount : ', $scope.totalAmount);
    }

    $scope.reCalcAddonAmount = function(){
        $scope.totalAddonAmount = 0;
        var loop = $scope.avgList.length;
        for(var i = 0; i < loop; i++){
            if($scope.avgList[i].addon_amount != null){
                $scope.totalAddonAmount += $scope.avgList[i].addon_amount;
            }else{
                $scope.avgList[i].addon_amount = 0;
            }
        }
        $scope.totalAddonAmount = parseFloat($scope.totalAddonAmount.toFixed(2));
        console.log('Total amount : ', $scope.totalAddonAmount);
    }

    $scope.reCalcPrice = function(){
        $scope.totalPriceValue = 0;
        var loop = $scope.avgList.length;
        for(var i = 0; i < loop; i++){
            if($scope.avgList[i].price_value != null){
                $scope.totalPriceValue += $scope.avgList[i].price_value;    
            }else{
                $scope.avgList[i].price_value = 0;
            }
        }
        $scope.totalPriceValue = parseFloat($scope.totalPriceValue.toFixed(2));
    }

    $scope.avgData = function(Data){

        if(Data.id == ''){
            $scope.AVGAction = true;
            
            $scope.avgList = [];
            $scope.totalAmount = 0;
            $scope.totalPriceValue = 0;
            var avgAmount = parseFloat(Data.amount) / 12;
            var avgPriceValue = parseFloat(Data.price_value) / 12;
            avgAmount = parseFloat(avgAmount.toFixed(2));
            avgPriceValue = parseFloat(avgPriceValue.toFixed(2));
            var month = 10;
            var year = parseInt(Data.years) - 1;
            for(var i = 0; i < 12; i++){

                // Create Date from years
                if(month > 12){
                    month = 1;
                    year += 1;
                }
                var dateStr = year + '-' + padLeft(""+(month), '00') + '-01';
                // console.log(dateStr);
                // var curDate = new Date(dateStr);
                
                var avgData = {
                    'id':($scope.avgIDList[i] === undefined?'':$scope.avgIDList[i].id)
                    , 'goal_mission_id':''
                    , 'avg_date':dateStr
                    , 'amount':0//avgAmount
                    , 'addon_amount':0//avgAddonAmount
                    , 'price_value':0//avgPriceValue
                };

                $scope.avgList.push(avgData);
                month++;
                $scope.totalAmount += avgAmount;
                $scope.totalPriceValue += avgPriceValue;
            }

            $scope.totalAmount = 0;//parseFloat($scope.totalAmount.toFixed(2));
            $scope.totalPriceValue = 0;//parseFloat($scope.totalPriceValue.toFixed(2));

        }else{

            var month = 10;
            var year = parseInt(Data.years) - 1;
            
            for(var i = 0; i < 12; i++){
                if(month > 12){
                    month = 1;
                    year += 1;
                }
                var dateStr = year + '-' + padLeft(""+(month), '00') + '-01';

                $scope.avgList[i].avg_date = dateStr;

                month++;
            }

        }
        // console.log($scope.avgList);
    }

    // $scope. checkZero = function(data){
    //     console.log(data);
    //     if(data == 0){
    //         data = null;
    //     }else{
    //         data = 0;
    //     }
    //     // data == 0? data = null: data = data;
    // }

    $scope.getMonthYearText = function(dateStr){
        if(dateStr != null && dateStr != '' && dateStr != '0000-00-00'){
            return getMonthYearText(dateStr);
        }else{
            return '';
        }
        
    }

    $scope.changeGoalType = function(){
        $scope.MenuType = [];
        for(var i = 0; i < $scope.MenuTypeList.length; i++){
            if($scope.Data.goal_type == $scope.MenuTypeList[i].type){
                $scope.MenuType.push($scope.MenuTypeList[i]);
            }
        }   
    }

    $scope.checkMenu = function(){
        $scope.MenuType = [];
        console.log($scope.Data.goal_type);
        for(var i = 0; i < $scope.$parent.Menu.length; i++){
            if($scope.$parent.Menu[i].menu_name_th == 'ข้อมูลกิจการโคนม' && $scope.Data.goal_type == 'DBI'){
                for(var j = 0; j < $scope.$parent.Menu[i].sub_menu.length; j++){
                    console.log($scope.$parent.Menu[i].sub_menu[j].menu_name_th);
                    console.log($filter('MenuTypeFilter')($scope.MenuTypeList, $scope.$parent.Menu[i].sub_menu[j].menu_name_th));
                    if($filter('MenuTypeFilter')($scope.MenuTypeList, $scope.$parent.Menu[i].sub_menu[j].menu_name_th)){
                        $scope.MenuType.push({'type':'DBI', 'value':$scope.$parent.Menu[i].sub_menu[j].menu_name_th, 'name' : $scope.$parent.Menu[i].sub_menu[j].menu_name_th});
                    }
                }
            }
            else if($scope.$parent.Menu[i].menu_name_th == 'ข้อมูลอุตสาหกรรม' && $scope.Data.goal_type == 'II'){
                for(var j = 0; j < $scope.$parent.Menu[i].sub_menu.length; j++){
                    if($filter('MenuTypeFilter')($scope.MenuTypeList, $scope.$parent.Menu[i].sub_menu[j].menu_name_th)){
                        $scope.MenuType.push({'type':'II', 'value':$scope.$parent.Menu[i].sub_menu[j].menu_name_th, 'name' : $scope.$parent.Menu[i].sub_menu[j].menu_name_th});
                    }
                }
            }
        }
    }

    var curDate = new Date();
    $scope.AVGAction = false;
    $scope.totalAmount = 0;
    $scope.totalPriceValue = 0;
    $scope.YearList = getYearList(20);
    $scope.Data = {
        'id':''
        , 'years':curDate.getFullYear()
        , 'region_id':$scope.PersonRegion[0].RegionID
        , 'goal_id':null
        , 'amount':0
        , 'unit':null
        , 'price_value':0
        , 'editable':'Y'
        , 'actives':'Y'
        , 'create_date':''
        , 'create_by':$scope.currentUser.UserID
        , 'update_date':''
        , 'update_by':$scope.currentUser.UserID
    };
    $scope.avgList = [];
    $scope.avgIDList = [];
    $scope.historyList = [];

    $scope.ShowMenuTypeList = [];
    $scope.MenuTypeList = [{'type':'DBI', 'value':'บริการสัตวแพทย์และผสมเทียม', 'name' : 'บริการสัตวแพทย์และผสมเทียม'}
                            // ,{'type':'DBI', 'value':'ผสมเทียม', 'name' : 'ผสมเทียม'}
                            ,{'type':'DBI', 'value':'แร่ธาตุ พรีมิกซ์ และอาหาร', 'name' : 'แร่ธาตุ พรีมิกซ์ และอาหาร'}
                            ,{'type':'DBI', 'value':'ผลิตน้ำเชื้อแช่แข็ง', 'name' : 'ผลิตน้ำเชื้อแช่แข็ง'}
                            ,{'type':'DBI', 'value':'จำหน่ายน้ำเชื้อแช่แข็ง', 'name' : 'จำหน่ายน้ำเชื้อแช่แข็ง'}
                            ,{'type':'DBI', 'value':'วัสดุผสมเทียมและเวชภัณฑ์ยาสัตว์', 'name' : 'วัสดุผสมเทียมและเวชภัณฑ์ยาสัตว์'}
                            ,{'type':'DBI', 'value':'ปัจจัยการเลี้ยงดูโค (เคมีภัณฑ์)', 'name' : 'ปัจจัยการเลี้ยงดูโค (เคมีภัณฑ์)'}
                            ,{'type':'DBI', 'value':'ฝึกอบรม', 'name' : 'ฝึกอบรม'}
                            ,{'type':'DBI', 'value':'ท่องเที่ยว', 'name' : 'ท่องเที่ยว'}
                            ,{'type':'DBI', 'value':'สหกรณ์และปริมาณน้ำนม', 'name' : 'สหกรณ์และปริมาณน้ำนม'}
                            ,{'type':'DBI', 'value':'ข้อมูลฝูงโค', 'name' : 'ข้อมูลฝูงโค'}
                            ,{'type':'DBI', 'value':'ข้อมูลฝูงโคพ่อพันธุ์', 'name' : 'ข้อมูลฝูงโคพ่อพันธุ์'}
                            ,{'type':'II', 'value':'ข้อมูลการผลิต', 'name' : 'ข้อมูลการผลิต'}
                            ,{'type':'II', 'value':'ข้อมูลการขาย', 'name' : 'ข้อมูลการขาย'}
                            ,{'type':'II', 'value':'รับซื้อน้ำนมดิบ (ERP)', 'name' : 'รับซื้อน้ำนมดิบ (ERP)'}
                            ,{'type':'II', 'value':'จำหน่ายน้ำนมดิบ (ERP)', 'name' : 'จำหน่ายน้ำนมดิบ (ERP)'}
                            ,{'type':'II', 'value':'การสูญเสียในกระบวนการ', 'name' : 'การสูญเสียในกระบวนการ'}
                            ,{'type':'II', 'value':'การสูญเสียหลังกระบวนการ', 'name' : 'การสูญเสียหลังกระบวนการ'}
                            ,{'type':'II', 'value':'การสูญเสียรอจำหน่าย', 'name' : 'การสูญเสียรอจำหน่าย'}
                            // ,{'type':'II', 'value':'การสูญเสียในกระบวนการขนส่ง', 'name' : 'การสูญเสียในกระบวนการขนส่ง'}
                        ];
    // 
    if($scope.ID != null){
        
        $scope.loadData('goal-mission/get', $scope.ID);
    }

    $scope.loadSaleChanel = function(){
        var params = {'actives' : 'Y'};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('product-milk/sale-chanel/list', params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                $scope.SaleChanelList = result.data.DATA.List;
                
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadSaleChanel();
    $scope.checkMenu();

});