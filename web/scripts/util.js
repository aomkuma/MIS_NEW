if (!String.prototype.startsWith) {
  String.prototype.startsWith = function(searchString, position) {
    position = position || 0;
    return this.indexOf(searchString, position) === position;
  };
}

function getMenuName(page_type){
    if(page_type == 'history'){
        return {'menu_name_th':'เกี่ยวกับหน่วยงาน', 'sub_menu':{'menu_name_th':'ประวัติความเป็นมา'}};
    }
    if(page_type == 'authority'){
        return {'menu_name_th':'เกี่ยวกับหน่วยงาน', 'sub_menu':{'menu_name_th':'อำนาจหน้าที่'}};
    }
    if(page_type == 'structure'){
        return {'menu_name_th':'เกี่ยวกับหน่วยงาน', 'sub_menu':{'menu_name_th':'โครงสร้างหน่วยงาน'}};
    }
    if(page_type == 'law'){
        return {'menu_name_th':'กฎหมาย/ระเบียบที่เกี่ยวข้อง'};
    }
    if(page_type == 'news'){
        return {'menu_name_th':'ข่าวประชาสัมพันธ์'};
    }
    if(page_type == 'tradition'){
        return {'menu_name_th':'ขั้นตอนปฏิบัติเกี่ยวกับการเสนอต่อ ครม.'};
    }
    if(page_type == 'resolution'){
        return {'menu_name_th':'มติ ครม. ที่เกี่ยวข้อง'};
    }
    if(page_type == 'relatelink'){
        return {'menu_name_th':'ลิ้งค์ที่เกี่ยวข้อง'};
    }
}

function encodeValue(val) {

	var dt = new Date();
	var rand_num = Math.floor((Math.random() * 10) + 1);
	rand_num = rand_num == 0 ? 1 : rand_num;
	rand_num = 3;
	var str = window.btoa(val);
	for (var i = 1; i < rand_num; i++) {
		str = window.btoa(str);
	}
	// window.btoa(window.btoa(dt.getMilliseconds + window.btoa(val)));

	return window.btoa(rand_num + "::" + dt.getMilliseconds() + "::" + str);

}

function decodeBase64(val){
	return window.atob(val);
}

function regExValidate(fieldVal, regExType) {
	if (regExType == 'AZaz09') {
		return (/^[a-z0-9][a-z0-9]*$/i).test(fieldVal);
	} else if (regExType == 'EMAIL') {
		return (/\S+@\S+/).test(fieldVal);
	} else if (regExType == '09') {
		return (/^[0-9]+[0-9]/).test(fieldVal);
	}
}

// Number.prototype.toFixed = function(decimalPlaces) {
// 	return toFixedPoint((this * 1).toPrecision(10), decimalPlaces);
// };

