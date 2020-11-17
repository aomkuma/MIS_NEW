function returnResponse(response){
   if(!tryParseJSON(response.data)){

      //the json is ok
        return response.data;
    }else{

      //the json is not ok
        console.log(response.data);
        return false;
    }
}

function returnErrorResponse(errResponse){
    var errorDesc = errResponse.status + ' : ' + errResponse.statusText ;
    
    if(errResponse.data.DATA != null){
        errorDesc += ':: Cause ' + errResponse.data.DATA.errorInfo[2];
        alert( errorDesc );
    }else{
        if(errResponse.status == 401){
            alert( 'ไม่มีสิทธิ์เข้าใช้งานในหน้านี้' );
            window.location.replace("#/");
        }else{
            alert( errorDesc );
        }
        
    }

    console.error('Error while fetching specific Item', errResponse);
    return errResponse;
}

function tryParseJSON (jsonString){
    try {
        var o = JSON.parse(jsonString);
        if (o && typeof o === "object") {
            return o;
        }
    }
    catch (e) { //alert(jsonString); 
    }

    return false;
};

app.factory('IndexOverlayFactory', function(){
	var indexVar = 
	{
		overlay : false,
		overlayHide : function() {this.overlay = false},
		overlayShow : function() {this.overlay = true}
	};	
	
	return indexVar;
});

app.factory('HTTPService', ['$http', '$q', 'Upload', function($http, $q, Upload){

    return {
        loginRequest : function(action, obj) {
            return $http.post(serviceLoginUrl + action + '/',{"obj_login":obj})
                .then(
                    function(response){
                        return returnResponse(response);                    
                    }, 
                    function(errResponse){
                        return returnErrorResponse(errResponse);
                    }
                );
        },

        clientRequest : function(action, obj) {
            var user_session = angular.fromJson(sessionStorage.getItem('user_session'));
            console.log(user_session);
            return $http.post(serviceUrl + action + '/',{"obj":obj, 'user_session' : user_session})
                .then(
                    function(response){
                        return returnResponse(response);                    
                    }, 
                    function(errResponse){
                        return returnErrorResponse(errResponse);
                    }
                );
        },

        deleteRequest : function(action, id) {
            return $http.delete(serviceUrl + action + '/' + id)
                .then(
                    function(response){
                        return returnResponse(response);                    
                    }, 
                    function(errResponse){
                        return returnErrorResponse(errResponse);
                    }
                );
        },

        uploadRequest : function(action, obj) {
            var user_session = angular.fromJson(sessionStorage.getItem('user_session'));
            console.log(user_session);
            return Upload.upload({
                url: serviceUrl + action + '/',
                data: {"obj":obj, 'user_session' : user_session}
            }).then(
                function(response){
                    return returnResponse(response);                    
                }, 
                function(errResponse){
                    return returnErrorResponse(errResponse);
                }
            );
        }
    }
}]);
