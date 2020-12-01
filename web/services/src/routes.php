<?php
// Routes

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app->get('/user/{id}', 'UserController:getUser');
$app->post('/login/', 'LoginController:authenticate');
$app->post('/login/check-permission/', 'LoginController:checkPermission');

$app->post('/pages/', 'PageController:getPage');
$app->post('/pages/update/', 'PageController:updatePage');

$app->post('/menu/list/', 'MenuController:getMenuList');
$app->post('/menu/list/manage/', 'MenuController:getMenuListManage');
$app->post('/menu/get/', 'MenuController:getMenu');
$app->post('/menu/update/', 'MenuController:updateMenu');
$app->post('/menu/page/get/', 'MenuController:GetMenuPage');
$app->post('/menu/get/parent/', 'MenuController:GetMenuParent');
$app->post('/menu/get/type/', 'MenuController:getMenuByType');

$app->post('/account-permission/get/', 'AccountPermissionController:getData');
$app->post('/account-permission/update/', 'AccountPermissionController:updateData');

$app->post('/master-goal/list/', 'MasterGoalController:getList');
$app->post('/master-goal/get/', 'MasterGoalController:getData');
$app->post('/master-goal/update/', 'MasterGoalController:updateData');

$app->post('/goal-mission/list/', 'GoalMissionController:getList');
$app->post('/goal-mission/get/', 'GoalMissionController:getData');
$app->post('/goal-mission/update/', 'GoalMissionController:updateData');
$app->post('/goal-mission/update/editable/', 'GoalMissionController:updateDataEditable');
$app->post('/goal-mission/update/approve/', 'GoalMissionController:updateDataApprove');
$app->post('/goal-mission/menu/', 'GoalMissionController:getGoalByMenuType');

$app->post('/mou/list/', 'MouController:getList');
$app->post('/mou/get/', 'MouController:getData');
$app->post('/mou/update/', 'MouController:updateData');

$app->post('/cooperative/list/', 'CooperativeController:getList');
$app->post('/cooperative/list/region/', 'CooperativeController:getRegionList');
$app->post('/cooperative/get/', 'CooperativeController:getData');
$app->post('/cooperative/update/', 'CooperativeController:updateData');
$app->post('/cooperative/delete/', 'CooperativeController:deleteData');

$app->post('/dairy-farming/list/', 'DairyFarmingController:getList');
$app->post('/dairy-farming/list/parent/', 'DairyFarmingController:getParentList');
$app->post('/dairy-farming/list/veterinary/', 'DairyFarmingController:getListForVeterinary');
$app->post('/dairy-farming/get/', 'DairyFarmingController:getData');
$app->post('/dairy-farming/update/', 'DairyFarmingController:updateData');

$app->post('/veterinary/list/main/', 'VeterinaryController:getMainList');
$app->post('/veterinary/list/detail/', 'VeterinaryController:getDetailList');
$app->post('/veterinary/list/subdetail/', 'VeterinaryController:getSubDetailList');
$app->post('/veterinary/get/', 'VeterinaryController:getData');
$app->post('/veterinary/update/', 'VeterinaryController:updateData');
$app->post('/veterinary/delete/', 'VeterinaryController:removeData');
$app->post('/veterinary/delete/detail/', 'VeterinaryController:removeDetailData');
$app->post('/veterinary/delete/item/', 'VeterinaryController:removeItemData');
$app->post('/veterinary/report/', 'ReportController:exportVeterinaryExcel');
$app->post('/veterinary/update/approve/', 'VeterinaryController:updateDataApprove');
$app->post('/veterinary/list/approve/', 'VeterinaryController:loadDataApprove');

$app->post('/production-factor/list/', 'ProductionFactorController:getList');
$app->post('/production-factor/get/', 'ProductionFactorController:getData');
$app->post('/production-factor/update/', 'ProductionFactorController:updateData');
$app->post('/production-factor/delete/', 'ProductionFactorController:removeData');
$app->post('/production-factor/report/', 'ReportController:exportProductFactorReport');

