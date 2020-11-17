<?php

namespace App\Controller;

use App\Service\PersonalService;

class PersonalController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
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
            case 11 : $monthTxt = 'พฤษจิกายน';
                break;
            case 12 : $monthTxt = 'ธันวาคม';
                break;
        }
        return $monthTxt;
    }

    public function getMainList($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $condition = $params['obj']['condition'];


            if ($condition['DisplayType'] == 'monthly') {
                $Result = $this->getMonthDataList($condition);
            } else if ($condition['DisplayType'] == 'quarter') {
                $Result = $this->getQuarterDataList($condition);
            } else if ($condition['DisplayType'] == 'annually') {
                $Result = $this->getAnnuallyDataList($condition);
            }

            $DataList = $Result['DataList'];
            $Summary = $Result['Summary'];


            $this->data_result['DATA']['DataList'] = $DataList;
            $this->data_result['DATA']['Summary'] = $Summary;
//             print_r($DataList);
//            die();
            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMonthDataList($condition) {
        $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28'; // . VeterinaryController::getLastDayOfMonth($ymTo);
        //exit;
        $fromTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);

        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);

        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        // exit;
        // if($ymFrom != $ymTo){
        //     $diffMonth += 1;
        // }
        $curMonth = $condition['MonthTo'];
        $DataList = [];
        $DataSummary = [];

        $positiontype = PersonalService::getPositiontype();
//        $mf = PersonalController::getMonthName($condition['MonthFrom']);
        $mt = PersonalController::getMonthName($condition['MonthTo']);
