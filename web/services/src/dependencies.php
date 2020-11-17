<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    $logger->pushHandler(new Monolog\Handler\RotatingFileHandler($settings['path'], $settings['maxFiles'], $settings['level']));
    return $logger;
};

$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $capsule = new Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($settings);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    return $capsule;
};

$container['LoginController'] = function ($c) {
    return new \App\Controller\LoginController($c->get('logger'), $c->get('db'));
};

$container['PageController'] = function ($c) {
    return new \App\Controller\PageController($c->get('logger'), $c->get('db'));
};

$container['MenuController'] = function ($c) {
    return new \App\Controller\MenuController($c->get('logger'), $c->get('db'));
};

$container['AccountPermissionController'] = function ($c) {
    return new \App\Controller\AccountPermissionController($c->get('logger'), $c->get('db'));
};

$container['MasterGoalController'] = function ($c) {
    return new \App\Controller\MasterGoalController($c->get('logger'), $c->get('db'));
};

$container['GoalMissionController'] = function ($c) {
    return new \App\Controller\GoalMissionController($c->get('logger'), $c->get('db'));
};

$container['MouController'] = function ($c) {
    return new \App\Controller\MouController($c->get('logger'), $c->get('db'));
};

$container['CooperativeController'] = function ($c) {
    return new \App\Controller\CooperativeController($c->get('logger'), $c->get('db'));
};

$container['DairyFarmingController'] = function ($c) {
    return new \App\Controller\DairyFarmingController($c->get('logger'), $c->get('db'));
};

$container['VeterinaryController'] = function ($c) {
    return new \App\Controller\VeterinaryController($c->get('logger'), $c->get('db'));
};

$container['ProductionFactorController'] = function ($c) {
    return new \App\Controller\ProductionFactorController($c->get('logger'), $c->get('db'));
};

$container['FoodController'] = function ($c) {
    return new \App\Controller\FoodController($c->get('logger'), $c->get('db'));
};

$container['CowFoodController'] = function ($c) {
    return new \App\Controller\CowFoodController($c->get('logger'), $c->get('db'));
};

$container['TrainingController'] = function ($c) {
    return new \App\Controller\TrainingController($c->get('logger'), $c->get('db'));
};

$container['InseminationController'] = function ($c) {
    return new \App\Controller\InseminationController($c->get('logger'), $c->get('db'));
};

$container['MineralController'] = function ($c) {
    return new \App\Controller\MineralController($c->get('logger'), $c->get('db'));
};

$container['SpermSaleController'] = function ($c) {
    return new \App\Controller\SpermSaleController($c->get('logger'), $c->get('db'));
};

$container['MaterialController'] = function ($c) {
    return new \App\Controller\MaterialController($c->get('logger'), $c->get('db'));
};

$container['CowBreedController'] = function ($c) {
    return new \App\Controller\CowBreedController($c->get('logger'), $c->get('db'));
};

$container['TrainingCowBreedController'] = function ($c) {
    return new \App\Controller\TrainingCowBreedController($c->get('logger'), $c->get('db'));
};

$container['SpermController'] = function ($c) {
    return new \App\Controller\SpermController($c->get('logger'), $c->get('db'));
};

$container['TravelController'] = function ($c) {
    return new \App\Controller\TravelController($c->get('logger'), $c->get('db'));
};

$container['CooperativeMilkController'] = function ($c) {
    return new \App\Controller\CooperativeMilkController($c->get('logger'), $c->get('db'));
};

$container['CowGroupController'] = function ($c) {
    return new \App\Controller\CowGroupController($c->get('logger'), $c->get('db'));
};

$container['ReportController'] = function ($c) {
    return new \App\Controller\ReportController($c->get('logger'), $c->get('db'));
};
$container['MonthReportController'] = function ($c) {
    return new \App\Controller\MonthReportController($c->get('logger'), $c->get('db'));
};
$container['QuarterReportController'] = function ($c) {
    return new \App\Controller\QuarterReportController($c->get('logger'), $c->get('db'));
};
$container['AnnualReportController'] = function ($c) {
    return new \App\Controller\AnnualReportController($c->get('logger'), $c->get('db'));
};
$container['ImportPersonalController'] = function ($c) {
    return new \App\Controller\ImportPersonalController($c->get('logger'), $c->get('db'));
};
$container['PersonalController'] = function ($c) {
    return new \App\Controller\PersonalController($c->get('logger'), $c->get('db'));
};
$container['ChartController'] = function ($c) {
    return new \App\Controller\ChartController($c->get('logger'), $c->get('db'));
};

$container['MBIController'] = function ($c) {
    return new \App\Controller\MBIController($c->get('logger'), $c->get('db'));
};

$container['MSIController'] = function ($c) {
    return new \App\Controller\MSIController($c->get('logger'), $c->get('db'));
};

$container['LostInProcessController'] = function ($c) {
    return new \App\Controller\LostInProcessController($c->get('logger'), $c->get('db'));
};

$container['LostOutProcessController'] = function ($c) {
    return new \App\Controller\LostOutProcessController($c->get('logger'), $c->get('db'));
};

$container['LostWaitSaleController'] = function ($c) {
    return new \App\Controller\LostWaitSaleController($c->get('logger'), $c->get('db'));
};

$container['FactoryController'] = function ($c) {
    return new \App\Controller\FactoryController($c->get('logger'), $c->get('db'));
};

$container['ProductionInfoController'] = function ($c) {
    return new \App\Controller\ProductionInfoController($c->get('logger'), $c->get('db'));
};

$container['ProductionSaleInfoController'] = function ($c) {
    return new \App\Controller\ProductionSaleInfoController($c->get('logger'), $c->get('db'));
};

$container['SubcommitteeReportController'] = function ($c) {
    return new \App\Controller\SubcommitteeReportController($c->get('logger'), $c->get('db'));
};
$container['ProductMilkController'] = function ($c) {
    return new \App\Controller\ProductMilkController($c->get('logger'), $c->get('db'));
};
$container['SubProductMilkController'] = function ($c) {
    return new \App\Controller\SubProductMilkController($c->get('logger'), $c->get('db'));
};
$container['ProductMilkDetailController'] = function ($c) {
    return new \App\Controller\ProductMilkDetailController($c->get('logger'), $c->get('db'));
};

$container['CowgroupFatherController'] = function ($c) {
    return new \App\Controller\CowgroupFatherController($c->get('logger'), $c->get('db'));
};

$container['UploadLogController'] = function ($c) {
    return new \App\Controller\UploadLogController($c->get('logger'), $c->get('db'));
};

$container['MasterLossController'] = function ($c) {
    return new \App\Controller\MasterLossController($c->get('logger'), $c->get('db'));
};

$container['DIPController'] = function ($c) {
    return new \App\Controller\DIPController($c->get('logger'), $c->get('db'));
};

$container['BeginEndingBalanceController'] = function ($c) {
    return new \App\Controller\BeginEndingBalanceController($c->get('logger'), $c->get('db'));
};