$app->post('/food/list/', 'FoodController:getList');
$app->post('/food/get/', 'FoodController:getData');
$app->post('/food/update/', 'FoodController:updateData');
$app->post('/food/delete/', 'FoodController:removeData');

$app->post('/cow-food/list/', 'CowFoodController:getList');
$app->post('/cow-food/get/', 'CowFoodController:getData');
$app->post('/cow-food/update/', 'CowFoodController:updateData');
$app->post('/cow-food/delete/', 'CowFoodController:removeData');

$app->post('/training/list/', 'TrainingController:getList');
$app->post('/training/get/', 'TrainingController:getData');
$app->post('/training/update/', 'TrainingController:updateData');
$app->post('/training/delete/', 'TrainingController:removeData');


$app->post('/insemination/list/main/', 'InseminationController:getMainList'); 
$app->post('/insemination/get/', 'InseminationController:getData');
$app->post('/insemination/update/', 'InseminationController:updateData');
$app->post('/insemination/delete/detail/', 'InseminationController:removeDetailData');
$app->post('/insemination/report/', 'ReportController:exportInseminationExcel');
$app->post('/insemination/update/approve/', 'InseminationController:updateDataApprove');
$app->post('/insemination/list/approve/', 'InseminationController:loadDataApprove');

$app->post('/mineral/list/main/', 'MineralController:getMainList');
$app->post('/mineral/list/detail/', 'MineralController:getDetailList');
$app->post('/mineral/get/', 'MineralController:getData');
$app->post('/mineral/update/', 'MineralController:updateData');
$app->post('/mineral/delete/detail/', 'MineralController:removeDetailData');
$app->post('/mineral/report/', 'ReportController:exportMineralExcel');
$app->post('/mineral/update/approve/', 'MineralController:updateDataApprove');
$app->post('/mineral/list/approve/', 'MineralController:loadDataApprove');

$app->post('/sperm-sale/list/main/', 'SpermSaleController:getMainList');
$app->post('/sperm-sale/list/detail/', 'SpermSaleController:getDetailList');
$app->post('/sperm-sale/get/', 'SpermSaleController:getData');
$app->post('/sperm-sale/update/', 'SpermSaleController:updateData');
$app->post('/sperm-sale/delete/detail/', 'SpermSaleController:removeDetailData');
$app->post('/sperm-sale/update/approve/', 'SpermSaleController:updateDataApprove');
$app->post('/sperm-sale/list/approve/', 'SpermSaleController:loadDataApprove');

$app->post('/material/list/main/', 'MaterialController:getMainList');
$app->post('/material/list/detail/', 'MaterialController:getDetailList');
$app->post('/material/get/', 'MaterialController:getData');
$app->post('/material/update/', 'MaterialController:updateData');
$app->post('/material/delete/detail/', 'MaterialController:removeDetailData');
$app->post('/material/update/approve/', 'MaterialController:updateDataApprove');
$app->post('/material/list/approve/', 'MaterialController:loadDataApprove');

$app->post('/cow-breed/list/main/', 'CowBreedController:getMainList');
$app->post('/cow-breed/list/detail/', 'CowBreedController:getDetailList');
$app->post('/cow-breed/get/', 'CowBreedController:getData');
$app->post('/cow-breed/update/', 'CowBreedController:updateData');
$app->post('/cow-breed/delete/detail/', 'CowBreedController:removeDetailData');
$app->post('/cow-breed/report/', 'ReportController:exportCowbreedExcel');
$app->post('/cow-breed/update/approve/', 'CowBreedController:updateDataApprove');
$app->post('/cow-breed/list/approve/', 'CowBreedController:loadDataApprove');

$app->post('/training-cowbreed/list/main/', 'TrainingCowBreedController:getMainList');
$app->post('/training-cowbreed/get/', 'TrainingCowBreedController:getData');
$app->post('/training-cowbreed/update/', 'TrainingCowBreedController:updateData');
$app->post('/training-cowbreed/delete/detail/','TrainingCowBreedController:removeDetailData');
$app->post('/training-cowbreed/report/', 'ReportController:exportTrainingcowbreedExcel');
$app->post('/training-cowbreed/update/approve/', 'TrainingCowBreedController:updateDataApprove');
$app->post('/training-cowbreed/list/approve/', 'TrainingCowBreedController:loadDataApprove');

