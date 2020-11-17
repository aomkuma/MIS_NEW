angular.module('e-homework').controller('MainSPMController', function ($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
    //console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');

    if ($user_session != null) {
        $scope.$parent.currentUser = angular.fromJson($user_session);
    } else {
        window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));

    $scope.loadList = function (action) {
        var params = {'condition': $scope.condition};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                $scope.List = result.data.DATA.List;
                // console.log($scope.UserList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.getThaiDate = function (date) {
        // console.log(date);
        return convertDateToFullThaiDate(new Date(date));
    }
     $scope.cancelUpdate = function () {
        window.location.href = '#/product-milk';
    }


    $scope.goUpdate = function (id) {
        window.location.href = '#/subproduct-milk/update/' + id;
    }

  $scope.goEdit = function (detailid) {
        window.location.href = '#/subproduct-milk/update/' + detailid + '-xx';
    }

    $scope.goAddsub = function (id) {
        window.location.href = '#/product-milk-detail/update/' + id;
    }

    $scope.loadList('subproduct-milk/list', '');


});