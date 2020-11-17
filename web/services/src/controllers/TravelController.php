<?php

namespace App\Controller;

use App\Service\TravelService;
use App\Service\CooperativeService;
use App\Service\MasterGoalService;

class TravelController extends Controller {

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

            $Data = TravelService::loadDataApprove($user_session['UserID']);

            $this->data_result['DATA']['DataList'] = $Data;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getDetailList($request, $response, $args) {
        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();
            $condition = $params['obj']['condition'];
            $region_selected = $condition['Region'];
            $regions = $params['obj']['region'];

            $MasterGoalList = MasterGoalService::getList('Y', 'ท่องเที่ยว');

            $lastday = date("t", strtotime($condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-01'));
            
            $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-' . $lastday; // 
            $fromTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';
            // exit;
            $process_date = $fromTime;
            $lastday_process = date('t', strtotime($condition['YearTo'] . '-' . 
                                            str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-' . 
                                            str_pad(1, 2, "0", STR_PAD_LEFT)));

            $day = 0;
            $month = $condition['MonthFrom'];

            $DataList = [];
            while($process_date != $toTime){

                $day++;

                $process_date = date('Y-m-d', strtotime($condition['YearTo'] . '-' . 
                                            str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . 
                                            str_pad($day, 2, "0", STR_PAD_LEFT)));

                // get data
                $data = [];

                $data[]['label'] = $process_date;

                foreach ($MasterGoalList as $key => $value) {
                    # code...

                    $result = TravelService::getDetailByDay($condition['YearTo'], $month, $day, $value['id']);
                    $data[]['label'] = empty($result['total_person_pay'])?0:$result['total_person_pay'];
                    $data[]['label'] = empty($result['unit_price'])?0:$result['unit_price'];
                    $data[]['label'] = empty($result['discount'])?0:$result['discount'];
                    $data[]['label'] = empty($result['total_price'])?0:$result['total_price'];

                }
                
                $DataList[]['detail'] = $data;

                if($day == $lastday_process){
                    $day = 0;
                    $month++;
                    $lastday_process = date('t', strtotime($condition['YearTo'] . '-' . 
                                            str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . 
                                            str_pad(1, 2, "0", STR_PAD_LEFT)));
                }
                
            }

            // Labels
            $Labels = [];
            $Labels2 = [];
            foreach ($MasterGoalList as $key => $value) {
                $Labels[]['label'] = $value['goal_name'];

                $Labels2[]['label'] = 'จำนวนผู้เข้าชมที่ต้องจ่าย';
                $Labels2[]['label'] = 'ราคาต่อหน่วย';
                $Labels2[]['label'] = 'ส่วนลด';
                $Labels2[]['label'] = 'ราคารวม';
                
            }

            $this->data_result['DATA']['Labels'] = $Labels;
            $this->data_result['DATA']['Labels2'] = $Labels2;
            $this->data_result['DATA']['DataList'] = $DataList;

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
            $FooterSummary = $Result['FooterSummary'];
            // print_r($DataList);
            // exit;

            $this->data_result['DATA']['DataList'] = $DataList;
            $this->data_result['DATA']['Summary'] = $Summary;
            $this->data_result['DATA']['FooterSummary'] = $FooterSummary;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMonthDataList($condition, $regions) {

        $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28'; // .TravelController::getLastDayOfMonth($ym);
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
        $FooterSummary = [];

        // get master
        $MasterGoalList = MasterGoalService::getList('Y', 'ท่องเที่ยว');
        $Master = [];
        foreach ($MasterGoalList as $key => $value) {
            $data = [];
            $data['goal_id'] = $value['id'];
            $data['label'] = $value['goal_name'];
            $data['field_amount'] = 'total_person_pay';
            $data['field_price'] = 'total_price';
            array_push($Master, $data);
        }
        // $Master = [
        //     ['label' => 'บุคคลทั่วไป (ผู้ใหญ่)'
        //         , 'field_amount' => 'adult_pay'
        //         , 'field_price' => 'adult_price'
        //     ],
        //     ['label' => 'บุคคลทั่วไป (เด็ก)'
        //         , 'field_amount' => 'child_pay'
        //         , 'field_price' => 'child_price'
        //     ],
        //     ['label' => 'นักศึกษา'
        //         , 'field_amount' => 'student_pay'
        //         , 'field_price' => 'student_price'
        //     ]
        // ];
        for ($i = 0; $i < $diffMonth; $i++) {

            // Prepare condition
            $curYear = $condition['YearTo'];
            $beforeYear = $condition['YearTo'] - 1;

            // Loop User Regions
            foreach ($Master as $key => $value) {

                // $region_id = $value['label'];
                $field_amount = $value['field_amount'];
                $field_price = $value['field_price'];
                $goal_id = $value['goal_id'];

                $monthName = TravelController::getMonthName($curMonth);

                $data = [];
                $data['RegionName'] = $value['label'];
                $data['Month'] = $monthName;

                // get cooperative type

                $Current = TravelService::getMainList($curYear, $curMonth, $field_amount, $field_price, $goal_id);
                $data['CurrentAmount'] = floatval($Current['sum_amount']);
                $data['CurrentBaht'] = floatval($Current['sum_baht']);
                $FooterSummary['CurrentAmount'] += $data['CurrentAmount'];
                $FooterSummary['CurrentBaht'] += $data['CurrentBaht'];

                $Before = TravelService::getMainList($beforeYear, $curMonth, $field_amount, $field_price, $goal_id);
                $data['BeforeAmount'] = floatval($Before['sum_amount']);
                $data['BeforeBaht'] = floatval($Before['sum_baht']);
                $FooterSummary['BeforeAmount'] += $data['BeforeAmount'];
                $FooterSummary['BeforeBaht'] += $data['BeforeBaht'];

                $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                $data['DiffAmount'] = $DiffAmount;
                $FooterSummary['DiffAmount'] += $data['DiffAmount'];

                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                    $data['DiffAmountPercentage'] = 100;
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;
                $FooterSummary['DiffBaht'] += $data['DiffBaht'];

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
                    , 'region_id' => $region_id
                ];

                array_push($DataList, $data);

                $DataSummary['SummaryCurrentTravelAmount'] = $DataSummary['SummaryCurrentTravelAmount'] + $data['CurrentAmount'];
                $DataSummary['SummaryBeforTravelAmount'] = $DataSummary['SummaryBeforTravelAmount'] + $data['BeforeAmount'];

                $DataSummary['SummaryCurrentTravelIncome'] = $DataSummary['SummaryCurrentTravelIncome'] + $data['CurrentBaht'];
                $DataSummary['SummaryBeforeTravelIncome'] = $DataSummary['SummaryBeforeTravelIncome'] + $data['BeforeBaht'];

                $DataSummary['SummaryTravelAmountPercentage'] = $DataSummary['SummaryTravelAmountPercentage'] + $DataSummary['SummaryTravelAmount'] + $DataSummary['SummaryBeforTravelAmount'];
                $DataSummary['SummaryTravelIncomePercentage'] = $DataSummary['SummaryTravelIncomePercentage'] + $DataSummary['SummaryTravelIncome'] + $DataSummary['SummaryBeforeTravelIncome'];
            }

            $curMonth++;
        }
        if ($DataSummary['SummaryBeforTravelAmount'] != 0) {
            $DataSummary['SummaryTravelAmountPercentage'] = (($DataSummary['SummaryCurrentTravelAmount'] - $DataSummary['SummaryBeforTravelAmount']) / $DataSummary['SummaryBeforTravelAmount']) * 100;
        } else {
            $DataSummary['SummaryTravelAmountPercentage'] = 100;
        }
        if ($DataSummary['SummaryBeforeTravelIncome'] != 0) {
            $DataSummary['SummaryTravelIncomePercentage'] = (($DataSummary['SummaryCurrentTravelIncome'] - $DataSummary['SummaryBeforeTravelIncome']) / $DataSummary['SummaryBeforeTravelIncome']) * 100;
        } else {
            $DataSummary['SummaryTravelIncomePercentage'] = 100;
        }

        if ($FooterSummary['BeforeAmount'] != 0) {
            $FooterSummary['DiffAmountPercentage'] = (($FooterSummary['CurrentAmount'] - $FooterSummary['BeforeAmount']) / $FooterSummary['BeforeAmount']) * 100;
        }  else if(empty($FooterSummary['BeforeAmount']) && !empty($FooterSummary['CurrentAmount'])){
            $FooterSummary['DiffAmountPercentage'] = 100;
        }

        if ($FooterSummary['BeforeBaht'] != 0) {
            $FooterSummary['DiffBahtPercentage'] = (($FooterSummary['CurrentBaht'] - $FooterSummary['BeforeBaht']) / $FooterSummary['BeforeBaht'])  * 100;
        } else if(empty($FooterSummary['BeforeBaht']) && !empty($FooterSummary['CurrentBaht'])){
            $FooterSummary['DiffBahtPercentage'] = 100;
        }

        return ['DataList' => $DataList, 'Summary' => $DataSummary, 'FooterSummary' => $FooterSummary];
    }

    public function getQuarterDataList($condition, $regions) {

        // get loop to query
        // $loop = intval($condition['YearTo'] . $condition['QuarterTo']) - intval($condition['YearFrom'] . $condition['QuarterFrom']) + 1;
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

        // get master goal
        $Master = [
            ['label' => 'บุคคลทั่วไป (ผู้ใหญ่)'
                , 'field_amount' => 'adult_pay'
                , 'field_price' => 'adult_price'
            ],
            ['label' => 'บุคคลทั่วไป (เด็ก)'
                , 'field_amount' => 'child_pay'
                , 'field_price' => 'child_price'
            ],
            ['label' => 'นักศึกษา'
                , 'field_amount' => 'student_pay'
                , 'field_price' => 'student_price'
            ]
        ];

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
            // foreach ($regions as $key => $value) {

            foreach ($Master as $key => $value) {

                $field_amount = $value['field_amount'];
                $field_price = $value['field_price'];


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

                    $Current = TravelService::getMainList($curYear, $curMonth, $field_amount, $field_price);
                    $SumCurrentAmount += floatval($Current['sum_amount']);
                    $SumCurrentBaht += floatval($Current['sum_baht']);

                    $Before = TravelService::getMainList($beforeYear, $beforeMonth, $field_amount, $field_price);
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

                $monthName = TravelController::getMonthName($curMonth);

                $data = [];
                $data['RegionName'] = $value['label'];
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

                $DataSummary['SummaryCurrentTravelAmount'] = $DataSummary['SummaryCurrentTravelAmount'] + $data['CurrentAmount'];
                $DataSummary['SummaryBeforTravelAmount'] = $DataSummary['SummaryBeforTravelAmount'] + $data['BeforeAmount'];

                $DataSummary['SummaryCurrentTravelIncome'] = $DataSummary['SummaryCurrentTravelIncome'] + $data['CurrentBaht'];
                $DataSummary['SummaryBeforeTravelIncome'] = $DataSummary['SummaryBeforeTravelIncome'] + $data['BeforeBaht'];

                $DataSummary['SummaryTravelAmountPercentage'] = $DataSummary['SummaryTravelAmountPercentage'] + $DataSummary['SummaryCurrentCowBreedAmount'] + $DataSummary['SummaryBeforeCowBreedAmount'];
                $DataSummary['SummaryTravelIncomePercentage'] = $DataSummary['SummaryTravelIncomePercentage'] + $DataSummary['SummaryCurrentCowBreedIncome'] + $DataSummary['SummaryBeforeCowBreedIncome'];
            }
            // }

            $curQuarter++;
            if ($curQuarter > 4) {
                $curQuarter = 1;
            }
        }

        if ($DataSummary['SummaryBeforTravelAmount'] != 0) {
            $DataSummary['SummaryTravelAmountPercentage'] = (($DataSummary['SummaryCurrentTravelAmount'] - $DataSummary['SummaryBeforTravelAmount']) / $DataSummary['SummaryBeforTravelAmount']) * 100;
        } else {
            $DataSummary['SummaryTravelAmountPercentage'] = 100;
        }
        if ($DataSummary['SummaryBeforeTravelIncome'] != 0) {
            $DataSummary['SummaryTravelIncomePercentage'] = (($DataSummary['SummaryCurrentTravelIncome'] - $DataSummary['SummaryBeforeTravelIncome']) / $DataSummary['SummaryBeforeTravelIncome']) * 100;
        } else {
            $DataSummary['SummaryTravelIncomePercentage'] = 100;
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
        $curYear = $condition['YearFrom'];

        // get master goal
        $MasterGoalList = MasterGoalService::getList('Y', 'ฝึกอบรม');

        for ($i = 0; $i < $loop; $i++) {

            // Loop User Regions
            // foreach ($regions as $key => $value) {

            foreach ($MasterGoalList as $k => $v) {

                $training_cowbreed_type_id = $v['id'];
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

                    $Current = TravelService::getMainList($curYear, $curMonth, $field_amount, $field_price);
                    $SumCurrentAmount += floatval($Current['sum_amount']);
                    $SumCurrentBaht += floatval($Current['sum_baht']);

                    $Before = TravelService::getMainList($beforeYear, $beforeMonth, $field_amount, $field_price);
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
                $data['CowBreedName'] = $v['goal_name'];
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

                $DataSummary['SummaryCurrentCowBreedAmount'] = $DataSummary['SummaryCurrentCowBreedAmount'] + $data['CurrentAmount'];
                $DataSummary['SummaryBeforeCowBreedAmount'] = $DataSummary['SummaryBeforeCowBreedAmount'] + $data['BeforeAmount'];

                $DataSummary['SummaryCurrentCowBreedIncome'] = $DataSummary['SummaryCurrentCowBreedIncome'] + $data['CurrentBaht'];
                $DataSummary['SummaryBeforeCowBreedIncome'] = $DataSummary['SummaryBeforeCowBreedIncome'] + $data['BeforeBaht'];

                $DataSummary['SummaryCowBreedAmountPercentage'] = $DataSummary['SummaryCowBreedAmountPercentage'] + $DataSummary['SummaryCurrentCowBreedAmount'] + $DataSummary['SummaryBeforeCowBreedAmount'];
                $DataSummary['SummaryCowBreedIncomePercentage'] = $DataSummary['SummaryCowBreedIncomePercentage'] + $DataSummary['SummaryCurrentCowBreedIncome'] + $DataSummary['SummaryBeforeCowBreedIncome'];
            }
            // }

            $curYear++;
        }

        if ($DataSummary['SummaryBeforTravelAmount'] != 0) {
            $DataSummary['SummaryTravelAmountPercentage'] = (($DataSummary['SummaryCurrentTravelAmount'] - $DataSummary['SummaryBeforTravelAmount']) / $DataSummary['SummaryBeforTravelAmount']) * 100;
        } else {
            $DataSummary['SummaryTravelAmountPercentage'] = 100;
        }
        if ($DataSummary['SummaryBeforeTravelIncome'] != 0) {
            $DataSummary['SummaryTravelIncomePercentage'] = (($DataSummary['SummaryCurrentTravelIncome'] - $DataSummary['SummaryBeforeTravelIncome']) / $DataSummary['SummaryBeforeTravelIncome']) * 100;
        } else {
            $DataSummary['SummaryTravelIncomePercentage'] = 100;
        }
        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getData($request, $response, $args) {
        try {
            $params = $request->getParsedBody();

            $id = $params['obj']['id'];
            $days = $params['obj']['days'];
            $months = $params['obj']['months'];
            $years = $params['obj']['years'];

            if (!empty($id)) {
                $_Data = TravelService::getDataByID($id);
            } else {
                $_Data = TravelService::getData($days, $months, $years);
            }
            // print_r($_Data['travelDetail']);
            // get item
            $Data = [];
            foreach ($_Data['travelDetail'] as $key => $value) {
                $value['Item'] = TravelService::getItem($value['id']);
                array_push($Data, $value);
            }

            $_Data['travel_detail'] = $Data;
            // exit;
            $this->data_result['DATA']['Data'] = $_Data;

            return $this->returnResponse(200, $this->data_result, $response, true);
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

            $id = TravelService::updateData($_Data);

            foreach ($_Detail as $key => $value) {
                $value['travel_id'] = $id;
                $TravelItem = $value['Item'];
                unset($value['Item']);
                $detail_id = TravelService::updateDetailData($value);

                foreach ($TravelItem as $k => $v) {
                    $item = [];
                    $item['id'] = $v['id'];
                    $item['travel_id'] = $id;
                    $item['travel_detail_id'] = $detail_id;
                    $item['goal_id'] = $v['goal_id'];
                    $item['total_person_pay'] = $v['total_person_pay'];
                    $item['unit_price'] = $v['unit_price'];
                    $item['discount'] = $v['discount'];
                    $item['total_price'] = $v['total_price'];

                    TravelService::updateItemData($item);
                }
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
            $result = TravelService::removeDetailData($id);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getAnnuallyDataListreport($condition, $regions) {


        $curYear = $condition['YearFrom'];

        $beforeYear = $curYear - 1;
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $DataList = [];
        $DataSummary = [];


        // get master goal
        $Master = [
            ['label' => 'บุคคลทั่วไป (ผู้ใหญ่)'
                , 'field_amount' => 'adult_pay'
                , 'field_price' => 'adult_price'
            ],
            ['label' => 'บุคคลทั่วไป (เด็ก)'
                , 'field_amount' => 'child_pay'
                , 'field_price' => 'child_price'
            ],
            ['label' => 'นักศึกษา'
                , 'field_amount' => 'student_pay'
                , 'field_price' => 'student_price'
            ]
        ];



        // Loop User Regions
        // foreach ($regions as $key => $value) {

        foreach ($Master as $k => $v) {

//                $training_cowbreed_type_id = $v['id'];
//                $region_id = $value['RegionID'];
            $field_amount = $v['field_amount'];
            $field_price = $v['field_price'];



            $SumCurrentAmount = 0;
            $SumCurrentBaht = 0;
            $SumBeforeAmount = 0;
            $SumBeforeBaht = 0;
            // loop get quarter sum data
            for ($j = 0; $j < 12; $j++) {
                $curMonth = $monthList[$j];

                $Current = TravelService::getMainList($curYear - $yearList[$j], $curMonth, $field_amount, $field_price);
                $SumCurrentAmount += floatval($Current['sum_amount']);
                $SumCurrentBaht += floatval($Current['sum_baht']);

                $Before = TravelService::getMainList($beforeYear - $yearList[$j], $curMonth, $field_amount, $field_price);
                $SumBeforeAmount += floatval($Before['sum_amount']);
                $SumBeforeBaht += floatval($Before['sum_baht']);
            }

            $data = [];
            $data['RegionName'] = $v['label'];
            //$data['CowBreedName'] = $v['goal_name'];
            $data['Year'] = $curYear + 543;

            $data['CurrentAmount'] = $SumCurrentAmount;
            $data['CurrentBaht'] = $SumCurrentBaht;

            $data['BeforeAmount'] = $SumBeforeAmount;
            $data['BeforeBaht'] = $SumBeforeBaht;

            $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
            $data['DiffAmount'] = $DiffAmount;
            $data['DiffAmountPercentage'] = 0;

            $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
            $data['DiffBaht'] = $DiffBaht;
            $data['DiffBahtPercentage'] = 0;

            $data['CreateDate'] = $CurrentCowService['update_date'];
            $data['ApproveDate'] = '';
            $data['Status'] = '';
            $data['Description'] = ['months' => $curMonth
                , 'years' => $curYear
                , 'region_id' => $region_id
            ];

            array_push($DataList, $data);

            $DataSummary['SummaryCurrentTravelAmount'] = $DataSummary['SummaryCurrentTravelAmount'] + $data['CurrentAmount'];
            $DataSummary['SummaryBeforTravelAmount'] = $DataSummary['SummaryBeforTravelAmount'] + $data['BeforeAmount'];

            $DataSummary['SummaryCurrentTravelIncome'] = $DataSummary['SummaryCurrentTravelIncome'] + $data['CurrentBaht'];
            $DataSummary['SummaryBeforeTravelIncome'] = $DataSummary['SummaryBeforeTravelIncome'] + $data['BeforeBaht'];

            $DataSummary['SummaryTravelAmountPercentage'] = $DataSummary['SummaryTravelAmountPercentage'] + $DataSummary['SummaryCurrentCowBreedAmount'] + $DataSummary['SummaryBeforeCowBreedAmount'];
            $DataSummary['SummaryTravelIncomePercentage'] = $DataSummary['SummaryTravelIncomePercentage'] + $DataSummary['SummaryCurrentCowBreedIncome'] + $DataSummary['SummaryBeforeCowBreedIncome'];
        }
        // }



        return ['DataList' => $DataList, 'Summary' => $DataSummary];
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
            $result = TravelService::updateDataApprove($id, $data);

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