$app->post('/sperm/list/main/', 'SpermController:getMainList');
$app->post('/sperm/list/detail/', 'SpermController:getDetailList');
$app->post('/sperm/get/', 'SpermController:getData');
$app->post('/sperm/update/', 'SpermController:updateData');
$app->post('/sperm/delete/detail/', 'SpermController:removeDetailData');
$app->post('/sperm/report/', 'ReportController:exportSpermExcel');
$app->post('/sperm/update/approve/', 'SpermController:updateDataApprove');
$app->post('/sperm/list/approve/', 'SpermController:loadDataApprove');

$app->post('/travel/list/main/', 'TravelController:getMainList');
$app->post('/travel/get/', 'TravelController:getData');
$app->post('/travel/update/', 'TravelController:updateData');
$app->post('/travel/delete/detail/', 'TravelController:removeDetailData');
$app->post('/travel/report/', 'ReportController:exportTravelExcel');
$app->post('/travel/update/approve/', 'TravelController:updateDataApprove');
$app->post('/travel/list/approve/', 'TravelController:loadDataApprove');
$app->post('/travel/list/detail/', 'TravelController:getDetailList');


$app->post('/cooperative-milk/list/main/', 'CooperativeMilkController:getMainList');
$app->post('/cooperative-milk/get/', 'CooperativeMilkController:getData');
$app->post('/cooperative-milk/update/', 'CooperativeMilkController:updateData');
$app->post('/cooperative-milk/delete/detail/', 'CooperativeMilkController:removeDetailData');
$app->post('/cooperative-milk/update/approve/', 'CooperativeMilkController:updateDataApprove');
$app->post('/cooperative-milk/list/approve/', 'CooperativeMilkController:loadDataApprove');

$app->post('/cow-group/list/main/', 'CowGroupController:getMainList');
$app->post('/cow-group/get/', 'CowGroupController:getData');
$app->post('/cow-group/update/', 'CowGroupController:updateData');
$app->post('/cow-group/delete/detail/', 'CowGroupController:removeDetailData');
$app->post('/cow-group/report/', 'ReportController:exportCowgroupExcel');
$app->post('/cow-group/update/approve/', 'CowGroupController:updateDataApprove');
$app->post('/cow-group/list/approve/', 'CowGroupController:loadDataApprove');

$app->post('/cow-group-father/list/main/', 'CowgroupFatherController:getMainList');
$app->post('/cow-group-father/get/', 'CowgroupFatherController:getData');
$app->post('/cow-group-father/update/', 'CowgroupFatherController:updateData');
$app->post('/cow-group-father/delete/detail/', 'CowgroupFatherController:removeDetailData');
$app->post('/cow-group-father/report/', 'ReportController:exportCowgroupFatherExcel');
$app->post('/cow-group-father/update/approve/', 'CowgroupFatherController:updateDataApprove');
$app->post('/cow-group-father/list/approve/', 'CowgroupFatherController:loadDataApprove');

$app->post('/monthreport/report/', 'MonthReportController:exportmonthreportExcel');
$app->post('/quarterreport/report/', 'QuarterReportController:exportquarterreportExcel');
$app->post('/annuallyreport/report/', 'AnnualReportController:exportannuallyreportExcel');
$app->post('/subcommittee/report/', 'SubcommitteeReportController:exportsubreportExcel');

$app->post('/import-personal/', 'ImportPersonalController:import');
$app->post('/import-personal/list/main/', 'ImportPersonalController:getMainList');

$app->post('/personal/list/main/', 'PersonalController:getMainList');

$app->post('/chart/main/dbi/', 'ChartController:getDataDBI');
$app->post('/chart/main/ii/', 'ChartController:getDataII');

