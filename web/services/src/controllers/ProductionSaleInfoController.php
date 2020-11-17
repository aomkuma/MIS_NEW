<?php

namespace App\Controller;

use App\Service\ProductionSaleInfoService;
use App\Service\MasterGoalService;
use App\Service\ProductMilkService;
use App\Service\SubProductMilkService;
use App\Service\ProductMilkDetailService;
use App\Service\FactoryService;
use App\Service\GoalMissionService;
use App\Service\UploadLogService;
use PHPExcel;

class ProductionSaleInfoController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function getLastDayOfMonth($time) {
        return $date = date("t", strtotime($time . '-' . '01'));

        // return date("t", $last_day_timestamp);
    }

    public function getMonthName($month) {
        switch ($month) {
            case 1 : $monthTxt = 'มกราคม';
                break;
            case 2 : $monthTxt = 'กุมภาพันธ์';
                break;
            case 3 : $monthTxt = 'มีนาคม';
                break;
            case 4 : $monthTxt = 'เมษายน';
                break;
            case 5 : $monthTxt = 'พฤษภาคม';
                break;
            case 6 : $monthTxt = 'มิถุนายน';
                break;
            case 7 : $monthTxt = 'กรกฎาคม';
                break;
            case 8 : $monthTxt = 'สิงหาคม';
                break;
            case 9 : $monthTxt = 'กันยายน';
                break;
            case 10 : $monthTxt = 'ตุลาคม';
                break;
            case 11 : $monthTxt = 'พฤศจิกายน';
                break;
            case 12 : $monthTxt = 'ธันวาคม';
                break;
        }
        return $monthTxt;
    }

    public function loadDataApprove($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $user_session = $params['user_session'];

            $Data = ProductionSaleInfoService::loadDataApprove($user_session['UserID']);

            $this->data_result['DATA']['DataList'] = $Data;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMainList($request, $response, $args) {
        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();
            $condition = $params['obj']['condition'];
            $RegionList = [];
            foreach ($region as $key => $value) {
                $RegionList[] = $value['RegionID'];
            }

            // print_r($RegionList);
            // exit;
            if ($condition['DisplayType'] == 'monthly') {
                $Result = $this->getMonthDataList($condition, $RegionList);
            } else if ($condition['DisplayType'] == 'quarter') {
                $Result = $this->getQuarterDataList($condition, $RegionList);
            } else if ($condition['DisplayType'] == 'annually') {
                $Result = $this->getAnnuallyDataList($condition, $RegionList);
            }
            $DataList = $Result['DataList'];
            $Summary = $Result['Summary'];
            // print_r($DataList);
            // exit;

            $this->data_result['DATA']['DataList'] = $DataList;
            $this->data_result['DATA']['Summary'] = $Summary;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMonthDataList($condition, $RegionList) {

        $factory_id = $condition['Factory'];
        $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }
        $curMonth = $condition['MonthFrom'];
        $DataList = [];
        $DataSummary = [];

        $GrandTotal_CurrentAmount = 0;
        $GrandTotal_CurrentBaht = 0;
        $GrandTotal_BeforeAmount = 0;
        $GrandTotal_BeforeBaht = 0;

        // get ProductMilkService
        $ProducMilkList = ProductMilkService::getList('Y', '', [], $factory_id);

        // get Factory
        // $FactoryList = FactoryService::getList($RegionList, $factory_id);
        $curYear = $condition['YearTo'];
        $beforeYear = $condition['YearTo'] - 1;

        for ($i = 0; $i < $diffMonth; $i++) {

            foreach ($ProducMilkList as $key => $value) {

                $data = [];
                $data['bg_color'] = '#ccc';
                $data['ProductionInfoName'] = $value['name'];
                $data['show_button'] = 'Y';
                $data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'factory_id' => $factory_id
                    , 'DisplayType' => $condition['DisplayType']
                ];
                array_push($DataList, $data);

                $Total_CurrentAmount = 0;
                $Total_CurrentBaht = 0;
                $Total_BeforeAmount = 0;
                $Total_BeforeBaht = 0;

                // loop by factory
                // foreach ($FactoryList as $key1 => $value1) {
                // $data = [];
                // $data['bg_color'] = '#ccc';
                // $data['ProductionInfoName'] = $value1['factory_name'];
                // array_push($DataList, $data);
                // get SubProductMilkService
                $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id'], 'Y');

                // Prepare condition

                $Sum_CurrentAmount = 0;
                $Sum_CurrentBaht = 0;
                $Sum_BeforeAmount = 0;
                $Sum_BeforeBaht = 0;

                // $factory_id = $value1['id'];

                foreach ($SubProductMilkList as $k => $v) {

                    $master_type_id = $v['id'];

                    $monthName = ProductionSaleInfoController::getMonthName($curMonth);

                    $data = [];
                    // $data['RegionName'] = $value['RegionName'];
                    $data['ProductionInfoName'] = $v['product_character']. ' ' . $v['name'];
                    $data['Month'] = $monthName;

                    // get cooperative type
                    // echo "$curYear, $curMonth, $factory_id, $master_type_id";exit;
                    $Current = ProductionSaleInfoService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                    // print_r($Current);exit;
                    $data['CurrentAmount'] = floatval($Current['sum_amount']);
                    $data['CurrentBaht'] = floatval($Current['sum_baht']);

                    $Before = ProductionSaleInfoService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
                    $data['BeforeAmount'] = floatval($Before['sum_amount']);
                    $data['BeforeBaht'] = floatval($Before['sum_baht']);

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;

                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }

                    $data['CreateDate'] = $Current['update_date'];
                    $data['ApproveDate'] = $Current['office_approve_date'];
                    if (!empty($Current['office_approve_id'])) {
                        if (empty($Current['office_approve_comment'])) {
                            $data['Status'] = 'อนุมัติ';
                        } else {
                            $data['Status'] = 'ไม่อนุมัติ';
                        }
                    }
                    $data['Description'] = ['months' => $curMonth
                        , 'years' => $curYear
                        , 'factory_id' => $factory_id
                    ];

                    array_push($DataList, $data);

                    $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                    $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];

                    $Sum_CurrentAmount += $data['CurrentAmount'];
                    $Sum_CurrentBaht += $data['CurrentBaht'];
                    $Sum_BeforeAmount += $data['BeforeAmount'];
                    $Sum_BeforeBaht += $data['BeforeBaht'];

                    $Total_CurrentAmount += $data['CurrentAmount'];
                    $Total_CurrentBaht += $data['CurrentBaht'];
                    $Total_BeforeAmount += $data['BeforeAmount'];
                    $Total_BeforeBaht += $data['BeforeBaht'];

                    $GrandTotal_CurrentAmount += $data['CurrentAmount'];
                    $GrandTotal_CurrentBaht += $data['CurrentBaht'];
                    $GrandTotal_BeforeAmount += $data['BeforeAmount'];
                    $GrandTotal_BeforeBaht += $data['BeforeBaht'];
                    // }

                    $data = [];
                    $data['bg_color'] = '#AFE1FA';
                    $data['ProductionInfoName'] = 'รวม' . $value['name'];

                    $data['CurrentAmount'] = $Sum_CurrentAmount;
                    $data['CurrentBaht'] = $Sum_CurrentBaht;

                    $data['BeforeAmount'] = $Sum_BeforeAmount;
                    $data['BeforeBaht'] = $Sum_BeforeBaht;

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;
                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }
                    
                }
                array_push($DataList, $data);
                // $data = [];
                // $data['bg_color'] = '#A0DEFD';
                // $data['ProductionInfoName'] = 'รวมทั้งสิ้น';
                // $data['CurrentAmount'] = $Total_CurrentAmount;
                // $data['CurrentBaht'] = $Total_CurrentBaht;
                // $data['BeforeAmount'] = $Total_BeforeAmount;
                // $data['BeforeBaht'] = $Total_BeforeBaht;
                // $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                // $data['DiffAmount'] = $DiffAmount;
                // if ($data['BeforeAmount'] != 0) {
                //     $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                // } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                //     $data['DiffAmountPercentage'] = 100;
                // }
                // $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                // $data['DiffBaht'] = $DiffBaht;
                // if ($data['BeforeBaht'] != 0) {
                //     $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                // } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                //     $data['DiffBahtPercentage'] = 100;
                // }
                // array_push($DataList, $data);
            }

            $curMonth++;
        }

        $data = [];
        // $data['RegionName'] = $value['RegionName'];
        $data['bg_color'] = '#87D0F5';
        $data['ProductionInfoName'] = 'รวมทุกรายการ';
        $data['CurrentAmount'] = $GrandTotal_CurrentAmount;
        $data['CurrentBaht'] = $GrandTotal_CurrentBaht;
        $data['BeforeAmount'] = $GrandTotal_BeforeAmount;
        $data['BeforeBaht'] = $GrandTotal_BeforeBaht;

        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
        $data['DiffAmount'] = $DiffAmount;
        if ($data['BeforeAmount'] != 0) {
            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
            $data['DiffAmountPercentage'] = 100;
        } else {
            $data['DiffAmountPercentage'] = 0;
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        } else {
            $data['DiffBahtPercentage'] = 0;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getQuarterDataList($condition, $RegionList) {

        $factory_id = $condition['Factory'];
        // get loop to query
        // $loop = intval($condition['YearTo'] . $condition['QuarterTo']) - intval($condition['YearFrom'] . $condition['QuarterFrom']) + 1;
        $diffYear = 1; //($condition['YearTo'] - $condition['YearFrom']) + 1;
        $cnt = 0;
        $loop = 0;
        $j = $condition['QuarterFrom'];

        for ($i = 0; $i < $diffYear; $i++) {
            if ($cnt == $diffYear) {
                for ($k = 0; $k < $condition['QuarterFrom']; $k++) {
                    $loop++;
                }
            } else {

                if ($i > 0) {
                    $j = 0;
                }

                if ($diffYear == 1) {
                    $length = $condition['QuarterFrom'];
                } else {
                    $length = 4;
                }
                for (; $j < $length; $j++) {
                    $loop++;
                }
            }
            $cnt++;
        }
        $loop++;
        $curQuarter = intval($condition['QuarterFrom']);

        if (intval($curQuarter) == 1) {
            $curYear = intval($condition['YearTo']) - 1;
            $beforeYear = $curYear - 1;
        } else {
            $curYear = intval($condition['YearTo']);
            $beforeYear = $curYear - 1;
        }

        $DataList = [];
        $DataSummary = [];

        $GrandTotal_CurrentAmount = 0;
        $GrandTotal_CurrentBaht = 0;
        $GrandTotal_BeforeAmount = 0;
        $GrandTotal_BeforeBaht = 0;

        // get ProductMilkService
        $ProducMilkList = ProductMilkService::getList('Y', '', [], $factory_id);
        for($a = 0; $a < 4; $a++){
        for ($i = 0; $i < $loop; $i++) {

            if (/*$i > 0 &&*/ $curQuarter == 2) {
                $curYear++;
                $beforeYear = $curYear - 1;
            }

            // find month in quarter
            if ($curQuarter == 1) {
                $monthList = [10, 11, 12];
            } else if ($curQuarter == 2) {
                $monthList = [1, 2, 3];
            } else if ($curQuarter == 3) {
                $monthList = [4, 5, 6];
            } else if ($curQuarter == 4) {
                $monthList = [7, 8, 9];
            }

            foreach ($ProducMilkList as $key => $value) {

                $data = [];
                $data['bg_color'] = '#ccc';
                $data['ProductionInfoName'] = $v['product_character'] . ' ' . $value['name'];
                $data['show_button'] = 'Y';
                $data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'quarter' => $curQuarter
                    , 'factory_id' => $factory_id
                    , 'DisplayType' => $condition['DisplayType']
                ];
                array_push($DataList, $data);

                $Total_CurrentAmount = 0;
                $Total_CurrentBaht = 0;
                $Total_BeforeAmount = 0;
                $Total_BeforeBaht = 0;

                // loop by factory
                // foreach ($FactoryList as $key1 => $value1) {
                // $data = [];
                // $data['bg_color'] = '#ccc';
                // $data['ProductionInfoName'] = $value1['factory_name'];
                // array_push($DataList, $data);
                // get SubProductMilkService
                $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id'], 'Y');

                // Prepare condition

                $Sum_CurrentAmount = 0;
                $Sum_CurrentBaht = 0;
                $Sum_BeforeAmount = 0;
                $Sum_BeforeBaht = 0;

                // $factory_id = $value1['id'];

                foreach ($SubProductMilkList as $k => $v) {

                    $master_type_id = $v['id'];

                    $monthName = ProductionSaleInfoController::getMonthName($curMonth);

                    // get cooperative type
                    $SumCurrentAmount = 0;
                    $SumCurrentBaht = 0;
                    $SumBeforeAmount = 0;
                    $SumBeforeBaht = 0;
                    $UpdateDate = '';
                    $ApproveDate = '';
                    $ApproveComment = '';
                    // loop get quarter sum data

                    for ($j = 0; $j < count($monthList); $j++) {

                        $curMonth = $monthList[$j];
                        $Current = ProductionSaleInfoService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                        // print_r($Current);exit;
                        $SumCurrentAmount += floatval($Current['sum_amount']);
                        $SumCurrentBaht += floatval($Current['sum_baht']);

                        $Before = ProductionSaleInfoService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
                        $SumBeforeAmount += floatval($Before['sum_amount']);
                        $SumBeforeBaht += floatval($Before['sum_baht']);

                        if (!empty($Current['update_date'])) {
                            $UpdateDate = $Current['update_date'];
                        }
                        if (!empty($Current['office_approve_id'])) {
                            $ApproveDate = $Current['office_approve_date'];
                        }
                        if (!empty($Current['office_approve_comment'])) {
                            $ApproveComment = $Current['office_approve_comment'];
                        }
                    }

                    $data = [];
                    // $data['RegionName'] = $value['RegionName'];
                    $data['ProductionInfoName'] = $v['product_character'] . ' ' . $v['name'];
                    $data['Quarter'] = $curQuarter;

                    $data['CurrentAmount'] = $SumCurrentAmount;
                    $data['CurrentBaht'] = $SumCurrentBaht;

                    $data['BeforeAmount'] = $SumBeforeAmount;
                    $data['BeforeBaht'] = $SumBeforeBaht;

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;

                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }

                    $data['CreateDate'] = $Current['update_date'];
                    $data['ApproveDate'] = $Current['office_approve_date'];
                    if (!empty($Current['office_approve_id'])) {
                        if (empty($Current['office_approve_comment'])) {
                            $data['Status'] = 'อนุมัติ';
                        } else {
                            $data['Status'] = 'ไม่อนุมัติ';
                        }
                    }
                    $data['Description'] = ['months' => $curMonth
                        , 'years' => $curYear
                        , 'factory_id' => $factory_id
                    ];

                    array_push($DataList, $data);

                    $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                    $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];

                    $Sum_CurrentAmount += $data['CurrentAmount'];
                    $Sum_CurrentBaht += $data['CurrentBaht'];
                    $Sum_BeforeAmount += $data['BeforeAmount'];
                    $Sum_BeforeBaht += $data['BeforeBaht'];

                    $Total_CurrentAmount += $data['CurrentAmount'];
                    $Total_CurrentBaht += $data['CurrentBaht'];
                    $Total_BeforeAmount += $data['BeforeAmount'];
                    $Total_BeforeBaht += $data['BeforeBaht'];

                    $GrandTotal_CurrentAmount += $data['CurrentAmount'];
                    $GrandTotal_CurrentBaht += $data['CurrentBaht'];
                    $GrandTotal_BeforeAmount += $data['BeforeAmount'];
                    $GrandTotal_BeforeBaht += $data['BeforeBaht'];
                    // }

                    $data = [];
                    $data['bg_color'] = '#AFE1FA';
                    $data['ProductionInfoName'] = 'รวม';

                    $data['CurrentAmount'] = $Sum_CurrentAmount;
                    $data['CurrentBaht'] = $Sum_CurrentBaht;

                    $data['BeforeAmount'] = $Sum_BeforeAmount;
                    $data['BeforeBaht'] = $Sum_BeforeBaht;

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;
                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }
                    array_push($DataList, $data);
                }
            }

            $curMonth++;
        }
        $curQuarter++;
        }

        $data = [];
        // $data['RegionName'] = $value['RegionName'];
        $data['bg_color'] = '#87D0F5';
        $data['ProductionInfoName'] = 'รวมทุกรายการ';
        $data['CurrentAmount'] = $GrandTotal_CurrentAmount;
        $data['CurrentBaht'] = $GrandTotal_CurrentBaht;
        $data['BeforeAmount'] = $GrandTotal_BeforeAmount;
        $data['BeforeBaht'] = $GrandTotal_BeforeBaht;

        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
        $data['DiffAmount'] = $DiffAmount;
        if ($data['BeforeAmount'] != 0) {
            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
            $data['DiffAmountPercentage'] = 100;
        } else {
            $data['DiffAmountPercentage'] = 0;
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        } else {
            $data['DiffBahtPercentage'] = 0;
        }

        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getAnnuallyDataList($condition, $RegionList) {

        $factory_id = $condition['Factory'];

        $loop = 1; //intval($condition['YearTo']) - intval($condition['YearFrom']) + 1;
        $curYear = $condition['YearTo'];

        $beforeYear = $curYear - 1;
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        $DataList = [];
        $DataSummary = [];
        $curYear = $condition['YearTo'];

        // get master goal
        $GrandTotal_CurrentAmount = 0;
        $GrandTotal_CurrentBaht = 0;
        $GrandTotal_BeforeAmount = 0;
        $GrandTotal_BeforeBaht = 0;

        // get ProductMilkService
        $ProducMilkList = ProductMilkService::getList('Y', '', [], $factory_id);

        for ($i = 0; $i < $loop; $i++) {

            foreach ($ProducMilkList as $key => $value) {

                $data = [];
                $data['bg_color'] = '#ccc';
                $data['ProductionInfoName'] = $value['name'];
                $data['show_button'] = 'Y';
                $data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'factory_id' => $factory_id
                    , 'DisplayType' => $condition['DisplayType']
                ];
                array_push($DataList, $data);

                $Total_CurrentAmount = 0;
                $Total_CurrentBaht = 0;
                $Total_BeforeAmount = 0;
                $Total_BeforeBaht = 0;

                // loop by factory
                // foreach ($FactoryList as $key1 => $value1) {
                // $data = [];
                // $data['bg_color'] = '#ccc';
                // $data['ProductionInfoName'] = $value1['factory_name'];
                // array_push($DataList, $data);
                // get SubProductMilkService
                $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id'], 'Y');

                // Prepare condition

                $Sum_CurrentAmount = 0;
                $Sum_CurrentBaht = 0;
                $Sum_BeforeAmount = 0;
                $Sum_BeforeBaht = 0;

                // $factory_id = $value1['id'];

                foreach ($SubProductMilkList as $k => $v) {

                    $master_type_id = $v['id'];

                    $monthName = ProductionSaleInfoController::getMonthName($curMonth);

                    // get cooperative type
                    $SumCurrentAmount = 0;
                    $SumCurrentBaht = 0;
                    $SumBeforeAmount = 0;
                    $SumBeforeBaht = 0;
                    $UpdateDate = '';
                    $ApproveDate = '';
                    $ApproveComment = '';
                    // loop get quarter sum data

                    for ($j = 0; $j < 12; $j++) {

                        $curMonth = $monthList[$j];
                        $Current = ProductionSaleInfoService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                        // print_r($Current);exit;
                        $SumCurrentAmount += floatval($Current['sum_amount']);
                        $SumCurrentBaht += floatval($Current['sum_baht']);

                        $Before = ProductionSaleInfoService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
                        $SumBeforeAmount += floatval($Before['sum_amount']);
                        $SumBeforeBaht += floatval($Before['sum_baht']);

                        if (!empty($Current['update_date'])) {
                            $UpdateDate = $Current['update_date'];
                        }
                        if (!empty($Current['office_approve_id'])) {
                            $ApproveDate = $Current['office_approve_date'];
                        }
                        if (!empty($Current['office_approve_comment'])) {
                            $ApproveComment = $Current['office_approve_comment'];
                        }
                    }

                    $data = [];
                    // $data['RegionName'] = $value['RegionName'];
                    $data['ProductionInfoName'] = $v['product_character'] . ' ' . $v['name'];
                    // $data['Quarter'] = $curQuarter;

                    $data['CurrentAmount'] = $SumCurrentAmount;
                    $data['CurrentBaht'] = $SumCurrentBaht;

                    $data['BeforeAmount'] = $SumBeforeAmount;
                    $data['BeforeBaht'] = $SumBeforeBaht;

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;

                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }

                    $data['CreateDate'] = $Current['update_date'];
                    $data['ApproveDate'] = $Current['office_approve_date'];
                    if (!empty($Current['office_approve_id'])) {
                        if (empty($Current['office_approve_comment'])) {
                            $data['Status'] = 'อนุมัติ';
                        } else {
                            $data['Status'] = 'ไม่อนุมัติ';
                        }
                    }
                    $data['Description'] = ['months' => $curMonth
                        , 'years' => $curYear
                        , 'factory_id' => $factory_id
                    ];

                    array_push($DataList, $data);

                    $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                    $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];

                    $Sum_CurrentAmount += $data['CurrentAmount'];
                    $Sum_CurrentBaht += $data['CurrentBaht'];
                    $Sum_BeforeAmount += $data['BeforeAmount'];
                    $Sum_BeforeBaht += $data['BeforeBaht'];

                    $Total_CurrentAmount += $data['CurrentAmount'];
                    $Total_CurrentBaht += $data['CurrentBaht'];
                    $Total_BeforeAmount += $data['BeforeAmount'];
                    $Total_BeforeBaht += $data['BeforeBaht'];

                    $GrandTotal_CurrentAmount += $data['CurrentAmount'];
                    $GrandTotal_CurrentBaht += $data['CurrentBaht'];
                    $GrandTotal_BeforeAmount += $data['BeforeAmount'];
                    $GrandTotal_BeforeBaht += $data['BeforeBaht'];
                    // }

                    $data = [];
                    $data['bg_color'] = '#AFE1FA';
                    $data['ProductionInfoName'] = 'รวม';

                    $data['CurrentAmount'] = $Sum_CurrentAmount;
                    $data['CurrentBaht'] = $Sum_CurrentBaht;

                    $data['BeforeAmount'] = $Sum_BeforeAmount;
                    $data['BeforeBaht'] = $Sum_BeforeBaht;

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;
                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }
                    array_push($DataList, $data);
                }
            }

            $curMonth++;
        }

        $data = [];
        // $data['RegionName'] = $value['RegionName'];
        $data['bg_color'] = '#87D0F5';
        $data['ProductionInfoName'] = 'รวมทุกรายการ';
        $data['CurrentAmount'] = $GrandTotal_CurrentAmount;
        $data['CurrentBaht'] = $GrandTotal_CurrentBaht;
        $data['BeforeAmount'] = $GrandTotal_BeforeAmount;
        $data['BeforeBaht'] = $GrandTotal_BeforeBaht;

        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
        $data['DiffAmount'] = $DiffAmount;
        if ($data['BeforeAmount'] != 0) {
            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
            $data['DiffAmountPercentage'] = 100;
        } else {
            $data['DiffAmountPercentage'] = 0;
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        } else {
            $data['DiffBahtPercentage'] = 0;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getMainListDetail($request, $response, $args) {

        try {

            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            $condition = $params['obj']['condition'];

            if ($condition['DisplayType'] == 'monthly') {
                $monthList = [$condition['months']];
            } else if ($condition['DisplayType'] == 'quarter') {

                $curQuarter = intval($condition['quarter']);
                if ($curQuarter == 1) {
                    $monthList = [10, 11, 12];
                } else if ($curQuarter == 2) {
                    $monthList = [1, 2, 3];
                } else if ($curQuarter == 3) {
                    $monthList = [4, 5, 6];
                } else if ($curQuarter == 4) {
                    $monthList = [7, 8, 9];
                }
            } else if ($condition['DisplayType'] == 'annually') {
                $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            }

            $factory_id = $condition['factory_id'];
            // $ymFrom = $condition['years'] . '-' . str_pad($condition['months'], 2, "0", STR_PAD_LEFT);
            // $ymTo = $condition['years'] . '-' . str_pad($condition['months'], 2, "0", STR_PAD_LEFT);
            // $toTime = $condition['years'] . '-' . str_pad($condition['months'], 2, "0", STR_PAD_LEFT) . '-28';
            // $fromTime = $condition['years'] . '-' . str_pad($condition['months'], 2, "0", STR_PAD_LEFT) . '-01';
            // $date1 = new \DateTime($toTime);
            // $date2 = new \DateTime($fromTime);
            // $diff = $date1->diff($date2);
            // $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
            // if ($diffMonth == 0) {
            //     $diffMonth = 1;
            // } else {
            //     $diffMonth += 1;
            // }
            $curMonth = $condition['months'];
            $curYear = $condition['years'];
            $DataList = [];
            $DataSummary = [];

            $GrandTotal_CurrentAmount = 0;
            $GrandTotal_CurrentBaht = 0;
            $GrandTotal_BeforeAmount = 0;
            $GrandTotal_BeforeBaht = 0;

            // get ProductMilkService
            $ProducMilkList = ProductMilkService::getList('Y', '', [], $factory_id);

            // get Factory
            $FactoryList = FactoryService::getData($factory_id);

            // for ($i = 0; $i < $diffMonth; $i++) {
            // loop by factory
            foreach ($FactoryList as $key1 => $value1) {

                $data = [];
                $data['bg_color'] = '#ccc';
                $data['ProductionInfoName'] = $value1['factory_name'];
                $data['show_button'] = 'Y';
                array_push($DataList, $data);

                $Total_CurrentAmount = 0;
                $Total_CurrentBaht = 0;
                $Total_BeforeAmount = 0;
                $Total_BeforeBaht = 0;


                foreach ($ProducMilkList as $key => $value) {

                    $data = [];
                    $data['bg_color'] = '#ccc';
                    $data['ProductionInfoName'] = $value['name'];
                    array_push($DataList, $data);

                    // get SubProductMilkService
                    $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id'], 'Y');

                    // Prepare condition


                    $Sum_CurrentAmount = 0;
                    $Sum_CurrentAddon = 0;
                    $Sum_CurrentBaht = 0;
                    $Sum_BeforeAmount = 0;
                    $Sum_BeforeAddon = 0;
                    $Sum_BeforeBaht = 0;

                    // $factory_id = $value1['id'];

                    foreach ($SubProductMilkList as $k => $v) {

                        $data = [];
                        $data['bg_color'] = '#ccc';
                        $data['ProductionInfoName'] = $v['product_character'] . ' ' . $v['name'];
                        array_push($DataList, $data);

                        // get ProductMilkDetailService
                        $ProductMilkDetailList = ProductMilkDetailService::getListByParent($v['id'], 'Y');

                        foreach ($ProductMilkDetailList as $k1 => $v1) {

                            $SumCurrentAmount = 0;
                            $SumCurrentBaht = 0;
                            $SumBeforeAmount = 0;
                            $SumBeforeBaht = 0;
                            $SumCurrentAddon = 0;
                            $SumBeforeAddon = 0;
                            $master_type_id = $v1['id'];

                            $monthName = ProductionSaleInfoController::getMonthName($curMonth);

                            for ($j = 0; $j < count($monthList); $j++) {

                                $curMonth = $monthList[$j];
                                $Current = ProductionSaleInfoService::getDetailList($curYear, $curMonth, $factory_id, $master_type_id);
                                // print_r($Current);exit;
                                $SumCurrentAmount += floatval($Current['sum_amount']);
                                $SumCurrentAddon += floatval($Current['sum_addon']);
                                $SumCurrentBaht += floatval($Current['sum_baht']);

                                $Before = ProductionSaleInfoService::getDetailList($beforeYear, $curMonth, $factory_id, $master_type_id);
                                $SumBeforeAmount += floatval($Before['sum_amount']);
                                $SumBeforeAddon += floatval($Before['sum_addon']);
                                $SumBeforeBaht += floatval($Before['sum_baht']);
                            }

                            $data = [];
                            // $data['RegionName'] = $value['RegionName'];
                            $data['master_type_id'] = $v1['id'];
                            $data['ProductionInfoName'] = $v1['name']  . ' ' . $v1['number_of_package'] . ' ' . $v1['unit'] . ' ' . $v1['amount'] . ' ' . $v1['amount_unit'] . ' ' . $v1['taste'];
                            $data['Month'] = $monthName;
                            $data['Quarter'] = $curQuarter;
                            $data['Year'] = ($curYear + 543);

                            $data['CurrentAmount'] = $SumCurrentAmount;
                            $data['CurrentAddon'] = $SumCurrentAddon;
                            $data['CurrentBaht'] = $SumCurrentBaht;

                            $data['BeforeAmount'] = $SumBeforeAmount;
                            $data['BeforeAddon'] = $SumBeforeAddon;
                            $data['BeforeBaht'] = $SumBeforeBaht;

                            // $data = [];
                            // $data['RegionName'] = $value['RegionName'];
                            // $data['ProductionInfoName'] = $v1['name'];
                            // $data['Month'] = $monthName;
                            // get cooperative type
                            // $Current = ProductionSaleInfoService::getDetailList($curYear, $curMonth, $factory_id, $master_type_id);
                            // // print_r($Current);exit;
                            // $data['CurrentAmount'] = floatval($Current['sum_amount']);
                            // $data['CurrentBaht'] = floatval($Current['sum_baht']);
                            // $Before = ProductionSaleInfoService::getDetailList($beforeYear, $curMonth, $factory_id, $master_type_id);
                            // $data['BeforeAmount'] = floatval($Before['sum_amount']);
                            // $data['BeforeBaht'] = floatval($Before['sum_baht']);

                            $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                            $data['DiffAmount'] = $DiffAmount;
                            if ($data['BeforeAmount'] != 0) {
                                $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                            } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                                $data['DiffAmountPercentage'] = 100;
                            } else {
                                $data['DiffAmountPercentage'] = 0;
                            }


                            $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                            $data['DiffBaht'] = $DiffBaht;

                            if ($data['BeforeBaht'] != 0) {
                                $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                            } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                                $data['DiffBahtPercentage'] = 100;
                            } else {
                                $data['DiffBahtPercentage'] = 0;
                            }

                            $data['CreateDate'] = $Current['update_date'];
                            $data['ApproveDate'] = $Current['office_approve_date'];
                            if (!empty($Current['office_approve_id'])) {
                                if (empty($Current['office_approve_comment'])) {
                                    $data['Status'] = 'อนุมัติ';
                                } else {
                                    $data['Status'] = 'ไม่อนุมัติ';
                                }
                            }
                            $data['Description'] = ['months' => $curMonth
                                , 'years' => $curYear
                                , 'factory_id' => $factory_id
                            ];

                            array_push($DataList, $data);

                            $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                            $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];

                            $Sum_CurrentAmount += $data['CurrentAmount'];
                            $Sum_CurrentAddon += $data['CurrentAddon'];
                            $Sum_CurrentBaht += $data['CurrentBaht'];
                            $Sum_BeforeAmount += $data['BeforeAmount'];
                            $Sum_BeforeAddon += $data['BeforeAddon'];
                            $Sum_BeforeBaht += $data['BeforeBaht'];

                            $Total_CurrentAmount += $data['CurrentAmount'];
                            $Total_CurrentAddon += $data['CurrentAddon'];
                            $Total_CurrentBaht += $data['CurrentBaht'];
                            $Total_BeforeAmount += $data['BeforeAmount'];
                            $Total_BeforeAddon += $data['BeforeAddon'];
                            $Total_BeforeBaht += $data['BeforeBaht'];

                            $GrandTotal_CurrentAmount += $data['CurrentAmount'];
                            $GrandTotal_CurrentBaht += $data['CurrentBaht'];
                            $GrandTotal_BeforeAmount += $data['BeforeAmount'];
                            $GrandTotal_BeforeBaht += $data['BeforeBaht'];
                        }
                    }

                    $data = [];
                    $data['bg_color'] = '#AFE1FA';
                    $data['ProductionInfoName'] = 'รวม';

                    $data['CurrentAmount'] = $Sum_CurrentAmount;
                    $data['CurrentAddon'] = $Sum_CurrentAddon;
                    $data['CurrentBaht'] = $Sum_CurrentBaht;
                    $data['BeforeAmount'] = $Sum_BeforeAmount;
                    $data['BeforeAddon'] = $Sum_BeforeAddon;
                    $data['BeforeBaht'] = $Sum_BeforeBaht;

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;
                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }
                    array_push($DataList, $data);
                }
                $data = [];
                $data['bg_color'] = '#A0DEFD';
                $data['ProductionInfoName'] = 'รวมทั้งสิ้น';

                $data['CurrentAmount'] = $Total_CurrentAmount;
                $data['CurrentAddon'] = $Total_CurrentAddon;
                $data['CurrentBaht'] = $Total_CurrentBaht;

                $data['BeforeAmount'] = $Total_BeforeAmount;
                $data['BeforeAddon'] = $Total_BeforeAddon;
                $data['BeforeBaht'] = $Total_BeforeBaht;

                $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                $data['DiffAmount'] = $DiffAmount;
                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                    $data['DiffAmountPercentage'] = 100;
                } else {
                    $data['DiffAmountPercentage'] = 0;
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    $data['DiffBahtPercentage'] = 100;
                } else {
                    $data['DiffBahtPercentage'] = 0;
                }
                array_push($DataList, $data);
            }

            $curMonth++;
            // }



            $this->data_result['DATA']['DataList'] = $DataList;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getData($request, $response, $args) {
        try {
            $params = $request->getParsedBody();

            $id = $params['obj']['id'];

            $factory_id = $params['obj']['factory_id'];
            $months = $params['obj']['months'];
            $years = $params['obj']['years'];

            if (!empty($id)) {
                $_Data = ProductionSaleInfoService::getDataByID($id);
            } else {
                $_Data = ProductionSaleInfoService::getData($factory_id, $months, $years);
            }

            $this->data_result['DATA']['Data'] = $_Data;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function updateData($request, $response, $args) {
        // error_reporting(E_ERROR);
        //     error_reporting(E_ALL);
        //     ini_set('display_errors','On');
        $URL = '127.0.0.1';
        try {
            $params = $request->getParsedBody();
            $_Data = $params['obj']['Data'];
            foreach ($_Data as $key => $value) {
                if($value == 'null'){
                    $_Data[$key] = '';
                }
            }

            $user_session = $params['user_session'];

            $OrgID = $user_session['OrgID'];

            $HeaderData = $this->do_post_request('http://' . $URL . '/dportal/dpo/public/mis/get/org/header/', "POST", ['OrgID' => $OrgID, 'Type' => 'OWNER']);
            $HeaderData = json_decode(trim($HeaderData), TRUE);
            // print_r($HeaderData);exit;
            if ($HeaderData['data']['DATA']['Header']['OrgType'] == 'DEPARTMENT') {
                $_Data['dep_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['dep_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
            } else if ($HeaderData['data']['DATA']['Header']['OrgType'] == 'DIVISION') {
                $_Data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['division_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
            } else if ($HeaderData['data']['DATA']['Header']['OrgType'] == 'OFFICE') {
                $_Data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['office_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
            }

            $_Detail = $params['obj']['Detail'];

            // get region from cooperative id
            // $Cooperative = CooperativeService::getData($_Data['cooperative_id']);
            // $_Data['region_id'] = $Cooperative['region_id'];
            // print_r($_Data);
            // exit();

            $id = ProductionSaleInfoService::updateData($_Data);

            foreach ($_Detail as $key => $value) {
                $value['production_sale_info_id'] = $id;
                ProductionSaleInfoService::updateDetailData($value);
            }

            //           
            $this->data_result['DATA']['id'] = $id;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function uploadData($request, $response, $args) {
        // error_reporting(E_ERROR);
        //     error_reporting(E_ALL);
        //     ini_set('display_errors','On');
        $_WEB_FILE_PATH = 'files/files';
        try {
            $params = $request->getParsedBody();
            $_Data = $params['obj']['Data'];

            foreach ($_Data as $key => $value) {
                if($value == 'null'){
                    $_Data[$key] = '';
                }
            }

            $_FileDate = $params['obj']['FileDate'];

            $user_session = $params['user_session'];

            $id = ProductionSaleInfoService::updateData($_Data);

            // clear item
            ProductionSaleInfoService::removeDetailDataByParent($id);            

            $files = $request->getUploadedFiles();
            $f = $files['obj']['AttachFile'];
            $_UploadFile = [];
            if($f != null){
                if($f->getClientFilename() != ''){
                    // Unset old image if exist
                    
                    $ext = pathinfo($f->getClientFilename(), PATHINFO_EXTENSION);
                    $FileName = date('YmdHis').'_'.rand(100000,999999). '.'.$ext;
                    $FilePath = $_WEB_FILE_PATH . '/upload/'.$FileName;
                    
                    $_UploadFile['file_name'] = $f->getClientFilename();
                    $_UploadFile['file_path'] = $FilePath;
                    
                    $f->moveTo('../../' . $FilePath);
                }        
            }

            // read file 
            $file = '../../' . $FilePath;
            $_Detail = $this->readExcelFile($file, $id);

            // print_r($_Detail);
            // exit;
            foreach ($_Detail as $main_key => $main_value) {

                foreach ($main_value as $key => $value) {
                    
                    $data = [];

                    $value['product_milk_detail'] = str_replace('0.00', '0', $value['product_milk_detail']);
                    
                    $data['production_sale_info_id'] = $value['production_sale_info_id'];
                    $data['production_sale_info_type1'] = $this->getProductInfoType1($value['product_milk'], $_Data['factory_id']);
                    $data['production_sale_info_type2'] = $this->getProductInfoType2($value['sub_product_milk'], $data['production_sale_info_type1']);
                    $data['production_sale_info_type3'] = $this->getProductInfoType3($value['product_milk_detail'], $data['production_sale_info_type2']);
                    $data['sale_chanel_id'] = $this->getSaleChanelID($value['agent']);
                    $data['customer_name'] = $value['customer_name'];

                    // $data_detail = ProductionSaleInfoService::findIDWithProductTypeID($data['production_sale_info_type1'], $data['production_sale_info_type2'], $data['production_sale_info_type3'], $data['sale_chanel_id']);

                    $value['result_amount'] = str_replace(',', '', $value['result_amount']);
                    $value['result_addon'] = str_replace(',', '', $value['result_addon']);
                    $value['result_thb'] = str_replace(',', '', $value['result_thb']);

                    // if(empty($data_detail)){
                    $data['package_amount'] = empty($value['result_package_amount'])?0:$value['result_package_amount'];
                    $data['amount'] = empty($value['result_amount'])?0:$value['result_amount'] /*+ $value['result_addon']*/;
                    $data['addon'] = empty($value['result_addon'])?0:$value['result_addon'];
                    $data['addon_package_amount'] = empty($value['result_addon_package'])?0:$value['result_addon_package'];

                    $ProductMilkDetailData = ProductMilkDetailService::getData($data['production_sale_info_type3']);

                    if(!empty($value['result_package_amount']) && ($ProductMilkDetailData['unit'] == 'ซีซี' || $ProductMilkDetailData['unit'] == 'มิลลิลิตร')){

                        // $data['amount'] = empty($value['result_amount'])?0:$value['result_amount'];
                        $box = 0;
                        $amount_data = explode('.', ''.$value['result_package_amount']);
                        $amount = $amount_data[0];
                        if(!empty($amount_data[1])){
                            $box = $amount_data[1];
                        }
                        
                        $data['amount'] = ((($amount * $ProductMilkDetailData['amount']) + $box) * $ProductMilkDetailData['number_of_package']) / 1000;
                    }else{
                        $data['amount'] = empty($value['result_amount'])?0:$value['result_amount'];
                    
                    }  

                    if(!empty($value['result_addon_package']) && ($ProductMilkDetailData['unit'] == 'ซีซี' || $ProductMilkDetailData['unit'] == 'มิลลิลิตร')){

                        // $data['amount'] = empty($value['result_amount'])?0:$value['result_amount'];
                        $box = 0;
                        $amount_data = explode('.', ''.$value['result_addon_package']);
                        $amount = $amount_data[0];
                        if(!empty($amount_data[1])){
                            $box = $amount_data[1];
                        }
                        
                        $data['addon'] = ((($amount * $ProductMilkDetailData['amount']) + $box) * $ProductMilkDetailData['number_of_package']) / 1000;
                    }else{
                        $data['addon'] = empty($value['result_addon'])?0:$value['result_addon'];
                    }   

                    $data['price_value'] = empty($value['result_thb'])?0:$value['result_thb'];
                    $data['id'] = '';
                    // }else{
                    //     $data['amount'] = empty($value['result_amount'])?0:$value['result_amount'] + $value['result_addon'] + $data_detail['amount'];
                    //     $data['price_value'] = empty($value['result_thb'])?0:$value['result_thb'] + $data_detail['price_value'];
                    //     $data['id'] = $data_detail['id'];
                    // }
                    

                    ProductionSaleInfoService::updateDetailData($data);
                }
            }

            // add log
            $_UploadFile['menu_type'] = 'production-sale-info';
            $_UploadFile['file_date'] = $_FileDate;
            $_UploadFile['data_id'] = $id;
            UploadLogService::updateLog($_UploadFile);

            //           
            $this->data_result['DATA']['id'] = $id;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function getProductInfoType1($product_milk, $factory_id){
        return ProductMilkService::getIDByName($product_milk, $factory_id);
    }

    private function getProductInfoType2($sub_product_milk, $production_sale_info_type1){
        return SubProductMilkService::getIDByName($sub_product_milk, $production_sale_info_type1);
    }

    private function getProductInfoType3($product_milk_detail, $production_sale_info_type2){
        return ProductMilkDetailService::getIDByName($product_milk_detail, $production_sale_info_type2);
    }

    private function getSaleChanelID($chanel_name){
        return ProductMilkService::getSaleChanelIDByName($chanel_name);
    }
    
    private function readExcelFile($file, $production_sale_info_id){

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        // $sheetData = $spreadsheet->getActiveSheet()->toArray();

        $field_array = ['agent', 'customer_name', 'product_milk', 'sub_product_milk', 'product_milk_detail', 'goal_year_amount', 'goal_year_addon', 'goal_year_thb',  'goal_month_amount', 'goal_month_addon', 'goal_month_thb', 'result_package_amount', 'result_amount', 'result_thb', 'result_addon_package', 'result_addon'];

        $cnt = 0;
        $DataList = [];
        
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetData = $sheet->toArray();

            $cnt_row = 1;

            $ItemList = [];
            foreach ($sheetData as $key => $value) {
                
                if($cnt_row >= 3){
                    
                    $cnt_col = 0;
                    $cnt_field = 0;
                    $Item = [];
                    $Item[ 'production_sale_info_id' ] = $production_sale_info_id;

                    foreach ($value as $k => $v) {
                        // if($cnt_col >= 1 && $cnt_col <= 7){
                            
                            $Item[ $field_array[$cnt_field] ] = $v;
                            $cnt_field++;
                            
                        // }
                        $cnt_col++;
                    }
                    
                    array_push($ItemList, $Item);
                    
                }

                $cnt_row++;

            }

            array_push($DataList, $ItemList);
        }
        // print_r($DataList);
        // exit;
        return $DataList;
    }

    public function removeDetailData($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $id = $params['obj']['id'];
            $result = ProductionSaleInfoService::removeDetailData($id);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function updateDataApprove($request, $response, $args) {
        // $URL = '172.23.10.224';
        $URL = '127.0.0.1';
        try {
            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            $id = $params['obj']['id'];
            $ApproveStatus = $params['obj']['ApproveStatus'];
            $ApproveComment = $params['obj']['ApproveComment'];
            $OrgType = $params['obj']['OrgType'];
            $approval_id = $user_session['UserID'];
            $OrgID = $user_session['OrgID'];

            if ($ApproveStatus == 'approve') {
                // http post to dpo database to retrieve division's header
                $HeaderData = $this->do_post_request('http://' . $URL . '/dportal/dpo/public/mis/get/org/header/', "POST", ['UserID' => $approval_id, 'OrgID' => $OrgID]);
                $HeaderData = json_decode(trim($HeaderData), TRUE);

                $data = [];
                $ApproveComment = '';

                if ($OrgType == 'dep') {
                    $data['dep_approve_date'] = date('Y-m-d H:i:s');
                    $data['dep_approve_comment'] = $ApproveComment;
                    $data['dep_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];

                    $data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                } else if ($OrgType == 'division') {
                    $data['division_approve_date'] = date('Y-m-d H:i:s');
                    $data['division_approve_comment'] = $ApproveComment;
                    $data['division_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];

                    $data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                } else if ($OrgType == 'office') {
                    $data['office_approve_date'] = date('Y-m-d H:i:s');
                    $data['office_approve_comment'] = $ApproveComment;
                    $data['office_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                }
            } else if ($ApproveStatus == 'reject') {

                if ($OrgType == 'dep') {
                    $data['dep_approve_date'] = date('Y-m-d H:i:s');
                    $data['dep_approve_comment'] = $ApproveComment;
                    $data['dep_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                } else if ($OrgType == 'division') {
                    $data['dep_approve_date'] = NULL;
                    $data['dep_approve_comment'] = NULL;

                    $data['division_approve_id'] = NULL;
                    $data['division_approve_date'] = date('Y-m-d H:i:s');
                    $data['division_approve_comment'] = $ApproveComment;
                    $data['division_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                } else if ($OrgType == 'office') {

                    $data['dep_approve_date'] = NULL;
                    $data['dep_approve_comment'] = NULL;

                    $data['division_approve_id'] = NULL;
                    $data['division_approve_date'] = NULL;
                    $data['division_approve_comment'] = NULL;

                    $data['office_approve_id'] = NULL;
                    $data['office_approve_date'] = date('Y-m-d H:i:s');
                    $data['office_approve_comment'] = $ApproveComment;
                    $data['office_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                }
            }

            // print_r($data );
            // exit;
            $result = ProductionSaleInfoService::updateDataApprove($id, $data);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function do_post_request($url, $method, $data = [], $optional_headers = null) {
        $params = array('http' => array(
                'method' => $method,
                'content' => http_build_query($data)
        ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            print_r($fp);
            return array("STATUS" => 'ERROR', "MSG" => "ERROR :: Problem with $url");
            //throw new Exception("Problem with $url, $php_errormsg");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            print_r($response);
            return array("STATUS" => 'ERROR', "MSG" => "ERROR :: Problem reading data from $url");
            //            throw new Exception("Problem reading data from $url");
        }

        return $response;
    }

    public function getMonthDatasubproductreport($condition) {



        $ymFrom = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }


        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        $DataList = [];
        $DataSummary = [];

        // get ProductMilkService
        $ProducMilkList = ProductMilkService::getList('Y', '', [], '');
        foreach ($ProducMilkList as $value) {
            $Datapro['productname'] = $value['name'];
            $Datapro['item'] = [];
            $Datapro['sumCurrentAmount'] = 0;
            $Datapro['sumBeforeAmount'] = 0;
            $Datapro['sumDiffAmount'] = 0;
            $Datapro['sumDiffAmountPercentage'] = 0;
            $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);
            foreach ($SubProductMilkList as $k => $v) {
                $curMonth = $condition['MonthFrom'];
                $curYear = $condition['YearFrom'];
                $data = [];
                $data['subProductionInfoName'] = $v['name'];
                for ($i = 0; $i < $diffMonth; $i++) {

                    $monthName = ProductionSaleInfoController::getMonthName($curMonth);
                    $data['Month'] = $monthName;


                    // get ProductMilkDetailService
                    $ProductMilkDetailList = ProductMilkDetailService::getListByParent($v['id']);

                    foreach ($ProductMilkDetailList as $k1 => $v1) {

                        $master_type_id = $v1['id'];

                        $Current = ProductionSaleInfoService::getDetailListsub($curYear, $curMonth, $master_type_id);
                        //  print_r($Current);
                        $data['CurrentAmount'] += floatval($Current['sum_amount']);
//                            $data['CurrentBaht'] += floatval($Current['sum_baht']);

                        $Before = ProductionSaleInfoService::getDetailListsub($curYear - 1, $curMonth, $master_type_id);
                        $data['BeforeAmount'] += floatval($Before['sum_amount']);
//                            $data['BeforeBaht'] += floatval($Before['sum_baht']);

                        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                        $data['DiffAmount'] = $DiffAmount;



//                            $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
//                            $data['DiffBaht'] += $DiffBaht;
//                            if ($data['BeforeBaht'] != 0) {
//                                $data['DiffBahtPercentage'] += (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
//                            } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
//                                $data['DiffBahtPercentage'] = 100;
//                            }

                        $data['CreateDate'] = $Current['update_date'];
                        $data['ApproveDate'] = $Current['office_approve_date'];
//                        if (!empty($Current['office_approve_id'])) {
//                            if (empty($Current['office_approve_comment'])) {
//                                $data['Status'] = 'อนุมัติ';
//                            } else {
//                                $data['Status'] = 'ไม่อนุมัติ';
//                            }
//                        }
                        $data['Description'] = ['months' => $curMonth
                            , 'years' => $curYear
                        ];

                        //  array_push($DataList, $data);
                        $Datapro['sumCurrentAmount'] += floatval($Current['sum_amount']);
                        $Datapro['sumBeforeAmount'] += floatval($Before['sum_amount']);

                        $DataSummary['sumCurrentAmount'] += floatval($Current['sum_amount']);
                        $DataSummary['sumBeforeAmount'] += floatval($Before['sum_amount']);

                        $Datapro['sumDiffAmount'] = 0;
                        $Datapro['sumDiffAmountPercentage'] = 0;
                    }
                    $Datapro['sumDiffAmount'] = $Datapro['sumCurrentAmount'] - $Datapro['sumBeforeAmount'];


                    if ($Datapro['sumBeforeAmount'] != 0) {
                        $Datapro['sumDiffAmountPercentage'] += (( $Datapro['sumCurrentAmount'] - $Datapro['sumBeforeAmount']) / $Datapro['sumBeforeAmount']) * 100;
                    } else if (empty($Datapro['sumBeforeAmount']) && !empty($Datapro['sumCurrentAmount'])) {
                        $Datapro['sumDiffAmountPercentage'] = 100;
                    }
                    $curMonth++;
                    if ($curMonth > 12) {
                        $curMonth = 1;
                        $curYear++;
                    }
                }
//                $ck=[$date1,$date2,$diffMonth];
                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] += (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                    $data['DiffAmountPercentage'] = 100;
                }
                array_push($Datapro['item'], $data);
            }
            array_push($DataList, $Datapro);
        }
        $DataSummary['sumDiffAmount'] = $DataSummary['sumCurrentAmount'] - $DataSummary['sumBeforeAmount'];


        if ($DataSummary['sumBeforeAmount'] != 0) {
            $DataSummary['sumDiffAmountPercentage'] += (( $DataSummary['sumCurrentAmount'] - $DataSummary['sumBeforeAmount']) / $Datapro['sumBeforeAmount']) * 100;
        } else if (empty($DataSummary['sumBeforeAmount']) && !empty($DataSummary['sumCurrentAmount'])) {
            $DataSummary['sumDiffAmountPercentage'] = 100;
        }
        // print_r($DataList);
        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getMonthDataListreport($condition, $fac_id) {

        $factory_id = $fac_id;
        $ymFrom = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }
        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        $DataList = [];
        $DataSummary = [];

        $GrandTotal_CurrentAmount = 0;
        $GrandTotal_CurrentBaht = 0;
        $GrandTotal_BeforeAmount = 0;
        $GrandTotal_BeforeBaht = 0;

        // get ProductMilkService
        $ProducMilkList = ProductMilkService::getList('Y', '', [], '');

        // get Factory
        $FactoryList = FactoryService::getData($factory_id);
        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        for ($i = 0; $i < $diffMonth; $i++) {


            // loop by factory
            foreach ($FactoryList as $key1 => $value1) {

                $data = [];
                // $data['bg_color'] = '#ccc';
                $DataList['factory_name'] = $value1['factory_name'];
                $DataList['product'] = [];
                //  array_push($DataList, $data);

                $Total_CurrentAmount = 0;
                $Total_CurrentBaht = 0;
                $Total_BeforeAmount = 0;
                $Total_BeforeBaht = 0;


                foreach ($ProducMilkList as $key => $value) {

                    $datapro = [];

                    $datapro['ProductionInfoName'] = $value['name'];
                    $datapro['sub'] = [];
                    $datapro['summary'] = [];
                    // array_push($DataList, $data);
                    // get SubProductMilkService
                    $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);

                    // Prepare condition


                    $Sum_CurrentAmount = 0;
                    $Sum_CurrentBaht = 0;
                    $Sum_BeforeAmount = 0;
                    $Sum_BeforeBaht = 0;

                    $factory_id = $value1['id'];

                    foreach ($SubProductMilkList as $k => $v) {

                        $data = [];
                        // $data['bg_color'] = '#ccc';
                        $data['subProductionInfoName'] = $v['name'];
                        $data['Month'] = $monthName;
                        $data['CurrentAmount'] = 0;
                        $data['BeforeAmount'] = 0;
                        $data['DiffAmount'] = 0;
                        $data['DiffAmountPercentage'] = 0;
                        // $data['detail'] = [];
                        //array_push($DataList, $data);
                        $monthName = ProductionSaleInfoController::getMonthName($curMonth);
                        // get ProductMilkDetailService
                        $ProductMilkDetailList = ProductMilkDetailService::getListByParent($v['id']);
                        //    print_r($ProductMilkDetailList);
                        foreach ($ProductMilkDetailList as $k1 => $v1) {

                            $master_type_id = $v1['id'];

//                            $monthName = ProductionSaleInfoController::getMonthName($curMonth);
                            //   $data = [];
//                            $data['subProductionInfoName'] = $v['name'];
//                            $data['Month'] = $monthName;
//                            // $data['RegionName'] = $value['RegionName'];
//                            $data['ProductionInfoName'] = $v1['name'];
//                            $data['Month'] = $monthName;
                            // get cooperative type

                            $Current = ProductionSaleInfoService::getDetailList($curYear, $curMonth, $factory_id, $master_type_id);
                            // print_r($Current);exit;
                            $data['CurrentAmount'] += floatval($Current['sum_amount']);
//                          

                            $Before = ProductionSaleInfoService::getDetailList($curYear - 1, $curMonth, $factory_id, $master_type_id);
                            $data['BeforeAmount'] += floatval($Before['sum_amount']);
//                            $data['BeforeBaht'] += floatval($Before['sum_baht']);

                            $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                            //  print_r($DiffAmount);
                            $data['DiffAmount'] = $DiffAmount;



//                         

                            $data['CreateDate'] = $Current['update_date'];

                            $data['Description'] = ['months' => $curMonth
                                , 'years' => $curYear
                                , 'factory_id' => $factory_id
                            ];

                            //  array_push($DataList, $data);

                            $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + floatval($Current['sum_amount']);
                            $DataSummary['SummaryBefore'] = $DataSummary['SummaryBefore'] + floatval($Before['sum_amount']);

                            $Sum_CurrentAmount += floatval($Current['sum_amount']);
                            //$Sum_CurrentBaht += $data['CurrentBaht'];
                            $Sum_BeforeAmount += floatval($Before['sum_amount']);
                            //  $Sum_BeforeBaht += $data['BeforeBaht'];

                            $Total_CurrentAmount += floatval($Current['sum_amount']);
                            $Total_CurrentBaht += $data['CurrentBaht'];
                            $Total_BeforeAmount += floatval($Before['sum_amount']);
                            $Total_BeforeBaht += $data['BeforeBaht'];

                            $GrandTotal_CurrentAmount += floatval($Current['sum_amount']);
                            $GrandTotal_CurrentBaht += $data['CurrentBaht'];
                            $GrandTotal_BeforeAmount += floatval($Before['sum_amount']);
                            $GrandTotal_BeforeBaht += $data['BeforeBaht'];
                            //  array_push($datapro['sub'], $data);
                        }
                        if ($data['BeforeAmount'] != 0) {
                            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                            $data['DiffAmountPercentage'] = 100;
                        }
                        array_push($datapro['sub'], $data);
                    }


                    $data = [];
                    $data['bg_color'] = '#AFE1FA';
                    $data['ProductionInfoName'] = 'รวม';

                    $data['CurrentAmount'] = $Sum_CurrentAmount;


                    $data['BeforeAmount'] = $Sum_BeforeAmount;
                    // print_r($Sum_CurrentAmount);

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $data['CurrentAmount'] - $data['BeforeAmount'];
                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    }


//                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
//                    $data['DiffBaht'] = $DiffBaht;
//
//                    if ($data['BeforeBaht'] != 0) {
//                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
//                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
//                        $data['DiffBahtPercentage'] = 100;
//                    }
                    array_push($datapro['summary'], $data);
                    array_push($DataList['product'], $datapro);
                }
            }

            $curMonth++;
            if ($curMonth > 12) {
                $curMonth = 1;
                $curYear++;
            }
        }

        return ['DataList' => $DataList, 'Summary' => $DataSummary, 'Detail' => $Datadetail];
    }

    public function getMonthreportforsubcom($condition, $fac_id) {
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $factory_id = $fac_id;
        $ymFrom = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }
        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        $DataList = [];
        $DataSummary = [];
        $beforemonth = $condition['MonthFrom'] - 1;
        $year = $condition['YearFrom'];
        if ($curMonth == 12) {
            $beforemonth = 1;
            $year--;
        }
        $GrandTotal_CurrentAmount = 0;
        $GrandTotal_CurrentBaht = 0;
        $GrandTotal_BeforeAmount = 0;
        $GrandTotal_BeforeBaht = 0;



        // get Factory
        $FactoryList = FactoryService::getData($factory_id);
        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        if ($diffMonth == 12) {
            $curYear--;
        }
        for ($i = 0; $i < $diffMonth; $i++) {
            // loop by factory

            foreach ($FactoryList as $key1 => $value1) {
                // get ProductMilkService
                // $ProducMilkList = ProductMilkService::getList('', '', '', $value1['id']);
                $ProducMilkList = ProductMilkService::getList('Y', '', '', $factory_id);

                $DataList['factory_name'] = $value1['factory_name'];
                $DataList['product'] = [];
                $DataList['BeforemonthAmount'] = 0;
                $DataList['target'] = 0;
                $DataList['CurrentAmount'] = 0;
                $DataList['percentTarget'] = 100;
                $DataList['BeforeyearAmount'] = 0;
                $DataList['percentDiffAmount'] = 100;
                $DataList['ProductionInfoName'] = $value['name'];
                $DataList['Yeartarget'] = 0;
                $DataList['targetoct'] = 0;
                $DataList['collectoct'] = 0;
                $DataList['percentoct'] = 100;

                $datapro = [];
                foreach ($ProducMilkList as $key => $value) {
                    $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);
                    $factory_id = $value1['id'];
                    $datapro['BeforemonthAmount'] = 0;
                    $datapro['target'] = 0;
                    $datapro['CurrentAmount'] = 0;
                    $datapro['percentTarget'] = 100;
                    $datapro['BeforeyearAmount'] = 0;
                    $datapro['percentDiffAmount'] = 100;
                    $datapro['ProductionInfoName'] = $value['name'];
                    $datapro['Yeartarget'] = 0;
                    $datapro['targetoct'] = 0;
                    $datapro['collectoct'] = 0;
                    $datapro['percentoct'] = 100;
                    $type['goal_type'] = 'II';
                    $type['keyword'] = $value['name'];
                    $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
                    $mission = GoalMissionService::getMission($mastes[0]['id'], $value1['region_id'], $condition['YearFrom']);
                    $datapro['Yeartarget'] = $mission[0]['amount'] / 1000;
                    $DataList['Yeartarget'] += $mission[0]['amount'] / 1000;
                    $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearFrom'], $condition['MonthFrom']);
                    $datapro['target'] = $avg['amount'] / 1000;
                    $DataList['target'] += $avg['amount'] / 1000;
                    foreach ($SubProductMilkList as $k => $v) {
                        $ProductMilkDetailList = ProductMilkDetailService::getListByParent2($v['id'], $factory_id);
                        foreach ($ProductMilkDetailList as $k1 => $v1) {
                            $master_type_id = $v1['id'];
                            $BeforemonthAmount = ProductionSaleInfoService::getDetailList($year, $beforemonth, $factory_id, $master_type_id);
                            $Current = ProductionSaleInfoService::getDetailList($curYear, $curMonth, $factory_id, $master_type_id);
                            $Before = ProductionSaleInfoService::getDetailList($curYear - 1, $curMonth, $factory_id, $master_type_id);
                            $datapro['BeforemonthAmount'] += floatval($BeforemonthAmount['sum_amount'] / 1000);
                            $datapro['CurrentAmount'] += floatval($Current['sum_amount'] / 1000);
                            $datapro['BeforeyearAmount'] += floatval($Before['sum_amount'] / 1000);
                            $DataList['BeforemonthAmount'] += floatval($BeforemonthAmount['sum_amount'] / 1000);
                            $DataList['CurrentAmount'] += floatval($Current['sum_amount'] / 1000);
                            $DataList['BeforeyearAmount'] += floatval($Before['sum_amount'] / 1000);
                            foreach ($monthList as $key => $ml) {
                                $coct = ProductionSaleInfoService::getDetailList($condition['YearTo'] - $yearlist[$key], $ml, $factory_id, $master_type_id);
                                $avgoct = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);
                                $datapro['collectoct'] += $coct['sum_amount'] / 1000;
                                $datapro['targetoct'] += $avgoct['amount'] / 1000;
                                $DataList['collectoct'] += $coct['sum_amount'] / 1000;
                                $DataList['targetoct'] += $avgoct['amount'] / 1000;
                                if ($ml == $condition['MonthFrom']) {
                                    break;
                                }
                            }
                        }
                    }
                    if ($datapro['target'] > 0) {
                        $datapro['percentTarget'] = ($datapro['CurrentAmount'] * 100) / $datapro['target'];
                    }
                    if ($datapro['BeforeyearAmount'] > 0) {
                        $datapro['percentDiffAmount'] = (($datapro['CurrentAmount'] - $datapro['BeforeyearAmount']) * 100) / $datapro['BeforeyearAmount'];
                    }
                    if ($datapro['targetoct'] > 0) {
                        $datapro['percentoct'] = ($datapro['collectoct'] * 100) / $datapro['targetoct'];
                    }
                    array_push($DataList['product'], $datapro);
                    if ($DataList['target'] > 0) {
                        $DataList['percentTarget'] = ($DataList['CurrentAmount'] * 100) / $DataList['target'];
                    }
                    if ($DataList['BeforeyearAmount'] > 0) {
                        $DataList['percentDiffAmount'] = (($DataList['CurrentAmount'] - $DataList['BeforeyearAmount']) * 100) / $datapro['BeforeyearAmount'];
                    }
                    if ($DataList['targetoct'] > 0) {
                        $DataList['percentoct'] = ($DataList['collectoct'] * 100) / $DataList['targetoct'];
                    }
                }
            }

            $curMonth++;
            if ($curMonth > 12) {
                $curMonth = 1;
                $curYear++;
            }
        }

        return ['DataList' => $DataList];
    }

    public function getQreportforsubcom($condition, $fac_id) {
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $factory_id = $fac_id;
        $ymFrom = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';


        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        $DataList = [];
        $DataSummary = [];
        $beforemonth = $condition['MonthFrom'] - 1;


        if ($condition['QuarterFrom'] == 1) {
            $curMonth = 10;
            $beforemonth = 7;
            $curYear--;
            $beforeyear = $year;
        } else if ($condition['QuarterFrom'] == 2) {
            $curMonth = 1;
            $beforemonth = 10;
            $beforeyear--;

            $beforeQuarter--;
        } else if ($condition['QuarterFrom'] == 3) {

            $curMonth = 4;
            $beforemonth = 1;
            $beforeyear--;
        } else if ($condition['QuarterFrom'] == 4) {

            $curMonth = 7;
            $beforemonth = 4;
            $beforeyear--;
        }



        // get Factory
        $FactoryList = FactoryService::getData($factory_id);
//        $curMonth = $condition['MonthFrom'];
//        $curYear = $condition['YearFrom'];

        for ($i = 0; $i < 3; $i++) {
            // loop by factory

            foreach ($FactoryList as $key1 => $value1) {
                // get ProductMilkService
                // $ProducMilkList = ProductMilkService::getList('', '', '', $value1['id']);
                $ProducMilkList = ProductMilkService::getList('Y', '', '', '');

                $DataList['factory_name'] = $value1['factory_name'];
                $DataList['product'] = [];
                $DataList['BeforemonthAmount'] = 0;
                $DataList['target'] = 0;
                $DataList['CurrentAmount'] = 0;
                $DataList['percentTarget'] = 100;
                $DataList['BeforeyearAmount'] = 0;
                $DataList['percentDiffAmount'] = 100;
                $DataList['ProductionInfoName'] = $value['name'];
                $DataList['Yeartarget'] = 0;
                $DataList['targetoct'] = 0;
                $DataList['collectoct'] = 0;
                $DataList['percentoct'] = 100;

                $datapro = [];
                foreach ($ProducMilkList as $key => $value) {
                    $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);
                    $factory_id = $value1['id'];
                    $datapro['BeforemonthAmount'] = 0;
                    $datapro['target'] = 0;
                    $datapro['CurrentAmount'] = 0;
                    $datapro['percentTarget'] = 100;
                    $datapro['BeforeyearAmount'] = 0;
                    $datapro['percentDiffAmount'] = 100;
                    $datapro['ProductionInfoName'] = $value['name'];
                    $datapro['Yeartarget'] = 0;
                    $datapro['targetoct'] = 0;
                    $datapro['collectoct'] = 0;
                    $datapro['percentoct'] = 100;
                    $type['goal_type'] = 'II';
                    $type['keyword'] = $value['name'];
                    $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
                    $mission = GoalMissionService::getMission($mastes[0]['id'], $value1['region_id'], $condition['YearFrom']);
                    $datapro['Yeartarget'] = $mission[0]['amount'] / 1000;
                    $DataList['Yeartarget'] += $mission[0]['amount'] / 1000;
                    $avg = GoalMissionService::getMissionavg($mission[0]['id'], $curYear, $curMonth);
                    $datapro['target'] = $avg['amount'] / 1000;
                    $DataList['target'] += $avg['amount'] / 1000;
                    foreach ($SubProductMilkList as $k => $v) {
                        $ProductMilkDetailList = ProductMilkDetailService::getListByParent2($v['id'], $factory_id);
                        foreach ($ProductMilkDetailList as $k1 => $v1) {
                            $master_type_id = $v1['id'];
                            $BeforemonthAmount = ProductionSaleInfoService::getDetailList($beforeyear, $beforemonth, $factory_id, $master_type_id);
                            $Current = ProductionSaleInfoService::getDetailList($curYear, $curMonth, $factory_id, $master_type_id);
                            $Before = ProductionSaleInfoService::getDetailList($curYear - 1, $curMonth, $factory_id, $master_type_id);
                            $datapro['BeforemonthAmount'] += floatval($BeforemonthAmount['sum_amount'] / 1000);
                            $datapro['CurrentAmount'] += floatval($Current['sum_amount'] / 1000);
                            $datapro['BeforeyearAmount'] += floatval($Before['sum_amount'] / 1000);
                            $DataList['BeforemonthAmount'] += floatval($BeforemonthAmount['sum_amount'] / 1000);
                            $DataList['CurrentAmount'] += floatval($Current['sum_amount'] / 1000);
                            $DataList['BeforeyearAmount'] += floatval($Before['sum_amount'] / 1000);
                            foreach ($monthList as $key => $ml) {
                                $coct = ProductionSaleInfoService::getDetailList($condition['YearTo'] - $yearlist[$key], $ml, $factory_id, $master_type_id);
                                $avgoct = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);
                                $datapro['collectoct'] += $coct['sum_amount'] / 1000;
                                $datapro['targetoct'] += $avgoct['amount'] / 1000;
                                $DataList['collectoct'] += $coct['sum_amount'] / 1000;
                                $DataList['targetoct'] += $avgoct['amount'] / 1000;
                                if ($ml == $condition['MonthFrom']) {
                                    break;
                                }
                            }
                        }
                    }
                    if ($datapro['target'] > 0) {
                        $datapro['percentTarget'] = ($datapro['CurrentAmount'] * 100) / $datapro['target'];
                    }
                    if ($datapro['BeforeyearAmount'] > 0) {
                        $datapro['percentDiffAmount'] = (($datapro['CurrentAmount'] - $datapro['BeforeyearAmount']) * 100) / $datapro['BeforeyearAmount'];
                    }
                    if ($datapro['targetoct'] > 0) {
                        $datapro['percentoct'] = ($datapro['collectoct'] * 100) / $datapro['targetoct'];
                    }
                    array_push($DataList['product'], $datapro);
                    if ($DataList['target'] > 0) {
                        $DataList['percentTarget'] = ($DataList['CurrentAmount'] * 100) / $DataList['target'];
                    }
                    if ($DataList['BeforeyearAmount'] > 0) {
                        $DataList['percentDiffAmount'] = (($DataList['CurrentAmount'] - $DataList['BeforeyearAmount']) * 100) / $datapro['BeforeyearAmount'];
                    }
                    if ($DataList['targetoct'] > 0) {
                        $DataList['percentoct'] = ($DataList['collectoct'] * 100) / $DataList['targetoct'];
                    }
                }
            }
            $beforemonth++;
            $curMonth++;
            if ($curMonth > 12) {
                $curMonth = 1;
                $curYear++;
            }
        }

        return ['DataList' => $DataList];
    }

    public function getExcelTemplate($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            // $condition = $params['obj']['condition'];

            $factory_id = $params['obj']['factory_id'];
            $years = $params['obj']['years'];
            $months = $params['obj']['months'];
            $menu_type = 'ข้อมูลการขาย';

            $con_year = $years;
            $goal_mission_year = $years;
            $avgDate = $con_year . '-'. ($months<10?'0'.$months:$months) . '-01';
            
            if($months >= 10){
                $goal_mission_year = $years + 1;
            }

            // echo $goal_mission_year;exit;
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();

            // get sale chanel
            $ChanelList = ProductMilkService::getSaleChanelList('Y');

            $cnt = 0;
            foreach ($ChanelList as $c_key => $c_value) {
             
                if($cnt > 0){
                    
                    $objPHPExcel->createSheet($cnt);
                    $objPHPExcel->setActiveSheetIndex($cnt);
                    
                }

                // get sale chanel id by name
                $sale_chanel = GoalMissionService::getSaleChanel($c_value['chanel_name']);


                $objPHPExcel->getActiveSheet()->setTitle($c_value['chanel_name']);

                $objPHPExcel->getActiveSheet()->setCellValue('A1', 'ตัวแทนจำหน่าย');
                $objPHPExcel->getActiveSheet()->setCellValue('B1', 'ชื่อลูกค้า');
                $objPHPExcel->getActiveSheet()->setCellValue('C1', 'ประเภทของนม');
                $objPHPExcel->getActiveSheet()->setCellValue('D1', 'ชื่อผลิตภัณฑ์');
                $objPHPExcel->getActiveSheet()->setCellValue('E1', 'ชนิดผลิตภัณฑ์');
                $objPHPExcel->getActiveSheet()->setCellValue('F1', 'เป้าหมายทั้งปี');
                $objPHPExcel->getActiveSheet()->setCellValue('I1', 'เป้าหมายเดือน');
                $objPHPExcel->getActiveSheet()->setCellValue('L1', 'ผลดำเนินงาน');

                $objPHPExcel->getActiveSheet()->setCellValue('F2', 'ลิตร');
                $objPHPExcel->getActiveSheet()->setCellValue('G2', 'แถม');
                $objPHPExcel->getActiveSheet()->setCellValue('H2', 'บาท');

                $objPHPExcel->getActiveSheet()->setCellValue('I2', 'ลิตร');
                $objPHPExcel->getActiveSheet()->setCellValue('J2', 'แถม');
                $objPHPExcel->getActiveSheet()->setCellValue('K2', 'บาท');

                $objPHPExcel->getActiveSheet()->setCellValue('L2', 'หีบ : กล่อง');
                $objPHPExcel->getActiveSheet()->setCellValue('M2', 'ลิตร');
                $objPHPExcel->getActiveSheet()->setCellValue('N2', 'บาท');
                $objPHPExcel->getActiveSheet()->setCellValue('O2', 'หีบ : กล่อง (แถม)');
                $objPHPExcel->getActiveSheet()->setCellValue('P2', 'หีบ : กล่อง (ลิตร - แถม)');
                
                $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
                $objPHPExcel->getActiveSheet()->mergeCells('B1:B2');
                $objPHPExcel->getActiveSheet()->mergeCells('C1:C2');
                $objPHPExcel->getActiveSheet()->mergeCells('D1:D2');
                $objPHPExcel->getActiveSheet()->mergeCells('E1:E2');
                $objPHPExcel->getActiveSheet()->mergeCells('F1:H1');
                $objPHPExcel->getActiveSheet()->mergeCells('I1:K1');
                $objPHPExcel->getActiveSheet()->mergeCells('L1:P1');

                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);

                $item_cnt = 3;
                // Gen item

                
                // load product milk
                $ProductMilk = ProductMilkService::getList('Y', '', [], $factory_id);
                // print_r($ProductMilk);exit;
                foreach ($ProductMilk as $key => $value) {
                    
                    $product_milk_id = $value['id'];
                    $product_milk_name = $value['name'];
                    
                    $SubProductMilk = SubProductMilkService::getListByProductMilk($product_milk_id, 'Y');
                    // print_r($SubProductMilk);exit;
                    foreach ($SubProductMilk as $key1 => $value1) {
                        $subproduct_milk_id = $value1['id'];
                        $subproduct_milk_name = $value1['product_character'] . ' ' . $value1['name'];

                        $ProductMilkDetail = ProductMilkDetailService::getListByParent($subproduct_milk_id, 'Y');
                        foreach ($ProductMilkDetail as $key2 => $value2) {

                            if($value2['amount'] == 0){
                                $value2['amount'] = number_format($value2['amount'], 2);
                            }

                            $product_milk_detail_name = $value2['name'] . ' ' .$value2['number_of_package'] . ' ' . $value2['unit'] . ' ' . $value2['amount'] . ' ' . $value2['amount_unit'] . ' ' . $value2['taste'];

                            // find goal values from name
                            $goal_name = $product_milk_name . ' - ' .  $subproduct_milk_name . ' - ' . $product_milk_detail_name;

                            $_MasterGoal = MasterGoalService::getGoalIDByName($goal_name, $menu_type, $factory_id);

                            $GoalMissionData = GoalMissionService::getGoalMissionByGoalName($menu_type, $_MasterGoal['id'], $factory_id, $goal_mission_year, $sale_chanel->id);
                            // get goal mission in month
                            // echo $GoalMissionData['id'];exit;
                            $GoalMissionMonthData = GoalMissionService::getAvgMonth($GoalMissionData['id'], $avgDate);
                            

                            $objPHPExcel->getActiveSheet()->setCellValue('A' .$item_cnt, $c_value['chanel_name']);
                            $objPHPExcel->getActiveSheet()->setCellValue('B' .$item_cnt, '');
                            $objPHPExcel->getActiveSheet()->setCellValue('C' .$item_cnt, $product_milk_name);
                            $objPHPExcel->getActiveSheet()->setCellValue('D' .$item_cnt, $subproduct_milk_name);
                            $objPHPExcel->getActiveSheet()->setCellValue('E' .$item_cnt, $product_milk_detail_name);
                            $objPHPExcel->getActiveSheet()->setCellValue('F' .$item_cnt, $GoalMissionData['total_amount']);
                            $objPHPExcel->getActiveSheet()->setCellValue('G' .$item_cnt, $GoalMissionData['addon_amount']);
                            $objPHPExcel->getActiveSheet()->setCellValue('H' .$item_cnt, $GoalMissionData['price_value']);
                            $objPHPExcel->getActiveSheet()->setCellValue('I' .$item_cnt, $GoalMissionMonthData['amount']);
                            $objPHPExcel->getActiveSheet()->setCellValue('J' .$item_cnt, $GoalMissionMonthData['addon_amount']);
                            $objPHPExcel->getActiveSheet()->setCellValue('K' .$item_cnt, $GoalMissionMonthData['price_value']);
                            $objPHPExcel->getActiveSheet()->setCellValue('L' .$item_cnt, '');
                            $objPHPExcel->getActiveSheet()->setCellValue('M' .$item_cnt, '');
                            $objPHPExcel->getActiveSheet()->setCellValue('N' .$item_cnt, '');
                            $objPHPExcel->getActiveSheet()->setCellValue('O' .$item_cnt, '');
                            $objPHPExcel->getActiveSheet()->setCellValue('P' .$item_cnt, '');

                            $item_cnt++;
                        }
                    }

                }

               

                $objPHPExcel->getActiveSheet()->getStyle('A1:P2' . $highestRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()
                ->getStyle("A1:P" . $objPHPExcel->getActiveSheet()->getHighestRow())
                ->applyFromArray($this->getDefaultStyle());

                $cnt++;

            }
            // End gen template

            // exit;
            $filename = 'TEMPLATE__production-sale-info_' . date('YmdHis') . '.xlsx';
            $filepath = '../../files/files/download/' . $filename;

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            $objWriter->setPreCalculateFormulas();
            $objWriter->save($filepath);

            $this->data_result['DATA'] = 'files/files/download/' . $filename;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function getDefaultStyle(){
        return 
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    )
                    // ,
                    // 'font' => array(
                    //     'name' => 'AngsanaUPC'
                    // )
                );
    }

    public function readProductMilkFile($request, $response, $args){

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('../../files/productmilk.xlsx');
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            $field_array = ['factory', 
                            'product_milk', 
                            'product_character', 
                            'sub_product_milk', 
                            'product_milk_detail', 
                            'no_of_package', 
                            'unit_package', 
                            'amount', 
                            'unit_amount', 
                            'taste', 
                            'actives'
                        ];
            $cnt_row = 1;

            $ItemList = [];
            foreach ($sheetData as $key => $value) {
                
                if($cnt_row > 1){
                    
                    $cnt_col = 0;
                    $cnt_field = 0;
                    $Item = [];
                    
                    foreach ($value as $k => $v) {
                        // if($cnt_col >= 1 && $cnt_col <= 7){
                            
                            $Item[ $field_array[$cnt_field] ] = trim($v);
                            $cnt_field++;
                            
                        // }
                        $cnt_col++;
                    }
                    
                    array_push($ItemList, $Item);
                    
                }

                $cnt_row++;

            }

            // echo "<pre>";
            // print_r($ItemList);
            // exit;
            $factory = '';
            $product_milk_id = '';
            $product_milk_name = '';
            $subproduct_milk_id = '';
            $product_character = '';
            $subproduct_milk_name = '';
            $product_milk_detail_id = '';
            $product_milk_detail_name = '';

            foreach ($ItemList as $key => $value) {
                // insert product milk
                switch ($value['factory']) {
                    case 'สำนักงาน อ.ส.ค. ภาคกลาง':$factory = 1;
                        break;
                    case 'สำนักงาน อ.ส.ค. ภาคใต้':$factory = 2;
                        break;
                    case 'สำนักงาน อ.ส.ค. ภาคตะวันออกเฉียงเหนือ':$factory = 3;
                        break;
                    case 'สำนักงาน อ.ส.ค. ภาคเหนือตอนล่าง':$factory = 4;
                        break;
                    case 'สำนักงาน อ.ส.ค. ภาคเหนือตอนบน':$factory = 5;
                        break;
                    default:
                        # code...
                        break;
                }

                $cur_product_milk_name = $value['product_milk'];
                if($product_milk_name != $cur_product_milk_name){

                    $product_milk_name = $cur_product_milk_name;
                    // Insert new product milk
                    $ProductMilk = [
                                    'factory_id' => $factory,
                                    'name' => $cur_product_milk_name,
                                    'actives' => 'Y'
                                ];

                    $product_milk_id = ProductMilkService::updateData($ProductMilk);
                }

                $cur_product_character_name = $value['product_character'] . $value['sub_product_milk'];
                if($product_character != $cur_product_character_name){

                    $product_character = $cur_product_character_name;


                    // Insert new sub product milk
                    $SubProductMilk = [
                                    'product_milk_id' => $product_milk_id,
                                    'product_character' => $value['product_character'],
                                    'name' => $value['sub_product_milk'],
                                    'actives' => 'Y'
                                ];

                    $subproduct_milk_id = SubProductMilkService::updateData($SubProductMilk);
                }

                $cur_product_milk_detail_name = $value['product_milk_detail'] . 
                                                $value['taste'] . 
                                                $value['unit_package'] . 
                                                $value['no_of_package'] . 
                                                $value['unit_amount'] . 
                                                $value['amount'];
                if($product_milk_detail_name != $cur_product_milk_detail_name){

                    $product_milk_detail_name = $cur_product_milk_detail_name;
                    // Insert new product milk
                    $ProductMilkDetail = [
                                    'sub_product_milk_id' => $subproduct_milk_id,
                                    'name' => $value['product_milk_detail'],
                                    'taste' => $value['taste'],
                                    'unit' => $value['unit_package'],
                                    'number_of_package' => $value['no_of_package'],
                                    'amount_unit' => $value['unit_amount'],
                                    'amount' => $value['amount'],
                                    'actives' => ($value['actives']==1?'Y':'N')
                                ];

                    $product_milk_detail_id = ProductMilkDetailService::updateData($ProductMilkDetail);
                }
                
            }            
            exit;
            // return $ItemList;
        }

        public function readProductMilkFileToMaster($request, $response, $args){

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('../../files/productmilk.xlsx');
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            $field_array = ['factory', 
                            'product_milk', 
                            'product_character', 
                            'sub_product_milk', 
                            'product_milk_detail', 
                            'no_of_package', 
                            'unit_package', 
                            'amount', 
                            'unit_amount', 
                            'taste', 
                            'actives'
                        ];
            $cnt_row = 1;

            $ItemList = [];
            foreach ($sheetData as $key => $value) {
                
                if($cnt_row > 1){
                    
                    $cnt_col = 0;
                    $cnt_field = 0;
                    $Item = [];
                    
                    foreach ($value as $k => $v) {
                        // if($cnt_col >= 1 && $cnt_col <= 7){
                            
                            $Item[ $field_array[$cnt_field] ] = trim($v);
                            $cnt_field++;
                            
                        // }
                        $cnt_col++;
                    }
                    
                    array_push($ItemList, $Item);
                    
                }

                $cnt_row++;

            }

            // echo "<pre>";
            // print_r($ItemList);
            // exit;
            $factory = '';
            $product_milk_id = '';
            $product_milk_name = '';
            $subproduct_milk_id = '';
            $product_character = '';
            $subproduct_milk_name = '';
            $product_milk_detail_id = '';
            $product_milk_detail_name = '';

            $menuTypeList = ['ข้อมูลการผลิต', 'ข้อมูลการขาย', 'การสูญเสียในกระบวนการ'/*, 'การสูญเสียหลังกระบวนการ', 'การสูญเสียรอจำหน่าย'*/];
            // $FactoryList = [2,3,4,5];

            foreach ($ItemList as $key => $value) {

                foreach ($menuTypeList as $key1 => $value1) {
                    
                    // foreach ($FactoryList as $key2 => $value2) {
                    if($value['factory'] == 'สำนักงาน อ.ส.ค. ภาคกลาง'){
                        $value2 = 1;
                    }

                    else if($value['factory'] == 'สำนักงาน อ.ส.ค. ภาคใต้'){
                        $value2 = 2;
                    }
                    else if($value['factory'] == 'สำนักงาน อ.ส.ค. ภาคตะวันออกเฉียงเหนือ'){
                                            $value2 = 3;
                                        }
                    else if($value['factory'] == 'สำนักงาน อ.ส.ค. ภาคเหนือตอนล่าง'){
                                            $value2 = 4;
                                        }
                    else if($value['factory'] == 'สำนักงาน อ.ส.ค. ภาคเหนือตอนบน'){
                                            $value2 = 5;
                                        }

                        $data = [];
                        $data['goal_type'] = 'II';
                        $data['menu_type'] = $value1;//'ข้อมูลการผลิต';
                        $data['factory_id'] = $value2;
                        $data['goal_name'] = $value['product_milk'] . ' - ' . $value['product_character'] . ' ' . $value['sub_product_milk']. ' - '  .
                                                        $value['product_milk_detail'] . ' ' .$value['no_of_package'] . ' ' . $value['unit_package'] . ' ' . (empty($value['amount'])?'0':$value['amount']). ' ' . $value['unit_amount']. ' ' . $value['taste'];
                        if($value['actives']==1){
                            $data['actives'] = 'Y';
                        }else{
                            $data['actives'] = 'N';
                        }
                        
                        MasterGoalService::updateData($data);
                    // }
                }
            }            
            exit;
            // return $ItemList;
        }

}
