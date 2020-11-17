angular.module('e-homework').controller('MainPRController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
	
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'personal';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    
    $scope.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));   

    $scope.loadList = function(action){
        $scope.CurYear = $scope.condition.YearTo + 543;
        $scope.LastYear = $scope.CurYear - 1;
        var params = {
            'condition' : $scope.condition
            //, 'region' : $scope.PersonRegion
        };
       //  console.log(params);
      //  IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.List = result.data.DATA.DataList;
                $scope.SummaryData = result.data.DATA.Summary;
                 $scope.s2 = result.data.DATA.s2;
             //   console.log( $scope.SummaryData);
            }
            IndexOverlayFactory.overlayHide();
        });
    }
$scope.viewDetail = function(){
        $scope.ViewType = 'DETAIL';
        
        $scope.loadList('personal/list/main');
    }
    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    $scope.goUpdate = function(id){
        window.location.href = '#/veterinary/update/' + id;
    }

    $scope.goSearch = function(){
        $scope.ViewType = 'MAIN';
        $scope.loadList('personal/list/main');
    }

    $scope.numberFormat = function(num){
        if(num == null){
            return '';
        }
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    $scope.numberFormatComma = function(num){
        if(num == null){
            return '';
        }
        return num.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }


    $scope.Header = [];
    $scope.ViewType = 'MAIN';
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    var curDate = new Date();
    $scope.condition = {
                        'DisplayType':'monthly'
                        ,'MonthFrom' : curDate.getMonth() + 1
                        ,'YearFrom': curDate.getFullYear()
                        ,'MonthTo' : curDate.getMonth() + 1
                        ,'YearTo': curDate.getFullYear()
                        ,'QuarterFrom':'1'
                        ,'QuarterTo':'4'
                    };

   

    $scope.ResultYearList = [
                {'years' : (curDate.getFullYear() + 543)}
                ,{'years' : (curDate.getFullYear() + 543) - 1}
            ];
//console.log($scope.condition);
    // $scope.loadList('veterinary/list', '');
    IndexOverlayFactory.overlayHide();
   
  //  setTimeout(function(){
      $scope.loadList('personal/list/main');
        
    //},200);
    

});