$app->post('/mbi/list/', 'MBIController:getList');
$app->post('/mbi/list/milk-buy-info/month/', 'MBIController:getListMBIMonth');
$app->post('/mbi/list/milk-buy-info/quarter/', 'MBIController:getListMBIQuarter');
$app->post('/mbi/list/milk-buy-info/year/', 'MBIController:getListMBIYear');
$app->post('/mbi/list/milk-buy-info/detail/', 'MBIController:getListMBIDetail');
$app->post('/mbi/list/milk-buy-info/mou/', 'MBIController:getListMBIMOU');
$app->post('/mbi/list/milk-buy-info/mou/detail/', 'MBIController:getListMBIMOUDetail');
$app->post('/mbi/update/', 'MBIController:updateData');

$app->post('/msi/list/milk-sale-info/month/', 'MSIController:getListMSIMonth');
$app->post('/msi/list/milk-sale-info/quarter/', 'MSIController:getListMSIQuarter');
$app->post('/msi/list/milk-sale-info/year/', 'MSIController:getListMSIYear');
$app->post('/msi/list/milk-sale-info/detail/', 'MSIController:getListMSIDetail');

$app->post('/product-milk/list/', 'ProductMilkController:getList');
$app->post('/product-milk/get/', 'ProductMilkController:getData');
$app->post('/product-milk/update/', 'ProductMilkController:updateData');
$app->post('/product-milk/list/all/', 'ProductMilkController:getListAll');
$app->post('/product-milk/sale-chanel/list/', 'ProductMilkController:getSaleChanelList');
$app->post('/product-milk/sale-chanel/update/', 'ProductMilkController:updateSaleChanelData');

$app->post('/subproduct-milk/list/', 'SubProductMilkController:getList');
$app->post('/subproduct-milk/list/byparent/', 'SubProductMilkController:getListByParent');
$app->post('/subproduct-milk/get/', 'SubProductMilkController:getData');
$app->post('/subproduct-milk/update/', 'SubProductMilkController:updateData');

$app->post('/product-milk-detail/list/', 'ProductMilkDetailController:getList');
$app->post('/product-milk-detail/list/byparent/', 'ProductMilkDetailController:getListByParent');
$app->post('/product-milk-detail/list/byparent2/', 'ProductMilkDetailController:getListByParent2');
$app->post('/product-milk-detail/get/', 'ProductMilkDetailController:getData');
$app->post('/product-milk-detail/update/', 'ProductMilkDetailController:updateData');
$app->get('/product-milk-detail/master-goal/delete/', 'ProductMilkDetailController:deleteMasterGoalData');
$app->get('/product-milk-detail/master-goal/update/', 'ProductMilkDetailController:updateMasterGoalData');

$app->post('/lost-in-process/list/main/', 'LostInProcessController:getMainList');
$app->post('/lost-in-process/get/', 'LostInProcessController:getData');
$app->post('/lost-in-process/update/', 'LostInProcessController:updateData');
$app->post('/lost-in-process/delete/detail/', 'LostInProcessController:removeDetailData');
$app->post('/lost-in-process/update/approve/', 'LostInProcessController:updateDataApprove');
$app->post('/lost-in-process/list/approve/', 'LostInProcessController:loadDataApprove');
$app->post('/lost-in-process/upload/', 'LostInProcessController:uploadData');
$app->post('/lost-in-process/load/template/', 'LostInProcessController:getExcelTemplate');

$app->post('/lost-in-process/value/list/', 'LostInProcessController:getMainListValue');
$app->post('/lost-in-process/value/update/', 'LostInProcessController:updateValue');
$app->post('/lost-in-process/value/delete/', 'LostInProcessController:deleteValue');

$app->post('/lost-out-process/list/main/', 'LostOutProcessController:getMainList');
$app->post('/lost-out-process/get/', 'LostOutProcessController:getData');
$app->post('/lost-out-process/update/', 'LostOutProcessController:updateData');
$app->post('/lost-out-process/delete/detail/', 'LostOutProcessController:removeDetailData');
$app->post('/lost-out-process/update/approve/', 'LostOutProcessController:updateDataApprove');
$app->post('/lost-out-process/list/approve/', 'LostOutProcessController:loadDataApprove');
$app->post('/lost-out-process/upload/', 'LostOutProcessController:uploadData');
$app->post('/lost-out-process/load/template/', 'LostOutProcessController:getExcelTemplate');

