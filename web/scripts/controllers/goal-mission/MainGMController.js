angular.module('e-homework').controller('MainGMController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
	//console.log('Hello !');
    $scope.YearList = getYearList(20);
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
        console.log($scope.$parent.currentUser);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));   
    $scope.$parent.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));  

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
     
    $scope.Approval = false;
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

                    if($scope.UserRole[i].role == '2' && $scope.UserRole[i].actives == 'Y'){
                        $scope.Approval = true;
                    }
                }
                // console.log($scope.MasterGoalList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.Maker = false;

    $scope.changeGoalType = function(){
        $scope.MenuType = [];
        for(var i = 0; i < $scope.MenuTypeList.length; i++){
            if($scope.condition.goal_type == $scope.MenuTypeList[i].type){
                $scope.MenuType.push($scope.MenuTypeList[i]);
            }
        }   
    }

    $scope.loadMasterGoalList = function(action, goal_type){
        var params = {'actives' : 'Y', 'goal_type': goal_type, 'condition' : $scope.condition, 'htmlcode':''};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.MasterGoalList = result.data.DATA.List;
                $scope.loadList('goal-mission/list');
                // console.log($scope.MasterGoalList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadList = function(action){
        var params = {'condition' : $scope.condition, 'UserID' : $scope.currentUser.UserID, 'PersonRegion' : $scope.$parent.PersonRegion};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.List = result.data.DATA.List;
                // console.log($scope.UserList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertSQLDateTimeToReportDateTime(date);
    }

    $scope.goUpdate = function(id){
        window.location.href = '#/goal-mission/update/' + id;
    }

    $scope.getGoalType = function(val){
        if(val == 'DBI'){
            return 'ข้อมูลกิจการโคนม';
        }else if(val == 'II'){
            return 'ข้อมูลอุตสาหกรรม';
        }
    }

    $scope.getRegionName = function(region_id){
        for(var i = 0;  i < $scope.$parent.PersonRegion.length; i++){
            if(region_id == $scope.$parent.PersonRegion[i].RegionID){
                return $scope.$parent.PersonRegion[i].RegionName;
            }
        }   
    }

    $scope.getGoalName = function(goal_id){
        for(var i = 0;  i < $scope.MasterGoalList.length; i++){
            if(goal_id == $scope.MasterGoalList[i].id){
                return $scope.MasterGoalList[i].goal_name;
            }
        }   
    }

    $scope.findGoalType = function(goal_id){
        var goalType = '';
        for(var i = 0;  i < $scope.MasterGoalList.length; i++){
            if(goal_id == $scope.MasterGoalList[i].id){
                
                goalType = $scope.getGoalType($scope.MasterGoalList[i].goal_type);
            }
        }   
        return goalType;
    }

    $scope.updateEdit = function(id, editable){
        var params = {'id' : id, 'editable' : editable};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('goal-mission/update/editable', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.loadList('goal-mission/list');
                // console.log($scope.UserList);
            }
            IndexOverlayFactory.overlayHide();
        });
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
        return parseFloat(num).toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    $scope.changeGoalType = function(goal_type){
        $scope.MenuType = [];
        for(var i = 0; i < $scope.MenuTypeList.length; i++){
            if($scope.condition.goal_type == $scope.MenuTypeList[i].type){
                $scope.MenuType.push($scope.MenuTypeList[i]);
            }
        }   
        $scope.loadMasterGoalList('master-goal/list', goal_type); 
    }

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

    // $scope.condition = {'Year' : ''
    //                     , 'Region' : ''
    //                     , 'Goal' : ''
    //                 };
    
    var curDate = new Date();
    var fiscalYear = 5;
    if(curDate.getMonth() > 8){
        fiscalYear = 6;
    }

    $scope.condition = {'Year': $scope.YearList[fiscalYear], 'Region' : $scope.PersonRegion[0], 'goal_type' : ''};
    $scope.getUserRole();
    $scope.loadList('goal-mission/list');
    // $scope.loadMasterGoalList('master-goal/list');
    
// $scope.loadList('goal-mission/list');
    // console.log($scope.$parent.PersonRegion);
});