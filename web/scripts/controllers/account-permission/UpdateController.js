angular.module('e-homework').controller('UpdateAccController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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
    $scope.loadUser = function(action, id){
        var params = {'UserID' : id};
        IndexOverlayFactory.overlayShow();
        HTTPService.loginRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.User = result.data.DATA.MISUser;
                // console.log($scope.UserList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadMenuList = function(action){
        HTTPService.clientRequest(action, null).then(function(result){
            $scope.MenuList = result.data.DATA.Menu;
            $scope.loadAccountPermission('account-permission/get', $scope.ID);
        });
    }

    $scope.loadAccountPermission = function(action, id){
        var params = {'UserID' : id};
        HTTPService.clientRequest(action, params).then(function(result){
            var UserRole = result.data.DATA.Role;
            $scope.setRoles(UserRole);
            var PermissionList = result.data.DATA.Permission;
            $scope.setPermission(PermissionList);
        });
    }

    $scope.save = function(Role, Permission){
        var params = {'UserID' : $scope.ID
                    , 'Role' : Role
                    , 'Permission' : Permission
                    };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('account-permission/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                if($scope.ID !== undefined && $scope.ID !== null){
                    window.location.href = '#/account-permission/update/' + $scope.ID;
                }else{
                    location.reload(); 
                }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/account-permission';
    }

    $scope.setRoles = function(UserRole){
        for(var i = 0; i < UserRole.length; i++){
            if(UserRole[i].actives == 'Y'){
                if(UserRole[i].role == 1){
                    $scope.Role.Filler = true;
                }else if(UserRole[i].role == 2){
                    $scope.Role.Approver = true;
                }else if(UserRole[i].role == 3){
                    $scope.Role.General = true;
                }else if(UserRole[i].role == 4){
                    $scope.Role.Reporter = true;
                }else if(UserRole[i].role == 5){
                    $scope.Role.Admin = true;
                }
            }
        }
    }

    $scope.setPermission = function(Permission){
        // console.log($scope.MenuList);
        for(var i = 0; i < Permission.length; i++){
            for(var j = 0; j < $scope.MenuList.length; j++){
                for(var k = 0; k < $scope.MenuList[j].sub_menu.length; k++){
                    // console.log(Permission[i].menu_id , $scope.MenuList[j].sub_menu[k].id);
                    if(Permission[i].actives == 'Y'){
                        if(Permission[i].menu_id == $scope.MenuList[j].sub_menu[k].id){
                            $scope.MenuList[j].sub_menu[k].checked_menu = true;
                            console.log($scope.MenuList[j].sub_menu[k].checked_menu);
                        }
                    }
                }
            }
        }
        // console.log($scope.MenuList);
    }

    $scope.Role = {
        'Filler':false
        ,'Approver':false
        ,'General':false
        ,'Reporter':false
        ,'Admin':false
    };

    if($scope.ID !== undefined && $scope.ID !== null){
        $scope.loadMenuList('menu/list');
        $scope.loadUser('mis/get/user', $scope.ID);
    }

});