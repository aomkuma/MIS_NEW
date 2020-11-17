angular.module('e-homework').controller('UpdateLMController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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
    console.log($scope.$parent.Menu);

    $scope.loadData = function(action, id){
        var params = {
            'id' : id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Data = result.data.DATA.Data;
                $scope.changeGoalType();
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Data){
        console.log(Data);
        var params = {'Data' : Data};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('loss-manage/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                // if($scope.ID !== undefined && $scope.ID !== null){
                    // window.location.href = '#/master-goal/update/' + result.data.DATA.id;
                    window.location.href = '#/loss-manage';
                // }else{
                //     location.reload();    
                // }
                
            }else{
                alert(result.data.DATA);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/loss-manage';
    }

    $scope.changeGoalType = function(){
        $scope.MenuType = [];
        for(var i = 0; i < $scope.MenuTypeList.length; i++){
            if($scope.Data.goal_type == $scope.MenuTypeList[i].type){
                $scope.MenuType.push($scope.MenuTypeList[i]);
            }
        }   
    }

    $scope.filterSubType = function(menu_type, type){
        console.log(menu_type, type);
        return menu_type === type;
    }

    $scope.Data = {
        'id':''
        , 'goal_type':''
        , 'sub_goal_type':''
        , 'menu_type':null
        , 'goal_name':''
        , 'actives':'Y'
        , 'create_date':''
        , 'update_date':''
    };

    $scope.SubGoalTypeList = [{'type':'แร่ธาตุ พรีมิกซ์ และอาหาร','name':'แร่ธาตุ'}
                        ,{'type':'แร่ธาตุ พรีมิกซ์ และอาหาร','name':'พรีมิกซ์'}
                        ,{'type':'แร่ธาตุ พรีมิกซ์ และอาหาร','name':'อาหาร'}
                        ,{'type':'ข้อมูลฝูงโค','name':'โคเพิ่ม'}
                        ,{'type':'ข้อมูลฝูงโค','name':'โคลด'}
                        ,{'type':'ข้อมูลฝูงโคพ่อพันธุ์','name':'โคเพิ่ม'}
                        ,{'type':'ข้อมูลฝูงโคพ่อพันธุ์','name':'โคลด'}
                        ,{'type':'การสูญเสียในกระบวนการ','name':'น้ำนมที่รวบรวม'}
                        ,{'type':'การสูญเสียในกระบวนการ','name':'การแปรรูปน้ำนม'}
                    ];

    $scope.SubGoalTypeList1 = [{'type':'ข้อมูลฝูงโค','name':'โคเพิ่ม'}
                        ,{'type':'ข้อมูลฝูงโค','name':'โคลด'}
                        
                    ];

    $scope.MenuTypeList = [{'type':'DBI', 'value':'บริการสัตวแพทย์', 'name' : 'บริการสัตวแพทย์'}
                            ,{'type':'DBI', 'value':'ผสมเทียม', 'name' : 'ผสมเทียม'}
                            ,{'type':'DBI', 'value':'แร่ธาตุ พรีมิกซ์ และอาหาร', 'name' : 'แร่ธาตุ พรีมิกซ์ และอาหาร'}
                            ,{'type':'DBI', 'value':'ผลิตน้ำเชื้อแช่แข็ง', 'name' : 'ผลิตน้ำเชื้อแช่แข็ง'}
                            ,{'type':'DBI', 'value':'จำหน่ายน้ำเชื้อแช่แข็ง', 'name' : 'จำหน่ายน้ำเชื้อแช่แข็ง'}
                            ,{'type':'DBI', 'value':'วัสดุผสมเทียมและอื่นๆ', 'name' : 'วัสดุผสมเทียมและอื่นๆ'}
                            ,{'type':'DBI', 'value':'ปัจจัยการเลี้ยงโค', 'name' : 'ปัจจัยการเลี้ยงโค'}
                            ,{'type':'DBI', 'value':'ฝึกอบรม', 'name' : 'ฝึกอบรม'}
                            ,{'type':'DBI', 'value':'ท่องเที่ยว', 'name' : 'ท่องเที่ยว'}
                            ,{'type':'DBI', 'value':'สหกรณ์และปริมาณน้ำนม', 'name' : 'สหกรณ์และปริมาณน้ำนม'}
                            ,{'type':'DBI', 'value':'ข้อมูลฝูงโค', 'name' : 'ข้อมูลฝูงโค'}
                            ,{'type':'DBI', 'value':'ข้อมูลฝูงโคพ่อพันธุ์', 'name' : 'ข้อมูลฝูงโคพ่อพันธุ์'}
                            ,{'type':'II', 'value':'ข้อมูลการผลิต', 'name' : 'ข้อมูลการผลิต'}
                            ,{'type':'II', 'value':'ข้อมูลการขาย', 'name' : 'ข้อมูลการขาย'}
                            ,{'type':'II', 'value':'ข้อมูลรับซื้อน้ำนม', 'name' : 'ข้อมูลรับซื้อน้ำนม'}
                            ,{'type':'II', 'value':'ข้อมูลจำหน่ายน้ำนม', 'name' : 'ข้อมูลจำหน่ายน้ำนม'}
                            ,{'type':'II', 'value':'การสูญเสียในกระบวนการ', 'name' : 'การสูญเสียในกระบวนการ'}
                            ,{'type':'II', 'value':'การสูญเสียหลังกระบวนการ', 'name' : 'การสูญเสียหลังกระบวนการ'}
                            ,{'type':'II', 'value':'การสูญเสียรอจำหน่าย', 'name' : 'การสูญเสียรอจำหน่าย'}
                            ,{'type':'II', 'value':'การสูญเสียในกระบวนการขนส่ง', 'name' : 'การสูญเสียในกระบวนการขนส่ง'}
                        ];
    if($scope.ID !== undefined && $scope.ID !== null){
        $scope.loadData('loss-manage/get', $scope.ID);
    }

});