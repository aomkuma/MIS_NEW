angular.module('e-homework').controller('MainMGController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
	//console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    

    $scope.loadFactoryList = function () {

        //  IndexOverlayFactory.overlayShow();
        var params = {'region': $scope.PersonRegion};
        HTTPService.clientRequest('factory/list', params).then(function (result) {
            if (result.data.STATUS == 'OK') {
              
              
                $scope.FactoryList = result.data.DATA.DataList;
                 
                // console.log($scope.List);
              //  $scope.Data.factory_id = $scope.FactoryList[0].id;

            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadList = function(action){
        var params = {'condition' : $scope.condition, 'htmlcode':'Y'};
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
        return convertDateToFullThaiDate(new Date(date));
    }

    $scope.goUpdate = function(id){
        window.location.href = '#/master-goal/update/' + id;
    }

    $scope.getGoalType = function(val){
        if(val == 'DBI'){
            return 'ข้อมูลกิจการโคนม';
        }else if(val == 'II'){
            return 'ข้อมูลอุตสาหกรรม';
        }
    }

    $scope.changeGoalType = function(){
        $scope.MenuType = [];
        for(var i = 0; i < $scope.MenuTypeList.length; i++){
            if($scope.condition.goal_type == $scope.MenuTypeList[i].type){
                $scope.MenuType.push($scope.MenuTypeList[i]);
            }
        }   
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

    $scope.loadFactoryList();
    $scope.loadList('master-goal/list', '');


});