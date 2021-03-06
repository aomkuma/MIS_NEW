<?php

namespace App\Controller;

use App\Service\MBIService;
use App\Service\CooperativeService;

class MBIController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function getLastDayOfMonth($time) {
        return $date = date("t", strtotime($time . '-' . '01'));
    }

    public function getSQLDateFormat($date){
        $this->logger->info($date);
        if(empty($date)){
            return '';
        }

        $date_arr = explode('/', $date);
        $day = filter_var($date_arr[0], FILTER_SANITIZE_NUMBER_INT);
        $month = filter_var($date_arr[1], FILTER_SANITIZE_NUMBER_INT);
        $year = filter_var($date_arr[2], FILTER_SANITIZE_NUMBER_INT);
        if($day < 10){
            $day = '0' . $day;
        }

        if($month < 10){
            $month = '0' . $month;
        }

        return ($year - 543) . '-' . $month . '-' . $day;
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

    public function checkRegion($regionID) {
        switch ($regionID) {
            case '1':
                return 'สำนักงาน อ.ส.ค. ภาคกลาง';
                break;
            case '2':
                return 'สำนักงาน อ.ส.ค. ภาคกลาง';
                break;
            case '3':
                return 'สำนักงาน อ.ส.ค. ภาคกลาง';
                break;
            case '4':
                return 'สำนักงาน อ.ส.ค. ภาคใต้';
                break;
            case '5':
                return 'สำนักงาน อ.ส.ค. ภาคตะวันออกเฉียงเหนือ';
                break;
            case '6':
                return 'สำนักงาน อ.ส.ค. ภาคเหนือตอนล่าง';
                break;
            case '7':
                return 'สำนักงาน อ.ส.ค. ภาคเหนือตอนบน';
                break;
            default: break;
        }
    }

    public function checkProvince($regionID) {
        switch ($regionID) {
            case '1':
                return 'จังหวัดสระบุรี';
                break;
            case '2':
                return 'จังหวัดสระบุรี';
                break;
            case '3':
                return 'จังหวัดสระบุรี';
                break;
            case '4':
                return 'จังหวัดประจวบคีรีขันธ์';
                break;
            case '5':
                return 'จังหวัดขอนแก่น';
                break;
            case '6':
                return 'จังหวัดสุโขทัย';
                break;
            case '7':
                return 'จังหวัดเชียงใหม่';
                break;
            default: break;
        }
    }

    public function getProviceNameByOffice($office_name) {
        switch ($office_name) {
            case 'สำนักงาน อ.ส.ค. ภาคกลาง':
                return 'จังหวัดสระบุรี';
                break;
            case 'สำนักงาน อ.ส.ค. ภาคใต้':
                return 'จังหวัดประจวบคีรีขันธ์';
                break;
            case 'สำนักงาน อ.ส.ค. ภาคตะวันออกเฉียงเหนือ':
                return 'จังหวัดขอนแก่น';
                break;
            case 'สำนักงาน อ.ส.ค. ภาคเหนือตอนล่าง':
                return 'จังหวัดสุโขทัย';
                break;
            case 'สำนักงาน อ.ส.ค. ภาคเหนือตอนบน':
                return 'จังหวัดเชียงใหม่';
                break;
            default: break;
        }
        
    }

    public function getListMBIMonth($request, $response, $args) {
        // error_reporting(E_ERROR);
        //     error_reporting(E_ALL);
        //     ini_set('display_errors','On');

        try {
            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            $regions = $params['obj']['region'];

            $RegionList = [];
            foreach ($regions as $key => $value) {
                if($value['RegionID'] != 1 && $value['RegionID'] != 2){
                    $RegionList[] = $value;
                }
            }

            $regions = $RegionList;

            $monthFrom = $params['obj']['condition']['MonthFrom'];
            $monthTo = $params['obj']['condition']['MonthTo'];
            $years = $params['obj']['condition']['YearTo'];
            $selected_year = $params['obj']['condition']['YearTo'];

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

            $DataList = [];
            $DataSummary = [];
            $TotalLine = [];

            $beforYear = intval($years) - 1;
            $curMonth = $monthFrom;

            for ($i = 0; $i < $diffMonth; $i++) {

                foreach ($regions as $key => $value) {

                    $monthName = MBIController::getMonthName($curMonth);
                    $region = MBIController::checkProvince($value['RegionID']);
                    $region_id = $value['RegionID'];

                    $data = [];
                    $data['RegionName'] = MBIController::checkRegion($value['RegionID']);
                    $data['Month'] = $monthName;

                    // Cur year

                    $fromTime = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
                    $ymTo = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
                    $toTime = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-' . $this->getLastDayOfMonth($ymTo);

                    $Current = MBIService::getListMBI($fromTime, $toTime, $region);

                    $data['CurrentAmount'] = floatval($Current['sum_amount']);
                    $TotalLine['CurrentAmount'] = $TotalLine['CurrentAmount'] + $data['CurrentAmount'];
                    $data['CurrentBaht'] = floatval($Current['sum_baht']);
                    $TotalLine['CurrentBaht'] = $TotalLine['CurrentBaht'] + $data['CurrentBaht'];
                    // Before year

                    $fromTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
                    $ymTo = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
                    $toTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-' . $this->getLastDayOfMonth($ymTo);

                    $Before = MBIService::getListMBI($fromTime, $toTime, $region);
                    $data['BeforeAmount'] = floatval($Before['sum_amount']);
                    $TotalLine['BeforeAmount'] = $TotalLine['BeforeAmount'] + $data['BeforeAmount'];
                    $data['BeforeBaht'] = floatval($Before['sum_baht']);
                    $TotalLine['BeforeBaht'] = $TotalLine['BeforeBaht'] + $data['BeforeBaht'];

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;
                    $TotalLine['DiffAmount'] = $TotalLine['DiffAmount'] + $data['DiffAmount'];

                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;
                    $TotalLine['DiffBaht'] = $TotalLine['DiffBaht'] + $data['DiffBaht'];

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    }

                    $data['Description'] = ['months' => $curMonth
                        , 'years' => $selected_year
                        , 'region_id' => $region_id
                    ];

                    array_push($DataList, $data);

                    $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                    $DataSummary['SummaryValue'] = $DataSummary['SummaryValue'] + $data['CurrentBaht'];

                }

                $curMonth++;
            }
            $TotalLine['RegionName'] = 'รวม';

            if ($TotalLine['BeforeAmount'] != 0) {
                $TotalLine['DiffAmountPercentage'] = (($TotalLine['CurrentAmount'] - $TotalLine['BeforeAmount']) / $TotalLine['BeforeAmount']) * 100;
            } else if (empty($TotalLine['BeforeAmount']) && !empty($TotalLine['CurrentAmount'])) {
                $TotalLine['DiffAmountPercentage'] = 100;
            }

            if ($TotalLine['BeforeBaht'] != 0) {
                $TotalLine['DiffBahtPercentage'] = (($TotalLine['CurrentBaht'] - $TotalLine['BeforeBaht']) / $TotalLine['BeforeBaht']) * 100;
            } else if (empty($TotalLine['BeforeBaht']) && !empty($TotalLine['CurrentBaht'])) {
                $TotalLine['DiffBahtPercentage'] = 100;
            }

            array_push($DataList, $TotalLine);
            // exit;

            $this->data_result['DATA']['List'] = $DataList;
            $this->data_result['DATA']['Summary'] = $DataSummary;


            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getListMBIMonthdata($condition, $regions) {

        $monthFrom = 10;
        $yearst = $condition['YearTo'];
        if ($condition['MonthTo'] != 10 && $condition['MonthTo'] != 11 && $condition['MonthTo'] != 12) {
            $yearst--;
        }

        $ymFrom = ($yearst) . '-' . str_pad(10, 2, "0", STR_PAD_LEFT);
        $fromTime = ($yearst) . '-' . str_pad(10, 2, "0", STR_PAD_LEFT) . '-01';
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28' ; // .CowGroupController::getLastDayOfMonth($ym);
        $years = $condition['YearTo'];


        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else{
            $diffMonth += 1;
        }
            $DataList = [];
        $DataSummary = [];
        $DataOthersheet = [];
        $beforYear = intval($yearst) - 1;
        $curMonth = $monthFrom;

        for ($i = 0; $i < $diffMonth; $i++) {

            //    foreach ($regions as $key => $value) {

            $monthName = MBIController::getMonthName($curMonth);
            //   $region = MBIController::checkRegion($value['RegionID']);
            //    $region_id = $value['RegionID'];

            $data = [];
            // $data['RegionName'] = $region;
            $data['Month'] = $monthName;

            // Cur year

            $fromTime = $yearst . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
            $ymTo = $yearst . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
            $toTime = $yearst . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-' . MBIController::getLastDayOfMonth($ymTo);

            $Current = MBIService::getListMBIreoprt($fromTime, $toTime);

            $data['CurrentAmount'] = floatval($Current['sum_amount']);
            $data['CurrentBaht'] = floatval($Current['sum_baht']);

            // Before year

            $fromTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
            $ymTo = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
            $toTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-' . MBIController::getLastDayOfMonth($ymTo);

            $Before = MBIService::getListMBIreoprt($fromTime, $toTime);
            $data['BeforeAmount'] = floatval($Before['sum_amount']);
            $data['BeforeBaht'] = floatval($Before['sum_baht']);

            $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
            $data['DiffAmount'] = $DiffAmount;
            $data['DiffAmountPercentage'] = 0;
            $data['DiffBahtPercentage'] = 0;
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

            $data['Description'] = ['months' => $curMonth
                , 'years' => $years
                , 'region_id' => $region_id
            ];

            array_push($DataList, $data);

            $DataSummary['SummaryAmount'] += $data['CurrentAmount'];
            $DataSummary['SummaryBaht'] += $data['CurrentBaht'];
            $DataSummary['SummaryBeforeAmount'] += $data['BeforeAmount'];
            $DataSummary['SummaryBeforeBaht'] += $data['BeforeBaht'];
            $DataSummary['SummaryDiffAmount'] += $data['DiffAmount'];
            $DataSummary['SummaryDiffBaht'] += $data['DiffBaht'];
            if ($DataSummary['SummaryBeforeAmount'] != 0) {
                $DataSummary['DiffAmountPercentage'] = (($DataSummary['SummaryAmount'] - $DataSummary['SummaryBeforeAmount']) / $DataSummary['SummaryBeforeAmount']) * 100;
            } else if (empty($data['SummaryBeforeAmount']) && !empty($DataSummary['SummaryAmount'])) {
                $DataSummary['DiffAmountPercentage'] = 100;
            }
            if ($DataSummary['SummaryBeforeBaht'] != 0) {
                $DataSummary['DiffBahtPercentage'] = (($DataSummary['SummaryBaht'] - $DataSummary['SummaryBeforeBaht']) / $DataSummary['SummaryBeforeBaht']) * 100;
            } else if (empty($DataSummary['SummaryBeforeBaht']) && !empty($DataSummary['SummaryBaht'])) {
                $DataSummary['DiffBahtPercentage'] = 100;
            }



            //    }

            $curMonth++;
            if ($curMonth == 13) {
                $curMonth = 1;
                $yearst++;
            }
        }

        // exit;

        return ['DataList' => $DataList, 'Summary' => $DataSummary, 'DataOthersheet' => $DataOthersheet];
    }

    public function getListMBIMonthdataOther($condition, $regions) {
        $years = $condition['YearTo'];

        $region = [3, 4, 5, 6, 7];
        $monthFrom = 10;
        $yearst = $condition['YearTo'];
        if ($condition['MonthTo'] != 10 && $condition['MonthTo'] != 11 && $condition['MonthTo'] != 12) {
            $yearst--;
        }
        $dateforvendor = ($condition['YearTo']) . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-01';
        $dateendforvendor = ($condition['YearTo']) . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-' . MBIController::getLastDayOfMonth($dateforvendor);
        $dateforvendorcol = ($yearst) . '-' . str_pad($monthFrom, 2, "0", STR_PAD_LEFT) . '-01';
        $dateendforvendorcol = ($condition['YearTo']) . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-' . MBIController::getLastDayOfMonth(($yearst) . '-' . str_pad($monthFrom, 2, "0", STR_PAD_LEFT));
       
        
        $ymFrom = ($yearst) . '-' . str_pad($monthFrom, 2, "0", STR_PAD_LEFT);
        $fromTime = ($yearst) . '-' . str_pad($monthFrom, 2, "0", STR_PAD_LEFT) . '-01';

        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-' . MBIController::getLastDayOfMonth($ymTo); // .CowGroupController::getLastDayOfMonth($ym);



        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
//        $test = ['-/////////////////////////////////////////////-', $date1, $date2, $diffMonth];
//        print_r($test);
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else{
            $diffMonth += 1;
        }
        
        // print_r(MBIController::getLastDayOfMonth($ymTo));



        $DataOthersheet = [];


        foreach ($region as $reg) {
            $regionname = MBIController::checkRegion($reg);
            $subdetail = MBIService::getListMBIByVendorreport($dateforvendor, $dateendforvendor, $regionname);
            $collectsubdetail = MBIService::getListMBIByVendorreport($dateforvendorcol, $dateendforvendorcol, $regionname);

            $curMonth = $monthFrom;
            $DataList = [];
            $DataSummary = [];
            $yearst = $condition['YearTo'];
            if ($condition['MonthTo'] != 10 && $condition['MonthTo'] != 11 && $condition['MonthTo'] != 12) {
                $yearst--;
            }
            $beforYear = intval($yearst) - 1;
            for ($i = 0; $i < $diffMonth; $i++) {

                //    foreach ($regions as $key => $value) {

                $monthName = MBIController::getMonthName($curMonth);

                //    $region_id = $value['RegionID'];

                $data = [];
                $data['RegionName'] = $regionname;
                $data['Month'] = $monthName;

                // Cur year

                $fromTime = $yearst . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
                $ymTo = $yearst . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
                $toTime = $yearst . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-' . MBIController::getLastDayOfMonth($ymTo);

                $Current = MBIService::getListMBI($fromTime, $toTime, $regionname);
                //  print_r($Current);
                $data['CurrentAmount'] = floatval($Current['sum_amount']);
                $data['CurrentBaht'] = floatval($Current['sum_baht']);

                // Before year

                $fromTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
                $ymTo = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
                $toTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-' . MBIController::getLastDayOfMonth($ymTo);

                $Before = MBIService::getListMBI($fromTime, $toTime, $regionname);
                $data['BeforeAmount'] = floatval($Before['sum_amount']);
                $data['BeforeBaht'] = floatval($Before['sum_baht']);

                $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                $data['DiffAmount'] = $DiffAmount;
                $data['DiffAmountPercentage'] = 0;
                $data['DiffBahtPercentage'] = 0;
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

                $data['Description'] = ['months' => $curMonth
                    , 'years' => $yearst
                    , 'region_id' => $region_id
                ];

                array_push($DataList, $data);

                $DataSummary['SummaryAmount'] += $data['CurrentAmount'];
                $DataSummary['SummaryBaht'] += $data['CurrentBaht'];
                $DataSummary['SummaryBeforeAmount'] += $data['BeforeAmount'];
                $DataSummary['SummaryBeforeBaht'] += $data['BeforeBaht'];
                $DataSummary['SummaryDiffAmount'] += $data['DiffAmount'];
                $DataSummary['SummaryDiffBaht'] += $data['DiffBaht'];
                if ($DataSummary['SummaryBeforeAmount'] != 0) {
                    $DataSummary['DiffAmountPercentage'] = (($DataSummary['SummaryAmount'] - $DataSummary['SummaryBeforeAmount']) / $DataSummary['SummaryBeforeAmount']) * 100;
                } else if (empty($data['SummaryBeforeAmount']) && !empty($DataSummary['SummaryAmount'])) {
                    $DataSummary['DiffAmountPercentage'] = 100;
                }
                if ($DataSummary['SummaryBeforeBaht'] != 0) {
                    $DataSummary['DiffBahtPercentage'] = (($DataSummary['SummaryBaht'] - $DataSummary['SummaryBeforeBaht']) / $DataSummary['SummaryBeforeBaht']) * 100;
                } else if (empty($DataSummary['SummaryBeforeBaht']) && !empty($DataSummary['SummaryBaht'])) {
                    $DataSummary['DiffBahtPercentage'] = 100;
                }

                $curMonth++;
                if ($curMonth == 13) {
                    $curMonth = 1;
                    $yearst++;
                }
            }
            $_data['data'] = $DataList;
            $_data['Summary'] = $DataSummary;
            $_data['subdetail'] = $subdetail;
            $_data['collectsubdetail'] = $collectsubdetail;
            array_push($DataOthersheet, $_data);
        }

        // exit;

        return ['DataOthersheet' => $DataOthersheet];
    }

    public function getListMBIQuarter($request, $response, $args) {
        // error_reporting(E_ERROR);
        //     error_reporting(E_ALL);
        //     ini_set('display_errors','On');

        try {
            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            $regions = $params['obj']['region'];

            $RegionList = [];
            foreach ($regions as $key => $value) {
                if($value['RegionID'] != 1 && $value['RegionID'] != 2){
                    $RegionList[] = $value;
                }
            }

            $regions = $RegionList;

            $quarter = $params['obj']['condition']['QuarterFrom'];
            $years = $params['obj']['condition']['YearTo'];
            $selected_year = $params['obj']['condition']['YearTo'];

            if ($quarter == '1') {
                $monthFrom = 10;
                $monthTo = 12;
                $years = intval($years) - 1;
            } else if ($quarter == '2') {
                $monthFrom = 1;
                $monthTo = 3;
            } else if ($quarter == '3') {
                $monthFrom = 4;
                $monthTo = 6;
            } else if ($quarter == '4') {
                $monthFrom = 7;
                $monthTo = 9;
            }

            $DataList = [];
            $DataSummary = [];
            $TotalLine = [];

            foreach ($regions as $key => $value) {

                $monthName = MBIController::getMonthName($curMonth);
                $region = MBIController::checkProvince($value['RegionID']);
                $region_id = $value['RegionID'];

                $data = [];
                $data['RegionName'] = MBIController::checkRegion($value['RegionID']);;
                $data['Month'] = $quarter;

                // Cur year
                $ymTo = $years . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT);
                $toTime = $years . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT) . '-28';
                $fromTime = $years . '-' . str_pad($monthFrom, 2, "0", STR_PAD_LEFT) . '-01';

                $Current = MBIService::getListMBI($fromTime, $toTime, $region);

                $data['CurrentAmount'] = floatval($Current['sum_amount']);
                $data['CurrentBaht'] = floatval($Current['sum_baht']);
                $TotalLine['CurrentBaht'] = $TotalLine['CurrentBaht'] + $data['CurrentBaht'];
                // Before year

                $ymTo = ($years - 1) . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT);
                $toTime = ($years - 1) . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT) . '-28';
                $fromTime = ($years - 1) . '-' . str_pad($monthFrom, 2, "0", STR_PAD_LEFT) . '-01';

                $Before = MBIService::getListMBI($fromTime, $toTime, $region);
                $data['BeforeAmount'] = floatval($Before['sum_amount']);
                $data['BeforeBaht'] = floatval($Before['sum_baht']);
                $TotalLine['BeforeBaht'] = $TotalLine['BeforeBaht'] + $data['BeforeBaht'];

                $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                $data['DiffAmount'] = $DiffAmount;

                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                    $data['DiffAmountPercentage'] = 100;
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;
                $TotalLine['DiffBaht'] = $TotalLine['DiffBaht'] + $data['DiffBaht'];

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    $data['DiffBahtPercentage'] = 100;
                }

                $data['Description'] = ['months' => $curMonth
                    , 'years' => $selected_year
                    , 'region_id' => $region_id
                ];

                array_push($DataList, $data);

                $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                $DataSummary['SummaryValue'] = $DataSummary['SummaryValue'] + $data['CurrentBaht'];
            }

            $TotalLine['RegionName'] = 'รวม';
            array_push($DataList, $TotalLine);
            // $curMonth++;
            // exit;

            $this->data_result['DATA']['List'] = $DataList;
            $this->data_result['DATA']['Summary'] = $DataSummary;


            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getListMBIYear($request, $response, $args) {
        // error_reporting(E_ERROR);
        //     error_reporting(E_ALL);
        //     ini_set('display_errors','On');

        try {
            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            $regions = $params['obj']['region'];

            $RegionList = [];
            foreach ($regions as $key => $value) {
                if($value['RegionID'] != 1 && $value['RegionID'] != 2){
                    $RegionList[] = $value;
                }
            }

            $regions = $RegionList;
            
            $years = $params['obj']['condition']['YearTo'];
            $selected_year = $params['obj']['condition']['YearTo'];

            $monthFrom = 10;
            $monthTo = 9;

            $DataList = [];
            $DataSummary = [];
            $TotalLine = [];

            foreach ($regions as $key => $value) {

                $monthName = MBIController::getMonthName($curMonth);
                $region = MBIController::checkProvince($value['RegionID']);
                $region_id = $value['RegionID'];

                $data = [];
                $data['RegionName'] = MBIController::checkRegion($value['RegionID']);;
                $data['Month'] = $quarter;

                // Cur year
                $ymTo = $years . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT);
                $toTime = $years . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT) . '-28';
                $fromTime = ($years - 1) . '-' . str_pad($monthFrom, 2, "0", STR_PAD_LEFT) . '-01';

                $Current = MBIService::getListMBI($fromTime, $toTime, $region);

                $data['CurrentAmount'] = floatval($Current['sum_amount']);
                $data['CurrentBaht'] = floatval($Current['sum_baht']);
                $TotalLine['CurrentBaht'] = $TotalLine['CurrentBaht'] + $data['CurrentBaht'];
                // Before year

                $ymTo = ($years - 1) . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT);
                $toTime = ($years - 1) . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT) . '-28';
                $fromTime = (($years - 1) - 1) . '-' . str_pad($monthFrom, 2, "0", STR_PAD_LEFT) . '-01';

                $Before = MBIService::getListMBI($fromTime, $toTime, $region);
                $data['BeforeAmount'] = floatval($Before['sum_amount']);
                $data['BeforeBaht'] = floatval($Before['sum_baht']);
                $TotalLine['BeforeBaht'] = $TotalLine['BeforeBaht'] + $data['BeforeBaht'];

                $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                $data['DiffAmount'] = $DiffAmount;

                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                    $data['DiffAmountPercentage'] = 100;
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;
                $TotalLine['DiffBaht'] = $TotalLine['DiffBaht'] + $data['DiffBaht'];

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    $data['DiffBahtPercentage'] = 100;
                }

                $data['Description'] = ['months' => $curMonth
                    , 'years' => $selected_year
                    , 'region_id' => $region_id
                ];

                array_push($DataList, $data);

                $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                $DataSummary['SummaryValue'] = $DataSummary['SummaryValue'] + $data['CurrentBaht'];
            }

            $TotalLine['RegionName'] = 'รวม';
            array_push($DataList, $TotalLine);

            // $curMonth++;
            // exit;

            $this->data_result['DATA']['List'] = $DataList;
            $this->data_result['DATA']['Summary'] = $DataSummary;


            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getListMBIDetail($request, $response, $args) {
        // error_reporting(E_ERROR);
        //     error_reporting(E_ALL);
        //     ini_set('display_errors','On');

        try {
            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            $region_id = $params['obj']['condition']['region_id'];
            $years = $params['obj']['condition']['years'];

            $region = MBIController::checkProvince($region_id);
            $region_list[] = $region;
            // $region = CooperativeService::getRegion($region_id);
            // $region_list[] = $region['RegionName'];
            // print_r($region_list);
            // exit;
            // $region_list = [];
            // foreach ($regions as $key => $value) {
            //     $region = MBIController::checkRegion($value['RegionID']);
            //     $region_list[] = $region;
            // }

            $DataList = [];

            $years = $years - 1;
            $beforYear = intval($years) - 1;
            $curMonth = 10;

            $quarter = 1;
            $Q_CurrentAmount = 0;
            $Q_CurrentBaht = 0;
            $Q_BeforeAmount = 0;
            $Q_BeforeBaht = 0;

            $Y_CurrentAmount = 0;
            $Y_CurrentBaht = 0;
            $Y_BeforeAmount = 0;
            $Y_BeforeBaht = 0;

            for ($i = 0; $i < 12; $i++) {

                if ($curMonth > 12) {
                    $curMonth = 1;
                    $years += 1;
                    $beforYear += 1;
                }

                $monthName = MBIController::getMonthName($curMonth);

                $data = [];
                $data['Month'] = $monthName;

                // Cur year

                $fromTime = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
                $ymTo = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
                $toTime = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-' . $this->getLastDayOfMonth($ymTo);

                $Current = MBIService::getListMBIDetail($fromTime, $toTime, $region_list);

                $data['CurrentAmount'] = floatval($Current['sum_amount']);
                $data['CurrentBaht'] = floatval($Current['sum_baht']);

                // Before year

                $fromTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
                $ymTo = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
                $toTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-' . $this->getLastDayOfMonth($ymTo);

                $Before = MBIService::getListMBIDetail($fromTime, $toTime, $region_list);
                $data['BeforeAmount'] = floatval($Before['sum_amount']);
                $data['BeforeBaht'] = floatval($Before['sum_baht']);

                $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                $data['DiffAmount'] = $DiffAmount;

                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] = ($data['CurrentAmount'] / $data['BeforeAmount']) * 100;
                } else {
                    $data['DiffAmountPercentage'] = 0;
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = ($data['CurrentBaht'] / $data['BeforeBaht']) * 100;
                } else {
                    $data['DiffBahtPercentage'] = 0;
                }

                $Q_CurrentAmount += $data['CurrentAmount'];
                $Q_CurrentBaht += $data['CurrentBaht'];
                $Q_BeforeAmount += $data['BeforeAmount'];
                $Q_BeforeBaht += $data['BeforeBaht'];

                $Y_CurrentAmount += $data['CurrentAmount'];
                $Y_CurrentBaht += $data['CurrentBaht'];
                $Y_BeforeAmount += $data['BeforeAmount'];
                $Y_BeforeBaht += $data['BeforeBaht'];

                $curMonth++;

                array_push($DataList, $data);

                if ($i > 0 && ($i + 1) % 3 == 0) { 

                    $data = [];
                    $data['Month'] = 'ไตรมาส ' . $quarter;
                    $data['CurrentAmount'] = $Q_CurrentAmount;
                    $data['CurrentBaht'] = $Q_CurrentBaht;
                    $data['BeforeAmount'] = $Q_BeforeAmount;
                    $data['BeforeBaht'] = $Q_BeforeBaht;

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

                    $Q_CurrentAmount = 0;
                    $Q_CurrentBaht = 0;
                    $Q_BeforeAmount = 0;
                    $Q_BeforeBaht = 0;

                    $data['bg_color'] = '#ccc';

                    $quarter++;

                    array_push($DataList, $data);
                }
            }

            $data = [];
            $data['Month'] = 'รวม';
            $data['CurrentAmount'] = $Y_CurrentAmount;
            $data['CurrentBaht'] = $Y_CurrentBaht;
            $data['BeforeAmount'] = $Y_BeforeAmount;
            $data['BeforeBaht'] = $Y_BeforeBaht;

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
            $data['bg_color'] = '#999';
            array_push($DataList, $data);

            // exit;

            $this->data_result['DATA']['List'] = $DataList;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getListMBIMOU($request, $response, $args) {
        // error_reporting(E_ERROR);
        //     error_reporting(E_ALL);
        //     ini_set('display_errors','On');

        try {
            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            $regions = $params['obj']['region'];

            $RegionList = [];
            foreach ($regions as $key => $value) {
                if($value['RegionID'] != 1 && $value['RegionID'] != 2){
                    $RegionList[] = $value;
                }
            }

            $regions = $RegionList;
            
            $displayType = $params['obj']['condition']['DisplayType'];
            $monthFrom = $params['obj']['condition']['MonthFrom'];
            $quarterFrom = $params['obj']['condition']['QuarterFrom'];
            $years = $params['obj']['condition']['YearTo'];

            if ($displayType == 'monthly') {
                $monthFrom = $monthFrom;
                $monthTo = $monthFrom;
                $yearFrom = $years;
                $yearTo = $years;
            } else if ($displayType == 'quarter') {
                if ($quarter == '1') {
                    $monthFrom = 10;
                    $monthTo = 12;
                    $yearFrom = intval($years) - 1;
                    $yearTo = intval($years) - 1;
                } else if ($quarter == '2') {
                    $monthFrom = 1;
                    $monthTo = 3;
                    $yearFrom = $years;
                    $yearTo = $years;
                } else if ($quarter == '3') {
                    $monthFrom = 4;
                    $monthTo = 6;
                    $yearFrom = $years;
                    $yearTo = $years;
                } else if ($quarter == '4') {
                    $monthFrom = 7;
                    $monthTo = 9;
                    $yearFrom = $years;
                    $yearTo = $years;
                }
            } else {
                $monthFrom = 10;
                $monthTo = 9;
                $yearFrom = intval($years) - 1;
                $yearTo = $years;
            }

            $DataList = [];
            $DataSummary = [];

            $curMonth = $monthFrom;
            $Sum_MOUAmount = 0;
            $Sum_MOUBaht = 0;
            $Sum_DataAmount = 0;
            $Sum_DataBaht = 0;
            $Sum_TotalMOUAmount = 0;
            $Sum_TotalMOUBaht = 0;
            $Sum_TotalDataAmount = 0;
            $Sum_TotalDataBaht = 0;

            foreach ($regions as $key => $value) {

                $monthName = MBIController::getMonthName($curMonth);
                $region = MBIController::checkRegion($value['RegionID']);//checkRegion($value['RegionID']);
                $region_id = $value['RegionID'];

                $data = [];
                $data['RegionName'] = $region;
                $data['Month'] = $monthName;
                $data['Quarter'] = $quarterFrom;

                $fromTime = $yearFrom . '-' . str_pad($monthFrom, 2, "0", STR_PAD_LEFT) . '-01';
                $ymTo = $yearTo . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT);
                $toTime = $yearTo . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT) . '-' . $this->getLastDayOfMonth($ymTo);


                // MOU
                $MOU = MBIService::getListMOU($fromTime, $toTime, $region_id);
                $data['MOUAmount'] = floatval($MOU['sum_amount']);
                $data['MOUBaht'] = floatval($MOU['sum_baht']);

                // MBI
                $MBI = MBIService::getListMBI($fromTime, $toTime, $region);
                $data['DataAmount'] = floatval($MBI['sum_amount']);
                $data['DataBaht'] = floatval($MBI['sum_baht']);

                // Total in Year
                // MOU
                $MOU = MBIService::getListMOUByYear($years, $region_id);
                $data['TotalMOUAmount'] = floatval($MOU['sum_amount']);
                $data['TotalMOUBaht'] = floatval($MOU['sum_baht']);

                // MBI
                $fromTime = ($years - 1) . '-10-01';
                $ymTo = $years . '-09';
                $toTime = $years . '-09-' . $this->getLastDayOfMonth($ymTo);

                $MBI = MBIService::getListMBIByYear($fromTime, $toTime, $region);
                $data['TotalDataAmount'] = floatval($MBI['sum_amount']);
                $data['TotalDataBaht'] = floatval($MBI['sum_baht']);

                $data['Description'] = ['months' => $curMonth
                    , 'quarters' => $quarterFrom
                    , 'years' => $years
                    , 'region_id' => $region_id
                    , 'display_type' => $displayType
                ];

                array_push($DataList, $data);

                $Sum_MOUAmount += $data['MOUAmount'];
                $Sum_MOUBaht += $data['MOUBaht'];
                $Sum_DataAmount += $data['DataAmount'];
                $Sum_DataBaht += $data['DataBaht'];
                $Sum_TotalMOUAmount += $data['TotalMOUAmount'];
                $Sum_TotalMOUBaht += $data['TotalMOUBaht'];
                $Sum_TotalDataAmount += $data['TotalDataAmount'];
                $Sum_TotalDataBaht += $data['TotalDataBaht'];
            }

            $data = [];
            $data['bg_color'] = '#999';
            $data['RegionName'] = '';
            $data['Month'] = 'รวม';
            $data['MOUAmount'] = $Sum_MOUAmount;
            $data['MOUBaht'] = $Sum_MOUBaht;
            $data['DataAmount'] = $Sum_DataAmount;
            $data['DataBaht'] = $Sum_DataBaht;
            $data['TotalMOUAmount'] = $Sum_TotalMOUAmount;
            $data['TotalMOUBaht'] = $Sum_TotalMOUBaht;
            $data['TotalDataAmount'] = $Sum_TotalDataAmount;
            $data['TotalDataBaht'] = $Sum_TotalDataBaht;
            array_push($DataList, $data);
            // exit;

            $this->data_result['DATA']['List'] = $DataList;
            $this->data_result['DATA']['Summary'] = $DataSummary;


            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getListMBIMOUDetail($request, $response, $args) {
        // error_reporting(E_ERROR);
        //     error_reporting(E_ALL);
        //     ini_set('display_errors','On');

        try {
            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            $displayType = $params['obj']['condition']['display_type'];
            $region_id = $params['obj']['condition']['region_id'];
            $monthFrom = $params['obj']['condition']['months'];
            $quarter = $params['obj']['condition']['quarters'];
            $years = $params['obj']['condition']['years'];

            if ($displayType == 'monthly') {
                $monthFrom = $monthFrom;
                $monthTo = $monthFrom;
                $yearFrom = $years;
                $yearTo = $years;
            } else if ($displayType == 'quarter') {
                if ($quarter == '1') {
                    $monthFrom = 10;
                    $monthTo = 12;
                    $yearFrom = intval($years) - 1;
                    $yearTo = intval($years) - 1;
                } else if ($quarter == '2') {
                    $monthFrom = 1;
                    $monthTo = 3;
                    $yearFrom = $years;
                    $yearTo = $years;
                } else if ($quarter == '3') {
                    $monthFrom = 4;
                    $monthTo = 6;
                    $yearFrom = $years;
                    $yearTo = $years;
                } else if ($quarter == '4') {
                    $monthFrom = 7;
                    $monthTo = 9;
                    $yearFrom = $years;
                    $yearTo = $years;
                }
            } else {
                $monthFrom = 10;
                $monthTo = 9;
                $yearFrom = intval($years) - 1;
                $yearTo = $years;
            }

            $DataList = [];


            // get cooperative from region
            if($region_id == 3){
                $co_region_id = 1;
            }else{
                $co_region_id = $region_id;
            }
            $CooperativeList = CooperativeService::getListByRegion($co_region_id);
            $region = MBIController::checkProvince($region_id);
            $provice = $region;
            $Sum_MOUAmount = 0;
            $Sum_MOUBaht = 0;
            $Sum_DataAmount = 0;
            $Sum_DataBaht = 0;
            $Sum_AVGBaht = 0;

            // In MOU
            $except_cooperative_list = [];
            foreach ($CooperativeList as $key => $value) {
                // print_r($value);
                $monthName = MBIController::getMonthName($curMonth);

                $cooperative_name = $value['cooperative_name'];
                $except_cooperative_list[] = $cooperative_name;
                $cooperative_id = $value['id'];

                $data = [];
                $data['RegionName'] = $cooperative_name;

                $fromTime = $yearFrom . '-' . str_pad($monthFrom, 2, "0", STR_PAD_LEFT) . '-01';
                $ymTo = $yearTo . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT);
                $toTime = $yearTo . '-' . str_pad($monthTo, 2, "0", STR_PAD_LEFT) . '-' . $this->getLastDayOfMonth($ymTo);

                // MOU
                $MOU = MBIService::getListMOUByCooperative($fromTime, $toTime, $cooperative_id);
                $data['MOUAmount'] = floatval($MOU['sum_amount']);
                $data['MOUBaht'] = floatval($MOU['sum_baht']);
                $data['AVGBaht'] = number_format($data['MOUBaht'] / $data['MOUAmount'], 2);

                // MBI
                // $fromTime = ($years - 1) . '-10-01';
                // $toTime = ($years) . '-09-30';
                // echo "$fromTime, $toTime, $region";exit;
                $MBI = MBIService::getListMBIByVendor($fromTime, $toTime, $cooperative_name, $provice);
                $data['DataAmount'] = floatval($MBI['sum_amount']);
                $data['DataBaht'] = floatval($MBI['sum_baht']);


                array_push($DataList, $data);

                $Sum_MOUAmount += $data['MOUAmount'];
                $Sum_MOUBaht += $data['MOUBaht'];
                $Sum_DataAmount += $data['DataAmount'];
                $Sum_DataBaht += $data['DataBaht'];
                $Sum_AVGBaht += $data['AVGBaht'];
            }

            $data = [];
            $data['RegionName'] = 'รวม';
            $data['MOUAmount'] = $Sum_MOUAmount;
            $data['MOUBaht'] = $Sum_MOUBaht;
            $data['DataAmount'] = $Sum_DataAmount;
            $data['DataBaht'] = $Sum_DataBaht;
            $data['AVGBaht'] = number_format($data['MOUBaht'] / $data['MOUAmount'], 2);
            array_push($DataList, $data);

            // Out MOU
            $data = [];
            $data['bg_color'] = '#ccc';
            $data['RegionName'] = 'นอก MOU';
            array_push($DataList, $data);

            $MBI = MBIService::getListMBIByExceptVendor($fromTime, $toTime, $except_cooperative_list, $region);
            $data = [];
            $data['DataAmount'] = floatval($MBI['sum_amount']);
            $data['DataBaht'] = floatval($MBI['sum_baht']);
            $Sum_DataAmount += $data['DataAmount'];
            $Sum_DataBaht += $data['DataBaht'];
            // $data['AVGBaht'] = number_format($data['DataBaht'] / $data['DataAmount'], 2);
            array_push($DataList, $data);

            // exit;
            $data = [];
            $data['bg_color'] = '#999';
            $data['RegionName'] = 'รวมทั้งสิ้น';

            $data['DataAmount'] = $Sum_DataAmount;
            $data['DataBaht'] = $Sum_DataBaht;
            // $data['AVGBaht'] = $Sum_AVGBaht;

            array_push($DataList, $data);
            // exit;

            $this->data_result['DATA']['List'] = $DataList;


            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getList($request, $response, $args) {
        error_reporting(E_ERROR);
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');

        try {
            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            $years = $params['obj']['condition']['Year'];
            $jsonArray = [];

            $db = $this->getConnection();

            echo $sql = "select *
                                    from  xxcust_order_rm_v
                                    where transaction_date between to_date('01-JAN-2019','DD-MON-YYYY') and  trunc(sysdate)";


            $stmt = $db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            if (count($result) >= 1) {
                foreach ($result as $result) {
                    $jsonArray[] = $result;
                }
            }
            print_r($jsonArray);
            exit;
            $this->data_result['DATA'] = $jsonArray;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\PDOException $e) {
            echo "<pre>";
            print_r($e);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function updateData($request, $response, $args){

        $params = $request->getParsedBody();
        $this->logger->info('MBI UPDATE DATA');
        // $this->logger->info(json_encode($params));//exit;
        $mbi_list = $params['mbi_list'];
        try{

            $total_update_item = 0; 

            if(count($mbi_list) > 0){

                $date = substr($this->getSQLDateFormat($mbi_list[0]['GLDate']), 0, 7);
                $region = $this->getProviceNameByOffice(trim($mbi_list[0]['Shipto']));
                MBIService::deleteOldData($date, $region);

            }

            foreach($mbi_list as $key => $value){
                $this->logger->info($value);
                $data = [];
                $data['REGION'] = $this->getProviceNameByOffice(trim($value['Shipto']));  //$value['Shipto'];
                $data['VENDOR_NAME'] = $value['Supplier'];
                $data['TRANSACTION_DATE'] = $this->getSQLDateFormat(trim($value['GLDate']));
                $data['ITEM_DESCRIPTION'] = $value['Description'];
                $data['ITEM_CODE'] = $value['InventoryItem'];
                $data['UOM'] = $value['UOM'];
                $data['QUANTITY'] = $value['QuantityInvoiced'];
                $data['AMOUNT'] = $value['Amount'];

                $result = MBIService::updateData($data, $this->logger);

                if($result){
                    $total_update_item++;
                }
            }
            
            $this->data_result['DATA']['Result'] = $total_update_item;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }

    }

    private function getConnection() {
        $DBHost = "10.10.1.10"; //Database Host URL or IP Address
        $DBOraclePort = "1530"; //DB Oracle Port
        $DBName = "TEST9"; //if MySQL use Database Name, if Oracle use Oracle System ID (SID)
        //Connection String
        //$connectionDB = "mysql:host={$DBHost};dbname={$DBName}";
        $connectionDB = "oci:dbname=(DESCRIPTION=(ADDRESS=(HOST={$DBHost})(PROTOCOL=tcp)(PORT={$DBOraclePort}))(CONNECT_DATA=(SID={$DBName})))";
        $DBUser = "XXCUST";
        $DBPswd = "XXCUST123";
        $dbh = new \PDO($connectionDB, $DBUser, $DBPswd);
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        return $dbh;
    }

}