function toFixedPoint(num, dotplace) {
	num = num + '';
	// console.log(num);
	if (num.indexOf('.') !== -1) {
		var precision = 6;
		var defaultZero = '000000';
		var num_separate = num.split('.');
		var n = parseInt(num_separate[0]);
		
		var firstIndex = '';
		if(num.indexOf('-')==0 && n == 0){
			firstIndex = '-';
		}
			
		var frac = num_separate[1];
		var fracPre = frac.substring(0, precision);
		fracPre = (fracPre + defaultZero).substring(0, precision);
		fracPre = parseInt(fracPre);
		var fracLim = frac.substring(0, dotplace);
		fracLim = (fracLim + defaultZero).substring(0, precision);
		fracLim = parseInt(fracLim);
		var fracPreLen = (fracPre + '').length;
		var prefixCnt = 0;
		// console.log("fracPreLen < precision ",fracPreLen, precision);
		if (fracPreLen < precision) {
			if (fracPre != '') {
				prefixCnt = precision - (fracPre + '').length;
				if (prefixCnt > dotplace) {
					prefixCnt = dotplace;
					//return prefixCnt;
				}
			}
		}

		var half = Math.pow(10, (precision - dotplace - 1)) * 5;
		var fracLimHalf = parseInt(fracLim) + half;

		fracPre = parseFloat(fracPre);
		fracLimHalf = parseFloat(fracLimHalf);
		fracLim = parseFloat(fracLim);
		half = parseFloat(half);
		// console.log(fracPre, fracLimHalf, fracLim, half);
		var fracRound = 0;
		if (fracPre >= fracLimHalf) {
			fracRound = fracLimHalf + half;

			if ((fracRound + '').length > fracPreLen) {
				// alert((fracRound+'').length +'>'+ precision);return;
				if ((fracRound + '').length > precision) {
					n += (n > 0) ? 1 : -1;
					fracRound = 0;
					prefixCnt = 0;
				} else {
					prefixCnt--;
					if (prefixCnt < 0) {
						prefixCnt = 0;
					}
				}
			}
		} else {
			fracRound = fracLim;
		}

		var fracRoundLen = dotplace - prefixCnt;
		fracRound = fracRound.toString().substring(0, fracRoundLen);
		fracRound = (fracRound + defaultZero).substring(0, fracRoundLen);
		for ( i = 0; i < prefixCnt; i++) {
			fracRound = '0' + fracRound;
		}
		// alert('modify function result : '+n + '.' + fracRound+'\n'
		// +'js function result : '+parseFloat(num).toFixed(2));
		return firstIndex + n + '.' + fracRound;
	} else {
		var decimalstr = parseInt(dotplace) > 0 ? '.' + ('000000').substring(0, dotplace) : '';
		return num + decimalstr;
	}

}

function makeDate(dateInput){
    var d2 = dateInput.split('-');
    var d = new Date();
    d.setDate(d2[2]);
    d.setMonth(d2[1] - 1);
    d.setYear(d2[0]);
     
    return d;
}

function makeDateTime(dateInput){
    // dateInput = dateInput.replace('.000','');
    var d1 = dateInput.split(' ');
    // d1[0] = date , d1[1] = time
    var d2 = d1[0].split('-');
    var d3 = d1[1].split(':');
    var d = new Date();
    d.setDate(d2[2]);
    d.setMonth(d2[1] - 1);
    d.setYear(d2[0]);
    d.setSeconds(d3[2]);
    d.setMinutes(d3[1]);
    d.setHours(d3[0]);
    
    return d;
}

function convertDateToSQLString(dateInput){
    // console.log(dateInput);
    // dateInput = dateInput.replace('.000','');
    var d1 = dateInput.split(' ');
    // d1[0] = date , d1[1] = time
    var d2 = d1[0].split('-');
    var d3 = d1[1].split(':');
    var d = new Date(d2[0], parseInt(d2[1]) - 1, d2[2]);
    // d.setDate(31);
    // d.setMonth(parseInt(d2[1]) - 1);
    // d.setYear(d2[0]);
    d.setSeconds(d3[2]);
    d.setMinutes(d3[1]);
    d.setHours(d3[0]);
    // console.log(d);
    return d;
}
function convertTimeToSQLString(dateInput){
    // dateInput = dateInput.replace('.000','');
    console.log(dateInput);
    var d1 = dateInput.split(' ');
    // d1[0] = date , d1[1] = time
    var d2 = d1[0].split('-');
    var d3 = d1[1].split(':');
    
    return d3[0] + ':' + d3[1];
}

function concatDateTime(d , t){
    // console.log(d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + ' ' + t + ':00.000');
    var times = t.split(':');
    d.setHours(times[0]);
    d.setMinutes(times[1]);
    d.setSeconds(0);
    console.log(d);
    return d;
    // return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + ' ' + t + ':00.000';
}
function concatDateTimeSQL(d , t){
    // console.log(d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + ' ' + t + ':00.000');
    // var times = t.split(':');
    // d.setHours(times[0]);
    // d.setMinutes(times[1]);
    // d.setSeconds(0);
    // console.log(d);
    // return d;
    return d.getFullYear() + '-' + padLeft(""+(d.getMonth() + 1), '00') + '-' + padLeft(""+(d.getDate()), '00') + ' ' + t + ':00';
}

