angular.module('e-homework').controller('ImportController', function ($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {

    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');

    if ($user_session != null) {
        $scope.$parent.currentUser = angular.fromJson($user_session);
    } else {
        window.location.replace('#/guest/logon');
    }
    $scope.popup1 = {
        opened: false
    };



    $scope.open1 = function () {
        $scope.popup1.opened = true;
    };
    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));
    $scope.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));
    $scope.MonthList = getMonthList();
    $scope.YearList = getYearListst(10, 2018);
    // $scope.MonthList = getMonthList();
    $scope.updateData = function (Data, AttachFile) {


        var Update = angular.copy(Data);
        if (Update.date !== null && Update.date !== '') {
            Update.date = makeSQLDate(Update.date);
        }

        var params = {'Data': Update, 'AttachFile': AttachFile};
        // console.log(Data.date);
        HTTPService.uploadRequest('import-personal', params).then(function (result) {

            $scope.loadList('import-personal/list/main');
            if (result == 'OK') {
                alert('นำเข้าข้อมูลบุคลากรเรียบร้อย');
                $scope.Data = null;
                $scope.AttachFile = null;
            } else {
                alert('นำเข้าข้อมูลบุคลากรไม่เรียบร้อย');
            }
            IndexOverlayFactory.overlayHide();
        });

    }
    $scope.loadList = function (action) {

        //  IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action).then(function (result) {
            //  console.log(result);
            // if(result.data.STATUS == 'OK'){
            $scope.List = result.data;

            //  }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadList('import-personal/list/main');
    //  console.log($scope.List);


});