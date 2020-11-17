<?php

namespace App\Controller;

use App\Service\CooperativeMilkService;
use App\Service\CooperativeService;

class CooperativeMilkController extends Controller {

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
            
            $Data = CooperativeMilkService::loadDataApprove($user_session['UserID']);
            
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
            $region_selected = $condition['Region'];
            $regions = $params['obj']['region'];
            $RegionList = [];
            if (!empty($region_selected)) {
                for ($i = 0; $i < count($regions); $i++) {
                    if ($regions[$i]['RegionID'] == $region_selected) {
                        array_push($RegionList, $regions[$i]);
                        break;
                    }
                }
            } else {
                $RegionList = $regions;
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

    public function getMonthDataList($condition, $regions) {

        $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28'; // .CooperativeMilkController::getLastDayOfMonth($ym);
        //exit;
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
        for ($i = 0; $i < $diffMonth; $i++) {

            // Prepare condition
            $curYear = $condition['YearTo'];
            $beforeYear = $condition['YearTo'] - 1;

            // Loop User Regions
            foreach ($regions as $key => $value) {

                $region_id = $value['RegionID'];

                // get cooperative by region
                $CooperativeList = CooperativeService::getListByRegion($region_id);

                foreach ($CooperativeList as $k => $v) {
                    // $monthName = CooperativeMilkController::getMonthName($curMonth);
                    $cooperative_id = $v['id'];
                    $data = [];
                    $data['RegionName'] = $value['RegionName'];
                    $data['CooperativeName'] = $v['cooperative_name'];

                    // get cooperative type

                    $Current = CooperativeMilkService::getMainList($curYear, $curMonth, $region_id, $cooperative_id);
                    $data['TotalPerson'] = floatval($Current['sum_total_person']);
                    $data['TotalPersonSent'] = floatval($Current['sum_total_person_sent']);
                    $data['TotalCow'] = floatval($Current['sum_total_cow']);
                    $data['TotalCowSent'] = floatval($Current['sum_total_person_sent']);
                    $data['TotalCowBeeb'] = floatval($Current['sum_total_cow_beeb']);
                    $data['TotalMilkAmount'] = floatval($Current['sum_milk_amount']);
                    $data['TotalValues'] = floatval($Current['sum_total_values']);
                    $data['AverageValues'] = floatval($Current['sum_average_values']);

                    $data['Description'] = ['months' => $curMonth
                        , 'years' => $curYear
                        , 'region_id' => $region_id
                    ];

                    array_push($DataList, $data);

                    $DataSummary['SummaryCooperativeMilkAmount'] = $DataSummary['SummaryCooperativeMilkAmount'] + $data['CurrentAmount'];
                    $DataSummary['SummaryBeforCooperativeMilkAmount'] = $DataSummary['SummaryBeforCooperativeMilkAmount'] + $data['BeforeAmount'];

                    $DataSummary['SummaryCooperativeMilkIncome'] = $DataSummary['SummaryCooperativeMilkIncome'] + $data['CurrentBaht'];
                    $DataSummary['SummaryBeforeCooperativeMilkIncome'] = $DataSummary['SummaryBeforeCooperativeMilkIncome'] + $data['BeforeBaht'];

                    $DataSummary['SummaryCooperativeMilkAmountPercentage'] = $DataSummary['SummaryCooperativeMilkAmountPercentage'] + $DataSummary['SummaryCooperativeMilkAmount'] + $DataSummary['SummaryBeforCooperativeMilkAmount'];
                    $DataSummary['SummaryCooperativeMilkIncomePercentage'] = $DataSummary['SummaryCooperativeMilkIncomePercentage'] + $DataSummary['SummaryCooperativeMilkIncome'] + $DataSummary['SummaryBeforeCooperativeMilkIncome'];
                }
            }

            $curMonth++;
        }

        $DataSummary['SummaryCooperativeMilkAmountPercentage'] = (($DataSummary['SummaryCooperativeMilkAmount'] - $DataSummary['SummaryBeforCooperativeMilkAmount']) /$DataSummary['SummaryBeforCooperativeMilkAmount']) * 100;
            $DataSummary['SummaryCooperativeMilkIncomePercentage'] = (($DataSummary['SummaryCooperativeMilkIncome'] - $DataSummary['SummaryBeforeCooperativeMilkIncome']) /$DataSummary['SummaryBeforeCooperativeMilkIncome']) * 100;

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getData($request, $response, $args) {

        try {
            $params = $request->getParsedBody();

            $id = $params['obj']['id'];

            $region_id = $params['obj']['region_id'];
            $months = $params['obj']['months'];
            $years = $params['obj']['years'];

            if (!empty($id)) {
                $_Data = CooperativeMilkService::getDataByID($id);
            } else {
                $_Data = CooperativeMilkService::getData($region_id, $months, $years);
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
            if($HeaderData['data']['DATA']['Header']['OrgType'] == 'DEPARTMENT'){
                $_Data['dep_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['dep_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];

            }else if($HeaderData['data']['DATA']['Header']['OrgType'] == 'DIVISION'){
                $_Data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['division_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];

            }else if($HeaderData['data']['DATA']['Header']['OrgType'] == 'OFFICE'){
                $_Data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['office_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
            }

            $_Detail = $params['obj']['Detail'];

            // get region from cooperative id
            // $Cooperative = CooperativeService::getData($_Data['cooperative_id']);
            // $_Data['region_id'] = $Cooperative['region_id'];
            // print_r($_Data);
            // exit();

            $id = CooperativeMilkService::updateData($_Data);

            foreach ($_Detail as $key => $value) {
                $value['cooperative_milk_id'] = $id;
                CooperativeMilkService::updateDetailData($value);
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
            $result = CooperativeMilkService::removeDetailData($id);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMonthData($condition, $region) {
//            $region = [0=>['RegionID'=>1,'RegionName'=>'อ.ส.ค. สำนักงานใหญ่ มวกเหล็ก'],1=>['RegionID'=>2,'RegionName'=>'อ.ส.ค. สำนักงานกรุงเทพฯ Office'],
//                   2=>['RegionID'=>3,'RegionName'=>'อ.ส.ค. สำนักงานภาคกลาง'],3=>['RegionID'=>4,'RegionName'=>'อ.ส.ค. ภาคใต้ (ประจวบคีรีขันธ์)'],
//                   4=>['RegionID'=>5,'RegionName'=>'อ.ส.ค. ภาคตะวันออกเฉียงเหนือ (ขอนแก่น)'],5=>['RegionID'=>6,'RegionName'=>'อ.ส.ค. ภาคเหนือตอนล่าง (สุโขทัย)'],
//                   6=>['RegionID'=>7,'RegionName'=>'อ.ส.ค. ภาคเหนือตอนบน (เชียงใหม่)']];
        $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28'; // .CooperativeMilkController::getLastDayOfMonth($ym);
        //exit;
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
        for ($i = 0; $i < $diffMonth; $i++) {

            // Prepare condition
            $curYear = $condition['YearTo'];
            $beforeYear = $condition['YearTo'] - 1;

            // Loop User Regions
            //    foreach ($region as $key => $value) {

            $region_id = $region['RegionID'];

            // get cooperative by region
            $CooperativeList = CooperativeService::getListByRegion($region_id);

            foreach ($CooperativeList as $k => $v) {
                // $monthName = CooperativeMilkController::getMonthName($curMonth);
                $cooperative_id = $v['id'];
                $data = [];
                $data['RegionName'] = $region['RegionName'];
                $data['CooperativeName'] = $v['cooperative_name'];

                // get cooperative type

                $Current = CooperativeMilkService::getMainList($curYear, $curMonth, $region_id, $cooperative_id);
                $data['TotalPerson'] = floatval($Current['sum_total_person']);
                $data['TotalPersonSent'] = floatval($Current['sum_total_person_sent']);
                $data['TotalCow'] = floatval($Current['sum_total_cow']);
                $data['TotalCowSent'] = floatval($Current['sum_total_person_sent']);
                $data['TotalCowBeeb'] = floatval($Current['sum_total_cow_beeb']);
                $data['TotalMilkAmount'] = floatval($Current['sum_milk_amount']);
                $data['TotalValues'] = floatval($Current['sum_total_values']);
                $data['AverageValues'] = floatval($Current['sum_average_values']);

                $data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                array_push($DataList, $data);

                $DataSummary['SummaryCooperativeMilkAmount'] = $DataSummary['SummaryCooperativeMilkAmount'] + $data['CurrentAmount'];
                $DataSummary['SummaryBeforCooperativeMilkAmount'] = $DataSummary['SummaryBeforCooperativeMilkAmount'] + $data['BeforeAmount'];

                $DataSummary['SummaryCooperativeMilkIncome'] = $DataSummary['SummaryCooperativeMilkIncome'] + $data['CurrentBaht'];
                $DataSummary['SummaryBeforeCooperativeMilkIncome'] = $DataSummary['SummaryBeforeCooperativeMilkIncome'] + $data['BeforeBaht'];

                $DataSummary['SummaryCooperativeMilkAmountPercentage'] = $DataSummary['SummaryCooperativeMilkAmountPercentage'] + $DataSummary['SummaryCooperativeMilkAmount'] + $DataSummary['SummaryBeforCooperativeMilkAmount'];
                $DataSummary['SummaryCooperativeMilkIncomePercentage'] = $DataSummary['SummaryCooperativeMilkIncomePercentage'] + $DataSummary['SummaryCooperativeMilkIncome'] + $DataSummary['SummaryBeforeCooperativeMilkIncome'];
            }
            //  }

            $curMonth++;
        }

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getQuarterData($condition, $region) {
        $curQuarter = intval($condition['QuarterFrom']);
        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        if (intval($curQuarter) == 1) {
            $curYear = intval($condition['YearFrom']) - 1;
        }
        if ($curQuarter == 1) {
            $monthst = 10;
            $monthen = 12;
        } else if ($curQuarter == 2) {
            $monthst = 1;
            $monthen = 3;
        } else if ($curQuarter == 3) {
            $monthst = 4;
            $monthen = 6;
        } else if ($curQuarter == 4) {
            $monthst = 7;
            $monthen = 9;
        }



        $DataList = [];
        $DataSummary = [];
        $region_id = $region['RegionID'];
        $CooperativeList = CooperativeService::getListByRegion($region_id);

        foreach ($CooperativeList as $k => $v) {
            // $monthName = CooperativeMilkController::getMonthName($curMonth);
            $cooperative_id = $v['id'];
            $data = [];
            $data['RegionName'] = $region['RegionName'];
            $data['CooperativeName'] = $v['cooperative_name'];

            // get cooperative type

            $Current = CooperativeMilkService::getMainListquar($curYear, $monthst, $monthen, $region_id, $cooperative_id);
            $data['TotalPerson'] = floatval($Current['sum_total_person']);
            $data['TotalPersonSent'] = floatval($Current['sum_total_person_sent']);
            $data['TotalCow'] = floatval($Current['sum_total_cow']);
            $data['TotalCowSent'] = floatval($Current['sum_total_person_sent']);
            $data['TotalCowBeeb'] = floatval($Current['sum_total_cow_beeb']);
            $data['TotalMilkAmount'] = floatval($Current['sum_milk_amount']);
            $data['TotalValues'] = floatval($Current['sum_total_values']);
            $data['AverageValues'] = floatval($Current['sum_average_values']);

            $data['Description'] = ['months' => $curMonth
                , 'years' => $curYear
                , 'region_id' => $region_id
            ];

            array_push($DataList, $data);

            $DataSummary['SummaryCooperativeMilkAmount'] = $DataSummary['SummaryCooperativeMilkAmount'] + $data['CurrentAmount'];
            $DataSummary['SummaryBeforCooperativeMilkAmount'] = $DataSummary['SummaryBeforCooperativeMilkAmount'] + $data['BeforeAmount'];

            $DataSummary['SummaryCooperativeMilkIncome'] = $DataSummary['SummaryCooperativeMilkIncome'] + $data['CurrentBaht'];
            $DataSummary['SummaryBeforeCooperativeMilkIncome'] = $DataSummary['SummaryBeforeCooperativeMilkIncome'] + $data['BeforeBaht'];

            $DataSummary['SummaryCooperativeMilkAmountPercentage'] = $DataSummary['SummaryCooperativeMilkAmountPercentage'] + $DataSummary['SummaryCooperativeMilkAmount'] + $DataSummary['SummaryBeforCooperativeMilkAmount'];
            $DataSummary['SummaryCooperativeMilkIncomePercentage'] = $DataSummary['SummaryCooperativeMilkIncomePercentage'] + $DataSummary['SummaryCooperativeMilkIncome'] + $DataSummary['SummaryBeforeCooperativeMilkIncome'];
        }




        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getAnnuallyDataListreport($condition, $regions) {


        $curYear = $condition['YearFrom'];

        //$beforeYear = $calcYear - 1;
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $DataList = [];
        $DataSummary = [];
        $region_id = $regions['RegionID'];
        $CooperativeList = CooperativeService::getListByRegion($region_id);

        // Loop User Regions
        foreach ($CooperativeList as $key => $value) {
            $SumCurrentAmount = 0;
            $SumCurrentBaht = 0;
            $SumBeforeAmount = 0;
            $SumBeforeBaht = 0;
            $data = [];
            $data['RegionName'] = $regions['RegionName'];
            $data['CooperativeName'] = $value['cooperative_name'];
            // loop get quarter sum data
            for ($j = 0; $j < 12; $j++) {
                $curMonth = $monthList[$j];
                $cooperative_id = $value['id'];


                // get cooperative type

                $Current = CooperativeMilkService::getMainList($curYear - $yearList[$j], $curMonth, $region_id, $cooperative_id);
               
                $data['TotalPerson'] += floatval($Current['sum_total_person']);
                $data['TotalPersonSent'] += floatval($Current['sum_total_person_sent']);
                $data['TotalCow'] += floatval($Current['sum_total_cow']);
                $data['TotalCowSent'] += floatval($Current['sum_total_person_sent']);
                $data['TotalCowBeeb'] += floatval($Current['sum_total_cow_beeb']);
                $data['TotalMilkAmount'] += floatval($Current['sum_milk_amount']);
                $data['TotalValues'] += floatval($Current['sum_total_values']);
                $data['AverageValues'] += floatval($Current['sum_average_values']);



                

                $DataSummary['SummaryCooperativeMilkAmount'] = $DataSummary['SummaryCooperativeMilkAmount'] + $data['CurrentAmount'];
                $DataSummary['SummaryBeforCooperativeMilkAmount'] = $DataSummary['SummaryBeforCooperativeMilkAmount'] + $data['BeforeAmount'];

                $DataSummary['SummaryCooperativeMilkIncome'] = $DataSummary['SummaryCooperativeMilkIncome'] + $data['CurrentBaht'];
                $DataSummary['SummaryBeforeCooperativeMilkIncome'] = $DataSummary['SummaryBeforeCooperativeMilkIncome'] + $data['BeforeBaht'];

                $DataSummary['SummaryCooperativeMilkAmountPercentage'] = $DataSummary['SummaryCooperativeMilkAmountPercentage'] + $DataSummary['SummaryCooperativeMilkAmount'] + $DataSummary['SummaryBeforCooperativeMilkAmount'];
                $DataSummary['SummaryCooperativeMilkIncomePercentage'] = $DataSummary['SummaryCooperativeMilkIncomePercentage'] + $DataSummary['SummaryCooperativeMilkIncome'] + $DataSummary['SummaryBeforeCooperativeMilkIncome'];
            }
            array_push($DataList, $data);
        }



        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function updateDataApprove($request, $response, $args){
            // $URL = '172.23.10.224';
            $URL = '127.0.0.1';
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $id = $params['obj']['id'];
                $ApproveStatus = $params['obj']['ApproveStatus'];
                $ApproveComment = $params['obj']['ApproveComment'];
                $OrgType = $params['obj']['OrgType'];
                $approval_id = $user_session['UserID'];
                $OrgID = $user_session['OrgID'];

                if($ApproveStatus == 'approve'){
                    // http post to dpo database to retrieve division's header
                    $HeaderData = $this->do_post_request('http://' . $URL . '/dportal/dpo/public/mis/get/org/header/', "POST", ['UserID' => $approval_id, 'OrgID' => $OrgID]);
                    $HeaderData = json_decode(trim($HeaderData), TRUE);
                    
                    $data = [];
                    $ApproveComment = '';

                    if($OrgType == 'dep'){
                        $data['dep_approve_date'] = date('Y-m-d H:i:s');
                        $data['dep_approve_comment'] = $ApproveComment;
                        $data['dep_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];

                        $data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                    }else if($OrgType == 'division'){
                        $data['division_approve_date'] = date('Y-m-d H:i:s');
                        $data['division_approve_comment'] = $ApproveComment;
                        $data['division_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];

                        $data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                    }else if($OrgType == 'office'){
                        $data['office_approve_date'] = date('Y-m-d H:i:s');
                        $data['office_approve_comment'] = $ApproveComment;
                        $data['office_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                        
                    }
                }else if($ApproveStatus == 'reject'){

                    if($OrgType == 'dep'){
                        $data['dep_approve_date'] = date('Y-m-d H:i:s');                  
                        $data['dep_approve_comment'] = $ApproveComment;
                        $data['dep_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                    }else if($OrgType == 'division'){
                        $data['dep_approve_date'] = NULL;                  
                        $data['dep_approve_comment'] = NULL;
                        
                        $data['division_approve_id'] = NULL;
                        $data['division_approve_date'] = date('Y-m-d H:i:s');
                        $data['division_approve_comment'] = $ApproveComment;
                        $data['division_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                    }else if($OrgType == 'office'){

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
                $result = CooperativeMilkService::updateDataApprove($id, $data);

                $this->data_result['DATA']['result'] = $result;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }   
        }

        private function do_post_request($url, $method, $data = [], $optional_headers = null)
        {
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
                    return array("STATUS"=>'ERROR',"MSG"=>"ERROR :: Problem with $url");
                //throw new Exception("Problem with $url, $php_errormsg");
              }
              $response = @stream_get_contents($fp);
              if ($response === false) {
                print_r($response);
                    return array("STATUS"=>'ERROR',"MSG"=>"ERROR :: Problem reading data from $url");
    //            throw new Exception("Problem reading data from $url");
              }

              return $response;
              
        }

}