function padLeft(str, pad){
    return pad.substring(0, pad.length - str.length) + str;
}

function makeSQLDate(dateObj){
  var m = ((dateObj.getMonth() + 1) < 10)?('0'+(dateObj.getMonth() + 1)):(dateObj.getMonth() + 1);  
  var d = dateObj.getDate() < 10?('0'+dateObj.getDate()):dateObj.getDate(); 
  return dateObj.getFullYear() + '-' + m + '-' + d;
}

function addDays(date, days) {
        var result = new Date(date);
        result.setDate(date.getDate() + days);
        return result;
    }
    
function convertDateToThai(dateObj){
    var dateTxt = '';
    var monthTxt = '';
    switch(dateObj.getDay()){
        case 5 : dateTxt = 'ศ.';break;
        case 6 : dateTxt = 'ส.';break;
        case 0 : dateTxt = 'อา.';break;
        case 1 : dateTxt = 'จ.';break;
        case 2 : dateTxt = 'อ.';break;
        case 3 : dateTxt = 'พ.';break;
        case 4 : dateTxt = 'พฤ.';break;
    } 
    switch(dateObj.getMonth()){
        case 0 : monthTxt = 'มค.';break;
        case 1 : monthTxt = 'กพ.';break;
        case 2 : monthTxt = 'มี.ค.';break;
        case 3 : monthTxt = 'เม.ย.';break;
        case 4 : monthTxt = 'พค.';break;
        case 5 : monthTxt = 'มิ.ย.';break;
        case 6 : monthTxt = 'กค.';break;
        case 7 : monthTxt = 'สค.';break;
        case 8 : monthTxt = 'กย';break;
        case 9 : monthTxt = 'ตค.';break;
        case 10 : monthTxt = 'พย.';break;
        case 11 : monthTxt = 'ธค.';break;
    }
   
    return dateTxt + ' ' + dateObj.getDate() + ' ' + monthTxt;
}

function convertDateToFullThaiDate(dateObj){
    var dateTxt = '';
    var monthTxt = '';
    switch(dateObj.getDay()){
        case 5 : dateTxt = 'ศุกร์';break;
        case 6 : dateTxt = 'เสาร์';break;
        case 0 : dateTxt = 'อาทิตย์';break;
        case 1 : dateTxt = 'จันทร์';break;
        case 2 : dateTxt = 'อังคาร';break;
        case 3 : dateTxt = 'พุธ';break;
        case 4 : dateTxt = 'พฤหัสบดี';break;
    } 
    switch(dateObj.getMonth()){
        case 0 : monthTxt = 'มกราคม';break;
        case 1 : monthTxt = 'กุมภาพันธ์';break;
        case 2 : monthTxt = 'มีนาคม';break;
        case 3 : monthTxt = 'เมษายน';break;
        case 4 : monthTxt = 'พฤษภาคม';break;
        case 5 : monthTxt = 'มิถุนายน';break;
        case 6 : monthTxt = 'กรกฎาคม';break;
        case 7 : monthTxt = 'สิงหาคม';break;
        case 8 : monthTxt = 'กันยายน';break;
        case 9 : monthTxt = 'ตุลาคม';break;
        case 10 : monthTxt = 'พฤศจิกายน';break;
        case 11 : monthTxt = 'ธันวาคม';break;
    }
   
   var minute = ''+dateObj.getMinutes();
   //console.log(minute.length);
   if(minute.length == 1){
      minute = '0' + minute;
   }
    return dateObj.getDate() + ' ' + monthTxt + ' ' + (dateObj.getFullYear()) + ' ' + dateObj.getHours() + ':' + minute;
}

