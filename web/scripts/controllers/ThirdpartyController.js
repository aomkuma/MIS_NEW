angular.module('e-homework').controller('ThirdpartyController',function($scope, $routeParams, HTTPService, IndexOverlayFactory){
    
    $scope.user = {'Username':$routeParams.username,'LoginSession':$routeParams.loginSession};

    var reDirect = '';
    if($routeParams.redirect_url !== undefined){
        reDirect = $routeParams.redirect_url;
        console.log(reDirect);
    }
    $scope.showError = false; // set Error flag
    $scope.showSuccess = false; // set Success Flag
    
    //------- Authenticate function
    $scope.authenticate = function (action, data){
        var flag= false;
        $scope.showError = false;
        $scope.showSuccess = false;
        IndexOverlayFactory.overlayShow();
        HTTPService.loginRequest(action, data).then(function(user){
            // console.log(user);
            if(user.data.STATUS == 'OK'){

                var params = {'UserID' : user.data.DATA.UserData.UserID};
                HTTPService.clientRequest('login/check-permission', params).then(function(result){

                    if(result.data.STATUS == 'OK'){
                        // Load menu
                        action = 'menu/list';
                        HTTPService.clientRequest(action, data).then(function(menu){
                            sessionStorage.setItem('menu_session' , JSON.stringify(menu.data.DATA.Menu));
                        });

                        $scope.showError = false;
                        $scope.showSuccess = true;
                        sessionStorage.setItem('user_session' , JSON.stringify(user.data.DATA.UserData));

                        sessionStorage.setItem('person_region_session' , JSON.stringify(user.data.DATA.PersonRegion));
                        setTimeout(function(){
                            window.location.replace('#/' + reDirect);    
                        }, 1000);

                    }else{
                        alert('คุณยังไม่มีสิทธิ์ในการเข้าใช้งาน กรุณาติดต่อผู้ดูแลระบบ');
                    }
                    
                });
            }else{
                $scope.showError = true;
                $scope.showSuccess = false;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.authenticate('login/thirdparty/session', $scope.user);

});