//        die();
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }
        for ($i = 0; $i < $diffMonth; $i++) {

            // Prepare condition
            $curYear = $condition['YearTo'];
            $beforeYear = $condition['YearTo'] - 1;
            $datatotal = [];
            $datatotal['text'] = 'รวมทั้งสิ้น';
            $datatotal['color'] = '#87ceeb';
            $datatotal['SummaryCurrentsum'] = 0;
            $datatotal['SummaryBeforesum'] = 0;
            $datatotal['SummaryCurrentdirector'] = 0;
            $datatotal['SummaryBeforedirector'] = 0;
            $datatotal['SummaryCurrent'] = 0;
            $datatotal['SummaryBefore'] = 0;
            $datatotal['SummaryPercentage'] = 0;
            $datatotal['SummarysumPercentage'] = 0;
            $datatotal['SummaryCurrentout'] = 0;
            $datatotal['SummaryBeforeout'] = 0;
            $datatotal['lv1'] = 0;
            $datatotal['lv2'] = 0;
            $datatotal['lv3'] = 0;
            $datatotal['lv4'] = 0;
            $datatotal['lv5'] = 0;
            $datatotal['lv6'] = 0;
            $datatotal['lv7'] = 0;
            $datatotal['lv8'] = 0;
            $datatotal['lv9'] = 0;
            $datatotal['lv10'] = 0;
            $datatotal['SummaryCurrentdirector'] = 0;
            // Loop User Regions
            foreach ($positiontype as $key => $value) {

                //     $region_id = $value['RegionID'];
                $monthName = PersonalController::getMonthName($curMonth);

                $data = [];
                $data['text'] = 'รวม';
                $data['color'] = '#B2BABB';
                $data['textdate'] = $mt . ' ' . $condition['YearTo'];
                $data['Month'] = $monthName;
                $data['Position'] = $value['positiontype'];
                $data['SummaryCurrentsum'] = 0;
                $data['SummaryBeforesum'] = 0;
                $data['SummaryCurrentdirector'] = 0;
                $data['SummaryBeforedirector'] = 0;
                $data['SummaryCurrent'] = 0;
                $data['SummaryBefore'] = 0;
                $data['SummaryPercentage'] = 0;
                $data['SummarysumPercentage'] = 0;
                $data['SummaryCurrentout'] = 0;
                $data['SummaryBeforeout'] = 0;
                $data['lv1'] = 0;
                $data['lv2'] = 0;
                $data['lv3'] = 0;
                $data['lv4'] = 0;
                $data['lv5'] = 0;
                $data['lv6'] = 0;
                $data['lv7'] = 0;
                $data['lv8'] = 0;
                $data['lv9'] = 0;
                $data['lv10'] = 0;
                // get cooperative type

                $data['CurrentEmployee'] = PersonalService::getMainList($curYear, $curMonth, $value['positiontype']);

                $data['BeforeEmployee'] = PersonalService::getMainList($beforeYear, $curMonth, $value['positiontype']);

//
                foreach ($data['CurrentEmployee'] as $key => $current) {
                    $data['SummaryCurrentout'] += $current['outsource'];

                    $data['CurrentEmployee'][$key]['sumc'] = 0;
                    $data['CurrentEmployee'][$key]['sumb'] = 0;
                    $data['CurrentEmployee'][$key]['sum'] = 0;
                    $data['CurrentEmployee'][$key]['percent'] = 0;
                    $data['SummaryCurrentsum'] += $current['summary'];
                    $data['SummaryCurrentdirector'] += $current['director'];
                    $data['lv1'] += $current['lv1'];
                    $data['lv2'] += $current['lv2'];
                    $data['lv3'] += $current['lv3'];
                    $data['lv4'] += $current['lv4'];
                    $data['lv5'] += $current['lv5'];
                    $data['lv6'] += $current['lv6'];
                    $data['lv7'] += $current['lv7'];
                    $data['lv8'] += $current['lv8'];
                    $data['lv9'] += $current['lv9'];
                    $data['lv10'] += $current['lv10'];
                }
                $datatotal['lv1'] += $data['lv1'];
                $datatotal['lv2'] += $data['lv2'];
                $datatotal['lv3'] += $data['lv3'];
                $datatotal['lv4'] += $data['lv4'];
                $datatotal['lv5'] += $data['lv5'];
                $datatotal['lv6'] += $data['lv6'];
                $datatotal['lv7'] += $data['lv7'];
                $datatotal['lv8'] += $data['lv8'];
                $datatotal['lv9'] += $data['lv9'];
                $datatotal['lv10'] += $data['lv10'];
                // $datatotal['SummaryCurrentdirector'] += $data['SummaryCurrentdirector'];

                if ($data['BeforeEmployee'] != '' && !is_null($data['BeforeEmployee'])) {

                    foreach ($data['CurrentEmployee'] as $keyitem => $item) {
//                          

                        $sumcurrent = $item['summary'] + $item['director'] + $item['outsource'];
                        $sumbefore = $data['BeforeEmployee'][$keyitem]['summary'] + $data['BeforeEmployee'][$keyitem]['director'] + $data['BeforeEmployee'][$keyitem]['outsource'];
                        $data['CurrentEmployee'][$keyitem]['sumc'] = $sumcurrent;
                        $data['CurrentEmployee'][$keyitem]['sumb'] = $sumbefore;
                        $data['CurrentEmployee'][$keyitem]['sum'] =  $sumcurrent-$sumbefore;
                        $data['SummaryCurrent'] += $sumcurrent;
//                        
                        $data['SummaryBeforesum'] += $data['BeforeEmployee'][$keyitem]['summary'];
                        $data['SummaryBeforedirector'] += $data['BeforeEmployee'][$keyitem]['director'];
                        $data['SummaryBefore'] += $sumbefore;
                        $data['SummaryBeforeout'] += $data['BeforeEmployee'][$keyitem]['outsource'];
                        if ($sumbefore != 0) {
                            $data['CurrentEmployee'][$keyitem]['percent'] = (($sumcurrent - $sumbefore) * 100) / $sumbefore;
                        } else {
                            $data['CurrentEmployee'][$keyitem]['percent'] = 100;
                        }

                        $data['SummaryPercentage'] += ($sumcurrent - $sumbefore);
                    }
                    if ($data['SummaryBefore'] != 0) {
                        $data['SummarysumPercentage'] = (($data['SummaryCurrent'] - $data['SummaryBefore']) * 100) / $data['SummaryBefore'];
                    } else {
                        $data['SummarysumPercentage'] = 100;
                    }
                }

                $data['Status'] = '';
                $data['Description'] = [
                    'months' => $curMonth
                    , 'years' => $curYear
                ];

                $datatotal['SummaryCurrentsum'] += $data['SummaryCurrentsum'];
                $datatotal['SummaryBeforesum'] += $data['SummaryBeforesum'];
                $datatotal['SummaryCurrentdirector'] += $data['SummaryCurrentdirector'];
                $datatotal['SummaryBeforedirector'] += $data['SummaryBeforedirector'];
                $datatotal['SummaryCurrent'] += $data['SummaryCurrent'];
                $datatotal['SummaryBefore'] += $data['SummaryBefore'];
                $datatotal['SummaryPercentage'] += $data['SummaryPercentage'];
                $datatotal['SummaryCurrentout'] += $data['SummaryCurrentout'];
                $datatotal['SummaryBeforeout'] += $data['SummaryBeforeout'];
                array_push($DataList, $data);

                #### End of cooperative 
            }
//lv8
            $monthName = PersonalController::getMonthName($curMonth);

            $data = [];
            $data['Position'] = 'นักวิชาการ 8';
            $data['Month'] = $monthName;
            $data['text'] = 'รวม';
            $data['color'] = '#B2BABB';
//            $data['Quarter'] = ($i + 1);
//            $data['Year'] = ($curYear);
            $data['SummaryCurrentsum'] = 0;
            $data['SummaryBeforesum'] = 0;
            $data['SummaryCurrentdirector'] = 0;
            $data['SummaryBeforedirector'] = 0;
            $data['SummaryCurrent'] = 0;
            $data['SummaryBefore'] = 0;
            $data['SummaryCurrentout'] = 0;
            $data['SummaryBeforeout'] = 0;
            $data['SummaryPercentage'] = 0;
            $data['SummarysumPercentage'] = 0;
            $data['lv8'] = 0;
            // get cooperative type

            $data['CurrentEmployee'] = PersonalService::get8($curYear, $curMonth);
            $data['CurrentEmployee'][0]['lv8'] = $data['CurrentEmployee'][0]['summary'];
            $data['lv8'] += $data['CurrentEmployee'][0]['summary'];
            $data['BeforeEmployee'] = PersonalService::get8($beforeYear, $curMonth);
            $data['CurrentEmployee'][0]['director'] = intval(0);
            $data['BeforeEmployee'][0]['director'] = intval(0);
            $data['CurrentEmployee'][0]['sumc'] = 0;
            $data['CurrentEmployee'][0]['sumb'] = 0;
            $data['CurrentEmployee'][0]['sum'] = 0;
            if ($data['BeforeEmployee'] != '' && !is_null($data['BeforeEmployee'])) {

                $sumcurrent = $data['CurrentEmployee'][0]['summary'];
                $sumbefore = $data['BeforeEmployee'][0]['summary'];
                $data['CurrentEmployee'][0]['sumc'] = $sumcurrent;
                $data['CurrentEmployee'][0]['sumb'] = $sumbefore;
                $data['CurrentEmployee'][0]['sum'] = $sumcurrent - $sumbefore;
                $data['SummaryCurrent'] += $sumcurrent;
                $data['SummaryCurrentsum'] += $data['CurrentEmployee'][0]['summary'];
                $data['SummaryBeforesum'] += $data['BeforeEmployee'][0]['summary'];
                $data['SummaryBeforedirector'] += $data['BeforeEmployee'][0]['director'];
                $data['SummaryBefore'] += $sumbefore;
                $data['SummaryCurrentout'] = $data['CurrentEmployee'][0]['outsource'];
                $data['SummaryBeforeout'] = $data['BeforeEmployee'][0]['outsource'];
                if ($sumbefore != 0) {
                    $data['CurrentEmployee'][0]['percent'] = (($sumcurrent - $sumbefore) * 100) / $sumbefore;
                } else {
                    $data['CurrentEmployee'][0]['percent'] = 100;
                }

                $data['SummaryPercentage'] += ($sumcurrent - $sumbefore);
            }
            if ($data['SummaryBefore'] != 0) {
                $data['SummarysumPercentage'] = (($data['SummaryCurrent'] - $data['SummaryBefore']) * 100) / $data['SummaryBefore'];
            } else {
                $data['SummarysumPercentage'] = 100;
            }
            $datatotal['SummaryCurrentsum'] += $data['SummaryCurrentsum'];
            $datatotal['SummaryBeforesum'] += $data['SummaryBeforesum'];
            $datatotal['SummaryCurrentdirector'] += $data['SummaryCurrentdirector'];
            $datatotal['SummaryBeforedirector'] += $data['SummaryBeforedirector'];
            $datatotal['SummaryCurrent'] += $data['SummaryCurrent'];
            $datatotal['SummaryBefore'] += $data['SummaryBefore'];
            $datatotal['SummaryCurrentout'] += $data['SummaryCurrentout'];
            $datatotal['SummaryBeforeout'] += $data['SummaryBeforeout'];
            $datatotal['SummaryPercentage'] += $data['SummaryPercentage'];
            $datatotal['lv8'] += $data['SummaryCurrent'];

            if ($datatotal['SummaryBefore'] != 0) {
                $datatotal['SummarysumPercentage'] = (($datatotal['SummaryCurrent'] - $datatotal['SummaryBefore']) * 100) / $datatotal['SummaryBefore'];
            } else {
                $datatotal['SummarysumPercentage'] = 100;
            }
            array_push($DataList, $data);
            array_push($DataList, $datatotal);
//            $datatotal = [];
//            $datatotal['text'] = 'รวมทั้งสิ้น';
//            $datatotal['SummaryCurrentsum'] = 0;
//            $datatotal['SummaryBeforesum'] = 0;
//            $datatotal['SummaryCurrentdirector'] = 0;
//            $datatotal['SummaryBeforedirector'] = 0;
//            $datatotal['SummaryCurrent'] = 0;
//            $datatotal['SummaryBefore'] = 0;
//            $datatotal['SummaryPercentage'] = 0;
//            $datatotal['SummarysumPercentage'] = 0;
            //tb2
            $DataSummary2['current'] = PersonalService::getMainListsheet3($curYear, $curMonth);
            $DataSummary2['before'] = PersonalService::getMainListsheet3($beforeYear, $curMonth);
            $DataSummary2['lv1'] = 0;
            $DataSummary2['lv2'] = 0;
            $DataSummary2['lv3'] = 0;
            $DataSummary2['lv4'] = 0;
            $DataSummary2['lv5'] = 0;
            $DataSummary2['lv6'] = 0;
            $DataSummary2['lv7'] = 0;
            $DataSummary2['lv8'] = 0;
            $DataSummary2['lv9'] = 0;
            $DataSummary2['lv10'] = 0;
            $DataSummary2['sumtext'] = 'รวมทั้งสิ้น';
            $DataSummary2['sumc'] = 0;
            $DataSummary2['sumb'] = 0;
            $DataSummary2['cb'] = 0;
            $DataSummary2['percb'] = 0;
            $DataSummary2['color'] = '#B2BABB';
            foreach ($DataSummary2['current'] as $key => $t) {
                $DataSummary2['sumc'] += $t['summary'];
                $DataSummary2['sumb'] += $DataSummary2['before'][$key]['summary'];
                $DataSummary2['cb'] += $t['summary'] - $DataSummary2['before'][$key]['summary'];
                $DataSummary2['text'] = 'รวม';
                $DataSummary2['lv1'] += $t['lv1'];
                $DataSummary2['lv2'] += $t['lv2'];
                $DataSummary2['lv3'] += $t['lv3'];
                $DataSummary2['lv4'] += $t['lv4'];
                $DataSummary2['lv5'] += $t['lv5'];
                $DataSummary2['lv6'] += $t['lv6'];
                $DataSummary2['lv7'] += $t['lv7'];
                $DataSummary2['lv8'] += $t['lv8'];
                $DataSummary2['lv9'] += $t['lv9'];
                $DataSummary2['lv10'] += $t['lv10'];
                if ($DataSummary2['before'][$key]['summary'] != 0 && $DataSummary2['before'][$key]['summary'] != '') {
                    $DataSummary2['current'][$key]['percent'] = (($t['summary'] - $DataSummary2['before'][$key]['summary']) * 100) / $DataSummary2['before'][$key]['summary'];
                } else {
                    $DataSummary2['current'][$key]['percent'] = 100;
                }
            }
            if ($DataSummary2['sumb'] != 0) {
                $DataSummary2['percb'] = (($DataSummary2['sumc'] - $DataSummary2['sumb']) * 100) / $DataSummary2['sumb'];
            } else {
                $DataSummary2['percb'] = 100;
            }
            $DataSummary2['Monthname'] = $monthName;
            if (sizeof($DataSummary2['current']) > 0) {
                array_push($DataSummary, $DataSummary2);
            }

            $curMonth++;
        }