function convertDateToFullThaiDateIgnoreTime(dateObj){
  var dateTxt = '';
    var monthTxt = '';
    switch(dateObj.getDay()){
        case 5 : dateTxt = 'ศุกร์';break;
        case 6 : dateTxt = 'เสาร์';break;
        case 0 : dateTxt = 'อาทิตย์';break;
        case 1 : dateTxt = 'จันทร์';break;
        case 2 : dateTxt = 'อังคาร';break;
        case 3 : dateTxt = 'พุธ';break;
        case 4 : dateTxt = 'พฤหัสบดี';break;
    } 
    switch(dateObj.getMonth()){
        case 0 : monthTxt = 'มกราคม';break;
        case 1 : monthTxt = 'กุมภาพันธ์';break;
        case 2 : monthTxt = 'มีนาคม';break;
        case 3 : monthTxt = 'เมษายน';break;
        case 4 : monthTxt = 'พฤษภาคม';break;
        case 5 : monthTxt = 'มิถุนายน';break;
        case 6 : monthTxt = 'กรกฎาคม';break;
        case 7 : monthTxt = 'สิงหาคม';break;
        case 8 : monthTxt = 'กันยายน';break;
        case 9 : monthTxt = 'ตุลาคม';break;
        case 10 : monthTxt = 'พฤศจิกายน';break;
        case 11 : monthTxt = 'ธันวาคม';break;
    }
    return dateObj.getDate() + ' ' + monthTxt + ' ' + (dateObj.getFullYear() + 543);
}

function convertDateToReportDate(dateObj){
  var dateTxt = '';
    var monthTxt = '';
    switch(dateObj.getDay()){
        case 5 : dateTxt = 'ศุกร์';break;
        case 6 : dateTxt = 'เสาร์';break;
        case 0 : dateTxt = 'อาทิตย์';break;
        case 1 : dateTxt = 'จันทร์';break;
        case 2 : dateTxt = 'อังคาร';break;
        case 3 : dateTxt = 'พุธ';break;
        case 4 : dateTxt = 'พฤหัสบดี';break;
    } 
    switch(dateObj.getMonth()){
        case 0 : monthTxt = 'มกราคม';break;
        case 1 : monthTxt = 'กุมภาพันธ์';break;
        case 2 : monthTxt = 'มีนาคม';break;
        case 3 : monthTxt = 'เมษายน';break;
        case 4 : monthTxt = 'พฤษภาคม';break;
        case 5 : monthTxt = 'มิถุนายน';break;
        case 6 : monthTxt = 'กรกฎาคม';break;
        case 7 : monthTxt = 'สิงหาคม';break;
        case 8 : monthTxt = 'กันยายน';break;
        case 9 : monthTxt = 'ตุลาคม';break;
        case 10 : monthTxt = 'พฤศจิกายน';break;
        case 11 : monthTxt = 'ธันวาคม';break;
    }
    //return 'วันที่ ' + dateObj.getDate() + ' เดือน ' + monthTxt + ' พศ. ' + (dateObj.getFullYear() + 543);
    return [dateObj.getDate(), monthTxt, (dateObj.getFullYear() + 543)];
}

function convertSQLDateTimeToReportDateTime(d){
    if(d != null && d != ''){
        var dateObj = d.split(' ');
        var date = dateObj[0].split('-');
        var time = dateObj[1].split(':');
        return date[2] + '/' + date[1] + '/' + (parseInt(date[0]) + 543) + ' ' 
                + time[0] + '.' + time[1] + ' น.';
    }else{
        return '';
    }
}

