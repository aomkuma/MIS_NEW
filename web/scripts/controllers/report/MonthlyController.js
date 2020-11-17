angular.module('e-homework').controller('MonthlyController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
	console.log('Hello veterinary !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'report';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    
    $scope.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));   

    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    $scope.exportReport = function(data,condition){
       // console.log(DetailList, $scope.data_description);
        // return;
        IndexOverlayFactory.overlayHide();
        var params = {
            'data' : data
           , 'condition' : condition
           , 'region' : $scope.PersonRegion
        }; 
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('monthreport/report', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                window.location.href="../" + result.data.DATA;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

});