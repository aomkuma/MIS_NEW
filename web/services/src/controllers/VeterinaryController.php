<?php

namespace App\Controller;

use App\Service\VeterinaryService;
use App\Service\CooperativeService;
use App\Service\DairyFarmingService;

class VeterinaryController extends Controller {

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

            $Data = VeterinaryService::loadDataApprove($user_session['UserID']);

            $this->data_result['DATA']['DataList'] = $Data;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMainList($request, $response, $args) {
        try {
            error_reporting(E_ERROR);
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();
            $condition = $params['obj']['condition'];
            $regions = $params['obj']['region'];

            $RegionList = [];
            foreach ($regions as $key => $value) {
                if($value['RegionID'] != 2){
                    $RegionList[] = $value;
                }
            }

            $regions = $RegionList;

            if ($condition['DisplayType'] == 'monthly') {
                $Result = $this->getMonthDataList($condition, $regions);
            } else if ($condition['DisplayType'] == 'quarter') {
                $Result = $this->getQuarterDataList($condition, $regions);
            } else if ($condition['DisplayType'] == 'annually') {
                $Result = $this->getAnnuallyDataList($condition, $regions);
            }
            $DataList = $Result['DataList'];
            $Summary = $Result['Summary'];
          

            $this->data_result['DATA']['DataList'] = $DataList;
            $this->data_result['DATA']['Summary'] = $Summary;
//            print_r($this->data_result);
//            die();
            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMonthDataList($condition, $regions) {
        $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28'; // . VeterinaryController::getLastDayOfMonth($ymTo);
        //exit;
        $fromTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);

        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);

        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        // exit;
        // if($ymFrom != $ymTo){
        //     $diffMonth += 1;
        // }
        $curMonth = $condition['MonthFrom'];
        $DataList = [];
        $DataSummary = [];
        $DataSummary['SummaryCurrentCow'] = 0;
        $DataSummary['SummaryBeforeCow'] = 0;
        $DataSummary['SummaryCowPercentage'] = 0;
        $DataSummary['SummaryCurrentService'] = 0;
        $DataSummary['SummaryBeforeService'] = 0;
        $DataSummary['SummaryServicePercentage'] = 0;
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
                $monthName = VeterinaryController::getMonthName($curMonth);

                $data = [];
                $data['RegionName'] = $value['RegionName'];
                $data['Month'] = $monthName;
                $data['Quarter'] = ($i + 1);
                $data['Year'] = ($curYear);
                // get cooperative type
                $farm_type = 'Cooperative';
                // $item_type = 'โคนม';

                $item_type = ['โคนม','ปริมาณงาน'];
                $dairy_farming_id = [1,4,20];

                $CurrentCowData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                $data['CurrentCowData'] = floatval($CurrentCowData['sum_amount']);
                $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                $data['BeforeCowData'] = floatval($BeforeCowData['sum_amount']);

                // get insemination
                $farm_type = 'Cooperative';
                // $item_type = 'โคนม';
                $item_type = ['โคนม','ปริมาณงาน'];
                $dairy_farming_id = [17, 29];

                $CurrentCowInsData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                $data['CurrentCowInsData'] = floatval($CurrentCowInsData['sum_amount']);
                $BeforeCowInsData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                $data['BeforeCowInsData'] = floatval($BeforeCowInsData['sum_amount']);

                // $item_type = 'ค่าบริการ';
                $item_type = ['ค่าเวชภัณฑ์','ค่าบริการ', 'ค่าวัสดุ', 'ค่าน้ำเชื้อ'];
                $dairy_farming_id = [1,4,20];
                $CurrentServiceData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                $data['CurrentServiceData'] = floatval($CurrentServiceData['sum_amount']);
                $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                $data['BeforeServiceData'] = floatval($BeforeServiceData['sum_amount']);

                // get insemination
                // $item_type = 'ค่าบริการ';
                $item_type = ['ค่าเวชภัณฑ์','ค่าบริการ', 'ค่าวัสดุ', 'ค่าน้ำเชื้อ'];
                $dairy_farming_id = [17, 29];

                $CurrentServiceInsData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                $data['CurrentServiceInsData'] = floatval($CurrentServiceInsData['sum_amount']);
                $BeforeServiceInsData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                $data['BeforeServiceInsData'] = floatval($BeforeServiceInsData['sum_amount']);

                $diffCowData = $data['CurrentCowData'] - $data['BeforeCowData'];
                $data['DiffCowData'] = $diffCowData;

                if ($data['BeforeCowData'] != 0) {
                    $data['DiffCowDataPercentage'] = (($data['CurrentCowData'] - $data['BeforeCowData']) / $data['BeforeCowData']) * 100;
                } else if (empty($data['BeforeCowData']) && !empty($data['CurrentCowData'])) {
                    $data['DiffCowDataPercentage'] = 100;
                }else{
                    $data['DiffCowDataPercentage'] = 0;
                }

                // Insemination
                $diffCowInsData = $data['CurrentCowInsData'] - $data['BeforeCowInsData'];
                $data['DiffCowInsData'] = $diffCowInsData;

                if ($data['BeforeCowInsData'] != 0) {
                    $data['DiffCowDataInsPercentage'] = (($data['CurrentCowInsData'] - $data['BeforeCowInsData']) / $data['BeforeCowInsData']) * 100;
                } else if (empty($data['BeforeCowInsData']) && !empty($data['CurrentCowInsData'])) {
                    $data['DiffCowInsDataPercentage'] = 100;
                }else{
                    $data['DiffCowInsDataPercentage'] = 0;
                }


                $diffServiceData = $data['CurrentServiceData'] - $data['BeforeServiceData'];
                $data['DiffServiceData'] = $diffServiceData;


                if ($data['BeforeServiceData'] != 0) {
                    $data['DiffServiceDataPercentage'] = (($data['CurrentServiceData'] - $data['BeforeServiceData']) / $data['BeforeServiceData']) * 100;
                } else if (empty($data['BeforeServiceData']) && !empty($data['CurrentServiceData'])) {
                    $data['DiffServiceDataPercentage'] = 100;
                }else{
                    $data['DiffServiceDataPercentage'] = 0;
                }

                // Insemination
                $diffServiceInsData = $data['CurrentServiceInsData'] - $data['BeforeServiceInsData'];
                $data['DiffServiceInsData'] = $diffServiceInsData;


                if ($data['BeforeServiceInsData'] != 0) {
                    $data['DiffServiceInsDataPercentage'] = (($data['CurrentServiceInsData'] - $data['BeforeServiceInsData']) / $data['BeforeServiceInsData']) * 100;
                } else if (empty($data['BeforeServiceInsData']) && !empty($data['CurrentServiceInsData'])) {
                    $data['DiffServiceInsDataPercentage'] = 100;
                }else{
                     $data['DiffServiceInsDataPercentage'] = 0;
                }

                $data['CreateDate'] = $CurrentCowData['update_date'];
                $data['ApproveDate'] = $CurrentCowData['office_approve_date'];
                if (!empty($CurrentCowData['office_approve_id'])) {
                    if (empty($CurrentCowData['office_approve_comment'])) {
                        $data['Status'] = 'อนุมัติ';
                    } else {
                        $data['Status'] = 'ไม่อนุมัติ';
                    }
                }

                $data['Description'] = ['farm_type' => $farm_type
                    , 'item_type' => $item_type
                    , 'months' => $curMonth
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'] + $data['CurrentCowInsData'];
                $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'] + $data['BeforeCowInsData'];
                $DataSummary['SummaryCowPercentage'] = 0;
                $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'] + $data['CurrentServiceInsData'];
                $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'] + $data['BeforeServiceInsData'];
                $DataSummary['SummaryServicePercentage'] = 0;

                array_push($DataList, $data);


                if($value['RegionID'] == 1){
                    #### End of cooperative 
                    // get lab type
                    $data = [];
                    $data['RegionName'] = $value['RegionName'] . ' (ห้องปฏิบัติการ)';
                    $data['Month'] = $monthName;
                    $data['Quarter'] = ($i + 1);
                    $data['Year'] = ($curYear);
                    $farm_type = 'lab';
                    // $item_type = 'โคนม';
                    $item_type = ['โคนม','ปริมาณงาน'];
                    $dairy_farming_id = [1,4,20];

                    $CurrentCowData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                    $data['CurrentCowData'] = floatval($CurrentCowData['sum_amount']);
                    $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                    $data['BeforeCowData'] = floatval($BeforeCowData['sum_amount']);

                    // get insemination
                    $farm_type = 'lab';
                    // $item_type = 'โคนม';
                    $item_type = ['โคนม','ปริมาณงาน'];
                    $dairy_farming_id = [17, 29];

                    $CurrentCowInsData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                    $data['CurrentCowInsData'] = floatval($CurrentCowInsData['sum_amount']);
                    $BeforeCowInsData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                    $data['BeforeCowInsData'] = floatval($BeforeCowInsData['sum_amount']);

                    // $item_type = 'ค่าบริการ';
                    $item_type = ['ค่าเวชภัณฑ์','ค่าบริการ', 'ค่าวัสดุ', 'ค่าน้ำเชื้อ'];
                    $dairy_farming_id = [1,4,20];

                    $CurrentServiceData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                    $data['CurrentServiceData'] = floatval($CurrentServiceData['sum_amount']);
                    $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                    $data['BeforeServiceData'] = floatval($BeforeServiceData['sum_amount']);

                    // get insemination
                    // $item_type = 'ค่าบริการ';
                    $item_type = ['ค่าเวชภัณฑ์','ค่าบริการ', 'ค่าวัสดุ', 'ค่าน้ำเชื้อ'];
                    $dairy_farming_id = [17, 29];

                    $CurrentServiceInsData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                    $data['CurrentServiceInsData'] = floatval($CurrentServiceInsData['sum_amount']);
                    $BeforeServiceInsData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type, $dairy_farming_id);
                    $data['BeforeServiceInsData'] = floatval($BeforeServiceInsData['sum_amount']);

                    $diffCowData = floatval($data['CurrentCowData']) - floatval($data['BeforeCowData']);
                    $data['DiffCowData'] = $diffCowData;

                    if (floatval($data['BeforeCowData']) != 0) {
                        $data['DiffCowDataPercentage'] = floatval($data['CurrentCowData']) / floatval($data['BeforeCowData'] * 100);
                    } else {
                        $data['DiffCowDataPercentage'] = 0;
                    }

                    // Insemination
                    $diffCowInsData = $data['CurrentCowInsData'] - $data['BeforeCowInsData'];
                    $data['DiffCowInsData'] = $diffCowInsData;

                    if ($data['BeforeCowInsData'] != 0) {
                        $data['DiffCowDataInsPercentage'] = (($data['CurrentCowInsData'] - $data['BeforeCowInsData']) / $data['BeforeCowInsData']) * 100;
                    } else if (empty($data['BeforeCowInsData']) && !empty($data['CurrentCowInsData'])) {
                        $data['DiffCowInsDataPercentage'] = 100;
                    }else{
                        $data['DiffCowInsDataPercentage'] = 0;
                    }
                    // if (is_nan($data['DiffCowDataPercentage'])) {
                    //     $data['DiffCowDataPercentage'] = 0;
                    // }
                    $diffServiceData = $data['CurrentServiceData'] - $data['BeforeServiceData'];
                    $data['DiffServiceData'] = $diffServiceData;

                    if ($data['BeforeServiceData'] != 0) {
                        $data['DiffServiceDataPercentage'] = floatval($data['CurrentServiceData']) / floatval($data['BeforeServiceData'] * 100);
                    } else {
                        $data['DiffServiceDataPercentage'] = 0;
                    }

                    $diffServiceInsData = $data['CurrentServiceInsData'] - $data['BeforeServiceInsData'];
                    $data['DiffServiceInsData'] = $diffServiceInsData;


                    if ($data['BeforeServiceInsData'] != 0) {
                        $data['DiffServiceInsDataPercentage'] = (($data['CurrentServiceInsData'] - $data['BeforeServiceInsData']) / $data['BeforeServiceInsData']) * 100;
                    } else if (empty($data['BeforeServiceInsData']) && !empty($data['CurrentServiceInsData'])) {
                        $data['DiffServiceInsDataPercentage'] = 100;
                    } else {
                        $data['DiffServiceInsDataPercentage'] = 0;
                    }
                    // if (is_nan($data['DiffServiceDataPercentage'])) {
                    //     $data['DiffServiceDataPercentage'] = 0;
                    // }
                    $data['CreateDate'] = $CurrentCowData['update_date'];
                    $data['ApproveDate'] = $CurrentCowData['office_approve_date'];
                    if (!empty($CurrentCowData['office_approve_id'])) {
                        if (empty($CurrentCowData['office_approve_comment'])) {
                            $data['Status'] = 'อนุมัติ';
                        } else {
                            $data['Status'] = 'ไม่อนุมัติ';
                        }
                    }

                    $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'] + $data['CurrentCowInsData'];
                    $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'] + $data['BeforeCowInsData'];

                    $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'] + $data['CurrentServiceInsData'];
                    $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'] + $data['BeforeServiceInsData'];

                    $data['Description'] = ['farm_type' => $farm_type
                        , 'item_type' => $item_type
                        , 'months' => $curMonth
                        , 'years' => $curYear
                        , 'region_id' => $region_id
                    ];
                    array_push($DataList, $data);
                }
                
                
                // echo $data['CurrentCowData'] . ' - ' . $DataSummary['SummaryCurrentCow']."\n";
            }//exit;
            $curMonth++;
        }
        if ($DataSummary['SummaryBeforeCow'] != 0) {
            $DataSummary['SummaryCowPercentage'] = (($DataSummary['SummaryCurrentCow'] - $DataSummary['SummaryBeforeCow']) / $DataSummary['SummaryBeforeCow']) * 100;
        }else{
            $DataSummary['SummaryCowPercentage'] =100;
        }
        if ($DataSummary['SummaryBeforeService'] != 0) {
            $DataSummary['SummaryServicePercentage'] = (($DataSummary['SummaryCurrentService'] - $DataSummary['SummaryBeforeService']) / $DataSummary['SummaryBeforeService']) * 100;
        }else{
            $DataSummary['SummaryServicePercentage'] =100;
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
                $Co_SumCurrentCowData = 0;
                $Co_SumBeforeCowData = 0;
                $Co_SumCurrentServiceData = 0;
                $Co_SumBeforeServiceData = 0;
                $Co_UpdateDate = '';
                $Co_ApproveDate = '';
                $Co_ApproveComment = '';

                $Lab_SumCurrentCowData = 0;
                $Lab_SumBeforeCowData = 0;
                $Lab_SumCurrentServiceData = 0;
                $Lab_SumBeforeServiceData = 0;
                $Lab_UpdateDate = '';
                $Lab_ApproveDate = '';
                $Lab_ApproveComment = '';

                // loop get quarter sum data
                for ($j = 0; $j < count($monthList); $j++) {
                    $curMonth = $monthList[$j];
                    $farm_type = 'Cooperative';
                    $item_type = 'โคนม';
                    $CurrentCowData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumCurrentCowData += floatval($CurrentCowData['sum_amount']);
                    $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumBeforeCowData += floatval($BeforeCowData['sum_amount']);

                    $item_type = 'ค่าบริการ';
                    $CurrentServiceData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumCurrentServiceData += floatval($CurrentServiceData['sum_amount']);
                    $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumBeforeServiceData += floatval($BeforeServiceData['sum_amount']);

                    if (!empty($CurrentCowData['update_date'])) {
                        $Co_UpdateDate = $CurrentCowData['update_date'];
                    }
                    if (!empty($CurrentCowData['office_approve_id'])) {
                        $Co_ApproveDate = $CurrentCowData['office_approve_date'];
                    }
                    if (!empty($CurrentCowData['office_approve_comment'])) {
                        $Co_ApproveComment = $CurrentCowData['office_approve_comment'];
                    }

                    $farm_type = 'lab';
                    $item_type = 'โคนม';
                    $CurrentCowData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumCurrentCowData += floatval($CurrentCowData['sum_amount']);
                    $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumBeforeCowData += floatval($BeforeCowData['sum_amount']);

                    $item_type = 'ค่าบริการ';
                    $CurrentServiceData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumCurrentServiceData += floatval($CurrentServiceData['sum_amount']);
                    $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumBeforeServiceData += floatval($BeforeServiceData['sum_amount']);

                    if (!empty($CurrentCowData['update_date'])) {
                        $Lab_UpdateDate = $CurrentCowData['update_date'];
                    }
                    if (!empty($CurrentCowData['office_approve_id'])) {
                        $Lab_ApproveDate = $CurrentCowData['office_approve_date'];
                    }
                    if (!empty($CurrentCowData['office_approve_comment'])) {
                        $Lab_ApproveComment = $CurrentCowData['office_approve_comment'];
                    }
                }

                // Cooperative
                $data = [];
                $data['RegionName'] = $value['RegionName'] . ' (สหกรณ์)';
                $data['Quarter'] = ($curQuarter) . ' (' . ($curQuarter == 1 ? $curYear + 543 + 1 : $curYear + 543) . ')';
                $data['CurrentCowData'] = $Co_SumCurrentCowData;
                $data['BeforeCowData'] = $Co_SumBeforeCowData;
                $data['CurrentServiceData'] = $Co_SumCurrentServiceData;
                $data['BeforeServiceData'] = $Co_SumBeforeServiceData;
                $data['DiffCowData'] = $Co_SumCurrentCowData - $Co_SumBeforeCowData;
                $data['DiffCowDataPercentage'] = 0;
                $data['DiffServiceData'] = $Co_SumCurrentServiceData - $Co_SumBeforeServiceData;
                $data['DiffServiceDataPercentage'] = 0;

                if ($data['BeforeCowData'] != 0) {
                    $data['DiffCowDataPercentage'] = (($data['CurrentCowData'] - $data['BeforeCowData']) / $data['BeforeCowData']) * 100;
                } else if (empty($data['BeforeCowData']) && !empty($data['CurrentCowData'])) {
                    $data['DiffCowDataPercentage'] = 100;
                }


                if ($data['BeforeServiceData'] != 0) {
                    $data['DiffServiceDataPercentage'] = (($data['CurrentServiceData'] - $data['BeforeServiceData']) / $data['BeforeServiceData']) * 100;
                } else if (empty($data['BeforeServiceData']) && !empty($data['CurrentServiceData'])) {
                    $data['DiffServiceDataPercentage'] = 100;
                }

                $data['CreateDate'] = $Co_UpdateDate;
                $data['ApproveDate'] = $Co_ApproveDate;
                if (!empty($Co_ApproveDate)) {
                    if (empty($Co_ApproveComment)) {
                        $data['Status'] = 'อนุมัติ';
                    } else {
                        $data['Status'] = 'ไม่อนุมัติ';
                    }
                }
                $data['Description'] = ['farm_type' => $farm_type
                    , 'item_type' => $item_type
                    , 'quarter' => $curQuarter
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'];
                $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'];
                $DataSummary['SummaryCowPercentage'] = 0;
                $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'];
                $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'];
                $DataSummary['SummaryServicePercentage'] = 0;

                array_push($DataList, $data);

                // Lab
                $data['RegionName'] = $value['RegionName'] . ' (ห้องปฏิบัติการ)';
                $data['Quarter'] = ($curQuarter) . ' (' . (($curQuarter == 1 ? $curYear + 543 + 1 : $curYear + 543)) . ')';
                $data['CurrentCowData'] = $Lab_SumCurrentCowData;
                $data['BeforeCowData'] = $Lab_SumBeforeCowData;
                $data['CurrentServiceData'] = $Lab_SumCurrentServiceData;
                $data['BeforeServiceData'] = $Lab_SumBeforeServiceData;
                $data['DiffCowData'] = $Lab_SumCurrentCowData - $Lab_SumBeforeCowData;
                $data['DiffCowDataPercentage'] = 0;
                $data['DiffServiceData'] = $Lab_SumCurrentServiceData - $Lab_SumBeforeServiceData;
                $data['DiffServiceDataPercentage'] = 0;
                $data['CreateDate'] = $Lab_UpdateDate;
                $data['ApproveDate'] = $Lab_ApproveDate;
                if (!empty($Lab_ApproveDate)) {
                    if (empty($Lab_ApproveComment)) {
                        $data['Status'] = 'อนุมัติ';
                    } else {
                        $data['Status'] = 'ไม่อนุมัติ';
                    }
                }
                $data['Description'] = ['farm_type' => $farm_type
                    , 'item_type' => $item_type
                    , 'quarter' => $curQuarter
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];
                array_push($DataList, $data);

                $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'];
                $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'];
                $DataSummary['SummaryCowPercentage'] = (($DataSummary['SummaryCurrentCow'] - $DataSummary['SummaryBeforeCow']) / $DataSummary['SummaryBeforeCow']) * 100;
                $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'];
                $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'];
                $DataSummary['SummaryServicePercentage'] = (($DataSummary['SummaryCurrentService'] - $DataSummary['SummaryBeforeService']) / $DataSummary['SummaryBeforeService']) * 100;
            }

            $curQuarter++;
            if ($curQuarter > 4) {
                $curQuarter = 1;
            }
        }

         if ($DataSummary['SummaryBeforeCow'] != 0) {
            $DataSummary['SummaryCowPercentage'] = (($DataSummary['SummaryCurrentCow'] - $DataSummary['SummaryBeforeCow']) / $DataSummary['SummaryBeforeCow']) * 100;
        }else{
            $DataSummary['SummaryCowPercentage'] =100;
        }
        if ($DataSummary['SummaryBeforeService'] != 0) {
            $DataSummary['SummaryServicePercentage'] = (($DataSummary['SummaryCurrentService'] - $DataSummary['SummaryBeforeService']) / $DataSummary['SummaryBeforeService']) * 100;
        }else{
            $DataSummary['SummaryServicePercentage'] =100;
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
            foreach ($regions as $key => $value) {

                $curYear = $condition['YearFrom'];
                $calcYear = intval($curYear) - 1;

                $region_id = $value['RegionID'];
                $Co_SumCurrentCowData = 0;
                $Co_SumBeforeCowData = 0;
                $Co_SumCurrentServiceData = 0;
                $Co_SumBeforeServiceData = 0;
                $Co_UpdateDate = '';

                $Co_ApproveDate = '';
                $Co_ApproveComment = '';

                $Lab_SumCurrentCowData = 0;
                $Lab_SumBeforeCowData = 0;
                $Lab_SumCurrentServiceData = 0;
                $Lab_SumBeforeServiceData = 0;
                $Lab_UpdateDate = '';
                $Lab_ApproveDate = '';
                $Lab_ApproveComment = '';

                for ($j = 0; $j < 12; $j++) {

                    $curMonth = $monthList[$j];

                    if (intval($curMonth) == 1) {
                        $calcYear++;
                        $beforeYear = $calcYear - 1;
                    }

                    $farm_type = 'Cooperative';
                    $item_type = 'โคนม';
                    // echo "$calcYear, $curMonth, $region_id, $farm_type, $item_type\n";
                    $CurrentCowData = VeterinaryService::getMainList($calcYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumCurrentCowData += floatval($CurrentCowData['sum_amount']);
                    $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumBeforeCowData += floatval($BeforeCowData['sum_amount']);

                    $item_type = 'ค่าบริการ';
                    $CurrentServiceData = VeterinaryService::getMainList($calcYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumCurrentServiceData += floatval($CurrentServiceData['sum_amount']);
                    $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumBeforeServiceData += floatval($BeforeServiceData['sum_amount']);

                    if (!empty($CurrentCowData['update_date'])) {
                        $Co_UpdateDate = $CurrentCowData['update_date'];
                    }
                    if (!empty($CurrentCowData['office_approve_id'])) {
                        $Co_ApproveDate = $CurrentCowData['office_approve_date'];
                    }
                    if (!empty($CurrentCowData['office_approve_comment'])) {
                        $Co_ApproveComment = $CurrentCowData['office_approve_comment'];
                    }

                    $farm_type = 'lab';
                    $item_type = 'โคนม';
                    echo "$calcYear, $curMonth, $region_id\n";
                    $CurrentCowData = VeterinaryService::getMainList($calcYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumCurrentCowData += floatval($CurrentCowData['sum_amount']);
                    $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumBeforeCowData += floatval($BeforeCowData['sum_amount']);

                    $item_type = 'ค่าบริการ';
                    $CurrentServiceData = VeterinaryService::getMainList($calcYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumCurrentServiceData += floatval($CurrentServiceData['sum_amount']);
                    $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumBeforeServiceData += floatval($BeforeServiceData['sum_amount']);

                    if (!empty($CurrentCowData['update_date'])) {
                        $Lab_UpdateDate = $CurrentCowData['update_date'];
                    }
                    if (!empty($CurrentCowData['office_approve_id'])) {
                        $Lab_ApproveDate = $CurrentCowData['office_approve_date'];
                    }
                    if (!empty($CurrentCowData['office_approve_comment'])) {
                        $Lab_ApproveComment = $CurrentCowData['office_approve_comment'];
                    }
                }

                // Cooperative
                $data = [];
                $data['RegionName'] = $value['RegionName'] . ' (สหกรณ์)';
                $data['Year'] = $curYear + 543;
                $data['CurrentCowData'] = $Co_SumCurrentCowData;
                $data['BeforeCowData'] = $Co_SumBeforeCowData;
                $data['CurrentServiceData'] = $Co_SumCurrentServiceData;
                $data['BeforeServiceData'] = $Co_SumBeforeServiceData;
                $data['DiffCowData'] = $Co_SumCurrentCowData - $Co_SumBeforeCowData;
                $data['DiffCowDataPercentage'] = 0;
                $data['DiffServiceData'] = $Co_SumCurrentServiceData - $Co_SumBeforeServiceData;
                $data['DiffServiceDataPercentage'] = 0;

                if ($data['BeforeCowData'] != 0) {
                    $data['DiffCowDataPercentage'] = (($data['CurrentCowData'] - $data['BeforeCowData']) / $data['BeforeCowData']) * 100;
                } else if (empty($data['BeforeCowData']) && !empty($data['CurrentCowData'])) {
                    $data['DiffCowDataPercentage'] = 100;
                }


                if ($data['BeforeServiceData'] != 0) {
                    $data['DiffServiceDataPercentage'] = (($data['CurrentServiceData'] - $data['BeforeServiceData']) / $data['BeforeServiceData']) * 100;
                } else if (empty($data['BeforeServiceData']) && !empty($data['CurrentServiceData'])) {
                    $data['DiffServiceDataPercentage'] = 100;
                }

                $data['CreateDate'] = $Co_UpdateDate;
                $data['ApproveDate'] = $Co_ApproveDate;
                if (!empty($Co_ApproveDate)) {
                    if (empty($Co_ApproveComment)) {
                        $data['Status'] = 'อนุมัติ';
                    } else {
                        $data['Status'] = 'ไม่อนุมัติ';
                    }
                }
                $data['Description'] = ['farm_type' => $farm_type
                    , 'item_type' => $item_type
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'];
                $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'];
                $DataSummary['SummaryCowPercentage'] = 0;
                $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'];
                $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'];
                $DataSummary['SummaryServicePercentage'] = 0;

                array_push($DataList, $data);

                // Lab
                $data['RegionName'] = $value['RegionName'] . ' (ห้องปฏิบัติการ)';
                $data['Year'] = $curYear + 543;
                $data['CurrentCowData'] = $Lab_SumCurrentCowData;
                $data['BeforeCowData'] = $Lab_SumBeforeCowData;
                $data['CurrentServiceData'] = $Lab_SumCurrentServiceData;
                $data['BeforeServiceData'] = $Lab_SumBeforeServiceData;
                $data['DiffCowData'] = $Lab_SumCurrentCowData - $Lab_SumBeforeCowData;
                $data['DiffCowDataPercentage'] = 0;
                $data['DiffServiceData'] = $Lab_SumCurrentServiceData - $Lab_SumBeforeServiceData;
                $data['DiffServiceDataPercentage'] = 0;
                $data['CreateDate'] = $Lab_UpdateDate;
                $data['ApproveDate'] = $Lab_ApproveDate;
                if (!empty($Lab_ApproveDate)) {
                    if (empty($Lab_ApproveComment)) {
                        $data['Status'] = 'อนุมัติ';
                    } else {
                        $data['Status'] = 'ไม่อนุมัติ';
                    }
                }

                $data['Description'] = ['farm_type' => $farm_type
                    , 'item_type' => $item_type
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];
                array_push($DataList, $data);

                $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'];
                $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'];
                $DataSummary['SummaryCowPercentage'] = (($DataSummary['SummaryCurrentCow'] - $DataSummary['SummaryBeforeCow']) / $DataSummary['SummaryBeforeCow']) * 100;
                $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'];
                $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'];
                $DataSummary['SummaryServicePercentage'] = (($DataSummary['SummaryCurrentService'] - $DataSummary['SummaryBeforeService']) / $DataSummary['SummaryBeforeService']) * 100;
            }

            $curYear++;
        }
       if ($DataSummary['SummaryBeforeCow'] != 0) {
            $DataSummary['SummaryCowPercentage'] = (($DataSummary['SummaryCurrentCow'] - $DataSummary['SummaryBeforeCow']) / $DataSummary['SummaryBeforeCow']) * 100;
        }else{
            $DataSummary['SummaryCowPercentage'] =100;
        }
        if ($DataSummary['SummaryBeforeService'] != 0) {
            $DataSummary['SummaryServicePercentage'] = (($DataSummary['SummaryCurrentService'] - $DataSummary['SummaryBeforeService']) / $DataSummary['SummaryBeforeService']) * 100;
        }else{
            $DataSummary['SummaryServicePercentage'] =100;
        } // exit;
        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getDetailList($request, $response, $args) {
        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();

            $description = $params['obj']['description'];
            $condition = $params['obj']['condition'];
            $regions = $params['obj']['region'];

            if ($condition['DisplayType'] == 'monthly') {
                $Result = $this->getMonthDetailList($condition, $regions, $description);
            } else if ($condition['DisplayType'] == 'quarter') {
                $Result = $this->getQuarterDetailList($condition, $regions, $description);
            } else if ($condition['DisplayType'] == 'annually') {
                $Result = $this->getAnnuallyDetailList($condition, $regions, $description);
            }



            $this->data_result['DATA']['DetailList'] = $Result['DetailList'];
            $this->data_result['DATA']['CooperativeList'] = $Result['CooperativeList'];

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function getMonthDetailList($condition, $regions, $description) {
        $years = $description['years'];
        $months = $description['months'];
        $region_id = $description['region_id'];
        $costList = [
            ['item' => 'สมาชิก', 'unit' => 'ราย']
            , ['item' => 'โคนม', 'unit' => 'ตัว']
            , ['item' => 'ค่าเวชภัณฑ์', 'unit' => 'บาท']
            , ['item' => 'ค่าบริการ', 'unit' => 'บาท']
            , ['item' => 'ค่าวัสดุ', 'unit' => 'บาท']
            , ['item' => 'ปริมาณงาน', 'unit' => 'ชิ้น']
            , ['item' => 'ค่าน้ำเชื้อ', 'unit' => 'บาท']
        ];

        // get cooperative list
        $CooperativeList = CooperativeService::getListByRegion($region_id);
        // get dairy farming
        $DairyFarming = DairyFarmingService::getList('Y');

        $DetailList = [];
        foreach ($DairyFarming as $key => $value) {
            // get child
            $dairy_farming_id = $value['id'];
            $SubDairyFarming = DairyFarmingService::getChildList($dairy_farming_id, 'Y');
            if (!empty($SubDairyFarming)) {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                array_push($DetailList, $data);
                foreach ($SubDairyFarming as $_key => $_value) {

                    $data = [];
                    $data['DairyFarmingName'] = $_value['child_name'];
                    $data['BGColor'] = '#BBECA9';
                    $data['Data'] = [];
                    // array_push($DetailList, $data);

                    $sub_dairy_farming_id = $_value['id'];
                    foreach ($costList as $cost_key => $cost_value) {

                        $item_type = $cost_value['item'];
                        $unit = $cost_value['unit'];

                        // prepare data
                        $sub_data = [];
                        $sub_data['ItemName'] = $item_type;
                        $sub_data['Unit'] = $unit;
                        $sub_data['Dataset'] = [];
                        $SumAmount = 0;
                        $amount_data = [];
                        foreach ($CooperativeList as $co_key => $co_value) {
                            $cooperative_id = $co_value['id'];
                            $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id, $sub_dairy_farming_id);
                            $amount_data[]['Amount'] = floatval($AmountList['sum_amount']);
                            $SumAmount += floatval($AmountList['sum_amount']);
                        }
                        $sub_data['Dataset'] = $amount_data;
                        $sub_data['Summary'] = $SumAmount;
                        array_push($data['Data'], $sub_data);
                    }


                    array_push($DetailList, $data);
                }
            } else {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                $data['Data'] = [];
                foreach ($costList as $cost_key => $cost_value) {

                    $item_type = $cost_value['item'];
                    $unit = $cost_value['unit'];

                    // prepare data
                    $sub_data = [];
                    $sub_data['ItemName'] = $item_type;
                    $sub_data['Unit'] = $unit;
                    $sub_data['Dataset'] = [];
                    $SumAmount = 0;
                    $amount_data = [];
                    $cnt = 0;
                    foreach ($CooperativeList as $co_key => $co_value) {
                        $cooperative_id = $co_value['id'];
                        $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id);
                        $amount_data[$cnt]['Amount'] = floatval($AmountList['sum_amount']);
                        $SumAmount += floatval($AmountList['sum_amount']);
                        $cnt++;
                    }
                    $sub_data['Dataset'] = $amount_data;
                    $sub_data['Summary'] = $SumAmount;
                    array_push($data['Data'], $sub_data);
                }

                array_push($DetailList, $data);
            }
        }

        return ['DetailList' => $DetailList, 'CooperativeList' => $CooperativeList];
    }

    private function getQuarterDetailList($condition, $regions, $description) {
        $years = $description['years'];
        $quarter = $description['quarter'];
        $region_id = $description['region_id'];
        $costList = [
            ['item' => 'สมาชิก', 'unit' => 'ราย']
            , ['item' => 'โคนม', 'unit' => 'ตัว']
            , ['item' => 'ค่าเวชภัณฑ์', 'unit' => 'บาท']
            , ['item' => 'ค่าบริการ', 'unit' => 'บาท']
        ];
        if ($quarter == 1) {
            $monthList = [10, 11, 12];
        } else if ($quarter == 2) {
            $monthList = [1, 2, 3];
        } else if ($quarter == 3) {
            $monthList = [4, 5, 6];
        } else if ($quarter == 4) {
            $monthList = [7, 8, 9];
        }

        // get cooperative list
        $CooperativeList = CooperativeService::getListByRegion($region_id);
        // get dairy farming
        $DairyFarming = DairyFarmingService::getList('Y');

        $DetailList = [];
        foreach ($DairyFarming as $key => $value) {
            // get child
            $dairy_farming_id = $value['id'];
            $SubDairyFarming = DairyFarmingService::getChildList($dairy_farming_id, 'Y');
            if (!empty($SubDairyFarming)) {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                array_push($DetailList, $data);
                foreach ($SubDairyFarming as $_key => $_value) {

                    $data = [];
                    $data['DairyFarmingName'] = $_value['child_name'];
                    $data['BGColor'] = '#BBECA9';
                    $data['Data'] = [];
                    // array_push($DetailList, $data);

                    $sub_dairy_farming_id = $_value['id'];
                    foreach ($costList as $cost_key => $cost_value) {

                        $item_type = $cost_value['item'];
                        $unit = $cost_value['unit'];

                        // prepare data
                        $sub_data = [];
                        $sub_data['ItemName'] = $item_type;
                        $sub_data['Unit'] = $unit;
                        $sub_data['Dataset'] = [];
                        $SumAmount = 0;
                        $amount_data = [];
                        foreach ($CooperativeList as $co_key => $co_value) {
                            $cooperative_id = $co_value['id'];
                            $totalAmount = 0;
                            for ($i = 0; $i < count($monthList); $i++) {
                                $months = $monthList[$i];
                                $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id, $sub_dairy_farming_id);
                                $totalAmount += floatval($AmountList['sum_amount']);
                            }
                            $amount_data[]['Amount'] = $totalAmount;
                            $SumAmount += $totalAmount;
                        }
                        $sub_data['Dataset'] = $amount_data;
                        $sub_data['Summary'] = $SumAmount;
                        array_push($data['Data'], $sub_data);
                    }


                    array_push($DetailList, $data);
                }
            } else {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                $data['Data'] = [];
                foreach ($costList as $cost_key => $cost_value) {

                    $item_type = $cost_value['item'];
                    $unit = $cost_value['unit'];

                    // prepare data
                    $sub_data = [];
                    $sub_data['ItemName'] = $item_type;
                    $sub_data['Unit'] = $unit;
                    $sub_data['Dataset'] = [];
                    $SumAmount = 0;
                    $amount_data = [];
                    foreach ($CooperativeList as $co_key => $co_value) {
                        $cooperative_id = $co_value['id'];
                        $totalAmount = 0;
                        for ($i = 0; $i < count($monthList); $i++) {
                            $months = $monthList[$i];
                            $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id);
                            $totalAmount += floatval($AmountList['sum_amount']);
                        }
                        $amount_data[]['Amount'] = $totalAmount;
                        $SumAmount += $totalAmount;
                    }
                    $sub_data['Dataset'] = $amount_data;
                    $sub_data['Summary'] = $SumAmount;
                    array_push($data['Data'], $sub_data);
                }

                array_push($DetailList, $data);
            }
        }

        return ['DetailList' => $DetailList, 'CooperativeList' => $CooperativeList];
    }

    public function getAnnuallyDetailList($condition, $regions, $description) {
        $years = $description['years'];
        $quarter = $description['quarter'];
        $region_id = $description['region_id'];

        $costList = [
            ['item' => 'สมาชิก', 'unit' => 'ราย']
            , ['item' => 'โคนม', 'unit' => 'ตัว']
            , ['item' => 'ค่าเวชภัณฑ์', 'unit' => 'บาท']
            , ['item' => 'ค่าบริการ', 'unit' => 'บาท']
        ];

        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        // get cooperative list
        $CooperativeList = CooperativeService::getListByRegion($region_id);
        // get dairy farming
        $DairyFarming = DairyFarmingService::getList('Y');

        $DetailList = [];
        foreach ($DairyFarming as $key => $value) {
            // get child
            $dairy_farming_id = $value['id'];
            $SubDairyFarming = DairyFarmingService::getChildList($dairy_farming_id, 'Y');
            if (!empty($SubDairyFarming)) {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                array_push($DetailList, $data);
                foreach ($SubDairyFarming as $_key => $_value) {

                    $data = [];
                    $data['DairyFarmingName'] = $_value['child_name'];
                    $data['BGColor'] = '#BBECA9';
                    $data['Data'] = [];
                    // array_push($DetailList, $data);

                    $sub_dairy_farming_id = $_value['id'];
                    foreach ($costList as $cost_key => $cost_value) {

                        $item_type = $cost_value['item'];
                        $unit = $cost_value['unit'];

                        // prepare data
                        $sub_data = [];
                        $sub_data['ItemName'] = $item_type;
                        $sub_data['Unit'] = $unit;
                        $sub_data['Dataset'] = [];
                        $SumAmount = 0;
                        $amount_data = [];
                        foreach ($CooperativeList as $co_key => $co_value) {
                            $cooperative_id = $co_value['id'];
                            $totalAmount = 0;
                            for ($i = 0; $i < count($monthList); $i++) {
                                $months = $monthList[$i];
                                $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id, $sub_dairy_farming_id);
                                $totalAmount += floatval($AmountList['sum_amount']);
                            }
                            $amount_data[]['Amount'] = $totalAmount;
                            $SumAmount += $totalAmount;
                        }
                        $sub_data['Dataset'] = $amount_data;
                        $sub_data['Summary'] = $SumAmount;
                        array_push($data['Data'], $sub_data);
                    }

                    array_push($DetailList, $data);
                }
            } else {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                $data['Data'] = [];
                foreach ($costList as $cost_key => $cost_value) {

                    $item_type = $cost_value['item'];
                    $unit = $cost_value['unit'];

                    // prepare data
                    $sub_data = [];
                    $sub_data['ItemName'] = $item_type;
                    $sub_data['Unit'] = $unit;
                    $sub_data['Dataset'] = [];
                    $SumAmount = 0;
                    $amount_data = [];
                    foreach ($CooperativeList as $co_key => $co_value) {
                        $cooperative_id = $co_value['id'];
                        $totalAmount = 0;
                        for ($i = 0; $i < count($monthList); $i++) {
                            $months = $monthList[$i];
                            $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id);
                            $totalAmount += floatval($AmountList['sum_amount']);
                        }
                        $amount_data[]['Amount'] = $totalAmount;
                        $SumAmount += $totalAmount;
                    }
                    $sub_data['Dataset'] = $amount_data;
                    $sub_data['Summary'] = $SumAmount;
                    array_push($data['Data'], $sub_data);
                }

                array_push($DetailList, $data);
            }
        }

        return ['DetailList' => $DetailList, 'CooperativeList' => $CooperativeList];
    }

    public function getSubDetailList($request, $response, $args) {
        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();

            $description = $params['obj']['description'];
            $condition = $params['obj']['condition'];
            $regions = $params['obj']['region'];
            $cooperative_id = $params['obj']['cooperative_id'];

            $years = $description['years'];
            $quarter = $description['quarter'];
            $region_id = $description['region_id'];

            $costList = [
                ['item' => 'สมาชิก', 'unit' => 'ราย']
                , ['item' => 'โคนม', 'unit' => 'ตัว']
                , ['item' => 'ค่าเวชภัณฑ์', 'unit' => 'บาท']
                , ['item' => 'ค่าบริการ', 'unit' => 'บาท']
            ];

            if ($condition['DisplayType'] == 'annually') {
                $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            } else {
                $quarter = $description['quarter'];
                if ($quarter == 1) {
                    $monthList = [10, 11, 12];
                } else if ($quarter == 2) {
                    $monthList = [1, 2, 3];
                } else if ($quarter == 3) {
                    $monthList = [4, 5, 6];
                } else if ($quarter == 4) {
                    $monthList = [7, 8, 9];
                }
            }

            // get cooperative by id
            $Cooperative = CooperativeService::getData($cooperative_id);

            // get dairy farming
            $DairyFarming = DairyFarmingService::getList('Y');

            $DetailList = [];
            foreach ($DairyFarming as $key => $value) {
                // get child
                $dairy_farming_id = $value['id'];
                $SubDairyFarming = DairyFarmingService::getChildList($dairy_farming_id, 'Y');
                if (!empty($SubDairyFarming)) {
                    $data = [];
                    $data['DairyFarmingName'] = $value['dairy_farming_name'];
                    $data['BGColor'] = '#B6CCFF';
                    array_push($DetailList, $data);
                    foreach ($SubDairyFarming as $_key => $_value) {

                        $data = [];
                        $data['DairyFarmingName'] = $_value['child_name'];
                        $data['BGColor'] = '#BBECA9';
                        $data['Data'] = [];
                        // array_push($DetailList, $data);

                        $sub_dairy_farming_id = $_value['id'];
                        foreach ($costList as $cost_key => $cost_value) {

                            $item_type = $cost_value['item'];
                            $unit = $cost_value['unit'];

                            // prepare data
                            $sub_data = [];
                            $sub_data['ItemName'] = $item_type;
                            $sub_data['Unit'] = $unit;
                            $sub_data['Dataset'] = [];
                            $SumAmount = 0;
                            $amount_data = [];
                            for ($i = 0; $i < count($monthList); $i++) {
                                $months = $monthList[$i];
                                $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id, $sub_dairy_farming_id);

                                $amount_data[]['Amount'] = floatval($AmountList['sum_amount']);
                                $SumAmount += floatval($AmountList['sum_amount']);
                            }

                            $sub_data['Dataset'] = $amount_data;
                            $sub_data['Summary'] = $SumAmount;
                            array_push($data['Data'], $sub_data);
                        }

                        array_push($DetailList, $data);
                    }
                } else {
                    $data = [];
                    $data['DairyFarmingName'] = $value['dairy_farming_name'];
                    $data['BGColor'] = '#B6CCFF';
                    $data['Data'] = [];
                    foreach ($costList as $cost_key => $cost_value) {

                        $item_type = $cost_value['item'];
                        $unit = $cost_value['unit'];

                        // prepare data
                        $sub_data = [];
                        $sub_data['ItemName'] = $item_type;
                        $sub_data['Unit'] = $unit;
                        $sub_data['Dataset'] = [];
                        $amount_data = [];
                        $SumAmount = 0;
                        for ($i = 0; $i < count($monthList); $i++) {
                            $months = $monthList[$i];
                            $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id, $sub_dairy_farming_id);

                            $amount_data[]['Amount'] = floatval($AmountList['sum_amount']);
                            $SumAmount += floatval($AmountList['sum_amount']);
                        }
                        $sub_data['Dataset'] = $amount_data;
                        $sub_data['Summary'] = $SumAmount;
                        array_push($data['Data'], $sub_data);
                    }

                    array_push($DetailList, $data);
                }
            }

            // gen month name
            $MonthNameList = [];
            for ($i = 0; $i < count($monthList); $i++) {
                $MonthNameList[]['month'] = $this->getMonthName($monthList[$i]);
            }

            $this->data_result['DATA']['SubDetailList'] = $DetailList;
            $this->data_result['DATA']['Cooperative'] = $Cooperative;
            $this->data_result['DATA']['MonthNameList'] = $MonthNameList;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getList($request, $response, $args) {
        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();
            $actives = $params['obj']['actives'];

            // group by region first
            $Regions = VeterinaryService::getRegionList();
            // Loop get by region
            $DataList = [];
            foreach ($Regions as $key => $value) {
                $_List = VeterinaryService::getList($value['region_id'], $actives);
                $value['Data'] = $_List;
                array_push($DataList, $value);
                // $Regions['Data'][] = $_List;
            }


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

            $cooperative_id = $params['obj']['cooperative_id'];
            $months = $params['obj']['months'];
            $years = $params['obj']['years'];

            if (!empty($id)) {
                $_Data = VeterinaryService::getDataByID($id);
            } else {
                $_Data = VeterinaryService::getData($cooperative_id, $months, $years);
            }

            $this->data_result['DATA']['Data'] = $_Data;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function updateData($request, $response, $args) {
        $URL = '127.0.0.1';
        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();
            $_Veterinary = $params['obj']['Veterinary'];
            $user_session = $params['user_session'];

            $OrgID = $user_session['OrgID'];

            $HeaderData = $this->do_post_request('http://' . $URL . '/dportal/dpo/public/mis/get/org/header/', "POST", ['OrgID' => $OrgID, 'Type' => 'OWNER']);
            $HeaderData = json_decode(trim($HeaderData), TRUE);
            // print_r($HeaderData);exit;
            if ($HeaderData['data']['DATA']['Header']['OrgType'] == 'DEPARTMENT') {
                $_Veterinary['dep_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $_Veterinary['dep_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
            } else if ($HeaderData['data']['DATA']['Header']['OrgType'] == 'DIVISION') {
                $_Veterinary['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $_Veterinary['division_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
            } else if ($HeaderData['data']['DATA']['Header']['OrgType'] == 'OFFICE') {
                $_Veterinary['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $_Veterinary['office_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
            }

            $_VeterinaryDetailList = $params['obj']['VeterinaryDetailList'];
            unset($_Veterinary['veterinary_detail']);

            // get region from cooperative id
            $Cooperative = CooperativeService::getData($_Veterinary['cooperative_id']);
            $_Veterinary['region_id'] = $Cooperative['region_id'];

            // print_r($Veterinary);exit;
            $id = VeterinaryService::updateData($_Veterinary);

            // update veterinary detail & item
            foreach ($_VeterinaryDetailList as $key => $value) {
                $_VeterinaryItemList = $value['veterinary_item'];

                $DairyFarming = DairyFarmingService::getData($value['dairy_farming_id']);
                $value['farm_type'] = $DairyFarming['dairy_farming_type'];
                $value['veterinary_id'] = $id;
                unset($value['veterinary_item']);
                $veterinary_detail_id = VeterinaryService::updateDetailData($value);

                // update item
                foreach ($_VeterinaryItemList as $_key => $_value) {
                    $_value['veterinary_id'] = $id;
                    $_value['veterinary_detail_id'] = $veterinary_detail_id;
                    $veterinary_item_id = VeterinaryService::updateItemData($_value);
                }
            }

            $this->data_result['DATA']['id'] = $id;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function removeData($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $id = $params['obj']['id'];
            $result = VeterinaryService::removeData($id);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function removeDetailData($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $id = $params['obj']['id'];
            $result = VeterinaryService::removeDetailData($id);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function removeItemData($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $id = $params['obj']['id'];
            $result = VeterinaryService::removeItemData($id);

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
            $result = VeterinaryService::updateDataApprove($id, $data);

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