function convertSQLDateTimeToReportDate(d){
    var date = d.split('-');
    var monthTxt = '';
    switch(parseInt(date[1])){
        case 1 : monthTxt = 'มกราคม';break;
        case 2 : monthTxt = 'กุมภาพันธ์';break;
        case 3 : monthTxt = 'มีนาคม';break;
        case 4 : monthTxt = 'เมษายน';break;
        case 5 : monthTxt = 'พฤษภาคม';break;
        case 6 : monthTxt = 'มิถุนายน';break;
        case 7 : monthTxt = 'กรกฎาคม';break;
        case 8 : monthTxt = 'สิงหาคม';break;
        case 9 : monthTxt = 'กันยายน';break;
        case 10 : monthTxt = 'ตุลาคม';break;
        case 11 : monthTxt = 'พฤศจิกายน';break;
        case 12 : monthTxt = 'ธันวาคม';break;
    }
    return parseInt(date[2]) + ' ' + monthTxt + ' ' + (parseInt(date[0]) + 543);
}

function getMonthList(){
    return [{'monthValue' : 1, 'monthText' : 'มกราคม'}
            , {'monthValue' : 2, 'monthText' : 'กุมภาพันธ์'}
            , {'monthValue' : 3, 'monthText' : 'มีนาคม'}
            , {'monthValue' : 4, 'monthText' : 'เมษายน'}
            , {'monthValue' : 5, 'monthText' : 'พฤษภาคม'}
            , {'monthValue' : 6, 'monthText' : 'มิถุนายน'}
            , {'monthValue' : 7, 'monthText' : 'กรกฎาคม'}
            , {'monthValue' : 8, 'monthText' : 'สิงหาคม'}
            , {'monthValue' : 9, 'monthText' : 'กันยายน'}
            , {'monthValue' : 10, 'monthText' : 'ตุลาคม'}
            , {'monthValue' : 11, 'monthText' : 'พฤศจิกายน'}
            , {'monthValue' : 12, 'monthText' : 'ธันวาคม'}
            ];
}

function getYearList(loop)
{
    var startYear = 2015;
    var yearList = [];
    for(var i =0 ; i < loop; i++){
        yearList.push({'yearValue': startYear + i + 543, 'yearText' : startYear + i});
    }
    return yearList;
}
function getYearListst(loop,st)
{
    var startYear = st;
    var yearList = [];
    for(var i =0 ; i < loop; i++){
        yearList.push({'yearValue': startYear + i + 543, 'yearText' : startYear + i});
    }
    return yearList;
}

function getDayList(loop)
{
    var dayList = [];
    for(var i =1 ; i <= 31; i++){
        dayList.push({'dayValue': i, 'dayText' : i});
    }
    return dayList;
}

function getThaiMonth()
{
    return [{monthText:'01',monthValue:'มกราคม'}
            ,{monthText:'02',monthValue:'กุมภาพันธ์'}
            ,{monthText:'03',monthValue:'มีนาคม'}
            ,{monthText:'04',monthValue:'เมษายน'}
            ,{monthText:'05',monthValue:'พฤษภาคม'}
            ,{monthText:'06',monthValue:'มิถุนายน'}
            ,{monthText:'07',monthValue:'กรกฎาคม'}
            ,{monthText:'08',monthValue:'สิงหาคม'}
            ,{monthText:'09',monthValue:'กันยายน'}
            ,{monthText:'10',monthValue:'ตุลาคม'}
            ,{monthText:'11',monthValue:'พฤศจิกายน'}
            ,{monthText:'12',monthValue:'ธันวาคม'}
            ];
}

function getThaiMonthInt(month)
{
    var monthTxt = '';
    switch(parseInt(month)){
        case 1 : monthTxt = 'มกราคม';break;
        case 2 : monthTxt = 'กุมภาพันธ์';break;
        case 3 : monthTxt = 'มีนาคม';break;
        case 4 : monthTxt = 'เมษายน';break;
        case 5 : monthTxt = 'พฤษภาคม';break;
        case 6 : monthTxt = 'มิถุนายน';break;
        case 7 : monthTxt = 'กรกฎาคม';break;
        case 8 : monthTxt = 'สิงหาคม';break;
        case 9 : monthTxt = 'กันยายน';break;
        case 10 : monthTxt = 'ตุลาคม';break;
        case 11 : monthTxt = 'พฤศจิกายน';break;
        case 12 : monthTxt = 'ธันวาคม';break;
    }

    return monthTxt;
}

