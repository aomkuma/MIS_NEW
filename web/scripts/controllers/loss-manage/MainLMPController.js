angular.module('e-homework').controller('MainLMPController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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

    $scope.loadData = function(){
        var params = {
            'factory_id' : $scope.Data.factory_id
            , 'loss_type' : $scope.Data.loss_type
            , 'loss_id' : $scope.Data.loss_id
            , 'product_milk_id' : $scope.Data.product_milk_id
            , 'subproduct_milk_id' : $scope.Data.subproduct_milk_id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('loss-manage/mapping/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.HistoryList = result.data.DATA.Data;
                // $scope.changeGoalType();
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Data){
        console.log(Data);
        var params = {'Data' : Data};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('loss-manage/mapping/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                // if($scope.ID !== undefined && $scope.ID !== null){
                    // window.location.href = '#/master-goal/update/' + result.data.DATA.id;
                    // window.location.href = '#/loss-mapping';
                // }else{
                    location.reload();    
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

    $scope.loadMasterLoss = function(){
        var params = {'actives' : 'Y'};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('loss-manage/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.MasterLossList = result.data.DATA.List;
            }else{
                alert(result.data.DATA);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadFactoryList = function(){
      var params = {'region' : $scope.PersonRegion};
        HTTPService.clientRequest('factory/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.FactoryList = result.data.DATA.DataList;

            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.ProductMilkList = [];
    $scope.loadProductMilk = function(index){
        // var params = {'actives':'Y', 'menu_type' : 'การสูญเสียในกระบวนการ'};
        var params = {'actives':'Y', 'facid':$scope.Data.factory_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('product-milk/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.ProductMilkList = result.data.DATA.List;
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.SubProductMilkList = [];
    $scope.loadSubProductMilk = function(product_milk_id){
        var params = {'product_milk_id':product_milk_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('subproduct-milk/list/byparent', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.SubProductMilkList = result.data.DATA.List;
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.ProductMilkDetailList = [];
    $scope.loadProductMilkDetail = function(subproduct_milk_id){
        var params = {'sub_product_milk_id':subproduct_milk_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('product-milk-detail/list/byparent', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.ProductMilkDetailList = result.data.DATA.List;
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.removeData = function(id){
        $scope.alertMessage = 'ต้องการลบข้อมูลนี้ ใช่หรือไม่ ?';
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
            HTTPService.clientRequest('loss-manage/mapping/delete', params).then(function(result){
                // $scope.load('Datas');
                if(result.data.STATUS == 'OK'){
                    location.reload();
                }
                IndexOverlayFactory.overlayHide();
            });
        });
        
    }

    $scope.createToMasterGoal = function(id){

        $scope.alertMessage = 'ต้องการสร้างข้อมูลนี้ไปยังรายการเป้า ใช่หรือไม่ ?';
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

            HTTPService.clientRequest('loss-manage/mapping/master-goal/create', params).then(function(result){
                // $scope.load('Datas');
                if(result.data.STATUS == 'OK'){
                    // location.reload();
                }
                IndexOverlayFactory.overlayHide();
            });
        });
    }

    $scope.Data = {'factory_id' : null, 'loss_id' : null, 'loss_type' : '', 'product_milk_id' : null, 'subproduct_milk_id' : null};

    $scope.loadData();
    $scope.loadFactoryList();
    $scope.loadMasterLoss();
    

});