angular.module('e-homework').controller('UpdateMGController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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

    $scope.loadFactoryList = function(){
      var params = {'region' : $scope.PersonRegion};
        HTTPService.clientRequest('factory/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.FactoryList = result.data.DATA.DataList;

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
                $scope.changeGoalType();
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Data){
        console.log(Data);
        var params = {'Data' : Data};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('master-goal/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                // if($scope.ID !== undefined && $scope.ID !== null){
                    // window.location.href = '#/master-goal/update/' + result.data.DATA.id;
                    window.location.href = '#/master-goal';
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
        window.location.href = '#/master-goal';
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
        , 'factory_id':null
        , 'create_date':''
        , 'update_date':''
    };

    $scope.SubGoalTypeList = [{'type':'แร่ธาตุ พรีมิกซ์ และอาหาร','name':'แร่ธาตุ'}
                        ,{'type':'แร่ธาตุ พรีมิกซ์ และอาหาร','name':'พรีมิกซ์'}
                        ,{'type':'แร่ธาตุ พรีมิกซ์ และอาหาร','name':'อาหาร'}
                        ,{'type':'ข้อมูลฝูงโค','name':'โคเพิ่ม'}
                        ,{'type':'ข้อมูลฝูงโค','name':'โคลด'}
                        // ,{'type':'ข้อมูลฝูงโค','name':'น้ำนมดิบที่ผลิตได้'}
                        ,{'type':'ข้อมูลฝูงโค','name':'ฝูงโคเพศเมีย (โคทดแทน)'}
                        ,{'type':'ข้อมูลฝูงโค','name':'ฝูงโคฟาร์มพัฒนาการเลี้ยงโคนม (1962)'}
                        ,{'type':'ข้อมูลฝูงโค','name':'ฝูงโคฟาร์ม 3 (โคนมอินทรีย์)'}
                        ,{'type':'ข้อมูลฝูงโค','name':'ฝูงโคเพศผู้'}
                        /*,{'type':'ข้อมูลฝูงโค','name':'ส่งเข้าโรงงาน'}
                        ,{'type':'ข้อมูลฝูงโค','name':'เลี้ยงลูกโค'}
                        ,{'type':'ข้อมูลฝูงโค','name':'เสื่อมคุณภาพ'}
                        ,{'type':'ข้อมูลฝูงโค','name':'นมน้ำเหลือง'}*/
                        ,{'type':'ข้อมูลฝูงโคพ่อพันธุ์','name':'ฝูงโคพ่อพันธุ์'}
                        // ,{'type':'ข้อมูลฝูงโคพ่อพันธุ์','name':'โคเพิ่ม'}
                        // ,{'type':'ข้อมูลฝูงโคพ่อพันธุ์','name':'โคลด'}
                        ,{'type':'การสูญเสียในกระบวนการ','name':'การแปรรูปน้ำนม (รวบรวม)'}
                        ,{'type':'การสูญเสียในกระบวนการ','name':'การแปรรูปน้ำนม (หักลบ)'}
                    ];

    $scope.SubGoalTypeList1 = [{'type':'ข้อมูลฝูงโค','name':'โคเพิ่ม'}
                        ,{'type':'ข้อมูลฝูงโค','name':'โคลด'}
                        
                    ];

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
    if($scope.ID !== undefined && $scope.ID !== null){

        $scope.loadData('master-goal/get', $scope.ID);
    }

});