function getMonthYearText(dateStr){
    var dateArr = dateStr.split('-');
    var month = dateArr[1];
    var year = dateArr[0];

    var monthTxt = '';
    switch(parseInt(month)){
        case 1 : monthTxt = 'มกราคม';break;
        case 2 : monthTxt = 'กุมภาพันธ์';break;
        case 3 : monthTxt = 'มีนาคม';break;
        case 4 : monthTxt = 'เมษายน';break;
        case 5 : monthTxt = 'พฤษภาคม';break;
        case 6 : monthTxt = 'มิถุนายน';break;
        case 7 : monthTxt = 'กรกฎาคม';break;
        case 8 : monthTxt = 'สิงหาคม';break;
        case 9 : monthTxt = 'กันยายน';break;
        case 10 : monthTxt = 'ตุลาคม';break;
        case 11 : monthTxt = 'พฤศจิกายน';break;
        case 12 : monthTxt = 'ธันวาคม';break;
    }

    return monthTxt + ' ปี  ' + (parseInt(year) + 543);
}

function getTotalDayInMonth(month, year){
    // var d = new Date(year, month, 0);
    // return d;
    var dayList = [];
    for(var i=1; i <= 31 ; i++){
        var day = i < 10?'0'+i:i;
        dayList.push({dayText:day,dayValue:i});
    }
    return dayList;
}

app.controller('ModalDialogCtrl', function ($scope, $uibModalInstance, params) {
	// console.log(params);
	
    $disabledOK = false;
    $scope.ok = function () {
        $uibModalInstance.close('OK');
    };
    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
});

app.controller('ModalDialogReturnFromOKBtnCtrl', function ($scope, $uibModalInstance, params) {
	// console.log(params);
	
    $disabledOK = false;
    $scope.ok = function (value) {
        $uibModalInstance.close(value);
    };
    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
});

app.directive('compile', ['$compile', function ($compile) {
      return function(scope, element, attrs) {
          scope.$watch(
            function(scope) {
               // watch the 'compile' expression for changes
              return scope.$eval(attrs.compile);
            },
            function(value) {
              // when the 'compile' expression changes
              // assign it into the current DOM
              element.html(value);

              // compile the new DOM and link it to the current
              // scope.
              // NOTE: we only compile .childNodes so that
              // we don't get into infinite loop compiling ourselves
              $compile(element.contents())(scope);
            }
        );
    };
}]);

app.directive('appDatepicker', function() {
  return {
      restrict: 'E',
      scope: {
          ngModel: "=",
          myid: "@",
          dateplaceholder:"@"

      },
      templateUrl: 'html/datepicker.html',
	  require: 'ngModel',
      link: function(scope, element, attrs, ngModel) {
        
          scope.popupOpen = false;
          scope.openPopup = function($event) {
              $event.preventDefault();
              $event.stopPropagation();
              scope.popupOpen = true;
          };

          scope.open = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            scope.opened = true;
          };
			//console.log(ngModel.$modelValue);
      }
  };
});

app.directive('compile', ['$compile', function ($compile) {
      return function(scope, element, attrs) {
          scope.$watch(
            function(scope) {
               // watch the 'compile' expression for changes
              return scope.$eval(attrs.compile);
            },
            function(value) {
              // when the 'compile' expression changes
              // assign it into the current DOM
              element.html(value);

              // compile the new DOM and link it to the current
              // scope.
              // NOTE: we only compile .childNodes so that
              // we don't get into infinite loop compiling ourselves
              $compile(element.contents())(scope);
            }
        );
    };
}]);

