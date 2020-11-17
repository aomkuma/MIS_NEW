angular.module('e-homework').controller('UpdatePMDController', function ($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
    //console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');

    if ($user_session != null) {
        $scope.$parent.currentUser = angular.fromJson($user_session);
    } else {
        window.location.replace('#/guest/logon');
    }
    $scope.ID = $routeParams.id;

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));


    $scope.loadData = function (action, id) {
        var params = {
            'id': id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function (result) {
           // console.log(result.data.DATA.Data.subid);
            if (result.data.STATUS == 'OK') {
                $scope.Data = result.data.DATA.Data;
                $scope.Subdata = {
                    'id': result.data.DATA.Data.id
                    , 'name': result.data.DATA.Data.name
                    , 'sub_product_milk_id': result.data.DATA.Data.subid
                    , 'agent' : result.data.DATA.Data.agent
                    , 'number_of_package' : result.data.DATA.Data.number_of_package
                    , 'amount' : result.data.DATA.Data.amount
                    , 'amount_unit' : result.data.DATA.Data.amount_unit
                    , 'unit' : result.data.DATA.Data.unit
                    , 'taste' : result.data.DATA.Data.taste
                    
                    , 'actives': 'Y'

                };
                $scope.loadList('product-milk-detail/list/byparent2',  $scope.Subdata.sub_product_milk_id);
            }
            IndexOverlayFactory.overlayHide();
        });
    }
     $scope.goUpdate = function (detailid) {
        window.location.href = '#/product-milk-detail/update/' + detailid + '-xx';
    }
    $scope.loadList = function (action, id) {
        var params = {
            'id': id
        };

        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                $scope.List2 = result.data.DATA.List;
                // console.log($scope.UserList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.save = function (Subdata) {
        var params = {'Subdata': Subdata};
        console.log(params);
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('product-milk-detail/update', params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                // if($scope.ID !== undefined && $scope.ID !== null){
                // window.location.href = '#/master-goal/update/' + result.data.DATA.id;
                window.location.href = '#/product-milk-detail';
                // }else{
                //     location.reload();    
                // }

            } else {
                alert(result.data.DATA);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.cancelUpdate = function () {
        if($scope.Data.product_milk_id != undefined && $scope.Data.product_milk_id != null && $scope.Data.product_milk_id != ''){
            window.location.href = '#/subproduct-milk/update/' + $scope.Data.product_milk_id;//'#/product-milk-detail';
        }else{
            // window.location.href = '#/subproduct-milk/update/' + $scope.ID;//'#/product-milk-detail';   
            history.back(); 
        }
        
    }



    var size = $scope.ID;

    var cksize = size.split('-');

    if ($scope.ID !== undefined && $scope.ID !== null && cksize.length === 1) {

        $scope.loadData('subproduct-milk/get', $scope.ID);
        $scope.Subdata = {
            'id': ''
            , 'name': ''
            , 'sub_product_milk_id': $scope.ID

            , 'actives': 'Y'
            , 'create_date': ''
            , 'update_date': ''
        };
    } else {

        $scope.loadData('product-milk-detail/get', $scope.ID);

    }
    $scope.loadList('product-milk-detail/list/byparent2', $scope.ID);

});