$app->post('/lost-wait-sale/list/main/', 'LostWaitSaleController:getMainList');
$app->post('/lost-wait-sale/get/', 'LostWaitSaleController:getData');
$app->post('/lost-wait-sale/update/', 'LostWaitSaleController:updateData');
$app->post('/lost-wait-sale/delete/detail/', 'LostWaitSaleController:removeDetailData');
$app->post('/lost-wait-sale/update/approve/', 'LostWaitSaleController:updateDataApprove');
$app->post('/lost-wait-sale/list/approve/', 'LostWaitSaleController:loadDataApprove');
$app->post('/lost-wait-sale/upload/', 'LostWaitSaleController:uploadData');
$app->post('/lost-wait-sale/load/template/', 'LostWaitSaleController:getExcelTemplate');

$app->post('/production-info/list/main/', 'ProductionInfoController:getMainList');
$app->post('/production-info/list/detail/', 'ProductionInfoController:getMainListDetail');
$app->post('/production-info/get/', 'ProductionInfoController:getData');
$app->post('/production-info/update/', 'ProductionInfoController:updateData');
$app->post('/production-info/delete/detail/', 'ProductionInfoController:removeDetailData');
$app->post('/production-info/update/approve/', 'ProductionInfoController:updateDataApprove');
$app->post('/production-info/list/approve/', 'ProductionInfoController:loadDataApprove');
$app->post('/production-info/upload/', 'ProductionInfoController:uploadData');
$app->post('/production-info/load/template/', 'ProductionInfoController:getExcelTemplate');

$app->post('/production-sale-info/list/main/', 'ProductionSaleInfoController:getMainList');
$app->post('/production-sale-info/list/detail/', 'ProductionSaleInfoController:getMainListDetail');
$app->post('/production-sale-info/get/', 'ProductionSaleInfoController:getData');
$app->post('/production-sale-info/update/', 'ProductionSaleInfoController:updateData');
$app->post('/production-sale-info/delete/detail/', 'ProductionSaleInfoController:removeDetailData');
$app->post('/production-sale-info/update/approve/', 'ProductionSaleInfoController:updateDataApprove');
$app->post('/production-sale-info/list/approve/', 'ProductionSaleInfoController:loadDataApprove');
$app->post('/production-sale-info/upload/', 'ProductionSaleInfoController:uploadData');
$app->post('/production-sale-info/load/template/', 'ProductionSaleInfoController:getExcelTemplate');
$app->get('/production-sale-info/read/productmilk/', 'ProductionSaleInfoController:readProductMilkFile');
$app->get('/production-sale-info/read/productmilk/master/', 'ProductionSaleInfoController:readProductMilkFileToMaster');


$app->post('/factory/list/', 'FactoryController:getList');

$app->post('/upload-log/list/', 'UploadLogController:getList');
$app->post('/upload-log/update/', 'UploadLogController:updateLog');

$app->post('/loss-manage/list/', 'MasterLossController:getList');
$app->post('/loss-manage/get/', 'MasterLossController:getData');
$app->post('/loss-manage/update/', 'MasterLossController:updateData');
$app->post('/loss-manage/mapping/list/', 'MasterLossController:getMappingList');
$app->post('/loss-manage/mapping/update/', 'MasterLossController:updateMappingData');
$app->post('/loss-manage/mapping/delete/', 'MasterLossController:deleteMappingData');
$app->get('/loss-manage/master-goal/update/', 'MasterLossController:updateMasterGoalData');
$app->post('/loss-manage/mapping/master-goal/create/', 'MasterLossController:createToMasterGoal');

$app->post('/dip/list/', 'DIPController:getList');
$app->post('/dip/get/', 'DIPController:getData');
$app->post('/dip/update/', 'DIPController:updateData');
$app->post('/dip/delete/', 'DIPController:removeData');

$app->post('/begin-ending-balance/list/', 'BeginEndingBalanceController:getMainList');
$app->post('/begin-ending-balance/get/', 'BeginEndingBalanceController:getData');
$app->post('/begin-ending-balance/update/', 'BeginEndingBalanceController:updateData');

// Default action
$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});