app.directive('scrolly', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            var raw = element[0];
            //console.log('loading directive');
                
            element.bind('scroll', function () {
                //console.log('in scroll');
                //console.log(raw.scrollTop + raw.offsetHeight);
                //console.log(raw.scrollHeight);
                if (Math.ceil(raw.scrollTop + raw.offsetHeight) >= raw.scrollHeight) {
                    //console.log("I am at the bottom");
                    scope.$apply(attrs.scrolly);
                }
            });
        }
    };
});

app.filter('FindUserID', function () {
    return function (input, val) {
        if (input !== undefined && input !== null) {
            var i = 0, len = input.length;
            for (; i < len; i++) {
                //console.log(input[i].UserID, '==' ,val);
                if (input[i].UserID == val) {
                    return i;
                }
            }
            return -1;
        } else {
            return -1;
        }
    };
});

app.filter('FindExUserID', function () {
    return function (input, val) {
        if (input !== undefined && input !== null) {
            var i = 0, len = input.length;
            for (; i < len; i++) {
                //console.log(input[i].UserID, '==' ,val);
                if (input[i].PhoneBookID == val) {
                    return i;
                }
            }
            return -1;
        } else {
            return -1;
        }
    };
});

app.filter('FindExUserName', function () {
    return function (input, val) {
        if (input !== undefined && input !== null) {
            var i = 0, len = input.length;
            for (; i < len; i++) {
                console.log(input[i].AttendeeName, '==' ,val);
                if (input[i].AttendeeName == val) {
                    return i;
                }
            }
            return -1;
        } else {
            return -1;
        }
    };
});

app.filter('FindDevice', function () {
    return function (input, val) {
        if (input !== undefined && input !== null) {
            var i = 0, len = input.length;
            for (; i < len; i++) {
                //console.log(input[i].UserID, '==' ,val);
                if (input[i].DeviceID == val) {
                    return i;
                }
            }
            return -1;
        } else {
            return -1;
        }
    };
});

app.filter('FindDataByID', function () {
    return function (input, val) {
        if (input !== undefined && input !== null) {
            var i = 0, len = input.length;
            for (; i < len; i++) {
                //console.log(input[i].UserID, '==' ,val);
                if (input[i].id == val) {
                    return i;
                }
            }
            return -1;
        } else {
            return -1;
        }
    };
});

app.filter('htmlToPlaintext', function () {
    return function(text) {
      return  text ? String(text).replace(/<[^>]+>/gm, '') : '';
    };
});

app.filter('cut', function () {
    return function (value, wordwise, max, tail) {
        if (!value) return '';

        max = parseInt(max, 10);
        if (!max) return value;
        if (value.length <= max) return value;

        value = value.substr(0, max);
        if (wordwise) {
            var lastspace = value.lastIndexOf(' ');
            if (lastspace !== -1) {
              //Also remove . and , so its gives a cleaner result.
              if (value.charAt(lastspace-1) === '.' || value.charAt(lastspace-1) === ',') {
                lastspace = lastspace - 1;
              }
              value = value.substr(0, lastspace);
            }
        }

        return value + (tail || ' …');
    };
});

app.filter('MenuFilter', function () {
    return function (input, val) {
        if (input !== undefined && input !== null) {
            var i = 0, len = input.length;
            for (; i < len; i++) {
                //console.log(input[i].MenuID, '==' ,val);
                if (input[i].AdminGroupID == val) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    };
});

app.filter('MenuTypeFilter', function () {
    return function (input, val) {
        if (input !== undefined && input !== null) {
            var i = 0, len = input.length;
            for (; i < len; i++) {
                console.log(input[i].name, '==' ,val);
                if (input[i].name == val) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    };
});

app.directive('convertToNumber', function() {
  return {
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      ngModel.$parsers.push(function(val) {
        return val != null ? parseInt(val, 10) : null;
      });
      ngModel.$formatters.push(function(val) {
        return val != null ? '' + val : null;
      });
    }
  };
});