angular.module('e-homework').controller('SubcommitteeController', function ($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
    console.log('Hello veterinary !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'report';
    var $user_session = sessionStorage.getItem('user_session');

    if ($user_session != null) {
        $scope.$parent.currentUser = angular.fromJson($user_session);
    } else {
        window.location.replace('#/guest/logon');
    }


    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));
    $scope.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));

    $scope.Header = [];
    $scope.ViewType = 'MAIN';
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    var curDate = new Date();
    $scope.condition = {
        'DisplayType': 'monthly'
        , 'MonthFrom': curDate.getMonth() + 1
        , 'YearFrom': curDate.getFullYear()
        , 'MonthTo': curDate.getMonth() + 1
        , 'YearTo': curDate.getFullYear()
        , 'QuarterFrom': '1'
        , 'QuarterTo': '4'
        , 'date': convertDateToFullThaiDateIgnoreTime(curDate)
    };

    $scope.SummaryData = {
        'SummaryCurrentCow': ''
        , 'SummaryCurrentService': ''
        , 'SummaryCowPercentage': ''
        , 'SummaryServicePercentage': ''
    };

    $scope.ResultYearList = [
        {'years': (curDate.getFullYear() + 543)}
        , {'years': (curDate.getFullYear() + 543) - 1}
    ];

    $scope.exportReport = function (condition) {
        // console.log(DetailList, $scope.data_description);
        // return;
        IndexOverlayFactory.overlayHide();
        var params = {

            'condition': condition
            , 'region': $scope.PersonRegion

        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('subcommittee/report', params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                window.location.href = "../" + result.data.DATA;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

});