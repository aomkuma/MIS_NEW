angular.module('e-homework').controller('UpdatePSIController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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

    $scope.page_type = 'production-sale-info';
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
                    $scope.loadData('production-sale-info/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadDairyFarming = function(type, parent_id){
        var params = {'type':type, 'parent_id' : parent_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/list/production-sale-info', params).then(function(result){
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

    $scope.loadDefaultProductMilk = function(){
        IndexOverlayFactory.overlayShow();
        var params = {'actives':'Y', 'facid':$scope.Data.factory_id};
        HTTPService.clientRequest('product-milk/list/all', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                // $scope.ProductMilkList[index] = result.data.DATA.List;
                console.log(result.data.DATA);
                var totalRows = result.data.DATA.length;
                $scope.ProductMilkList = [totalRows];
                $scope.SubProductMilkList = [totalRows];
                $scope.ProductMilkDetailList = [totalRows];

                for(var i = 0; i < totalRows; i++){
                    $scope.DataDetailList.push({'id':''
                                                ,'lost_in_process_id':''
                                                ,'lost_in_process_type':''
                                                ,'amount':''
                                                ,'value':''
                                                ,'production_sale_info_type1' : result.data.DATA[i].ProductMilk.DEFAULT 
                                                , 'production_sale_info_type2' : result.data.DATA[i].SubProductMilk.DEFAULT
                                                , 'production_sale_info_type3' : result.data.DATA[i].ProductMilkDetail.DEFAULT
                                            });

                    // console.log(result.data.DATA[i].ProductMilk);
                    $scope.ProductMilkList[i] = result.data.DATA[i].ProductMilk.DATA;
                    $scope.SubProductMilkList[i] = result.data.DATA[i].SubProductMilk.DATA;
                    $scope.ProductMilkDetailList[i] = result.data.DATA[i].ProductMilkDetail.DATA;
                }

                $scope.loadSaleChanel();
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.loadSaleChanelDefault = function(){
        // var params = {'actives':'Y', 'menu_type' : 'การสูญเสียในกระบวนการ'};
        var params = {'actives':'Y'};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('product-milk/sale-chanel/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.SaleChanelListDefault = result.data.DATA.List;
                IndexOverlayFactory.overlayHide();
                console.log($scope.SaleChanelListDefault);
            }
        });
    }

    $scope.SaleChanelList = [];
    $scope.loadSaleChanel = function(index){
        // var params = {'actives':'Y', 'menu_type' : 'การสูญเสียในกระบวนการ'};
        // var params = {'actives':'Y'};
        // IndexOverlayFactory.overlayShow();
        // HTTPService.clientRequest('product-milk/sale-chanel/list', params).then(function(result){
        //     if(result.data.STATUS == 'OK'){
        //         $scope.SaleChanelList[index] = result.data.DATA.List;
        //         IndexOverlayFactory.overlayHide();
        //     }
        // });
        $scope.SaleChanelList = [$scope.DataDetailList.length];
        for(var i = 0; i < $scope.DataDetailList.length; i++){
            $scope.SaleChanelList[i] = angular.copy($scope.SaleChanelListDefault);
            console.log($scope.SaleChanelList);
        }
    }

    $scope.ProductMilkList = [];
    $scope.loadProductMilk = function(index){
        // var params = {'actives':'Y', 'menu_type' : 'การสูญเสียในกระบวนการ'};
        var params = {'actives':'Y', 'facid':$scope.Data.factory_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('product-milk/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.ProductMilkList[index] = result.data.DATA.List;
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.SubProductMilkList = [];
    $scope.loadSubProductMilk = function(product_milk_id, index){
        var params = {'product_milk_id':product_milk_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('subproduct-milk/list/byparent', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.SubProductMilkList[index] = result.data.DATA.List;
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.ProductMilkDetailList = [];
    $scope.loadProductMilkDetail = function(subproduct_milk_id, index){
        var params = {'sub_product_milk_id':subproduct_milk_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('product-milk-detail/list/byparent', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.ProductMilkDetailList[index] = result.data.DATA.List;
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.loadData = function(action, id){

        $scope.loadSaleChanelDefault();
        
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
                $scope.DataDetailList = $scope.Data.production_sale_info_detail;
                console.log($scope.Data);

                $scope.getUploadLogList();
                /*$scope.ProductMilkList = [$scope.DataDetailList.length];
                $scope.SubProductMilkList = [$scope.DataDetailList.length];
                $scope.ProductMilkDetailList = [$scope.DataDetailList.length];
                // load sub dar=iry farming
                for(var i =0; i < $scope.DataDetailList.length; i++){
                    $scope.loadSaleChanel(i);
                    $scope.loadProductMilk(i);
                    $scope.loadSubProductMilk($scope.DataDetailList[i].production_sale_info_type1, i);
                    $scope.loadProductMilkDetail($scope.DataDetailList[i].production_sale_info_type2, i);
                }*/
                IndexOverlayFactory.overlayHide();
            }else{
                if($scope.Data.id != ''){
                    $scope.Data.id = '';
                }

                $scope.loadDefaultProductMilk();

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
        HTTPService.clientRequest('production-sale-info/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                // if($scope.ID !== undefined && $scope.ID !== null){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/production-sale-info';///update/' + result.data.DATA.id;
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/production-sale-info';
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
        $scope.loadData('production-sale-info/get');
        
        // $scope.DataDetailList = [
        //     {
        //         'id':''
        //         ,'production-sale-info_id':'1'
        //         ,'amount':'2'
        //         ,'price':'200'
        //         ,'values':'400'
        //     },
        //     {
        //         'id':''
        //         ,'production-sale-info_id':'2'
        //         ,'amount':'4'
        //         ,'price':'400'
        //         ,'values':'1600'
        //     }
        // ];
    }

    $scope.addDataDetail = function(){
        var detail = {
                'id':''
                ,'lost_in_process_id':''
                ,'lost_in_process_type':''
                ,'amount':''
                ,'value':''
            };

        $scope.DataDetailList.push(detail);

        var index = $scope.DataDetailList.length - 1;
        $scope.ProductMilkList.push({});
        $scope.SubProductMilkList.push({});
        $scope.ProductMilkDetailList.push({});
        
        $scope.loadProductMilk(index);
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
                HTTPService.clientRequest('production-sale-info/delete/detail', params).then(function(result){
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
            HTTPService.uploadRequest('production-sale-info/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/production-sale-info';///update/' + Data.id;
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
            HTTPService.uploadRequest('production-sale-info/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/production-sale-info';///update/' + Data.id;
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
        var params = {'id' : $scope.Data.id, 'menu_type' : 'production-sale-info'};
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
        var params = {'Data' : Data, 'AttachFile' : AttachFile, 'menu_type' : 'production-sale-info', 'FileDate' : $scope.FileDate};
            HTTPService.uploadRequest('production-sale-info/upload', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('อัพโหลดสำเร็จ');
                    window.location.href = '#/production-sale-info';///update/' + Data.id;
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
        HTTPService.clientRequest('production-sale-info/load/template', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                window.location.href="../" + result.data.DATA;
            }
            IndexOverlayFactory.overlayHide();
        });
    }


    $scope.FileDate = null;

    $scope.setData();
    $scope.loadFactoryList();
    $scope.loadProductMilk(0);

    // $scope.loadDairyFarming('MAIN', '');
    // $scope.loadDairyFarming('CHILD', '');


});