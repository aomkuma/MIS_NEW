<?php

namespace App\Controller;

use App\Service\InseminationService;
use App\Service\CooperativeService;

class InseminationController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public static function getLastDayOfMonth($time) {
        return $date = date("t", strtotime($time . '-' . '01'));

        // return date("t", $last_day_timestamp);
    }

    public static function getMonthName($month) {
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

            $Data = InseminationService::loadDataApprove($user_session['UserID']);

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
            $regions = $params['obj']['region'];

            if ($condition['DisplayType'] == 'monthly') {
                $Result = $this->getMonthDataList($condition, $regions);
            } else if ($condition['DisplayType'] == 'quarter') {
                $Result = $this->getQuarterDataList($condition, $regions);
            } else if ($condition['DisplayType'] == 'annually') {
                $Result = $this->getAnnuallyDataList($condition, $regions);
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
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28'; // .InseminationController::getLastDayOfMonth($ymTo);
        //exit;
        $fromTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        $curMonth = $condition['MonthFrom'];
        $DataList = [];
        $DataSummary = [];
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }
        for ($i = 0; $i < $diffMonth; $i++) {

            // Prepare condition
            $curYear = $condition['YearTo'];
            $beforeYear = $condition['YearTo'] - 1;

            // Loop User Regions
            foreach ($regions as $key => $value) {

                $region_id = $value['RegionID'];
                $monthName = InseminationController::getMonthName($curMonth);

                $data = [];
                $data['RegionName'] = $value['RegionName'];
                $data['Month'] = $monthName;

                // get cooperative type

                $CurrentCowService = InseminationService::getMainList($curYear, $curMonth, $region_id);
                $data['CurrentCowService'] = floatval($CurrentCowService['sum_cow_amount']);
                $data['CurrentIncomeService'] = floatval($CurrentCowService['sum_income_amount']);
                // $data['CurrentIncomeService'] = floatval($CurrentCowService['sum_amount']) + floatval($CurrentCowService['sum_amount']) + floatval($CurrentCowService['sum_amount']);

                $BeforeCowService = InseminationService::getMainList($beforeYear, $curMonth, $region_id);
                $data['BeforeCowService'] = floatval($BeforeCowService['sum_cow_amount']);
                $data['BeforeIncomeService'] = floatval($BeforeCowService['sum_income_amount']);
                // $data['BeforeIncomeService'] = floatval($BeforeIncomeService['sum_amount']) + floatval($BeforeIncomeService['sum_amount']); + floatval($BeforeIncomeService['sum_amount']);;

                $diffCowData = $data['CurrentCowService'] - $data['BeforeCowService'];
                $data['DiffCowService'] = $diffCowData;
                if ($data['BeforeCowService'] != 0) {
                    $data['DiffCowServicePercentage'] = (($data['CurrentCowService'] - $data['BeforeCowService']) / $data['BeforeCowService']) * 100;
                } else if (empty($data['BeforeCowService']) && !empty($data['CurrentCowService'])) {
                    $data['DiffCowServicePercentage'] = 100;
                }

                $diffServiceData = $data['CurrentIncomeService'] - $data['BeforeIncomeService'];
                $data['DiffIncomeService'] = $diffServiceData;
                if ($data['BeforeIncomeService'] != 0) {
                    $data['DiffIncomeServicePercentage'] = (($data['CurrentIncomeService'] - $data['BeforeIncomeService']) / $data['BeforeIncomeService']) * 100;
                } else if (empty($data['BeforeIncomeService']) && !empty($data['CurrentIncomeService'])) {
                    $data['DiffIncomeServicePercentage'] = 100;
                }

                $data['CreateDate'] = $CurrentCowService['update_date'];
                $data['ApproveDate'] = $CurrentCowService['office_approve_date'];
                if (!empty($CurrentCowService['office_approve_id'])) {
                    if (empty($CurrentCowService['office_approve_comment'])) {
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

                $DataSummary['SummaryCurrentCowService'] = $DataSummary['SummaryCurrentCowService'] + $data['CurrentCowService'];
                $DataSummary['SummaryBeforeCowService'] = $DataSummary['SummaryBeforeCowService'] + $data['BeforeCowService'];
                $DataSummary['SummaryCowServicePercentage'] = 0;
                $DataSummary['SummaryCurrentIncomeService'] = $DataSummary['SummaryCurrentIncomeService'] + $data['CurrentIncomeService'];
                $DataSummary['SummaryBeforeIncomeService'] = $DataSummary['SummaryBeforeIncomeService'] + $data['BeforeIncomeService'];
                $DataSummary['SummaryIncomeServicePercentage'] = 0;
            }

            $curMonth++;
        }
        if ($DataSummary['SummaryBeforeCowService'] != 0) {
            $DataSummary['SummaryCowServicePercentage'] = (($DataSummary['SummaryCurrentCowService'] - $DataSummary['SummaryBeforeCowService']) / $DataSummary['SummaryBeforeCowService']) * 100;
        } else {
            $DataSummary['SummaryCowServicePercentage'] = 100;
        }
        if ($DataSummary['SummaryBeforeIncomeService'] != 0) {
            $DataSummary['SummaryIncomeServicePercentage'] = (($DataSummary['SummaryCurrentIncomeService'] - $DataSummary['SummaryBeforeIncomeService']) / $DataSummary['SummaryBeforeIncomeService']) * 100;
        } else {
            $DataSummary['SummaryIncomeServicePercentage'] = 100;
        }

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getQuarterDataList($condition, $regions) {

        // get loop to query
        $diffYear = ($condition['YearTo'] - $condition['YearFrom']) + 1;
        $cnt = 0;
        $loop = 0;
        $j = $condition['QuarterFrom'];

        for ($i = 0; $i < $diffYear; $i++) {
            if ($cnt == $diffYear) {
                for ($k = 0; $k < $condition['QuarterTo']; $k++) {
                    $loop++;
                }
            } else {

                if ($i > 0) {
                    $j = 0;
                }

                if ($diffYear == 1) {
                    $length = $condition['QuarterTo'];
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
            $curYear = intval($condition['YearFrom']) - 1;
            $beforeYear = $curYear - 1;
        } else {
            $curYear = intval($condition['YearFrom']);
            $beforeYear = $curYear - 1;
        }

        $DataList = [];
        $DataSummary = [];

        for ($i = 0; $i < $loop; $i++) {

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

            // Loop User Regions
            foreach ($regions as $key => $value) {
                $region_id = $value['RegionID'];

                $SumCurrentCowService = 0;
                $SumCurrentIncomeService = 0;
                $SumBeforeCowService = 0;
                $SumBeforeIncomeService = 0;
                $UpdateDate = '';
                $ApproveDate = '';
                $ApproveComment = '';
                // loop get quarter sum data
                for ($j = 0; $j < count($monthList); $j++) {
                    $curMonth = $monthList[$j];
                    $CurrentCowService = InseminationService::getMainList($curYear, $curMonth, $region_id);
                    $SumCurrentCowService += floatval($CurrentCowService['sum_cow_amount']);
                    $SumCurrentIncomeService += floatval($CurrentCowService['sum_income_amount']);

                    $BeforeCowService = InseminationService::getMainList($beforeYear, $curMonth, $region_id);
                    $SumBeforeCowService += floatval($BeforeCowService['sum_cow_amount']);
                    $SumBeforeIncomeService += floatval($BeforeCowService['sum_income_amount']);

                    if (!empty($CurrentCowService['update_date'])) {
                        $UpdateDate = $CurrentCowService['update_date'];
                    }
                    if (!empty($CurrentCowService['office_approve_id'])) {
                        $ApproveDate = $CurrentCowService['office_approve_date'];
                    }
                    if (!empty($CurrentCowService['office_approve_comment'])) {
                        $ApproveComment = $CurrentCowService['office_approve_comment'];
                    }
                }

                $data['RegionName'] = $value['RegionName'];
                $data['Quarter'] = ($curQuarter) . ' (' . ($curQuarter == 1 ? $curYear + 543 + 1 : $curYear + 543) . ')';

                $data['CurrentCowService'] = $SumCurrentCowService;
                $data['CurrentIncomeService'] = $SumCurrentIncomeService;


                $data['BeforeCowService'] = $SumBeforeCowService;
                $data['BeforeIncomeService'] = $SumBeforeIncomeService;

                $diffCowData = $data['CurrentCowService'] - $data['BeforeCowService'];
                $data['DiffCowService'] = $diffCowData;

                if ($data['BeforeCowService'] != 0) {
                    $data['DiffCowServicePercentage'] = (($data['CurrentCowService'] - $data['BeforeCowService']) / $data['BeforeCowService']) * 100;
                } else if (empty($data['BeforeCowService']) && !empty($data['CurrentCowService'])) {
                    $data['DiffCowServicePercentage'] = 100;
                }

                $diffServiceData = $data['CurrentIncomeService'] - $data['BeforeIncomeService'];
                $data['DiffIncomeService'] = $diffServiceData;
                if ($data['BeforeIncomeService'] != 0) {
                    $data['DiffIncomeServicePercentage'] = (($data['CurrentIncomeService'] - $data['BeforeIncomeService']) / $data['BeforeIncomeService']) * 100;
                } else if (empty($data['BeforeIncomeService']) && !empty($data['CurrentIncomeService'])) {
                    $data['DiffIncomeServicePercentage'] = 100;
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
                $data['Description'] = ['quarter' => $curQuarter
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                array_push($DataList, $data);

                $DataSummary['SummaryCurrentCowService'] = $DataSummary['SummaryCurrentCowService'] + $data['CurrentCowService'];
                $DataSummary['SummaryBeforeCowService'] = $DataSummary['SummaryBeforeCowService'] + $data['BeforeCowService'];
                $DataSummary['SummaryCowServicePercentage'] = 0;
                $DataSummary['SummaryCurrentIncomeService'] = $DataSummary['SummaryCurrentIncomeService'] + $data['CurrentIncomeService'];
                $DataSummary['SummaryBeforeIncomeService'] = $DataSummary['SummaryBeforeIncomeService'] + $data['BeforeIncomeService'];
                $DataSummary['SummaryIncomeServicePercentage'] = 0;
            }

            $curQuarter++;
            if ($curQuarter > 4) {
                $curQuarter = 1;
            }
        }

        if ($DataSummary['SummaryBeforeCowService'] != 0) {
            $DataSummary['SummaryCowServicePercentage'] = (($DataSummary['SummaryCurrentCowService'] - $DataSummary['SummaryBeforeCowService']) / $DataSummary['SummaryBeforeCowService']) * 100;
        } else {
            $DataSummary['SummaryCowServicePercentage'] = 100;
        }
        if ($DataSummary['SummaryBeforeIncomeService'] != 0) {
            $DataSummary['SummaryIncomeServicePercentage'] = (($DataSummary['SummaryCurrentIncomeService'] - $DataSummary['SummaryBeforeIncomeService']) / $DataSummary['SummaryBeforeIncomeService']) * 100;
        } else {
            $DataSummary['SummaryIncomeServicePercentage'] = 100;
        }
        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getAnnuallyDataList($condition, $regions) {

        $loop = intval($condition['YearTo']) - intval($condition['YearFrom']) + 1;
        $curYear = $condition['YearFrom'];

        $beforeYear = $calcYear - 1;
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        $DataList = [];
        $DataSummary = [];

        for ($i = 0; $i < $loop; $i++) {

            // Loop User Regions
            foreach ($regions as $key => $value) {
                $region_id = $value['RegionID'];

                $curYear = $condition['YearFrom'];
                $calcYear = intval($curYear) - 1;

                $SumCurrentCowService = 0;
                $SumCurrentIncomeService = 0;
                $SumBeforeCowService = 0;
                $SumBeforeIncomeService = 0;
                $UpdateDate = '';
                $ApproveDate = '';
                $ApproveComment = '';
                // loop get quarter sum data
                for ($j = 0; $j < 12; $j++) {

                    $curMonth = $monthList[$j];

                    if ($curMonth == 1) {
                        $calcYear++;
                        $beforeYear = $calcYear - 1;
                    }

                    $curMonth = $monthList[$j];
                    $CurrentCowService = InseminationService::getMainList($calcYear, $curMonth, $region_id);
                    $SumCurrentCowService += floatval($CurrentCowService['sum_cow_amount']);
                    $SumCurrentIncomeService += floatval($CurrentCowService['sum_income_amount']);

                    $BeforeCowService = InseminationService::getMainList($beforeYear, $curMonth, $region_id);
                    $SumBeforeCowService += floatval($BeforeCowService['sum_cow_amount']);
                    $SumBeforeIncomeService += floatval($BeforeCowService['sum_income_amount']);

                    if (!empty($CurrentCowService['update_date'])) {
                        $UpdateDate = $CurrentCowService['update_date'];
                    }
                    if (!empty($CurrentCowService['office_approve_id'])) {
                        $ApproveDate = $CurrentCowService['office_approve_date'];
                    }
                    if (!empty($CurrentCowService['office_approve_comment'])) {
                        $ApproveComment = $CurrentCowService['office_approve_comment'];
                    }
                }

                $data['RegionName'] = $value['RegionName'];
                $data['Year'] = $curYear + 543;

                $data['CurrentCowService'] = $SumCurrentCowService;
                $data['CurrentIncomeService'] = $SumCurrentIncomeService;


                $data['BeforeCowService'] = $SumBeforeCowService;
                $data['BeforeIncomeService'] = $SumBeforeIncomeService;

                $diffCowData = $data['CurrentCowService'] - $data['BeforeCowService'];
                $data['DiffCowService'] = $diffCowData;

                if ($data['BeforeCowService'] != 0) {
                    $data['DiffCowServicePercentage'] = (($data['CurrentCowService'] - $data['BeforeCowService']) / $data['BeforeCowService']) * 100;
                } else if (empty($data['BeforeCowService']) && !empty($data['CurrentCowService'])) {
                    $data['DiffCowServicePercentage'] = 100;
                }

                $diffServiceData = $data['CurrentIncomeService'] - $data['BeforeIncomeService'];
                $data['DiffIncomeService'] = $diffServiceData;
                if ($data['BeforeIncomeService'] != 0) {
                    $data['DiffIncomeServicePercentage'] = (($data['CurrentIncomeService'] - $data['BeforeIncomeService']) / $data['BeforeIncomeService']) * 100;
                } else if (empty($data['BeforeIncomeService']) && !empty($data['CurrentIncomeService'])) {
                    $data['DiffIncomeServicePercentage'] = 100;
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
                $data['Description'] = ['quarter' => $curQuarter
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                array_push($DataList, $data);

                $DataSummary['SummaryCurrentCowService'] = $DataSummary['SummaryCurrentCowService'] + $data['CurrentCowService'];
                $DataSummary['SummaryBeforeCowService'] = $DataSummary['SummaryBeforeCowService'] + $data['BeforeCowService'];
                $DataSummary['SummaryCowServicePercentage'] = 0;
                $DataSummary['SummaryCurrentIncomeService'] = $DataSummary['SummaryCurrentIncomeService'] + $data['CurrentIncomeService'];
                $DataSummary['SummaryBeforeIncomeService'] = $DataSummary['SummaryBeforeIncomeService'] + $data['BeforeIncomeService'];
                $DataSummary['SummaryIncomeServicePercentage'] = 0;
            }


            $curYear++;
        }

        if ($DataSummary['SummaryBeforeCowService'] != 0) {
            $DataSummary['SummaryCowServicePercentage'] = (($DataSummary['SummaryCurrentCowService'] - $DataSummary['SummaryBeforeCowService']) / $DataSummary['SummaryBeforeCowService']) * 100;
        } else {
            $DataSummary['SummaryCowServicePercentage'] = 100;
        }
        if ($DataSummary['SummaryBeforeIncomeService'] != 0) {
            $DataSummary['SummaryIncomeServicePercentage'] = (($DataSummary['SummaryCurrentIncomeService'] - $DataSummary['SummaryBeforeIncomeService']) / $DataSummary['SummaryBeforeIncomeService']) * 100;
        } else {
            $DataSummary['SummaryIncomeServicePercentage'] = 100;
        }
        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getData($request, $response, $args) {
        try {
            $params = $request->getParsedBody();

            $id = $params['obj']['id'];

            $cooperative_id = $params['obj']['cooperative_id'];
            $months = $params['obj']['months'];
            $years = $params['obj']['years'];

            if (!empty($id)) {
                $_Data = InseminationService::getDataByID($id);
            } else {
                $_Data = InseminationService::getData($cooperative_id, $months, $years);
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
            $Cooperative = CooperativeService::getData($_Data['cooperative_id']);
            $_Data['region_id'] = $Cooperative['region_id'];
            // print_r($_Data);
            // exit();

            $id = InseminationService::updateData($_Data);

            foreach ($_Detail as $key => $value) {
                $value['insemination_id'] = $id;
                InseminationService::updateDetailData($value);
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
            $result = InseminationService::removeDetailData($id);

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
            $result = InseminationService::updateDataApprove($id, $data);

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

}
