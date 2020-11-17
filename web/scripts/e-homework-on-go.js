angular.module('e-homework').config(function ($routeProvider, $locationProvider) {
    $routeProvider
            .when("/", {
                templateUrl: "views/home.html",
                controller: "HomeController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/HomeController.js"]
                            });
                        }]
                }
            })

            .when("/guest/logon", {
                templateUrl: "views/login.html",
                controller: "LoginController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/LoginController.js"]
                            });
                        }]
                }
            })

            .when("/thirdparty/authen/:username/:loginSession", {
                templateUrl: "views/thirdparty.html",
                controller: "ThirdpartyController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/ThirdpartyController.js"]
                            });
                        }]
                }
            })



            .when("/menu-manage", {
                templateUrl: "views/menu/main.html",
                controller: "MenuController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/MenuController.js"]
                            });
                        }]
                }
            })

            .when("/menu-manage/update/:id?", {
                templateUrl: "views/menu/update.html",
                controller: "MenuUpdateController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/MenuUpdateController.js"]
                            });
                        }]
                }
            })

            .when("/update/pages/:pagetype", {
                templateUrl: "views/update/pages.html",
                controller: "UpdatePageController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/UpdatePageController.js"]
                            });
                        }]
                }
            })

            .when("/account-permission", {
                templateUrl: "views/account-permission/main.html",
                controller: "MainAccController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/account-permission/MainAccController.js"]
                            });
                        }]
                }
            })

            .when("/account-permission/update/:id?", {
                templateUrl: "views/account-permission/update.html",
                controller: "UpdateAccController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/account-permission/UpdateAccController.js"]
                            });
                        }]
                }
            })

            .when("/master-goal", {
                templateUrl: "views/master-goal/main.html",
                controller: "MainMGController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/master-goal/MainMGController.js"]
                            });
                        }]
                }
            })

            .when("/master-goal/update/:id?", {
                templateUrl: "views/master-goal/update.html",
                controller: "UpdateMGController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/master-goal/UpdateMGController.js"]
                            });
                        }]
                }
            })

            .when("/cooperative", {
                templateUrl: "views/cooperative/main.html",
                controller: "MainCooperativeController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cooperative/MainCooperativeController.js"]
                            });
                        }]
                }
            })

            .when("/cooperative/update/:id?", {
                templateUrl: "views/cooperative/update.html",
                controller: "UpdateCooperativeController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cooperative/UpdateCooperativeController.js"]
                            });
                        }]
                }
            })

            .when("/goal-mission", {
                templateUrl: "views/goal-mission/main.html",
                controller: "MainGMController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/goal-mission/MainGMController.js"]
                            });
                        }]
                }
            })

            .when("/goal-mission/update/:id?", {
                templateUrl: "views/goal-mission/update.html",
                controller: "UpdateGMController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/goal-mission/UpdateGMController.js"]
                            });
                        }]
                }
            })

            .when("/mou", {
                templateUrl: "views/mou/main.html",
                controller: "MainMOUController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/mou/MainMOUController.js"]
                            });
                        }]
                }
            })

            .when("/mou/update/:id?", {
                templateUrl: "views/mou/update.html",
                controller: "UpdateMOUController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/mou/UpdateMOUController.js"]
                            });
                        }]
                }
            })

            .when("/mou/ex/update/:id?", {
                templateUrl: "views/mou/update-ex.html",
                controller: "UpdateMOUExController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/mou/UpdateMOUExController.js"]
                            });
                        }]
                }
            })

            .when("/dairy-farming", {
                templateUrl: "views/dairy-farming/main.html",
                controller: "MainDFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/dairy-farming/MainDFController.js"]
                            });
                        }]
                }
            })

            .when("/dairy-farming/update/:id?", {
                templateUrl: "views/dairy-farming/update.html",
                controller: "UpdateDFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/dairy-farming/UpdateDFController.js"]
                            });
                        }]
                }
            })

            .when("/veterinary", {
                templateUrl: "views/veterinary/main.html",
                controller: "MainVTController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/veterinary/MainVTController.js"]
                            });
                        }]
                }
            })

            .when("/veterinary/update/:id?/:month?/:year?", {
                templateUrl: "views/veterinary/update.html",
                controller: "UpdateVTController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/veterinary/UpdateVTController.js"]
                            });
                        }]
                }
            })

            .when("/production-factor", {
                templateUrl: "views/production-factor/main.html",
                controller: "MainPFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/production-factor/MainPFController.js"]
                            });
                        }]
                }
            })

            .when("/production-factor/update/:id?", {
                templateUrl: "views/production-factor/update.html",
                controller: "UpdatePFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/production-factor/UpdatePFController.js"]
                            });
                        }]
                }
            })

            .when("/food", {
                templateUrl: "views/food/main.html",
                controller: "MainFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/food/MainFController.js"]
                            });
                        }]
                }
            })

            .when("/food/update/:id?", {
                templateUrl: "views/food/update.html",
                controller: "UpdateFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/food/UpdateFController.js"]
                            });
                        }]
                }
            })

            .when("/cow-food", {
                templateUrl: "views/cow-food/main.html",
                controller: "MainCFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cow-food/MainCFController.js"]
                            });
                        }]
                }
            })

            .when("/cow-food/update/:id?", {
                templateUrl: "views/cow-food/update.html",
                controller: "UpdateCFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cow-food/UpdateCFController.js"]
                            });
                        }]
                }
            })

            .when("/training", {
                templateUrl: "views/training/main.html",
                controller: "MainTNController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/training/MainTNController.js"]
                            });
                        }]
                }
            })

            .when("/training/update/:id?", {
                templateUrl: "views/training/update.html",
                controller: "UpdateTNController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/training/UpdateTNController.js"]
                            });
                        }]
                }
            })

            .when("/insemination", {
                templateUrl: "views/insemination/main.html",
                controller: "MainISController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/insemination/MainISController.js"]
                            });
                        }]
                }
            })

            .when("/insemination/update/:id?", {
                templateUrl: "views/insemination/update.html",
                controller: "UpdateISController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/insemination/UpdateISController.js"]
                            });
                        }]
                }
            })

            .when("/mineral", {
                templateUrl: "views/mineral/main.html",
                controller: "MainMNController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/mineral/MainMNController.js"]
                            });
                        }]
                }
            })

            .when("/mineral/update/:id?", {
                templateUrl: "views/mineral/update.html",
                controller: "UpdateMNController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/mineral/UpdateMNController.js"]
                            });
                        }]
                }
            })

            .when("/sperm", {
                templateUrl: "views/sperm/main.html",
                controller: "MainSPController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/sperm/MainSPController.js"]
                            });
                        }]
                }
            })

            .when("/sperm/update/:id?", {
                templateUrl: "views/sperm/update.html",
                controller: "UpdateSPController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/sperm/UpdateSPController.js"]
                            });
                        }]
                }
            })

            .when("/sperm-sale", {
                templateUrl: "views/sperm-sale/main.html",
                controller: "MainSPSController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/sperm-sale/MainSPSController.js"]
                            });
                        }]
                }
            })

            .when("/sperm-sale/update/:id?", {
                templateUrl: "views/sperm-sale/update.html",
                controller: "UpdateSPSController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/sperm-sale/UpdateSPSController.js"]
                            });
                        }]
                }
            })

            .when("/material", {
                templateUrl: "views/material/main.html",
                controller: "MainMTController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/material/MainMTController.js"]
                            });
                        }]
                }
            })

            .when("/material/update/:id?", {
                templateUrl: "views/material/update.html",
                controller: "UpdateMTController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/material/UpdateMTController.js"]
                            });
                        }]
                }
            })

            .when("/cow-breed", {
                templateUrl: "views/cow-breed/main.html",
                controller: "MainCBController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cow-breed/MainCBController.js"]
                            });
                        }]
                }
            })

            .when("/cow-breed/update/:id?", {
                templateUrl: "views/cow-breed/update.html",
                controller: "UpdateCBController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cow-breed/UpdateCBController.js"]
                            });
                        }]
                }
            })

            .when("/training-cowbreed", {
                templateUrl: "views/training-cowbreed/main.html",
                controller: "MainTCBController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/training-cowbreed/MainTCBController.js"]
                            });
                        }]
                }
            })

            .when("/training-cowbreed/update/:id?", {
                templateUrl: "views/training-cowbreed/update.html",
                controller: "UpdateTCBController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/training-cowbreed/UpdateTCBController.js"]
                            });
                        }]
                }
            })

            .when("/travel", {
                templateUrl: "views/travel/main.html",
                controller: "MainTVController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/travel/MainTVController.js"]
                            });
                        }]
                }
            })

            .when("/travel/update/:id?", {
                templateUrl: "views/travel/update.html",
                controller: "UpdateTVController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/travel/UpdateTVController.js"]
                            });
                        }]
                }
            })

            .when("/cooperative-milk", {
                templateUrl: "views/cooperative-milk/main.html",
                controller: "MainCMController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cooperative-milk/MainCMController.js"]
                            });
                        }]
                }
            })

            .when("/cooperative-milk/update/:id?", {
                templateUrl: "views/cooperative-milk/update.html",
                controller: "UpdateCMController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cooperative-milk/UpdateCMController.js"]
                            });
                        }]
                }
            })

            .when("/cow-group", {
                templateUrl: "views/cow-group/main.html",
                controller: "MainCGController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cow-group/MainCGController.js"]
                            });
                        }]
                }
            })

            .when("/cow-group/update/:id?", {
                templateUrl: "views/cow-group/update.html",
                controller: "UpdateCGController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cow-group/UpdateCGController.js"]
                            });
                        }]
                }
            })

            .when("/report-monthly", {
                templateUrl: "views/report/monthly.html",
                controller: "MonthlyController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/report/MonthlyController.js"]
                            });
                        }]
                }
            })

            .when("/report-quarter", {
                templateUrl: "views/report/quarter.html",
                controller: "QuarterController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/report/QuarterController.js"]
                            });
                        }]
                }
            })

            .when("/report-annually", {
                templateUrl: "views/report/annually.html",
                controller: "AnnuallyController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/report/AnnuallyController.js"]
                            });
                        }]
                }
            })

            .when("/report-subcommittee", {
                templateUrl: "views/report/subcommittee.html",
                controller: "SubcommitteeController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/report/SubcommitteeController.js"]
                            });
                        }]
                }
            })

            .when("/production-info", {
                templateUrl: "views/production-info/main.html",
                controller: "MainPFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/production-info/MainPFController.js"]
                            });
                        }]
                }
            })

            .when("/production-info/update/:id?", {
                templateUrl: "views/production-info/update.html",
                controller: "UpdatePFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/production-info/UpdatePFController.js"]
                            });
                        }]
                }
            })

            .when("/production-sale-info", {
                templateUrl: "views/production-sale-info/main.html",
                controller: "MainPSIController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/production-sale-info/MainPSIController.js"]
                            });
                        }]
                }
            })

            .when("/production-sale-info/update/:id?", {
                templateUrl: "views/production-sale-info/update.html",
                controller: "UpdatePSIController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/production-sale-info/UpdatePSIController.js"]
                            });
                        }]
                }
            })

            .when("/import-personal", {
                templateUrl: "views/import-personal/main.html",
                controller: "ImportController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/import-personal/ImportController.js"]
                            });
                        }]
                }
            })
            .when("/page/personal", {
                templateUrl: "views/personal/main.html",
                controller: "MainPRController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/personal/MainPRController.js"]
                            });
                        }]
                }
            })

            .when("/milk-buy-info", {
                templateUrl: "views/milk-buy-info/main.html",
                controller: "MainMBIController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/milk-buy-info//MainMBIController.js"]
                            });
                        }]
                }
            })

            .when("/milk-sale-info", {
			        templateUrl : "views/milk-sale-info/main.html",
			        controller : "MainMSIController",
			        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
			return $ocLazyLoad.load({
			files : [ "scripts/controllers/milk-sale-info//MainMSIController.js" ]
			});
			} ]
			}
			})

            .when("/product-milk", {
                templateUrl: "views/product-milk/main.html",
                controller: "MainPMController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/product-milk/MainPMController.js"]
                            });
                        }]
                }
            })

            .when("/product-milk/update/:id?", {
                templateUrl: "views/product-milk/update.html",
                controller: "UpdatePMController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/product-milk/UpdatePMController.js"]
                            });
                        }]
                }
            })

            .when("/subproduct-milk", {
                templateUrl: "views/subproduct-milk/main.html",
                controller: "MainSPMController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/subproduct-milk/MainSPMController.js"]
                            });
                        }]
                }
            })

            .when("/subproduct-milk/update/:id?", {
                templateUrl: "views/subproduct-milk/update.html",
                controller: "UpdateSPMController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/subproduct-milk/UpdateSPMController.js"]
                            });
                        }]
                }
            })

            .when("/product-milk-detail", {
                templateUrl: "views/product-milk-detail/main.html",
                controller: "MainPMDController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/product-milk-detail/MainPMDController.js"]
                            });
                        }]
                }
            })

            .when("/product-milk-detail/update/:id?", {
                templateUrl: "views/product-milk-detail/update.html",
                controller: "UpdatePMDController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/product-milk-detail/UpdatePMDController.js"]
                            });
                        }]
                }
            })

            .when("/lost-in-process", {
                templateUrl: "views/lost-in-process/main.html",
                controller: "MainLIPController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/lost-in-process/MainLIPController.js"]
                            });
                        }]
                }
            })

            .when("/lost-in-process/update/:id?", {
                templateUrl: "views/lost-in-process/update.html",
                controller: "UpdateLIPController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/lost-in-process/UpdateLIPController.js"]
                            });
                        }]
                }
            })

            .when("/lost-out-process", {
                templateUrl: "views/lost-out-process/main.html",
                controller: "MainLOPController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/lost-out-process/MainLOPController.js"]
                            });
                        }]
                }
            })

            .when("/lost-out-process/update/:id?", {
                templateUrl: "views/lost-out-process/update.html",
                controller: "UpdateLOPController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/lost-out-process/UpdateLOPController.js"]
                            });
                        }]
                }
            })

            .when("/lost-wait-sale", {
                templateUrl: "views/lost-wait-sale/main.html",
                controller: "MainLWSController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/lost-wait-sale/MainLWSController.js"]
                            });
                        }]
                }
            })

            .when("/lost-wait-sale/update/:id?", {
                templateUrl: "views/lost-wait-sale/update.html",
                controller: "UpdateLWSController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/lost-wait-sale/UpdateLWSController.js"]
                            });
                        }]
                }
            })

            .when("/cow-group-father", {
                templateUrl: "views/cow-group-father/main.html",
                controller: "MainCGFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cow-group-father/MainCGFController.js"]
                            });
                        }]
                }
            })

            .when("/cow-group-father/update/:id?", {
                templateUrl: "views/cow-group-father/update.html",
                controller: "UpdateCGFController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/cow-group-father/UpdateCGFController.js"]
                            });
                        }]
                }
            })

            .when("/loss-manage", {
                templateUrl: "views/loss-manage/main.html",
                controller: "MainLMController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/loss-manage/MainLMController.js"]
                            });
                        }]
                }
            })

            .when("/loss-manage/update/:id?", {
                templateUrl: "views/loss-manage/update.html",
                controller: "UpdateLMController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/loss-manage/UpdateLMController.js"]
                            });
                        }]
                }
            })

            .when("/loss-mapping", {
                templateUrl: "views/loss-manage/mapping.html",
                controller: "MainLMPController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/loss-manage/MainLMPController.js"]
                            });
                        }]
                }
            })

            .when("/page/dip", {
                templateUrl: "views/dip/main.html",
                controller: "MainDIPController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/dip/MainDIPController.js"]
                            });
                        }]
                }
            })

            .when("/dip/update/:id?", {
                templateUrl: "views/dip/update.html",
                controller: "UpdateDIPController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/dip/UpdateDIPController.js"]
                            });
                        }]
                }
            })

            .when("/lost-in-process/value", {
                templateUrl: "views/lost-in-process/main-value.html",
                controller: "MainLIPVController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/lost-in-process/MainLIPVController.js"]
                            });
                        }]
                }
            })

            .when("/begin-ending-balance", {
                templateUrl: "views/begin-ending-balance/main.html",
                controller: "MainBEBController",
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                            return $ocLazyLoad.load({
                                files: ["scripts/controllers/begin-ending-balance/MainBEBController.js"]
                            });
                        }]
                }
            })
            ;



    $locationProvider.hashPrefix('');
    // $locationProvider.html5Mode({
    //                 enabled: true,
    //                 requireBase: false
    //          });

});

/*app.config(function($routeProvider) {
 
 $routeProvider.when('/', {
 
 templateUrl: function(rd) {
 return 'views/home.html';
 },
 
 resolve: {
 load: function($q, $route, $rootScope) {
 
 var deferred = $q.defer();
 var dependencies = [
 'scripts/controllers/HomeController.js'
 ];
 
 $script(dependencies, function () {
 $rootScope.$apply(function() {
 deferred.resolve();
 });
 });
 
 console.log(deferred);
 return deferred.promise;
 }
 }
 });
 
 });*/