//        print_r($DataList);
//        die();
        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getQuarterDataList($condition) {

        // get loop to query
        $diffYear = ($condition['YearTo'] - $condition['YearFrom']) + 1;
        $cnt = 0;
        $loop = 0;
        $j = $condition['QuarterFrom'];
        $positiontype = PersonalService::getPositiontype();
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

        $Summary = [];
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
            foreach ($positiontype as $key => $value) {

                $data = [];
                $data['textdate'] = 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . ' - ' . ' ไตรมาสที่ ' . $condition['QuarterTo'] . ' ' . ($condition['YearTo'] + 543);
                $data['Position'] = $value['positiontype'];
                //$data['Month'] = $monthName;
                $data['Quarter'] = ($curQuarter) . ' (' . ($curQuarter == 1 ? $curYear + 543 + 1 : $curYear + 543) . ')';
                //  $data['Year'] = ($curYear);
                $data['SummaryCurrentsum'] = 0;
                $data['SummaryBeforesum'] = 0;
                $data['SummaryCurrentdirector'] = 0;
                $data['SummaryBeforedirector'] = 0;
                $data['SummaryCurrent'] = 0;
                $data['SummaryBefore'] = 0;
                $data['SummaryPercentage'] = 0;
                $data['SummarysumPercentage'] = 0;
                $data['lv1'] = 0;
                $data['lv2'] = 0;
                $data['lv3'] = 0;
                $data['lv4'] = 0;
                $data['lv5'] = 0;
                $data['lv6'] = 0;
                $data['lv7'] = 0;
                $data['lv8'] = 0;
                $data['lv9'] = 0;
                $data['lv10'] = 0;
                $DataSummary = [];

                $DataSummary['Quarter'] = ($curQuarter);

                $start = 0;
//find first
                for ($j = 0; $j < count($monthList); $j++) {
                    $data['CurrentEmployee'] = PersonalService::getMainList($curYear, $monthList[$j], $value['positiontype']);
                    $data['BeforeEmployee'] = PersonalService::getMainList($beforeYear, $monthList[$j], $value['positiontype']);
                    $DataSummary['current'] = PersonalService::getMainListsheet3($curYear, $monthList[$j]);
                    $DataSummary['before'] = PersonalService::getMainListsheet3($beforeYear, $monthList[$j]);
                    if (sizeof($data['CurrentEmployee']) > 0) {
                        $start = $j + 1;

                        break;
                    }
                }
                // loop get quarter sum data
                for ($j = $start; $j < count($monthList); $j++) {
                    $curMonth = $monthList[$j];
                    $data2['CurrentEmployee'] = PersonalService::getMainList($curYear, $curMonth, $value['positiontype']);
                    $data2['BeforeEmployee'] = PersonalService::getMainList($beforeYear, $curMonth, $value['positiontype']);
                    foreach ($data2['CurrentEmployee'] as $key => $itemdata2) {
                        $data['CurrentEmployee'][$key]['summary'] += $itemdata2['summary'];
                        $data['CurrentEmployee'][$key]['director'] += $itemdata2['director'];

                        $data['CurrentEmployee'][$key]['lv1'] += $itemdata2['lv1'];
                        $data['CurrentEmployee'][$key]['lv2'] += $itemdata2['lv2'];
                        $data['CurrentEmployee'][$key]['lv3'] += $itemdata2['lv3'];
                        $data['CurrentEmployee'][$key]['lv4'] += $itemdata2['lv4'];
                        $data['CurrentEmployee'][$key]['lv5'] += $itemdata2['lv5'];
                        $data['CurrentEmployee'][$key]['lv6'] += $itemdata2['lv6'];
                        $data['CurrentEmployee'][$key]['lv7'] += $itemdata2['lv7'];
                        $data['CurrentEmployee'][$key]['lv8'] += $itemdata2['lv8'];
                        $data['CurrentEmployee'][$key]['lv9'] += $itemdata2['lv9'];
                        $data['CurrentEmployee'][$key]['lv10'] += $itemdata2['lv10'];


                        $data['BeforeEmployee'][$key]['summary'] += $data2['BeforeEmployee'][$key]['summary'];
                        $data['BeforeEmployee'][$key]['director'] += $data2['BeforeEmployee'][$key]['director'];
                    }



                    $DataSummary2['current'] = PersonalService::getMainListsheet3($curYear, $curMonth);
                    $DataSummary2['before'] = PersonalService::getMainListsheet3($beforeYear, $curMonth);


                    foreach ($DataSummary2['current'] as $key => $itemsm) {
                        if ($DataSummary2['current'][$key]['summary'] != '') {
                            $DataSummary['current'][$key]['summary'] += $DataSummary2['current'][$key]['summary'];
                            $DataSummary['current'][$key]['lv1'] += $DataSummary2['current'][$key]['lv1'];
                            $DataSummary['current'][$key]['lv2'] += $DataSummary2['current'][$key]['lv2'];
                            $DataSummary['current'][$key]['lv3'] += $DataSummary2['current'][$key]['lv3'];
                            $DataSummary['current'][$key]['lv4'] += $DataSummary2['current'][$key]['lv4'];
                            $DataSummary['current'][$key]['lv5'] += $DataSummary2['current'][$key]['lv5'];
                            $DataSummary['current'][$key]['lv6'] += $DataSummary2['current'][$key]['lv6'];
                            $DataSummary['current'][$key]['lv7'] += $DataSummary2['current'][$key]['lv7'];
                            $DataSummary['current'][$key]['lv8'] += $DataSummary2['current'][$key]['lv8'];
                            $DataSummary['current'][$key]['lv9'] += $DataSummary2['current'][$key]['lv9'];
                            $DataSummary['current'][$key]['lv10'] += $DataSummary2['current'][$key]['lv10'];
                        }
                        if ($DataSummary2['before'][$key]['summary'] != '') {
                            $DataSummary['before'][$key]['summary'] += $DataSummary2['before'][$key]['summary'];
                        }
                    }
                }

                foreach ($data['CurrentEmployee'] as $key => $current) {
                    $data['CurrentEmployee'][$key]['percent'] = 0;
                    $data['SummaryCurrentsum'] += $current['summary'];
                    $data['SummaryCurrentdirector'] += $current['director'];
                    $data['lv1'] += $current['lv1'];
                    $data['lv2'] += $current['lv2'];
                    $data['lv3'] += $current['lv3'];
                    $data['lv4'] += $current['lv4'];
                    $data['lv5'] += $current['lv5'];
                    $data['lv6'] += $current['lv6'];
                    $data['lv7'] += $current['lv7'];
                    $data['lv8'] += $current['lv8'];
                    $data['lv9'] += $current['lv9'];
                    $data['lv10'] += $current['lv10'];
                }

                if ($data['BeforeEmployee'] != '' && !is_null($data['BeforeEmployee'])) {

                    foreach ($data['CurrentEmployee'] as $keyitem => $item) {
//                          

                        $sumcurrent = $item['summary'] + $item['director'];
                        $sumbefore = $data['BeforeEmployee'][$keyitem]['summary'] + $data['BeforeEmployee'][$keyitem]['director'];


                        $data['SummaryCurrent'] += $sumcurrent;
//                        
                        $data['SummaryBeforesum'] += $data['BeforeEmployee'][$keyitem]['summary'];
                        $data['SummaryBeforedirector'] += $data['BeforeEmployee'][$keyitem]['director'];
                        $data['SummaryBefore'] += $sumbefore;
                        if ($sumbefore != 0) {
                            $data['CurrentEmployee'][$keyitem]['percent'] = (($sumcurrent - $sumbefore) * 100) / $sumbefore;
                        }

                        $data['SummaryPercentage'] += ($sumcurrent + $sumbefore);
                    }
                    if ($data['SummaryBefore'] != 0) {
                        $data['SummarysumPercentage'] = (($data['SummaryCurrent'] - $data['SummaryBefore']) * 100) / $data['SummaryBefore'];
                    }
                }


                array_push($DataList, $data);
            }
            //lv8
            $monthName = PersonalController::getMonthName($curMonth);

            $data = [];
            $data['Position'] = 'นักวิชาการ 8';
//            $data['Month'] = $monthName;
            $data['Quarter'] = ($i + 1);
//            $data['Year'] = ($curYear);
            $data['SummaryCurrentsum'] = 0;
            $data['SummaryBeforesum'] = 0;
            $data['SummaryCurrentdirector'] = 0;
            $data['SummaryBeforedirector'] = 0;
            $data['SummaryCurrent'] = 0;
            $data['SummaryBefore'] = 0;
            $data['SummaryPercentage'] = 0;
            $data['SummarysumPercentage'] = 0;
            // get cooperative type

            $data['CurrentEmployee'] = PersonalService::get8($curYear, $curMonth);

            $data['BeforeEmployee'] = PersonalService::get8($beforeYear, $curMonth);
            $data['CurrentEmployee'][0]['director'] = intval(0);
            $data['BeforeEmployee'][0]['director'] = intval(0);
            $data['CurrentEmployee'][0]['sumc'] = 0;
            $data['CurrentEmployee'][0]['sumb'] = 0;
            $data['CurrentEmployee'][0]['sum'] = 0;
            if ($data['BeforeEmployee'] != '' && !is_null($data['BeforeEmployee'])) {

                $sumcurrent = $data['CurrentEmployee'][0]['summary'];
                $sumbefore = $data['BeforeEmployee'][0]['summary'];
                $data['CurrentEmployee'][0]['sumc'] = $sumcurrent;
                $data['CurrentEmployee'][0]['sumb'] = $sumbefore;
                $data['CurrentEmployee'][0]['sum'] = $sumcurrent + $sumbefore;
                $data['SummaryCurrent'] += $sumcurrent;
                $data['SummaryCurrentsum'] = $data['CurrentEmployee'][0]['summary'];
                $data['SummaryBeforesum'] = $data['BeforeEmployee'][0]['summary'];
                $data['SummaryBeforedirector'] += $data['BeforeEmployee'][0]['director'];
                $data['SummaryBefore'] += $sumbefore;
                if ($sumbefore != 0) {
                    $data['CurrentEmployee'][0]['percent'] = (($sumcurrent - $sumbefore) * 100) / $sumbefore;
                }

                $data['SummaryPercentage'] += ($sumcurrent + $sumbefore);
            }
            if ($data['SummaryBefore'] != 0) {
                $data['SummarysumPercentage'] = (($data['SummaryCurrent'] - $data['SummaryBefore']) * 100) / $data['SummaryBefore'];
            }

            array_push($DataList, $data);

            foreach ($DataSummary['current'] as $key => $t) {
                $DataSummary['text'] = 'รวม';
                $DataSummary['lv1'] += $t['lv1'];
                $DataSummary['lv2'] += $t['lv2'];
                $DataSummary['lv3'] += $t['lv3'];
                $DataSummary['lv4'] += $t['lv4'];
                $DataSummary['lv5'] += $t['lv5'];
                $DataSummary['lv6'] += $t['lv6'];
                $DataSummary['lv7'] += $t['lv7'];
                $DataSummary['lv8'] += $t['lv8'];
                $DataSummary['lv9'] += $t['lv9'];
                $DataSummary['lv10'] += $t['lv10'];
                if ($DataSummary['before'][$key]['summary'] != 0 && $DataSummary['before'][$key]['summary'] != '') {
                    $DataSummary['current'][$key]['percent'] = (($t['summary'] - $DataSummary['before'][$key]['summary']) * 100) / $DataSummary['before'][$key]['summary'];
                } else {
                    $DataSummary['current'][$key]['percent'] = 0;
                }
            }
            $DataSummary['Monthname'] = ($curQuarter) . ' (' . ($curQuarter == 1 ? $curYear + 543 + 1 : $curYear + 543) . ')';

            array_push($Summary, $DataSummary);
            $curQuarter++;
            if ($curQuarter > 4) {
                $curQuarter = 1;
            }
        }

        // print_r($Summary);
        //  die();
        return ['DataList' => $DataList, 'Summary' => $Summary];
    }

    public function getAnnuallyDataList($condition) {

        $loop = intval($condition['YearTo']) - intval($condition['YearFrom']) + 1;
        $curYear = $condition['YearFrom'];
        $calcYear = intval($curYear) - 1;
        $beforeYear = $calcYear - 1;
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        $positiontype = PersonalService::getPositiontype();
        $DataList = [];
        $Summary = [];


        for ($i = 0; $i < $loop; $i++) {

            foreach ($positiontype as $key => $value) {




                $data = [];
                $data['Position'] = $value['positiontype'];
                $data['textdate'] = ($condition['YearFrom'] + 543) . ' - ' . ($condition['YearTo'] + 543);

                $data['Year'] = ($curYear);
                $data['SummaryCurrentsum'] = 0;
                $data['SummaryBeforesum'] = 0;
                $data['SummaryCurrentdirector'] = 0;
                $data['SummaryBeforedirector'] = 0;
                $data['SummaryCurrent'] = 0;
                $data['SummaryBefore'] = 0;
                $data['SummaryPercentage'] = 0;
                $data['SummarysumPercentage'] = 0;
//                $data['CurrentEmployee'] = PersonalService::getMainList($curYear, $monthList[0], $value['positiontype']);
//                $data['BeforeEmployee'] = PersonalService::getMainList($beforeYear, $monthList[0], $value['positiontype']);
                $DataSummary = [];
//                $DataSummary['current'] = PersonalService::getMainListsheet3($curYear, $monthList[0]);
//                $DataSummary['before'] = PersonalService::getMainListsheet3($beforeYear, $monthList[0]);
                $DataSummary['Year'] = ($curYear);
                $start = 0;

                for ($j = 0; $j < count($monthList); $j++) {
                    $data['CurrentEmployee'] = PersonalService::getMainList($curYear, $monthList[$j], $value['positiontype']);
                    $data['BeforeEmployee'] = PersonalService::getMainList($beforeYear, $monthList[$j], $value['positiontype']);
                    $DataSummary['current'] = PersonalService::getMainListsheet3($curYear, $monthList[$j]);
                    $DataSummary['before'] = PersonalService::getMainListsheet3($beforeYear, $monthList[$j]);
                    if (sizeof($data['CurrentEmployee']) > 0) {
                        $start = $j + 1;
                        //   print_r($DataSummary);
                        break;
                    }
                }

                for ($j = $start; $j < count($monthList); $j++) {
                    $curMonth = $monthList[$j];
                    if (intval($curMonth) == 1) {
                        $calcYear++;
                        $beforeYear = $calcYear - 1;
                    }
                    $data2['CurrentEmployee'] = PersonalService::getMainList($curYear, $curMonth, $value['positiontype']);
                    $data2['BeforeEmployee'] = PersonalService::getMainList($beforeYear, $curMonth, $value['positiontype']);
                    foreach ($data2['CurrentEmployee'] as $key => $itemdata2) {
                        $data['CurrentEmployee'][$key]['summary'] += $itemdata2['summary'];
                        $data['CurrentEmployee'][$key]['director'] += $itemdata2['director'];
                        $data['CurrentEmployee'][$key]['lv1'] += $itemdata2['lv1'];
                        $data['CurrentEmployee'][$key]['lv2'] += $itemdata2['lv2'];
                        $data['CurrentEmployee'][$key]['lv3'] += $itemdata2['lv3'];
                        $data['CurrentEmployee'][$key]['lv4'] += $itemdata2['lv4'];
                        $data['CurrentEmployee'][$key]['lv5'] += $itemdata2['lv5'];
                        $data['CurrentEmployee'][$key]['lv6'] += $itemdata2['lv6'];
                        $data['CurrentEmployee'][$key]['lv7'] += $itemdata2['lv7'];
                        $data['CurrentEmployee'][$key]['lv8'] += $itemdata2['lv8'];
                        $data['CurrentEmployee'][$key]['lv9'] += $itemdata2['lv9'];
                        $data['CurrentEmployee'][$key]['lv10'] += $itemdata2['lv10'];

                        $data['BeforeEmployee'][$key]['summary'] += $data2['BeforeEmployee'][$key]['summary'];
                        $data['BeforeEmployee'][$key]['director'] += $data2['BeforeEmployee'][$key]['director'];
                    }



                    $DataSummary2['current'] = PersonalService::getMainListsheet3($curYear, $curMonth);
                    $DataSummary2['before'] = PersonalService::getMainListsheet3($beforeYear, $curMonth);

                    if (sizeof($DataSummary2['current']) > 0) {
                        foreach ($DataSummary2['current'] as $key => $itemsm) {
                            if ($DataSummary2['current'][$key]['summary'] != '') {
                                $DataSummary['current'][$key]['summary'] += $DataSummary2['current'][$key]['summary'];
                            }
                            if ($DataSummary2['before'][$key]['summary'] != '') {
                                $DataSummary['before'][$key]['summary'] += $DataSummary2['before'][$key]['summary'];
                            }
                        }
                    }
                }

                foreach ($data['CurrentEmployee'] as $key => $current) {
                    $data['lv1'] += $current['lv1'];
                    $data['lv2'] += $current['lv2'];
                    $data['lv3'] += $current['lv3'];
                    $data['lv4'] += $current['lv4'];
                    $data['lv5'] += $current['lv5'];
                    $data['lv6'] += $current['lv6'];
                    $data['lv7'] += $current['lv7'];
                    $data['lv8'] += $current['lv8'];
                    $data['lv9'] += $current['lv9'];
                    $data['lv10'] += $current['lv10'];
                    $data['CurrentEmployee'][$key]['percent'] = 0;
                    $data['SummaryCurrentsum'] += $current['summary'];
                    $data['SummaryCurrentdirector'] += $current['director'];
                }

                if ($data['BeforeEmployee'] != '' && !is_null($data['BeforeEmployee'])) {

                    foreach ($data['CurrentEmployee'] as $keyitem => $item) {
//                          

                        $sumcurrent = $item['summary'] + $item['director'];
                        $sumbefore = $data['BeforeEmployee'][$keyitem]['summary'] + $data['BeforeEmployee'][$keyitem]['director'];


                        $data['SummaryCurrent'] += $sumcurrent;
//                        
                        $data['SummaryBeforesum'] += $data['BeforeEmployee'][$keyitem]['summary'];
                        $data['SummaryBeforedirector'] += $data['BeforeEmployee'][$keyitem]['director'];
                        $data['SummaryBefore'] += $sumbefore;
                        if ($sumbefore != 0) {
                            $data['CurrentEmployee'][$keyitem]['percent'] = (($sumcurrent - $sumbefore) * 100) / $sumbefore;
                        }

                        $data['SummaryPercentage'] += ($sumcurrent + $sumbefore);
                    }
                    if ($data['SummaryBefore'] != 0) {
                        $data['SummarysumPercentage'] = (($data['SummaryCurrent'] - $data['SummaryBefore']) * 100) / $data['SummaryBefore'];
                    }
                }


                array_push($DataList, $data);
            }
            //lv8
            $monthName = PersonalController::getMonthName($curMonth);

            $data = [];
            $data['Position'] = 'นักวิชาการ 8';
//            $data['Month'] = $monthName;
//            $data['Quarter'] = ($i + 1);
            $data['Year'] = ($curYear);
            $data['SummaryCurrentsum'] = 0;
            $data['SummaryBeforesum'] = 0;
            $data['SummaryCurrentdirector'] = 0;
            $data['SummaryBeforedirector'] = 0;
            $data['SummaryCurrent'] = 0;
            $data['SummaryBefore'] = 0;
            $data['SummaryPercentage'] = 0;
            $data['SummarysumPercentage'] = 0;
            // get cooperative type

            $data['CurrentEmployee'] = PersonalService::get8($curYear, $curMonth);

            $data['BeforeEmployee'] = PersonalService::get8($beforeYear, $curMonth);
            $data['CurrentEmployee'][0]['director'] = intval(0);
            $data['BeforeEmployee'][0]['director'] = intval(0);
            $data['CurrentEmployee'][0]['sumc'] = 0;
            $data['CurrentEmployee'][0]['sumb'] = 0;
            $data['CurrentEmployee'][0]['sum'] = 0;
            if ($data['BeforeEmployee'] != '' && !is_null($data['BeforeEmployee'])) {

                $sumcurrent = $data['CurrentEmployee'][0]['summary'];
                $sumbefore = $data['BeforeEmployee'][0]['summary'];
                $data['CurrentEmployee'][0]['sumc'] = $sumcurrent;
                $data['CurrentEmployee'][0]['sumb'] = $sumbefore;
                $data['CurrentEmployee'][0]['sum'] = $sumcurrent + $sumbefore;
                $data['SummaryCurrent'] += $sumcurrent;
                $data['SummaryCurrentsum'] = $data['CurrentEmployee'][0]['summary'];
                $data['SummaryBeforesum'] = $data['BeforeEmployee'][0]['summary'];
                $data['SummaryBeforedirector'] += $data['BeforeEmployee'][0]['director'];
                $data['SummaryBefore'] += $sumbefore;
                if ($sumbefore != 0) {
                    $data['CurrentEmployee'][0]['percent'] = (($sumcurrent - $sumbefore) * 100) / $sumbefore;
                }

                $data['SummaryPercentage'] += ($sumcurrent + $sumbefore);
            }
            if ($data['SummaryBefore'] != 0) {
                $data['SummarysumPercentage'] = (($data['SummaryCurrent'] - $data['SummaryBefore']) * 100) / $data['SummaryBefore'];
            }

            array_push($DataList, $data);
            foreach ($DataSummary['current'] as $key => $t) {

                $DataSummary['text'] = 'รวม';
                $DataSummary['lv1'] += $t['lv1'];
                $DataSummary['lv2'] += $t['lv2'];
                $DataSummary['lv3'] += $t['lv3'];
                $DataSummary['lv4'] += $t['lv4'];
                $DataSummary['lv5'] += $t['lv5'];
                $DataSummary['lv6'] += $t['lv6'];
                $DataSummary['lv7'] += $t['lv7'];
                $DataSummary['lv8'] += $t['lv8'];
                $DataSummary['lv9'] += $t['lv9'];
                $DataSummary['lv10'] += $t['lv10'];
                if ($DataSummary['before'][$key]['summary'] != 0 && $DataSummary['before'][$key]['summary'] != '') {
                    $DataSummary['current'][$key]['percent'] = (($t['summary'] - $DataSummary['before'][$key]['summary']) * 100) / $DataSummary['before'][$key]['summary'];
                } else {
                    $DataSummary['current'][$key]['percent'] = 100;
                }
            }
            $DataSummary['Monthname'] = ($curYear + 543);
            array_push($Summary, $DataSummary);
            $curYear++;
        }
        // exit;

        return ['DataList' => $DataList, 'Summary' => $Summary];
    }

    public function getMonthList($condition) {
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

        $positiontype = PersonalService::getPositiontype();
        $mf = PersonalController::getMonthName($condition['MonthFrom']);
        $mt = PersonalController::getMonthName($condition['MonthTo']);
//        die();
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
            foreach ($positiontype as $key => $value) {

                //     $region_id = $value['RegionID'];
                $monthName = PersonalController::getMonthName($curMonth);

                $data = [];
                //  $data['text'] = 'รวม';
                $data['textdate'] = $mf . ' - ' . $mt . ' ' . $condition['YearTo'];
                $data['Month'] = $monthName;
                $data['Position'] = $value['positiontype'];
//                $data['Quarter'] = ($i + 1);
//                $data['Year'] = ($curYear);
                $data['SummaryCurrentsum'] = 0;
                $data['SummaryBeforesum'] = 0;
                $data['SummaryCurrentdirector'] = 0;
                $data['SummaryBeforedirector'] = 0;
                $data['SummaryCurrent'] = 0;
                $data['SummaryBefore'] = 0;
                $data['SummaryCurrentout'] = 0;
                $data['SummaryBeforeout'] = 0;
                $data['SummaryPercentage'] = 0;
                $data['SummarysumPercentage'] = 0;
                $data['lv1'] = 0;
                $data['lv2'] = 0;
                $data['lv3'] = 0;
                $data['lv4'] = 0;
                $data['lv5'] = 0;
                $data['lv6'] = 0;
                $data['lv7'] = 0;
                $data['lv8'] = 0;
                $data['lv9'] = 0;
                $data['lv10'] = 0;
                // get cooperative type

                $data['CurrentEmployee'] = PersonalService::getMainList($curYear, $curMonth, $value['positiontype']);

                $data['BeforeEmployee'] = PersonalService::getMainList($beforeYear, $curMonth, $value['positiontype']);

//
                foreach ($data['CurrentEmployee'] as $key => $current) {
                    $data['CurrentEmployee'][$key]['sumc'] = 0;
                    $data['CurrentEmployee'][$key]['sumb'] = 0;
                    $data['CurrentEmployee'][$key]['sum'] = 0;
                    $data['CurrentEmployee'][$key]['percent'] = 0;
                    $data['SummaryCurrentsum'] += $current['summary'];
                    $data['SummaryCurrentdirector'] += $current['director'];
                    $data['SummaryCurrentout'] += $current['outsource'];
                    $data['lv1'] += $current['lv1'];
                    $data['lv2'] += $current['lv2'];
                    $data['lv3'] += $current['lv3'];
                    $data['lv4'] += $current['lv4'];
                    $data['lv5'] += $current['lv5'];
                    $data['lv6'] += $current['lv6'];
                    $data['lv7'] += $current['lv7'];
                    $data['lv8'] += $current['lv8'];
                    $data['lv9'] += $current['lv9'];
                    $data['lv10'] += $current['lv10'];
                }

                if ($data['BeforeEmployee'] != '' && !is_null($data['BeforeEmployee'])) {

                    foreach ($data['CurrentEmployee'] as $keyitem => $item) {
//                          

                        $sumcurrent = $item['summary'] + $item['director'] + $item['outsource'];
                        $sumbefore = $data['BeforeEmployee'][$keyitem]['summary'] + $data['BeforeEmployee'][$keyitem]['director'] + $data['BeforeEmployee'][$keyitem]['outsource'];
                        $data['CurrentEmployee'][$keyitem]['sumc'] = $sumcurrent;
                        $data['CurrentEmployee'][$keyitem]['sumb'] = $sumbefore;
                        $data['CurrentEmployee'][$keyitem]['sum'] = $sumbefore + $sumcurrent;
                        $data['SummaryCurrent'] += $sumcurrent;
//                        
                        $data['SummaryBeforesum'] += $data['BeforeEmployee'][$keyitem]['summary'];
                        $data['SummaryBeforedirector'] += $data['BeforeEmployee'][$keyitem]['director'];
                        $data['SummaryBeforeout'] += $data['BeforeEmployee'][$keyitem]['outsource'];
                        $data['SummaryBefore'] += $sumbefore;
                        if ($sumbefore != 0) {
                            $data['CurrentEmployee'][$keyitem]['percent'] = (($sumcurrent - $sumbefore) * 100) / $sumbefore;
                        } else {
                            $data['CurrentEmployee'][$keyitem]['percent'] = 100;
                        }

                        $data['SummaryPercentage'] += ($sumcurrent + $sumbefore);
                    }
                    if ($data['SummaryBefore'] != 0) {
                        $data['SummarysumPercentage'] = (($data['SummaryCurrent'] - $data['SummaryBefore']) * 100) / $data['SummaryBefore'];
                    } else {
                        $data['SummarysumPercentage'] = 100;
                    }
                }

                $data['Status'] = '';
                $data['Description'] = [
                    'months' => $curMonth
                    , 'years' => $curYear
                ];


                array_push($DataList, $data);

                #### End of cooperative 
            }
//lv8
            $monthName = PersonalController::getMonthName($curMonth);

            $data = [];
            $data['Position'] = 'นักวิชาการ 8';
            $data['Month'] = $monthName;
            //   $data['text'] = 'รวม';
//            $data['Quarter'] = ($i + 1);
//            $data['Year'] = ($curYear);
            $data['SummaryCurrentsum'] = 0;
            $data['SummaryBeforesum'] = 0;
            $data['SummaryCurrentdirector'] = 0;
            $data['SummaryBeforedirector'] = 0;
            $data['SummaryCurrent'] = 0;
            $data['SummaryBefore'] = 0;
            $data['SummaryCurrentout'] = 0;
            $data['SummaryBeforeout'] = 0;
            $data['SummaryPercentage'] = 0;
            $data['SummarysumPercentage'] = 0;
            // get cooperative type

            $data['CurrentEmployee'] = PersonalService::get8($curYear, $curMonth);

            $data['BeforeEmployee'] = PersonalService::get8($beforeYear, $curMonth);
            $data['CurrentEmployee'][0]['director'] = intval(0);
            $data['BeforeEmployee'][0]['director'] = intval(0);
            $data['CurrentEmployee'][0]['sumc'] = 0;
            $data['CurrentEmployee'][0]['sumb'] = 0;
            $data['CurrentEmployee'][0]['sum'] = 0;
            if ($data['BeforeEmployee'] != '' && !is_null($data['BeforeEmployee'])) {

                $sumcurrent = $data['CurrentEmployee'][0]['summary'];
                $sumbefore = $data['BeforeEmployee'][0]['summary'];
                $data['CurrentEmployee'][0]['sumc'] = $sumcurrent;
                $data['CurrentEmployee'][0]['sumb'] = $sumbefore;
                $data['CurrentEmployee'][0]['sum'] = $sumcurrent + $sumbefore;
                $data['SummaryCurrent'] += $sumcurrent;
                $data['SummaryCurrentsum'] = $data['CurrentEmployee'][0]['summary'];
                $data['SummaryBeforesum'] = $data['BeforeEmployee'][0]['summary'];
                $data['SummaryBeforedirector'] += $data['BeforeEmployee'][0]['director'];
                $data['SummaryBefore'] += $sumbefore;
                if ($sumbefore != 0) {
                    $data['CurrentEmployee'][0]['percent'] = (($sumcurrent - $sumbefore) * 100) / $sumbefore;
                } else {
                    $data['CurrentEmployee'][0]['percent'] = 100;
                }

                $data['SummaryPercentage'] += ($sumcurrent + $sumbefore);
            }
            if ($data['SummaryBefore'] != 0) {
                $data['SummarysumPercentage'] = (($data['SummaryCurrent'] - $data['SummaryBefore']) * 100) / $data['SummaryBefore'];
            } else {
                $data['SummarysumPercentage'] = 100;
            }

            array_push($DataList, $data);

            //tb2
            $DataSummary2['current'] = PersonalService::getMainListsheet3($curYear, $curMonth);
            $DataSummary2['before'] = PersonalService::getMainListsheet3($beforeYear, $curMonth);
            $DataSummary2['lv1'] = 0;
            $DataSummary2['lv2'] = 0;
            $DataSummary2['lv3'] = 0;
            $DataSummary2['lv4'] = 0;
            $DataSummary2['lv5'] = 0;
            $DataSummary2['lv6'] = 0;
            $DataSummary2['lv7'] = 0;
            $DataSummary2['lv8'] = 0;
            $DataSummary2['lv9'] = 0;
            $DataSummary2['lv10'] = 0;
            foreach ($DataSummary2['current'] as $key => $t) {

                $DataSummary2['text'] = 'รวม';
                $DataSummary2['lv1'] += $t['lv1'];
                $DataSummary2['lv2'] += $t['lv2'];
                $DataSummary2['lv3'] += $t['lv3'];
                $DataSummary2['lv4'] += $t['lv4'];
                $DataSummary2['lv5'] += $t['lv5'];
                $DataSummary2['lv6'] += $t['lv6'];
                $DataSummary2['lv7'] += $t['lv7'];
                $DataSummary2['lv8'] += $t['lv8'];
                $DataSummary2['lv9'] += $t['lv9'];
                $DataSummary2['lv10'] += $t['lv10'];
                if ($DataSummary2['before'][$key]['summary'] != 0 && $DataSummary2['before'][$key]['summary'] != '') {
                    $DataSummary2['current'][$key]['percent'] = (($t['summary'] - $DataSummary2['before'][$key]['summary']) * 100) / $DataSummary2['before'][$key]['summary'];
                } else {
                    $DataSummary2['current'][$key]['percent'] = 100;
                }
            }
            $DataSummary2['Monthname'] = $monthName;
            array_push($DataSummary, $DataSummary2);
            $curMonth++;
        }

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

}
