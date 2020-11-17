<?php
	
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
	//header("Content-Type: application/xml; charset=utf-8");

	header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");//Dont cache
	header("Pragma: no-cache");//Dont cache
	header("Expires: " . date('D, d M Y H:i:s'));
	
?>
<!DOCTYPE html>
<html lang="en-US" ng-app="e-homework">
	<head>
		<meta charset="utf-8">

		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Management Infomation System (MIS)</title>
		<meta name="description" content="">
		<meta name="author" content="DPO">

		<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
		<link rel="shortcut icon" type="image/png" href="favicon.ico"/>
		<!-- include js -->
		<script type="text/javascript" src='scripts/node_modules/angular/angular.min.js' charset="utf-8"></script>
		<script type="text/javascript" src='scripts/node_modules/angular-ui-bootstrap/dist/ui-bootstrap-tpls.js' charset="utf-8"></script>
		<script type="text/javascript" src='scripts/node_modules/angular-route/angular-route.min.js' charset="utf-8"></script>
		<script type="text/javascript" src='scripts/node_modules/angular-animate/angular-animate.min.js' charset="utf-8"></script>
		<script type="text/javascript" src="scripts/node_modules/angular-cookies/angular-cookies.min.js"></script>
		<script type="text/javascript" src="scripts/node_modules/angular-ui-router/release/angular-ui-router.min.js"></script>
		<script type="text/javascript" src="scripts/node_modules/oclazyload/dist/ocLazyLoad.min.js"></script>
		<script type="text/javascript" src="scripts/node_modules/ng-file-upload/dist/ng-file-upload.min.js"></script>
		<script type="text/javascript" src="scripts/node_modules/ng-file-upload/dist/ng-file-upload-shim.min.js"></script>
		<script type="text/javascript" src="scripts/node_modules/angular-bind-html-compile-ci-dev/angular-bind-html-compile.min.js"></script>

		<script type="text/javascript" src="scripts/node_modules/chart.js/dist/Chart.bundle.min.js"></script>

		<script type="text/javascript" src='scripts/e-homework-main.js?version=1192878sf87sd6f87s6f39a98asdasd98184729836876sdf68sd6f87sd6f8s76f87s6df87sd6f87s6f39a98asdasd981847298323492878sf87sd6f87s6f39a98asdasd98184729836876sdf68sd6f87sd6f8s76f87s6df87sd6f87s6f39a98asdasd9818472983234' charset="utf-8"></script>
		<script type="text/javascript" src='scripts/e-homework-on-go.js?version=19239adf68sd6f87sd6f8s6876sdf68sd6f87sd6192878sf87sd6f87s6f39a98asdasd98184729836876sdf68sd6f87sd6f8s76f87s6df87sd6f87s6f39a98asdasd9818472983234f8s847298398df7sdf98797897sdf989sdasd9818472983234' charset="utf-8"></script>
		<script type="text/javascript" src='scripts/factory.js?version=19239a97s192878sf87sd6f87s6f39a98asdasd98184729836876sdf68sd6f87sd6f8s76f87s6df87sd6f87s6f39a98asdasd9818472983234d6f8s6876sdf68sd6f87sd6f8s83sdasd9658sdf877sdfs76776576818472983234' charset="utf-8"></script>
		<script type="text/javascript" src='scripts/util.js?version=19239a9192878sf87sd6f87s6f39a98asdasd98184729836876sdf68sd6f87sd6f8s76f87s6df87sd6f87s6f39a98asdasd98184729832348asd6876s6876sdf68sd6f87sd6f8s83asd98a98asdasd9658sdf877sdfs18472983234' charset="utf-8"></script>

		<script src="scripts/ckeditor/ckeditor.js"></script>
		<script src="scripts/ckeditor_sdk/samples/assets/picoModal-2.0.1.min.js"></script>
		<script src="scripts/ckeditor_sdk/samples/assets/contentloaded.js"></script>
		<script src="scripts/ckeditor_sdk/samples/assets/beautify-html.js"></script>
		<!-- include js end -->

		<!-- include css -->
		<link rel="stylesheet" href="scripts/node_modules/bootstrap/dist/css/bootstrap.min.css">
		<link rel="stylesheet" href="scripts/node_modules/angular-ui-bootstrap/dist/ui-bootstrap-csp.css">
		<!-- include css end -->
	</head>

	<body ng-controller="AppController">
		
		<div class="overlay" ng-show="overlay.overlay" style="padding-top: 0px;"><img src="img/spin.gif"  style="width:10%; margin: 0 auto; margin-top:20%; padding-top 50%;" /></div>
		<div class="ng-view container"></div>
		<div class="container">
			<div class="row form-group" style="height: 100px;">
				<div class="col-lg-12 text-center">
					Version : 1.6 , Update ล่าสุดเมื่อวันที่ 30/10/2563
				</div>
			</div>
		</div> 
		 
	</body>
	<style type="text/css" media="screen">
		.overlay{
		    margin:0 auto;
		    position: fixed;
		    height: 100%;
		    width: 100%;
		    z-index: 1000000;
		    opacity:8.0;
		    filter:alpha(opacity=80);
		    background-color: rgba(0, 0, 0, 0.05);
		    text-align:center;   
		}
	</style>
	<script src="scripts/jquery.min.js"></script>
	<script src="scripts/bootstrap.min.js"></script>
	<link rel="stylesheet" href="css/custom-theme.css">
</html>
