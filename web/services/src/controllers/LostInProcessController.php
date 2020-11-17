<?php

namespace App\Controller;

use App\Service\LostInProcessService;
use App\Service\MasterGoalService;
use App\Service\UploadLogService;
use App\Service\GoalMissionService;
use App\Service\ProductionInfoService;

use PHPExcel;

class LostInProcessController extends Controller {

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

    public function checkRegion($regionID) {
        switch ($regionID) {
            case '1':
                return 'สำนักงาน อ.ส.ค. ภาคกลาง';
                break;
            case '2':
                return 'สำนักงาน อ.ส.ค. ภาคใต้';
                break;
            case '3':
                return 'สำนักงาน อ.ส.ค. ภาคตะวันออกเฉียงเหนือ';
                break;
            case '4':
                return 'สำนักงาน อ.ส.ค. ภาคเหนือตอนล่าง';
                break;
            case '5':
                return 'สำนักงาน อ.ส.ค. ภาคเหนือตอนบน';
                break;
            default: break;
        }
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

    public function getFactoryID($regionID) {
        switch ($regionID) {
            case 1 : $monthTxt = 1;
                break;
            case 2 : $monthTxt = 1;
                break;
            case 3 : $monthTxt = 1;
                break;
            case 4 : $monthTxt = 2;
                break;
            case 5 : $monthTxt = 3;
                break;
            case 6 : $monthTxt = 4;
                break;
            case 7 : $monthTxt = 5;
                break;
        }
        return $monthTxt;
    }

    private function findQuarterAndFiscalYear($months, $years){

        $quarter = null;
        $fiscal_year = null;

        switch ($months) {
            case '1': $quarter = 2; $fiscal_year = $years; break;
            case '2': $quarter = 2; $fiscal_year = $years; break;
            case '3': $quarter = 2; $fiscal_year = $years; break;
            case '4': $quarter = 3; $fiscal_year = $years; break;
            case '5': $quarter = 3; $fiscal_year = $years; break;
            case '6': $quarter = 3; $fiscal_year = $years; break;
            case '7': $quarter = 4; $fiscal_year = $years; break;
            case '8': $quarter = 4; $fiscal_year = $years; break;
            case '9': $quarter = 4; $fiscal_year = $years; break;
            case '10': $quarter = 1; $fiscal_year = $years + 1; break;
            case '11': $quarter = 1; $fiscal_year = $years + 1; break;
            case '12': $quarter = 1; $fiscal_year = $years + 1; break;
        }

        return ['quarters' => $quarter, 'fiscal_years' => $fiscal_year];
    }

    public function loadDataApprove($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $user_session = $params['user_session'];

            $Data = LostInProcessService::loadDataApprove($user_session['UserID']);

            $this->data_result['DATA']['DataList'] = $Data;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMainListValue($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $condition = $params['obj']['condition'];

            $DataList = LostInProcessService::getMainListValue($condition);

            $this->data_result['DATA']['DataList'] = $DataList;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function updateValue($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $Data = $params['obj']['Data'];

            $duplicate = LostInProcessService::checkDuplicateValue($Data);

            if($duplicate){

                $this->data_result['STATUS'] = 'ERROR';
                $this->data_result['MSG'] = 'มีข้อมูลนี้อยู่แล้วในระบบ';

                return $this->returnResponse(200, $this->data_result, $response, false);
            }

            $find_result = $this->findQuarterAndFiscalYear($Data['months'], $Data['years']);

            $Data['quarters'] = $find_result['quarters'];
            $Data['fiscal_years'] = $find_result['fiscal_years'];

            $result = LostInProcessService::updateValue($Data);
            
            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function deleteValue($request, $response, $args) {
        try {
            
            $params = $request->getParsedBody();
            $id = $params['obj']['id'];

            $result = LostInProcessService::removeValue($id);

            $this->data_result['DATA']['result'] = $result;

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

            // print_r($RegionList);
            // exit;
            if ($condition['DisplayType'] == 'monthly') {
                $Result = $this->getMonthDataList($condition);
            } else if ($condition['DisplayType'] == 'quarter') {
                $Result = $this->getQuarterDataList($condition);
            } else if ($condition['DisplayType'] == 'annually') {
                $Result = $this->getAnnuallyDataList($condition);
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

    public function getMonthDataList($condition) {

        $factory_id = $condition['Factory'];
        $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

//print_r($x);
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

        $TotalGroupData = [];
        $Sum_CurrentAmount = 0;
        $Sum_CurrentBaht = 0;
        $Sum_BeforeAmount = 0;
        $Sum_BeforeBaht = 0;

        // get sub type list
        $SubTypeList = MasterGoalService::getSubTypeList('การสูญเสียในกระบวนการ');
        // print_r($SubTypeList);exit;
        foreach($SubTypeList as $_k => $_v){
            $data = [];
            // $data['RegionName'] = $value['RegionName'];
            $data['bg_color'] = '#7adcde';
            $data['LostInProcessName'] = $_v['sub_goal_type'];
            array_push($DataList, $data);

            // get master goal
            $MasterGoalList = MasterGoalService::getList('Y', 'การสูญเสียในกระบวนการ', [], $_v['sub_goal_type'],$factory_id);

            

            // for ($i = 0; $i < $diffMonth; $i++) {

                // Prepare condition
                $curYear = $condition['YearTo'];
                $beforeYear = $condition['YearTo'] - 1;

                $TotalData = [];
                $TotalData['SummaryAmount'] = 0;
                $TotalData['SummaryBaht'] = 0;
                $TotalData['SummaryBeforeAmount'] = 0;
                $TotalData['SummaryBeforeBaht'] = 0;
                foreach ($MasterGoalList as $k => $v) {

                    $master_type_id = $v['id'];

                    $monthName = LostInProcessController::getMonthName($curMonth);

                    $data = [];
                    // $data['RegionName'] = $value['RegionName'];
                    $data['LostInProcessName'] = $v['goal_name'];
                    $data['master_type_id'] = $master_type_id;
                    $data['Month'] = $monthName;

                    // get cooperative type
                    $Current = LostInProcessService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                    // print_r($Current);exit;
                    $data['CurrentAmount'] = floatval($Current['sum_amount']);
                    $data['CurrentBaht'] = floatval($Current['sum_baht']);

                    $Before = LostInProcessService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
                    $data['BeforeAmount'] = floatval($Before['sum_amount']);
                    $data['BeforeBaht'] = floatval($Before['sum_baht']);

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;
                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
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

                    // $TotalData = [];

                    $TotalData['SummaryAmount'] = $TotalData['SummaryAmount'] + $data['CurrentAmount'];
                    $TotalData['SummaryBaht'] = $TotalData['SummaryBaht'] + $data['CurrentBaht'];
                    $TotalData['SummaryBeforeAmount'] = $TotalData['SummaryBeforeAmount'] + $data['BeforeAmount'];
                    $TotalData['SummaryBeforeBaht'] = $TotalData['SummaryBeforeBaht'] + $data['BeforeBaht'];

                    $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                    $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];
                    $DataSummary['SummaryBeforeAmount'] = $DataSummary['SummaryBeforeAmount'] + $data['BeforeAmount'];
                    $DataSummary['SummaryBeforeBaht'] = $DataSummary['SummaryBeforeBaht'] + $data['BeforeBaht'];

                    $Sum_CurrentAmount += $data['CurrentAmount'];
                    $Sum_CurrentBaht += $data['CurrentBaht'];
                    $Sum_BeforeAmount += $data['BeforeAmount'];
                    $Sum_BeforeBaht += $data['BeforeBaht'];

                }

                $data = [];
                $data['bg_color'] = '#92deb8';
                $data['Month'] = 'รวม ' . $_v['sub_goal_type'];
                $data['CurrentAmount'] = $TotalData['SummaryAmount'];
                $data['CurrentBaht'] = $TotalData['SummaryBaht'];
                $data['BeforeAmount'] = $TotalData['SummaryBeforeAmount'];
                $data['BeforeBaht'] = $TotalData['SummaryBeforeBaht'];

                $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                
                $data['DiffAmount'] = $DiffAmount;
                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                    $data['DiffAmountPercentage'] = 100;
                }

                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    $data['DiffBahtPercentage'] = 100;
                }
                $data['DiffBaht'] = $DiffBaht;

                array_push($DataList, $data);

                array_push($TotalGroupData, $data);
                // $curMonth++;
            // }
        }

        // $data = [];
        // // $data['RegionName'] = $value['RegionName'];
        // $data['bg_color'] = '#ccc';
        // $data['Month'] = 'รวม';
        // $data['CurrentAmount'] = $Sum_CurrentAmount;
        // $data['CurrentBaht'] = $Sum_CurrentBaht;
        // $data['BeforeAmount'] = $Sum_BeforeAmount;
        // $data['BeforeBaht'] = $Sum_BeforeBaht;

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
        // $data['DiffBaht'] = $DiffBaht;

        // array_push($DataList, $data);

        $data = [];
        
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'รวมน้ำนมเข้ากระบวนการผลิต';
        $data['CurrentAmount'] = $TotalGroupData[0]['CurrentAmount'] - $TotalGroupData[1]['CurrentAmount'];
        $data['CurrentBaht'] = $TotalGroupData[0]['CurrentBaht'] - $TotalGroupData[1]['CurrentBaht'];
        $data['BeforeAmount'] = $TotalGroupData[0]['BeforeAmount'] - $TotalGroupData[1]['BeforeAmount'];
        $data['BeforeBaht'] = $TotalGroupData[0]['BeforeBaht'] - $TotalGroupData[1]['BeforeBaht'];

        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
        $data['DiffAmount'] = $DiffAmount;
        if ($data['BeforeAmount'] != 0) {
            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
            $data['DiffAmountPercentage'] = 100;
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        // get from production info
        $data = [];
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'รวมผลิตภัณฑ์ที่ผลิตได้';
        $production_info = ProductionInfoService::getMonthList($curYear, $curMonth, $factory_id);
        // print_r($production_info);exit;
        $current_production_info_amount = floatval($production_info['sum_amount']);
        $data['CurrentAmount'] = floatval($production_info['sum_amount']);
        $data['CurrentBaht'] = floatval($production_info['sum_baht']);

        $production_info = ProductionInfoService::getMonthList($beforeYear, $curMonth, $factory_id);

        $data['BeforeAmount'] = floatval($production_info['sum_amount']);
        $data['BeforeBaht'] = floatval($production_info['sum_baht']);

        array_push($DataList, $data);

        $total_loss = $TotalGroupData[0]['CurrentAmount'] - $TotalGroupData[1]['CurrentAmount'];

        $data = [];
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'รวมปริมาณสูญเสียในกระบวนการผลิต';

        $total_loss = $TotalGroupData[0]['CurrentAmount'] - $TotalGroupData[1]['CurrentAmount'];

        $data['CurrentAmount'] = ($total_loss - $current_production_info_amount);
        
        array_push($DataList, $data);

        $data = [];
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'เปอร์เซ็นต์สูญเสียร้อยละ';

        
        $data['CurrentAmount'] = (($total_loss - $current_production_info_amount) * 100) / $total_loss;
        
        array_push($DataList, $data);

        $DataSummary['SummaryAmount'] = $data['CurrentAmount'];
        $DataSummary['SummaryBaht'] = $data['CurrentBaht'];

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getMonthDataListAll($condition) {
        
        $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';
        $factory_id = null;

        if(!empty($condition['Factory'])){
            $factory_id = $condition['Factory'];
        }

//print_r($x);
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

        $TotalGroupData = [];
        $Sum_CurrentAmount = 0;
        $Sum_CurrentBaht = 0;
        $Sum_BeforeAmount = 0;
        $Sum_BeforeBaht = 0;

        // get sub type list
        $SubTypeList = MasterGoalService::getSubTypeList('การสูญเสียในกระบวนการ');
        // print_r($SubTypeList);exit;
        foreach($SubTypeList as $_k => $_v){
            $data = [];
            // $data['RegionName'] = $value['RegionName'];
            $data['bg_color'] = '#7adcde';
            $data['LostInProcessName'] = $_v['sub_goal_type'];
            array_push($DataList, $data);

            // get master goal
            $MasterGoalList = MasterGoalService::getList('Y', 'การสูญเสียในกระบวนการ', [], $_v['sub_goal_type'],$factory_id);

            

            // for ($i = 0; $i < $diffMonth; $i++) {

                // Prepare condition
                $curYear = $condition['YearTo'];
                $beforeYear = $condition['YearTo'] - 1;

                $TotalData = [];
                $TotalData['SummaryAmount'] = 0;
                $TotalData['SummaryBaht'] = 0;
                $TotalData['SummaryBeforeAmount'] = 0;
                $TotalData['SummaryBeforeBaht'] = 0;
                foreach ($MasterGoalList as $k => $v) {

                    $master_type_id = $v['id'];

                    $monthName = LostInProcessController::getMonthName($curMonth);

                    $data = [];
                    // $data['RegionName'] = $value['RegionName'];
                    $data['LostInProcessName'] = $v['goal_name'];
                    $data['master_type_id'] = $master_type_id;
                    $data['Month'] = $monthName;

                    // get cooperative type
                    $Current = LostInProcessService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                    // print_r($Current);exit;
                    $data['CurrentAmount'] = floatval($Current['sum_amount']);
                    $data['CurrentBaht'] = floatval($Current['sum_baht']);

                    $Before = LostInProcessService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
                    $data['BeforeAmount'] = floatval($Before['sum_amount']);
                    $data['BeforeBaht'] = floatval($Before['sum_baht']);

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;
                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
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

                    // $TotalData = [];

                    $TotalData['SummaryAmount'] = $TotalData['SummaryAmount'] + $data['CurrentAmount'];
                    $TotalData['SummaryBaht'] = $TotalData['SummaryBaht'] + $data['CurrentBaht'];
                    $TotalData['SummaryBeforeAmount'] = $TotalData['SummaryBeforeAmount'] + $data['BeforeAmount'];
                    $TotalData['SummaryBeforeBaht'] = $TotalData['SummaryBeforeBaht'] + $data['BeforeBaht'];

                    $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                    $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];
                    $DataSummary['SummaryBeforeAmount'] = $DataSummary['SummaryBeforeAmount'] + $data['BeforeAmount'];
                    $DataSummary['SummaryBeforeBaht'] = $DataSummary['SummaryBeforeBaht'] + $data['BeforeBaht'];

                    $Sum_CurrentAmount += $data['CurrentAmount'];
                    $Sum_CurrentBaht += $data['CurrentBaht'];
                    $Sum_BeforeAmount += $data['BeforeAmount'];
                    $Sum_BeforeBaht += $data['BeforeBaht'];

                }

                $data = [];
                $data['bg_color'] = '#92deb8';
                $data['Month'] = 'รวม ' . $_v['sub_goal_type'];
                $data['CurrentAmount'] = $TotalData['SummaryAmount'];
                $data['CurrentBaht'] = $TotalData['SummaryBaht'];
                $data['BeforeAmount'] = $TotalData['SummaryBeforeAmount'];
                $data['BeforeBaht'] = $TotalData['SummaryBeforeBaht'];

                $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                
                $data['DiffAmount'] = $DiffAmount;
                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                    $data['DiffAmountPercentage'] = 100;
                }

                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    $data['DiffBahtPercentage'] = 100;
                }
                $data['DiffBaht'] = $DiffBaht;

                array_push($DataList, $data);

                array_push($TotalGroupData, $data);
                // $curMonth++;
            // }
        }

        // $data = [];
        // // $data['RegionName'] = $value['RegionName'];
        // $data['bg_color'] = '#ccc';
        // $data['Month'] = 'รวม';
        // $data['CurrentAmount'] = $Sum_CurrentAmount;
        // $data['CurrentBaht'] = $Sum_CurrentBaht;
        // $data['BeforeAmount'] = $Sum_BeforeAmount;
        // $data['BeforeBaht'] = $Sum_BeforeBaht;

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
        // $data['DiffBaht'] = $DiffBaht;

        // array_push($DataList, $data);

        $data = [];
        
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'รวมน้ำนมเข้ากระบวนการผลิต';
        $data['CurrentAmount'] = $TotalGroupData[0]['CurrentAmount'] - $TotalGroupData[1]['CurrentAmount'];
        $data['CurrentBaht'] = $TotalGroupData[0]['CurrentBaht'] - $TotalGroupData[1]['CurrentBaht'];
        $data['BeforeAmount'] = $TotalGroupData[0]['BeforeAmount'] - $TotalGroupData[1]['BeforeAmount'];
        $data['BeforeBaht'] = $TotalGroupData[0]['BeforeBaht'] - $TotalGroupData[1]['BeforeBaht'];

        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
        $data['DiffAmount'] = $DiffAmount;
        if ($data['BeforeAmount'] != 0) {
            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
            $data['DiffAmountPercentage'] = 100;
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        // get from production info
        $data = [];
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'รวมผลิตภัณฑ์ที่ผลิตได้';
        $production_info = ProductionInfoService::getMonthList($curYear, $curMonth, $factory_id);
        // print_r($production_info);exit;
        $current_production_info_amount = floatval($production_info['sum_amount']);
        $data['CurrentAmount'] = floatval($production_info['sum_amount']);
        $data['CurrentBaht'] = floatval($production_info['sum_baht']);

        $production_info = ProductionInfoService::getMonthList($beforeYear, $curMonth, $factory_id);

        $data['BeforeAmount'] = floatval($production_info['sum_amount']);
        $data['BeforeBaht'] = floatval($production_info['sum_baht']);

        array_push($DataList, $data);

        $total_loss = $TotalGroupData[0]['CurrentAmount'] - $TotalGroupData[1]['CurrentAmount'];

        $data = [];
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'รวมปริมาณสูญเสียในกระบวนการผลิต';

        $total_loss = $TotalGroupData[0]['CurrentAmount'] - $TotalGroupData[1]['CurrentAmount'];
        
        $data['CurrentAmount'] = ($total_loss - $current_production_info_amount);
        
        array_push($DataList, $data);

        $data = [];
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'เปอร์เซ็นต์สูญเสียร้อยละ';

        
        $data['CurrentAmount'] = (($total_loss - $current_production_info_amount) * 100) / $total_loss;
        
        array_push($DataList, $data);

        $DataSummary['SummaryAmount'] = $data['CurrentAmount'];
        $DataSummary['SummaryBaht'] = $data['CurrentBaht'];

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getMonthDataListreport($condition) {
        $factory_id = $condition['Factory'];
        $ymFrom = $condition['YearFrom'] - 1 . '-' . str_pad(10, 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearFrom'] - 1 . '-' . str_pad(10, 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }

        $DataList = [];
        $DataSummary = [];

        // get master goal
        $MasterGoalList = MasterGoalService::getList('Y', 'การสูญเสียในกระบวนการ');

        $Sum_CurrentAmount = 0;




        // Prepare condition


        foreach ($MasterGoalList as $k => $v) {
            $curYear = $condition['YearTo'] - 1;
            $curMonth = 10;
            for ($i = 0; $i < $diffMonth; $i++) {
                $master_type_id = $v['id'];

                $monthName = LostInProcessController::getMonthName($curMonth);

                $data = [];
                // $data['RegionName'] = $value['RegionName'];
                $data['LostInProcessName'] = $v['goal_name'];
                $data['Month'] = $monthName;

                // get cooperative type
                $Current = LostInProcessService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                // print_r($Current);exit;
                $data['CurrentAmount'] += floatval($Current['sum_amount']);


                $curMonth++;
                if ($curMonth > 12) {
                    $curMonth = 1;
                    $curYear++;
                }
            }
            array_push($DataList, $data);
        }





        return ['DataList' => $DataList];
    }

    public function getQuarterDataList($condition, $regions) {

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
        $TotalGroupData = [];
        $Sum_CurrentAmount = 0;
        $Sum_CurrentBaht = 0;
        $Sum_BeforeAmount = 0;
        $Sum_BeforeBaht = 0;

        // get sub type list
        $SubTypeList = MasterGoalService::getSubTypeList('การสูญเสียในกระบวนการ');
        // print_r($SubTypeList);exit;
        foreach($SubTypeList as $_k => $_v){

            $data = [];
            // $data['RegionName'] = $value['RegionName'];
            $data['bg_color'] = '#7adcde';
            $data['LostInProcessName'] = $_v['sub_goal_type'];
            array_push($DataList, $data);

            // get master goal
            $MasterGoalList = MasterGoalService::getList('Y', 'การสูญเสียในกระบวนการ', [], $_v['sub_goal_type'],$factory_id);
            // get master goal
            // $MasterGoalList = MasterGoalService::getList('Y', 'การสูญเสียในกระบวนการ');
            $Sum_CurrentAmount = 0;
            $Sum_CurrentBaht = 0;
            $Sum_BeforeAmount = 0;
            $Sum_BeforeBaht = 0;

        // for ($i = 0; $i < $loop; $i++) {

            if ($i > 0 && $curQuarter == 2) {
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

            $TotalData = [];
            $TotalData['SummaryAmount'] = 0;
            $TotalData['SummaryBaht'] = 0;
            $TotalData['SummaryBeforeAmount'] = 0;
            $TotalData['SummaryBeforeBaht'] = 0;
            
            foreach ($MasterGoalList as $k => $v) {

                $master_type_id = $v['id'];

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

                    $Current = LostInProcessService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                    $SumCurrentAmount += floatval($Current['sum_amount']);
                    $SumCurrentBaht += floatval($Current['sum_baht']);

                    $Before = LostInProcessService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
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
                $data['RegionName'] = $value['RegionName'];
                $data['LostInProcessName'] = $v['goal_name'];
                $data['Quarter'] = $curQuarter . ' (' . (($curQuarter == 1 ? $curYear + 543 + 1 : $curYear + 543)) . ')';

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
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    $data['DiffBahtPercentage'] = 100;
                }

                $data['CreateDate'] = $UpdateDate;
                $data['ApproveDate'] = $ApproveDate;
                if (!empty($ApproveDate)) {
                    if (empty($ApproveComment)) {
                        $data['Status'] = 'อนุมัติ';
                    } else {
                        $data['Status'] = 'ไม่อนุมัติ';
                    }
                }
                $data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'quarter' => $curQuarter
                    , 'region_id' => $region_id
                ];

                array_push($DataList, $data);

                $TotalData['SummaryAmount'] = $TotalData['SummaryAmount'] + $data['CurrentAmount'];
                $TotalData['SummaryBaht'] = $TotalData['SummaryBaht'] + $data['CurrentBaht'];
                $TotalData['SummaryBeforeAmount'] = $TotalData['SummaryBeforeAmount'] + $data['BeforeAmount'];
                $TotalData['SummaryBeforeBaht'] = $TotalData['SummaryBeforeBaht'] + $data['BeforeBaht'];

                $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];
                $DataSummary['SummaryBeforeAmount'] = $DataSummary['SummaryBeforeAmount'] + $data['BeforeAmount'];
                $DataSummary['SummaryBeforeBaht'] = $DataSummary['SummaryBeforeBaht'] + $data['BeforeBaht'];


                $Sum_CurrentAmount += $data['CurrentAmount'];
                $Sum_CurrentBaht += $data['CurrentBaht'];
                $Sum_BeforeAmount += $data['BeforeAmount'];
                $Sum_BeforeBaht += $data['BeforeBaht'];
            }

            $data = [];
            $data['bg_color'] = '#92deb8';
            $data['Month'] = 'รวม ' . $_v['sub_goal_type'];
            $data['CurrentAmount'] = $TotalData['SummaryAmount'];
            $data['CurrentBaht'] = $TotalData['SummaryBaht'];
            $data['BeforeAmount'] = $TotalData['SummaryBeforeAmount'];
            $data['BeforeBaht'] = $TotalData['SummaryBeforeBaht'];

            $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
            
            $data['DiffAmount'] = $DiffAmount;
            if ($data['BeforeAmount'] != 0) {
                $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
            } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                $data['DiffAmountPercentage'] = 100;
            }

            $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
            $data['DiffBaht'] = $DiffBaht;

            if ($data['BeforeBaht'] != 0) {
                $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
            } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                $data['DiffBahtPercentage'] = 100;
            }
            $data['DiffBaht'] = $DiffBaht;

            array_push($DataList, $data);

            array_push($TotalGroupData, $data);
        }

        // $data = [];
        // // $data['RegionName'] = $value['RegionName'];
        // $data['bg_color'] = '#ccc';
        // $data['Month'] = 'รวม';
        // $data['CurrentAmount'] = $Sum_CurrentAmount;
        // $data['CurrentBaht'] = $Sum_CurrentBaht;

        // $data['BeforeAmount'] = $Sum_BeforeAmount;
        // $data['BeforeBaht'] = $Sum_BeforeBaht;

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
        // $data['DiffBaht'] = $DiffBaht;

        // array_push($DataList, $data);

        $data = [];
        
        $data['bg_color'] = '#999';
        $data['Quarter'] = 'รวม';
        $data['CurrentAmount'] = $TotalGroupData[0]['CurrentAmount'] - $TotalGroupData[1]['CurrentAmount'];
        $data['CurrentBaht'] = $TotalGroupData[0]['CurrentBaht'] - $TotalGroupData[1]['CurrentBaht'];
        $data['BeforeAmount'] = $TotalGroupData[0]['BeforeAmount'] - $TotalGroupData[1]['BeforeAmount'];
        $data['BeforeBaht'] = $TotalGroupData[0]['BeforeBaht'] - $TotalGroupData[1]['BeforeBaht'];

        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
        $data['DiffAmount'] = $DiffAmount;
        if ($data['BeforeAmount'] != 0) {
            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
            $data['DiffAmountPercentage'] = 100;
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        $DataSummary['SummaryAmount'] = $data['CurrentAmount'];
        $DataSummary['SummaryBaht'] = $data['CurrentBaht'];

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getAnnuallyDataList($condition, $regions) {

        $factory_id = $condition['Factory'];

        $loop = 1; //intval($condition['YearTo']) - intval($condition['YearFrom']) + 1;
        $curYear = $condition['YearTo'];

        $beforeYear = $calcYear - 1;
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        $DataList = [];
        $DataSummary = [];
        $curYear = $condition['YearTo'];

        $TotalGroupData = [];
        $Sum_CurrentAmount = 0;
        $Sum_CurrentBaht = 0;
        $Sum_BeforeAmount = 0;
        $Sum_BeforeBaht = 0;

        // get sub type list
        $SubTypeList = MasterGoalService::getSubTypeList('การสูญเสียในกระบวนการ');
        // print_r($SubTypeList);exit;
        foreach($SubTypeList as $_k => $_v){
            $data = [];
            // $data['RegionName'] = $value['RegionName'];
            $data['bg_color'] = '#7adcde';
            $data['LostInProcessName'] = $_v['sub_goal_type'];
            array_push($DataList, $data);

            // get master goal
            $MasterGoalList = MasterGoalService::getList('Y', 'การสูญเสียในกระบวนการ', [], $_v['sub_goal_type'],$factory_id);
            // get master goal
            // $MasterGoalList = MasterGoalService::getList('Y', 'การสูญเสียในกระบวนการ');
            $Sum_CurrentAmount = 0;
            $Sum_CurrentBaht = 0;
            $Sum_BeforeAmount = 0;
            $Sum_BeforeBaht = 0;


        // for ($i = 0; $i < $loop; $i++) {
            $TotalData = [];
            $TotalData['SummaryAmount'] = 0;
            $TotalData['SummaryBaht'] = 0;
            $TotalData['SummaryBeforeAmount'] = 0;
            $TotalData['SummaryBeforeBaht'] = 0;
            
            foreach ($MasterGoalList as $k => $v) {

                $master_type_id = $v['id'];
                $region_id = $value['RegionID'];


                $calcYear = intval($curYear) - 1;

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

                    $Current = LostInProcessService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                    $SumCurrentAmount += floatval($Current['sum_amount']);
                    $SumCurrentBaht += floatval($Current['sum_baht']);

                    $Before = LostInProcessService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
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
                $data['RegionName'] = $value['RegionName'];
                $data['LostInProcessName'] = $v['goal_name'];
                $data['Year'] = $curYear + 543;

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
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    $data['DiffBahtPercentage'] = 100;
                }

                $data['CreateDate'] = $UpdateDate;
                $data['ApproveDate'] = $ApproveDate;
                if (!empty($ApproveDate)) {
                    if (empty($ApproveComment)) {
                        $data['Status'] = 'อนุมัติ';
                    } else {
                        $data['Status'] = 'ไม่อนุมัติ';
                    }
                }
                $data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                array_push($DataList, $data);

                $TotalData['SummaryAmount'] = $TotalData['SummaryAmount'] + $data['CurrentAmount'];
                $TotalData['SummaryBaht'] = $TotalData['SummaryBaht'] + $data['CurrentBaht'];
                $TotalData['SummaryBeforeAmount'] = $TotalData['SummaryBeforeAmount'] + $data['BeforeAmount'];
                $TotalData['SummaryBeforeBaht'] = $TotalData['SummaryBeforeBaht'] + $data['BeforeBaht'];

                $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];
                $DataSummary['SummaryBeforeAmount'] = $DataSummary['SummaryBeforeAmount'] + $data['BeforeAmount'];
                $DataSummary['SummaryBeforeBaht'] = $DataSummary['SummaryBeforeBaht'] + $data['BeforeBaht'];

                $Sum_CurrentAmount += $data['CurrentAmount'];
                $Sum_CurrentBaht += $data['CurrentBaht'];
                $Sum_BeforeAmount += $data['BeforeAmount'];
                $Sum_BeforeBaht += $data['BeforeBaht'];
            }

            $data = [];
            $data['bg_color'] = '#92deb8';
            $data['Year'] = 'รวม ' . $_v['sub_goal_type'];
            $data['CurrentAmount'] = $TotalData['SummaryAmount'];
            $data['CurrentBaht'] = $TotalData['SummaryBaht'];
            $data['BeforeAmount'] = $TotalData['SummaryBeforeAmount'];
            $data['BeforeBaht'] = $TotalData['SummaryBeforeBaht'];

            $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
            
            $data['DiffAmount'] = $DiffAmount;
            if ($data['BeforeAmount'] != 0) {
                $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
            } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                $data['DiffAmountPercentage'] = 100;
            }

            $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
            $data['DiffBaht'] = $DiffBaht;

            if ($data['BeforeBaht'] != 0) {
                $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
            } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                $data['DiffBahtPercentage'] = 100;
            }
            $data['DiffBaht'] = $DiffBaht;

            array_push($DataList, $data);

            array_push($TotalGroupData, $data);
        }

        // $data = [];
        // // $data['RegionName'] = $value['RegionName'];
        // $data['bg_color'] = '#ccc';
        // $data['Month'] = 'รวม';
        // $data['CurrentAmount'] = $Sum_CurrentAmount;
        // $data['CurrentBaht'] = $Sum_CurrentBaht;

        // $data['BeforeAmount'] = $Sum_BeforeAmount;
        // $data['BeforeBaht'] = $Sum_BeforeBaht;

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
        // $data['DiffBaht'] = $DiffBaht;

        // array_push($DataList, $data);

        $data = [];
        
        $data['bg_color'] = '#999';
        $data['Month'] = 'รวม';
        $data['CurrentAmount'] = $TotalGroupData[0]['CurrentAmount'] - $TotalGroupData[1]['CurrentAmount'];
        $data['CurrentBaht'] = $TotalGroupData[0]['CurrentBaht'] - $TotalGroupData[1]['CurrentBaht'];
        $data['BeforeAmount'] = $TotalGroupData[0]['BeforeAmount'] - $TotalGroupData[1]['BeforeAmount'];
        $data['BeforeBaht'] = $TotalGroupData[0]['BeforeBaht'] - $TotalGroupData[1]['BeforeBaht'];

        $DataSummary['SummaryAmount'] = $data['CurrentAmount'];
        $DataSummary['SummaryBaht'] = $data['CurrentBaht'];
        
        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
        $data['DiffAmount'] = $DiffAmount;
        if ($data['BeforeAmount'] != 0) {
            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
            $data['DiffAmountPercentage'] = 100;
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);



        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getData($request, $response, $args) {
        try {
            $params = $request->getParsedBody();

            $id = $params['obj']['id'];

            $factory_id = $params['obj']['factory_id'];
            $months = $params['obj']['months'];
            $years = $params['obj']['years'];

            if (!empty($id)) {
                $_Data = LostInProcessService::getDataByID($id);
            } else {
                $_Data = LostInProcessService::getData($factory_id, $months, $years);
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

            $id = LostInProcessService::updateData($_Data);

            foreach ($_Detail as $key => $value) {
                $value['lost_in_process_id'] = $id;
                LostInProcessService::updateDetailData($value);
            }

            //           
            $this->data_result['DATA']['id'] = $id;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function removeDetailData($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $id = $params['obj']['id'];
            $result = LostInProcessService::removeDetailData($id);

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
            $result = LostInProcessService::updateDataApprove($id, $data);

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

    public function getExcelTemplate($request, $response, $args) {
        try {

            error_reporting(E_ERROR);
            error_reporting(E_ALL);
            ini_set('display_errors','On');

            $params = $request->getParsedBody();
            // $condition = $params['obj']['condition'];

            $factory_id = $params['obj']['factory_id'];
            $years = $params['obj']['years'];
            $months = $params['obj']['months'];
            $menu_type = 'การสูญเสียในกระบวนการ';
            $con_year = $years;
            $goal_mission_year = $years;
            $avgDate = $con_year . '-'. ($months<10?'0'.$months:$months) . '-01';
            
            if($months >= 10){
                $goal_mission_year = $years + 1;
            }
            
            $avgDate = $con_year . '-'. ($months<10?'0'.$months:$months) . '-01';
            
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();

            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'รายงานการสูญเสียในกระบวนการผลิตประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $this->checkRegion($factory_id) . ' เดือน ' . $this->getMonthName($months) . ' ปีงบประมาณ ' . ($years + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:F2');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'รายการน้ำนม');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'รายการการสูญเสียในกระบวนการผลิต');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เป้ารายปี');
            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'เป้ารายเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ปริมาณน้ำนม (ลิตร)');
            $objPHPExcel->getActiveSheet()->setCellValue('F4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
            
            $item_cnt = 5;
            // Gen item

            // load goal mission
            // GoalMissionService::

            // load Master goal
            $MasterGoalList = MasterGoalService::getListOrderByName('Y', 'การสูญเสียในกระบวนการ', [], '', $factory_id);
            // print_r($ProductMilk);exit;
            
            foreach ($MasterGoalList as $key2 => $value2) {

                $goal_name = $value2['goal_name'];
                
                $_MasterGoal = MasterGoalService::getGoalIDByName($goal_name, $menu_type, $factory_id);

                $GoalMissionData = GoalMissionService::getGoalMissionByGoalName($menu_type, $_MasterGoal['id'], $factory_id, $goal_mission_year);
                // get goal mission in month
                // echo $GoalMissionData['id'];exit;
                $GoalMissionMonthData = GoalMissionService::getAvgMonth($GoalMissionData['id'], $avgDate);

                $objPHPExcel->getActiveSheet()->setCellValue('A' .$item_cnt, $value2['sub_goal_type']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' .$item_cnt, $value2['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' .$item_cnt, $GoalMissionData['total_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' .$item_cnt, $GoalMissionMonthData['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' .$item_cnt, '');
                $objPHPExcel->getActiveSheet()->setCellValue('F' .$item_cnt, '');
                
                $item_cnt++;
            }
             

           

            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle('A1:A' . ($item_cnt - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle('C2:F' . ($item_cnt - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()
            ->getStyle("A4:F" . $objPHPExcel->getActiveSheet()->getHighestRow())
            ->applyFromArray($this->getDefaultStyle());

            // exit;
            $filename = 'TEMPLATE__lost-in-process_' . date('YmdHis') . '.xlsx';
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

            $id = LostInProcessService::updateData($_Data);

            // clear item
            LostInProcessService::removeDetailDataByParent($id);            

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
            foreach ($_Detail as $key => $value) {

                $data = [];
                
                $data['lost_in_process_id'] = $value['lost_in_process_id'];
                $data['lost_in_process_type'] = $this->getMasterGoal($value['goal_name'], $_Data['factory_id']);
                
                $data['amount'] = empty($value['result_amount'])?0:$value['result_amount'];
                $data['price_value'] = empty($value['result_thb'])?0:$value['result_thb'];

                $data['amount'] = str_replace(',', '', $data['amount']);
                $data['price_value'] = str_replace(',', '', $data['price_value']);
                
                $data['id'] = '';

                LostInProcessService::updateDetailData($data);
            }

            // add log
            $_UploadFile['menu_type'] = 'lost-in-process';
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

    private function getMasterGoal($goal_name, $factory_id){
        return MasterGoalService::getIDByName($goal_name, $factory_id);
    }


    private function readExcelFile($file, $lost_in_process_id){

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        $field_array = ['sub_goal_type', 'goal_name', 'goal_year_amount',  'goal_month_amount'/*, 'result_package_amount'*/, 'result_amount', 'result_thb'];
        $cnt_row = 1;

        $ItemList = [];
        foreach ($sheetData as $key => $value) {
            
            if($cnt_row > 1){
                
                $cnt_col = 0;
                $cnt_field = 0;
                $Item = [];
                $Item[ 'lost_in_process_id' ] = $lost_in_process_id;

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
        
        return $ItemList;
    }

}
