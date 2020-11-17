<?php

namespace App\Controller;

use App\Service\FoodService;
use App\Service\MasterGoalService;
use App\Service\SpermService;
use App\Service\GoalMissionService;
use App\Service\TravelService;
use App\Service\CowBreedService;
use App\Service\CowGroupService;
use App\Service\TrainingCowBreedService;
use App\Service\InseminationService;
use App\Service\VeterinaryService;
use App\Service\MineralService;
use App\Service\SpermSaleService;
use App\Service\MouService;
use App\Service\MBIService;
use App\Service\MSIService;
use App\Service\ProductionInfoService;
use App\Service\ProductionSaleInfoService;
use App\Service\LostInProcessService;
use App\Service\LostOutProcessService;
use App\Service\LostWaitSaleService;
use App\Service\FactoryService;
use PHPExcel;

class SubcommitteeReportController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    private function getMonthshName($month) {
        switch ($month) {
            case 1 : $monthTxt = 'ม.ค.';
                break;
            case 2 : $monthTxt = 'ก.พ.';
                break;
            case 3 : $monthTxt = 'มี.ค.';
                break;
            case 4 : $monthTxt = 'ม.ย.';
                break;
            case 5 : $monthTxt = 'พ.ค.';
                break;
            case 6 : $monthTxt = 'มิ.ย.';
                break;
            case 7 : $monthTxt = 'ก.ค.';
                break;
            case 8 : $monthTxt = 'ส.ค.';
                break;
            case 9 : $monthTxt = 'ก.ย.';
                break;
            case 10 : $monthTxt = 'ต.ค.';
                break;
            case 11 : $monthTxt = 'พ.ย.';
                break;
            case 12 : $monthTxt = 'ธ.ค.';
                break;
        }
        return $monthTxt;
    }

    private function getMonthName($month) {
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

    public function exportsubreportExcel($request, $response) {
        try {
            $obj = $request->getParsedBody();
            $condition = $obj['obj']['condition'];


            $objPHPExcel = new PHPExcel();

            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'สรุปรายงานผลการดำเนินงานประจำ ปี ' . ($condition['YearFrom'] + 543);
                    $objPHPExcel = $this->generatesheet1($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet2($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet3($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet4($objPHPExcel, $condition, $header);
                    break;
                case 'monthly' :$header = 'สรุปรายงานผลการดำเนินงานประจำเดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ปี ' . ($condition['YearTo'] + 543);
                    $objPHPExcel = $this->generatesheet1($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet2($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet3($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet4($objPHPExcel, $condition, $header);
                    break;
                case 'quarter' :$header = 'สรุปรายงานผลการดำเนินงานประจำ ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543);
                    $objPHPExcel = $this->generatesheet1($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet2($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet3($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet4($objPHPExcel, $condition, $header);
                    break;

                default : $result = null;
            }
//            die();
            // $filename = 'MIS_Report-รายงานรายเดือน' . '_' . date('YmdHis') . '.xlsx';
            $filename = 'MIS_Report-Subcommittee_' . '_' . date('YmdHis') . '.xlsx';
            $filepath = '../../files/files/download/' . $filename;

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            $objWriter->setPreCalculateFormulas();


            $objWriter->save($filepath);

            $this->data_result['DATA'] = 'files/files/download/' . $filename;

            return $this->returnResponse(200, $this->data_result, $response);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function generatesheet1($objPHPExcel, $condition, $header) {
        $objPHPExcel->getActiveSheet()->setTitle("สรุป");

        $mastername = ['บริการสัตวแพทย์', 'ผสมเทียม', 'ผลิตน้ำนม', 'ผลิตน้ำเชื้อแช่แข็ง', 'แร่ธาตุ พรีมิกซ์ และอาหาร', 'ปัจจัยการเลี้ยงโค', 'ฝึกอบรม', 'จำหน่ายน้ำเชื้อแช่แข็ง', 'ข้อมูลการผลิต', 'ข้อมูลการขาย', 'ข้อมูลรับซื้อน้ำนม', 'ข้อมูลจำหน่ายน้ำนม'];




        if ($condition['DisplayType'] == 'annually') {
            $year = $condition['YearFrom'];
            $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row = 0;
            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ปี ' . ($condition['YearFrom'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ผลการดำเนินงานปี ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('D4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ปี ' . ($condition['YearFrom'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');
            $data = [];

            foreach ($mastername as $item) {
                $mastes = MasterGoalService::getList('Y', $item);
                $detail['name'] = $item;

                $detail['data'] = [];
                foreach ($mastes as $itemmaster) {

                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearFrom']);
                    $detail2['target'] = 0;
                    $detail2['actual'] = 0;
                    $detail2['percen'] = 0;
                    foreach ($monthList as $i => $monthloop) {
                        $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year - $yearlist[$i], $monthloop);
                        switch ($itemmaster['menu_type']) {
                            case 'ผสมเทียม' :
                                $actually = InseminationService::getDetailmonth($year - $yearlist[$i], $monthloop, 3);
                                break;
                            case 'บริการสัตวแพทย์' :
                                $actually = VeterinaryService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                $actually = SpermService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ท่องเที่ยว' :
                                $actually = TravelService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id']);

                                break;
                            case 'ปัจจัยการเลี้ยงโค' :
                                $actually = CowBreedService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ข้อมูลฝูงโค' :
                                $actually = CowGroupService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'ฝึกอบรม' :
                                $actually = TrainingCowBreedService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                $actually = MineralService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                $actually = SpermSaleService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'ข้อมูลการผลิต' :
                                $actually = ProductionInfoService::getDetailList2($year - $yearlist[$i], $monthloop);
                                break;
                            case 'ข้อมูลการขาย' :
                                $actually = ProductionSaleInfoService::getDetailList2($year - $yearlist[$i], $monthloop);
                                break;
                            case 'ข้อมูลรับซื้อน้ำนม' :
                                $actually = MBIService::getListMBIreoprt2($year - $yearlist[$i], $monthloop);
                                break;
                            case 'ข้อมูลจำหน่ายน้ำนม' :
                                $actually = MSIService::getListMSIreoprt2($year - $yearlist[$i], $monthloop);
                                break;

                            default : $result = null;
                        }

                        $detail2['mission'] = $itemmaster['goal_name'];
                        $detail2['unit'] = $mission[0]['unit'];
                        $detail2['target'] += $avg[0]['amount'];
                        $detail2['actual'] += $actually['amount'];
                        if ($detail2['target'] > 0) {
                            $detail2['percen'] += ($detail2['actual'] * 100) / $detail2['target'];
                        } else {
                            $detail2['percen'] += 0;
                        }
                    }
                    array_push($detail['data'], $detail2);
                    //       
                }
                array_push($data, $detail);
            }
        } else if ($condition['DisplayType'] == 'monthly') {
            $row = 0;
            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ผลการดำเนินงานเดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('D4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');


            foreach ($mastername as $key => $item) {
                $mastes = MasterGoalService::getList('Y', $item);
                // $detail['name'] = $item;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), ($position + $key) . '.' . $item);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setBold(true);
                $row++;
//                $detail['data'] = [];
                foreach ($mastes as $keyitem => $itemmaster) {
                    $subposition = 1;
                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearTo']);

                    $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);
                    switch ($itemmaster['menu_type']) {
                        case 'ผสมเทียม' :
                            $actually = InseminationService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], 3);
                            break;
                        case 'บริการสัตวแพทย์' :
                            $actually = VeterinaryService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ผลิตน้ำเชื้อแช่แข็ง' :
                            $actually = SpermService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ท่องเที่ยว' :
                            $actually = TravelService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id']);

                            break;
                        case 'ปัจจัยการเลี้ยงโค' :
                            $actually = CowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ข้อมูลฝูงโค' :
                            $actually = CowGroupService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            break;
                        case 'ฝึกอบรม' :
                            $actually = TrainingCowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            break;
                        case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                            $actually = MineralService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                            $actually = SpermSaleService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            break;
                        case 'ข้อมูลการผลิต' :
                            $actually = ProductionInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
                            break;
                        case 'ข้อมูลการขาย' :
                            $actually = ProductionSaleInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
                            break;
                        case 'ข้อมูลรับซื้อน้ำนม' :
                            $actually = MBIService::getListMBIreoprt2($condition['YearTo'], $condition['MonthFrom']);
                            break;
                        case 'ข้อมูลจำหน่ายน้ำนม' :
                            $actually = MSIService::getListMSIreoprt2($condition['YearTo'], $condition['MonthFrom']);
                            break;

                        default : $result = null;
                    }

                    $detail2['mission'] = $itemmaster['goal_name'];
                    $detail2['unit'] = $mission[0]['unit'];
                    $detail2['target'] = $avg[0]['amount'];
                    $detail2['actual'] = $actually['amount'];
                    if ($detail2['target'] > 0) {
                        $detail2['percen'] = ($detail2['actual'] * 100) / $detail2['target'];
                    } else {
                        $detail2['percen'] = 0;
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), '  ' . ($position + $key) . '.' . ($subposition + $keyitem) . ' ' . $detail2['mission']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $detail2['unit']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $detail2['target']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $detail2['actual']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $detail2['percen']);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(14);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . (6 + $row))->getFont()->setSize(14);
                    $objPHPExcel->getActiveSheet()->getStyle('C' . (6 + $row))->getFont()->setSize(14);
                    $objPHPExcel->getActiveSheet()->getStyle('D' . (6 + $row))->getFont()->setSize(14);
                    $objPHPExcel->getActiveSheet()->getStyle('E' . (6 + $row))->getFont()->setSize(14);
                    $row++;
                    // array_push($detail['data'], $detail2);
                }
                //       array_push($data, $detail);
            }
        } else {
            $year = $condition['YearFrom'];

            if ($condition['QuarterFrom'] == 1) {
                $year--;
            }
            if ($condition['QuarterFrom'] == 1) {
                $monthList = [10, 11, 12];
            } else if ($condition['QuarterFrom'] == 2) {
                $monthList = [1, 2, 3];
            } else if ($condition['QuarterFrom'] == 3) {
                $monthList = [4, 5, 6];
            } else if ($condition['QuarterFrom'] == 4) {
                $monthList = [7, 8, 9];
            }
            $row = 0;
            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ผลการดำเนินงานไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('D4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');
            $data = [];
            $type['goal_type'] = DBI;
            foreach ($mastername as $item) {
                $mastes = MasterGoalService::getList('Y', $item, $type);
                $detail['name'] = $item;

                $detail['data'] = [];
                foreach ($mastes as $itemmaster) {

                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearFrom']);
                    $detail2['target'] = 0;
                    $detail2['actual'] = 0;
                    $detail2['percen'] = 0;
                    foreach ($monthList as $monthloop) {
                        $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year, $monthloop);
                        switch ($itemmaster['menu_type']) {
                            case 'ผสมเทียม' :
                                $actually = InseminationService::getDetailmonth($year, $monthloop, 3);
                                break;
                            case 'บริการสัตวแพทย์' :
                                $actually = VeterinaryService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                $actually = SpermService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ท่องเที่ยว' :
                                $actually = TravelService::getDetailmonth($year, $monthloop, $itemmaster['id']);

                                break;
                            case 'ปัจจัยการเลี้ยงโค' :
                                $actually = CowBreedService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ข้อมูลฝูงโค' :
                                $actually = CowGroupService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'ฝึกอบรม' :
                                $actually = TrainingCowBreedService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                $actually = MineralService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                $actually = SpermSaleService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'ข้อมูลการผลิต' :
                                $actually = ProductionInfoService::getDetailList2($year, $monthloop);
                                break;
                            case 'ข้อมูลการขาย' :
                                $actually = ProductionSaleInfoService::getDetailList2($year, $monthloop);
                                break;
                            case 'ข้อมูลรับซื้อน้ำนม' :
                                $actually = MBIService::getListMBIreoprt2($year, $monthloop);
                                break;
                            case 'ข้อมูลจำหน่ายน้ำนม' :
                                $actually = MSIService::getListMSIreoprt2($year, $monthloop);
                                break;

                            default : $result = null;
                        }

                        $detail2['mission'] = $itemmaster['goal_name'];
                        $detail2['unit'] = $mission[0]['unit'];
                        $detail2['target'] += $avg[0]['amount'];
                        $detail2['actual'] += $actually['amount'];
                        if ($detail2['target'] > 0) {
                            $detail2['percen'] += ($detail2['actual'] * 100) / $detail2['target'];
                        } else {
                            $detail2['percen'] += 0;
                        }
                    }
                    array_push($detail['data'], $detail2);
                    //       
                }
                array_push($data, $detail);
            }
        }
        session_start();

        foreach ($data as $key => $itemdata) {
            $_SESSION["postion"] = ($position + $key);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), ($position + $key) . '.' . $itemdata['name']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setBold(true);
            $row++;
            $subposition = 1;
            foreach ($itemdata['data'] as $key2 => $item2) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), '  ' . ($position + $key) . '.' . ($subposition + $key2) . ' ' . $item2['mission']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $item2['unit']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $item2['target']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $item2['actual']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $item2['percen']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('B' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('C' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('D' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('E' . (6 + $row))->getFont()->setSize(14);
                $row++;
            }
        }

        // header style

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18);

        $objPHPExcel->getActiveSheet()->getStyle('A4:E5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E5')->getFont()->setSize(16);


        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:E5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );

        $_SESSION["row"] = 6 + $row;
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('B6:E' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E' . (6 + $row ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C6:E' . (6 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E' . (6 + $row + 1))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    ),
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generatesheet2($objPHPExcel, $condition, $header) {
        $mastername = ['สัตวแพท', 'ผสมเทียม', 'ผลิตน้ำนม', 'ผลิตน้ำเชื้อแช่แข็ง', 'แร่ธาตุ พรีมิกซ์ และอาหาร', 'ปัจจัยการเลี้ยงโค', 'ฝึกอบรม', 'จำหน่ายน้ำเชื้อแช่แข็ง'];
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $objPHPExcel->createSheet(1);
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 2-3");
        $row = 0;
        if ($condition['DisplayType'] == 'annually') {
            $position = 1;


            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '1.ผลการดำเนินงานด้านกิจการโคนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ปี ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ปี' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ' . $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');
            $objPHPExcel->getActiveSheet()->setCellValue('K5', $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');
            $data = [];
            $type['goal_type'] = DBI;
            foreach ($mastername as $key => $item) {
                $mastes = MasterGoalService::getList('Y', $item, $type);
                $detail['name'] = $item;

                $detail['beforemonth']['amount'] = 0;
                $detail['target']['amount'] = 0;
                $detail['collectmonth']['amount'] = 0;
                $detail['permonth']['amount'] = 0;
                $detail['beforeyear']['amount'] = 0;
                $detail['perbeforeyear']['amount'] = 0;
                $detail['yeartarget']['amount'] = 0;
                $detail['targetoct']['amount'] = 0;
                $detail['collectoct']['amount'] = 0;
                $detail['peroct']['amount'] = 0;
                $detail['beforemonth']['price_value'] = 0;
                $detail['target']['price_value'] = 0;
                $detail['collectmonth']['price_value'] = 0;
                $detail['permonth']['price_value'] = 0;
                $detail['beforeyear']['price_value'] = 0;
                $detail['perbeforeyear']['price_value'] = 0;
                $detail['yeartarget']['price_value'] = 0;
                $detail['targetoct']['price_value'] = 0;
                $detail['collectoct']['price_value'] = 0;
                $detail['peroct']['price_value'] = 0;
                $detail['unit'] = 0;
                foreach ($mastes as $keyitem => $itemmaster) {

                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearFrom']);
                    $detail['unit'] = $mission[0]['unit'];
                    $detail['yeartarget']['amount'] += $mission[0]['amount'];
                    $detail['yeartarget']['price_value'] += $mission[0]['price_value'] / 1000000;

                    $detail['targetoct']['amount'] += $mission[0]['amount'];
                    $detail['targetoct']['price_value'] += $mission[0]['price_value'] / 1000000;
                    if ($detail['targetoct']['amount'] > 0) {
                        $detail['peroct']['amount'] += ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
                    } else {
                        $detail['peroct']['amount'] += 0;
                    }
                    if ($detail['targetoct']['price_value'] > 0) {
                        $detail['peroct']['price_value'] += ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
                    } else {
                        $detail['peroct']['price_value'] += 0;
                    }
                    foreach ($monthList as $key => $ml) {

                        $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearFrom'] - $yearlist[$key], $ml);

                        $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearFrom'] - $yearlist[$key], $ml);
                        switch ($itemmaster['menu_type']) {
                            case 'ผสมเทียม' :
                                $actually = InseminationService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, 3);
                                $beforeactually = InseminationService::getDetailmonth(($condition['YearFrom'] - $yearlist[$key]) - 1, $ml, 3);

                                break;
                            case 'สัตวแพท' :
                                $actually = VeterinaryService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = VeterinaryService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                $actually = SpermService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = SpermService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'ท่องเที่ยว' :
                                $actually = TravelService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id']);
                                $beforeactually = TravelService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id']);

                                break;
                            case 'ปัจจัยการเลี้ยงโค' :
                                $actually = CowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = CowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'ข้อมูลฝูงโค' :
                                $actually = CowGroupService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = CowGroupService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'ฝึกอบรม' :
                                $actually = TrainingCowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = TrainingCowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                $actually = MineralService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = MineralService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                $actually = SpermSaleService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = SpermSaleService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);


                                break;

                            default : $result = null;
                        }
                        $detail['beforemonth']['amount'] += $beforeavg[0]['amount'];
                        $detail['beforemonth']['price_value'] += $beforeavg[0]['price_value'] / 1000000;
                        $detail['target']['amount'] += $avg[0]['amount'];
                        $detail['target']['price_value'] += $avg[0]['price_value'] / 1000000;
                        $detail['collectmonth']['amount'] += $actually['amount'];
                        $detail['collectmonth']['price_value'] += $actually['price'] / 1000000;
                        if ($detail['target']['amount'] > 0) {
                            $detail['permonth']['amount'] += ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
                        } else {
                            $detail['permonth']['amount'] += 0;
                        }
                        if ($detail['target']['price_value'] > 0) {
                            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
                        } else {
                            $detail['permonth']['price_value'] += 0;
                        }
//
                        $detail['beforeyear']['amount'] += $beforeactually['amount'];
                        $detail['beforeyear']['price_value'] += $beforeactually['price'];
                        if ($detail['beforeyear']['amount'] > 0) {
                            $detail['perbeforeyear']['amount'] += (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
                        } else {
                            $detail['perbeforeyear']['amount'] += 0;
                        }
                        if ($detail['beforeyear']['price_value'] > 0) {
                            $detail['perbeforeyear']['price_value'] += (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
                        } else {
                            $detail['perbeforeyear']['price_value'] += 0;
                        }


                        $detail['collectoct']['amount'] += $actually['amount'];
                        $detail['collectoct']['price_value'] += $actually['price'] / 1000000;
                    }
                }
                array_push($data, $detail);
            }
        } else if ($condition['DisplayType'] == 'monthly') {
            $beforemonth = $condition['MonthFrom'];
            if ($condition['MonthFrom'] == 1) {
                $beforemonth = 12;
            } else {
                $beforemonth--;
            }

            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '1.ผลการดำเนินงานด้านกิจการโคนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', $this->getMonthName($beforemonth) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ' . $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');
            $objPHPExcel->getActiveSheet()->setCellValue('K5', $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');
            $data = [];
            $type['goal_type'] = DBI;
            foreach ($mastername as $key => $item) {
                $mastes = MasterGoalService::getList('Y', $item, $type);
                $detail['name'] = $item;

                $detail['beforemonth']['amount'] = 0;
                $detail['target']['amount'] = 0;
                $detail['collectmonth']['amount'] = 0;
                $detail['permonth']['amount'] = 0;
                $detail['beforeyear']['amount'] = 0;
                $detail['perbeforeyear']['amount'] = 0;
                $detail['yeartarget']['amount'] = 0;
                $detail['targetoct']['amount'] = 0;
                $detail['collectoct']['amount'] = 0;
                $detail['peroct']['amount'] = 0;
                $detail['beforemonth']['price_value'] = 0;
                $detail['target']['price_value'] = 0;
                $detail['collectmonth']['price_value'] = 0;
                $detail['permonth']['price_value'] = 0;
                $detail['beforeyear']['price_value'] = 0;
                $detail['perbeforeyear']['price_value'] = 0;
                $detail['yeartarget']['price_value'] = 0;
                $detail['targetoct']['price_value'] = 0;
                $detail['collectoct']['price_value'] = 0;
                $detail['peroct']['price_value'] = 0;
                $detail['unit'] = 0;
                foreach ($mastes as $keyitem => $itemmaster) {

                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearTo']);

                    $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);

                    $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);
                    switch ($itemmaster['menu_type']) {
                        case 'ผสมเทียม' :
                            $actually = InseminationService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], 3);
                            $beforeactually = InseminationService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], 3);

                            break;
                        case 'สัตวแพท' :
                            $actually = VeterinaryService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = VeterinaryService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ผลิตน้ำเชื้อแช่แข็ง' :
                            $actually = SpermService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = SpermService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ท่องเที่ยว' :
                            $actually = TravelService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id']);
                            $beforeactually = TravelService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id']);

                            break;
                        case 'ปัจจัยการเลี้ยงโค' :
                            $actually = CowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = CowBreedService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ข้อมูลฝูงโค' :
                            $actually = CowGroupService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = CowGroupService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ฝึกอบรม' :
                            $actually = TrainingCowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = TrainingCowBreedService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                            $actually = MineralService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = MineralService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                            $actually = SpermSaleService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = SpermSaleService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);


                            break;

                        default : $result = null;
                    }
                    $detail['beforemonth']['amount'] += $beforeavg[0]['amount'];
                    $detail['beforemonth']['price_value'] += $beforeavg[0]['price_value'] / 1000000;
                    $detail['target']['amount'] += $avg[0]['amount'];
                    $detail['target']['price_value'] += $avg[0]['price_value'] / 1000000;
                    $detail['collectmonth']['amount'] += $actually['amount'];
                    $detail['collectmonth']['price_value'] += $actually['price'] / 1000000;
                    if ($detail['target']['amount'] > 0) {
                        $detail['permonth']['amount'] += ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
                    } else {
                        $detail['permonth']['amount'] += 0;
                    }
                    if ($detail['target']['price_value'] > 0) {
                        $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
                    } else {
                        $detail['permonth']['price_value'] += 0;
                    }
//
                    $detail['beforeyear']['amount'] += $beforeactually['amount'];
                    $detail['beforeyear']['price_value'] += $beforeactually['price'] / 1000000;
                    ;
                    if ($detail['beforeyear']['amount'] > 0) {
                        $detail['perbeforeyear']['amount'] += (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
                    } else {
                        $detail['perbeforeyear']['amount'] += 0;
                    }
                    if ($detail['beforeyear']['price_value'] > 0) {
                        $detail['perbeforeyear']['price_value'] += (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
                    } else {
                        $detail['perbeforeyear']['price_value'] += 0;
                    }

                    $detail['unit'] = $mission[0]['unit'];
                    $detail['yeartarget']['amount'] += $mission[0]['amount'];
                    $detail['yeartarget']['price_value'] += $mission[0]['price_value'];
                    foreach ($monthList as $key => $ml) {
                        $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);
                        $detail['targetoct']['amount'] += $octavg[0]['amount'];
                        $detail['targetoct']['price_value'] += $octavg[0]['price_value'];
                        switch ($itemmaster['menu_type']) {
                            case 'ผสมเทียม' :
                                $actually = InseminationService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, 3);
                                break;
                            case 'สัตวแพท' :
                                $actually = VeterinaryService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                break;
                            case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                $actually = SpermService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                break;
                            case 'ท่องเที่ยว' :
                                $actually = TravelService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id']);

                                break;
                            case 'ปัจจัยการเลี้ยงโค' :
                                $actually = CowBreedService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                break;
                            case 'ข้อมูลฝูงโค' :
                                $actually = CowGroupService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                break;
                            case 'ฝึกอบรม' :
                                $actually = TrainingCowBreedService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                break;
                            case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                $actually = MineralService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                break;
                            case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                $actually = SpermSaleService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $condition['MonthFrom'], $itemmaster['id'], 3);
                                break;

                            default : $result = null;
                        }
                        $detail['collectoct']['amount'] += $actually['amount'];
                        $detail['collectoct']['price_value'] += $actually['price'] / 1000000;
                        ;
                        if ($ml == $condition['MonthFrom']) {
                            break;
                        }
                    }
                    if ($detail['targetoct']['amount'] > 0) {
                        $detail['peroct']['amount'] += ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
                    } else {
                        $detail['peroct']['amount'] += 0;
                    }
                    if ($detail['targetoct']['price_value'] > 0) {
                        $detail['peroct']['price_value'] += ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
                    } else {
                        $detail['peroct']['price_value'] += 0;
                    }





                    // array_push($detail['data'], $detail2);
                }
                array_push($data, $detail);
            }
        } else {
            $beforeQuarter = $condition['QuarterFrom'];
            $year = $condition['YearFrom'];
            $beforeyear = $condition['YearFrom'];
            $loop = [10, 11, 12];
            if ($condition['QuarterFrom'] == 1) {
                $montharr = [10, 11, 12];
                $beforemontharr = [7, 8, 9];
                $year--;
                $beforeyear--;
                $beforeQuarter = 4;
            } else if ($condition['QuarterFrom'] == 2) {
                $montharr = [1, 2, 3];
                $beforemontharr = [10, 11, 12];
                $beforeyear--;
                $loop = [10, 11, 12, 1, 2, 3];
                $beforeQuarter--;
            } else if ($condition['QuarterFrom'] == 3) {
                $montharr = [4, 5, 6];
                $beforemontharr = [1, 2, 3];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6];
                $beforeQuarter--;
            } else if ($condition['QuarterFrom'] == 4) {
                $montharr = [7, 8, 9];
                $beforemontharr = [4, 5, 6];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
                $beforeQuarter--;
            }

            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '1.ผลการดำเนินงานด้านกิจการโคนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ไตรมาสที่ ' . $beforeQuarter . ' ' . ($year + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ไตรมาสที่1 - ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');
            $objPHPExcel->getActiveSheet()->setCellValue('K5', 'ไตรมาสที่1 - ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');
            $data = [];
            $type['goal_type'] = DBI;

            foreach ($mastername as $key => $item) {
                $mastes = MasterGoalService::getList('Y', $item, $type);
                $detail['name'] = $item;

                $detail['beforemonth']['amount'] = 0;
                $detail['target']['amount'] = 0;
                $detail['collectmonth']['amount'] = 0;
                $detail['permonth']['amount'] = 0;
                $detail['beforeyear']['amount'] = 0;
                $detail['perbeforeyear']['amount'] = 0;
                $detail['yeartarget']['amount'] = 0;
                $detail['targetoct']['amount'] = 0;
                $detail['collectoct']['amount'] = 0;
                $detail['peroct']['amount'] = 0;
                $detail['beforemonth']['price_value'] = 0;
                $detail['target']['price_value'] = 0;
                $detail['collectmonth']['price_value'] = 0;
                $detail['permonth']['price_value'] = 0;
                $detail['beforeyear']['price_value'] = 0;
                $detail['perbeforeyear']['price_value'] = 0;
                $detail['yeartarget']['price_value'] = 0;
                $detail['targetoct']['price_value'] = 0;
                $detail['collectoct']['price_value'] = 0;
                $detail['peroct']['price_value'] = 0;
                $detail['unit'] = 0;
                foreach ($mastes as $keyitem => $itemmaster) {

                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearFrom']);

                    foreach ($beforemontharr as $bm) {
                        $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $beforeyear, $bm);
                        $detail['beforemonth']['amount'] += $beforeavg[0]['amount'];
                        $detail['beforemonth']['price_value'] += $beforeavg[0]['price_value'] / 1000000;
                    }

                    foreach ($montharr as $ma) {
                        $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year, $ma);
                        switch ($itemmaster['menu_type']) {
                            case 'ผสมเทียม' :
                                $actually = InseminationService::getDetailmonth($year, $ma, 3);
                                $beforeactually = InseminationService::getDetailmonth($year - 1, $ma, 3);
                                break;
                            case 'สัตวแพท' :
                                $actually = VeterinaryService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = VeterinaryService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;
                            case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                $actually = SpermService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = SpermService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;
                            case 'ท่องเที่ยว' :
                                $actually = TravelService::getDetailmonth($year, $ma, $itemmaster['id']);
                                $beforeactually = TravelService::getDetailmonth($year - 1, $ma, $itemmaster['id']);
                                break;
                            case 'ปัจจัยการเลี้ยงโค' :
                                $actually = CowBreedService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = CowBreedService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;
                            case 'ข้อมูลฝูงโค' :
                                $actually = CowGroupService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = CowGroupService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;
                            case 'ฝึกอบรม' :
                                $actually = TrainingCowBreedService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = TrainingCowBreedService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;
                            case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                $actually = MineralService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = MineralService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);

                                break;
                            case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                $actually = SpermSaleService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = SpermSaleService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;

                            default : $result = null;
                        }

                        $detail['target']['amount'] += $avg[0]['amount'];
                        $detail['target']['price_value'] += $avg[0]['price_value'] / 1000000;
                        ;
                        $detail['collectmonth']['amount'] += $actually['amount'];
                        $detail['collectmonth']['price_value'] += $actually['price'] / 1000000;
                        ;
                        if ($detail['target']['amount'] > 0) {
                            $detail['permonth']['amount'] += ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
                        } else {
                            $detail['permonth']['amount'] += 0;
                        }
                        if ($detail['target']['price_value'] > 0) {
                            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
                        } else {
                            $detail['permonth']['price_value'] += 0;
                        }
                        $detail['beforeyear']['amount'] += $beforeactually['amount'];
                        $detail['beforeyear']['price_value'] += $beforeactually['price'] / 1000000;
                        ;
                        if ($detail['beforeyear']['amount'] > 0) {
                            $detail['perbeforeyear']['amount'] += (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
                        } else {
                            $detail['perbeforeyear']['amount'] += 0;
                        }
                        if ($detail['beforeyear']['price_value'] > 0) {
                            $detail['perbeforeyear']['price_value'] += (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
                        } else {
                            $detail['perbeforeyear']['price_value'] += 0;
                        }

                        $detail['unit'] = $mission[0]['unit'];
                        $detail['yeartarget']['amount'] += $mission[0]['amount'];
                        $detail['yeartarget']['price_value'] += $mission[0]['price_value'] / 1000000;


                        foreach ($loop as $key => $ml) {
                            $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);
                            $detail['targetoct']['amount'] += $octavg[0]['amount'];
                            $detail['targetoct']['price_value'] += $octavg[0]['price_value'];
                            switch ($itemmaster['menu_type']) {
                                case 'ผสมเทียม' :
                                    $actually = InseminationService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, 3);
                                    break;
                                case 'สัตวแพท' :
                                    $actually = VeterinaryService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                    break;
                                case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                    $actually = SpermService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                    break;
                                case 'ท่องเที่ยว' :
                                    $actually = TravelService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id']);

                                    break;
                                case 'ปัจจัยการเลี้ยงโค' :
                                    $actually = CowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                    break;
                                case 'ข้อมูลฝูงโค' :
                                    $actually = CowGroupService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                    break;
                                case 'ฝึกอบรม' :
                                    $actually = TrainingCowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                    break;
                                case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                    $actually = MineralService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                    break;
                                case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                    $actually = SpermSaleService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $condition['MonthFrom'], $itemmaster['id'], 3);
                                    break;

                                default : $result = null;
                            }
                            $detail['collectoct']['amount'] += $actually['amount'];
                            $detail['collectoct']['price_value'] += $actually['price'] / 1000000;
                            ;
                        }
                        if ($detail['targetoct']['amount'] > 0) {
                            $detail['peroct']['amount'] += ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
                        } else {
                            $detail['peroct']['amount'] += 0;
                        }
                        if ($detail['targetoct']['price_value'] > 0) {
                            $detail['peroct']['price_value'] += ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
                        } else {
                            $detail['peroct']['price_value'] += 0;
                        }
                    }
                }
                array_push($data, $detail);
            }
        }
// print

        foreach ($data as $key => $itemdata) {
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), ($position + $key) . '.' . $itemdata['name']);
            $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
            $row++;

            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   จำนวน');
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ' . $itemdata['unit']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['amount']);
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   รายได้');
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ล้านบาท');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['price_value']);
            $row++;
        }

        // header style

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:L2');
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(14);

        $objPHPExcel->getActiveSheet()->getStyle('A4:L5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L5')->getFont()->setSize(16);


        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:L5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('B6:L' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . (6 + $row ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A6:L' . (6 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . (6 + $row - 1 ))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    ),
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesheet3($objPHPExcel, $condition, $header) {
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $objPHPExcel->createSheet(2);
        $objPHPExcel->setActiveSheetIndex(2);
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 4-5");
        $row = 0;
        if ($condition['DisplayType'] == 'annually') {
            $position = 1;


            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. ผลการดำเนินงานด้านอุตสาหกรรมนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ปี ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ปี' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมาย ' . $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'ผลการดำเนินงานสะสม ');
            $objPHPExcel->getActiveSheet()->mergeCells('J4:K4');

            $objPHPExcel->getActiveSheet()->setCellValue('J5', $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('K5', '%/เป้าหมายสะสม');
            $data = [];

            ////////รับซื้อ

            $moumission = MouService::getMission($condition['YearFrom'], $condition['MonthFrom']);
            $mbi = MBIService::getactualMBIDetail($condition['YearFrom'], $condition['MonthFrom']);

            $beforeyearmbi = MBIService::getactualMBIDetail($condition['YearFrom'] - 1, $condition['MonthFrom']);
            $moumissionyear = MouService::getMissionyear($condition['YearFrom']);
            $detail['name'] = '1.การรับซื้อน้ำนม';
            $detail['detailname'] = '1 การรับซื้อน้ำนม (ตัน) ';
            $detail['beforemonth'] = 0;
            $detail['target'] = $moumissionyear['amount'] / 1000;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmbi['sum_amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $moumissionyear['amount'] / 1000;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

//        


            foreach ($monthList as $key => $ml) {
                $beforembi = MBIService::getactualMBIDetail($condition['YearFrom'] - $yearlist[$key] - 1, $ml);
                $detail['beforemonth'] += $beforembi['sum_amount'] / 1000;
                $mbi = MBIService::getactualMBIDetail($condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectmonth'] += $mbi['sum_amount'] / 1000;
                $mbioct = MBIService::getactualMBIDetail($condition['YearFrom'] - $yearlist[$key], $ml);
                $mouoct = MouService::getMission($condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectoct'] += $mbioct['sum_amount'] / 1000;
                $detail['targetoct'] += $mouoct['amount'] / 1000;
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////จำหน่าย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การจำหน่ายน้ำนม';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลจำหน่ายน้ำนม', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $msi = MSIService::getactualMSIDetail($condition['YearTo'], $condition['MonthFrom']);
            $beforemsi = MSIService::getactualMSIDetail($year, $beforemonth);
            $beforeyearmsi = MSIService::getactualMSIDetail($condition['YearTo'] - 1, $condition['MonthFrom']);

            $detail['detailname'] = '2 การจำหน่ายน้ำนม (ตัน) ';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;



            $detail['beforemonth'] = $beforeyearmsi['sum_amount'] / 1000;

            $detail['target'] = $mission[0]['amount'];
            $detail['collectmonth'] = $msi['sum_amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmsi['sum_amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);

                $msioct = MSIService::getactualMSIDetail($condition['YearTo'] - $yearlist[$key], $ml);

                $detail['collectoct'] += $msioct['sum_amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];

//                if ($ml == $condition['MonthFrom']) {
//                    break;
//                }
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////
            /////ข้อมูลการผลิต
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            //   $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            //  $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $pro = ProductionInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
            $beforepro = ProductionInfoService::getDetailList2($year, $beforemonth);
            $beforeyearpro = ProductionInfoService::getDetailList2($condition['YearTo'] - 1, $condition['MonthFrom']);

            $data2 = [];
            $detail['name'] = '2. การผลิต';
            $detail['detailname'] = '2.1 น้ำนมดิบเข้ากระบวนการผลิต (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            $detail['beforemonth'] = $beforeyearpro['amount'] / 1000;

            $detail['target'] = $mission[0]['amount'];
            $detail['collectmonth'] = $pro['amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;


            foreach ($monthList as $key => $ml) {
                $beforepro = ProductionInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key] - 1, $ml);
                $detail['beforemonth'] += $beforepro['amount'] / 1000;
                $pro = ProductionInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectmonth'] += $pro['amount'] / 1000;
                $prooct = ProductionInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key], $ml);
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg['amount'] / 1000;
            }

            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data2, $detail);
            /////ข้อมูลการขาย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการขาย', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
//            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
//            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $pro = ProductionSaleInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
            $beforepro = ProductionSaleInfoService::getDetailList2($year, $beforemonth);
            $beforeyearpro = ProductionSaleInfoService::getDetailList2($condition['YearTo'] - 1, $condition['MonthFrom']);

            $data3 = [];
            $detail['name'] = '3. การจำหน่าย';
            $detail['detailname'] = '3.1  ผลิตภัณฑ์ที่ผลิตได้ (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;
            $detail['beforemonth'] = $beforeyearpro['amount'] / 1000;

            $detail['target'] = $mission[0]['amount'];
            $detail['collectmonth'] = $pro['amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $beforepro = ProductionSaleInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key] - 1, $ml);
                $detail['beforemonth'] += $beforepro['amount'] / 1000;
                $pro = ProductionSaleInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectmonth'] += $pro['amount'] / 1000;
                $prooct = ProductionSaleInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key], $ml);
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg['amount'] / 1000;
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data3, $detail);
        } else if ($condition['DisplayType'] == 'monthly') {
            $beforemonth = $condition['MonthFrom'];
            $year = $condition['YearTo'];
            if ($condition['MonthFrom'] == 1) {
                $beforemonth = 12;
                $year--;
            } else {
                $beforemonth--;
            }

            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. ผลการดำเนินงานด้านอุตสาหกรรมนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', $this->getMonthName($beforemonth) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมาย ' . $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'ผลการดำเนินงานสะสม ');
            $objPHPExcel->getActiveSheet()->mergeCells('J4:K4');

            $objPHPExcel->getActiveSheet()->setCellValue('J5', $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('K5', '%/เป้าหมายสะสม');
            $data = [];
////////รับซื้อ

            $moumission = MouService::getMission($condition['YearTo'], $condition['MonthFrom']);
            $mbi = MBIService::getactualMBIDetail($condition['YearTo'], $condition['MonthFrom']);
            $beforembi = MBIService::getactualMBIDetail($year, $beforemonth);
            $beforeyearmbi = MBIService::getactualMBIDetail($condition['YearTo'] - 1, $condition['MonthFrom']);
            $moumissionyear = MouService::getMissionyear($condition['YearTo']);
            $detail['name'] = '1.การรับซื้อน้ำนม';
            $detail['detailname'] = '1 การรับซื้อน้ำนม (ตัน) ';
            $detail['beforemonth'] = $beforembi['sum_amount'] / 1000;
            $detail['target'] = $moumission['amount'] / 1000;
            $detail['collectmonth'] = $mbi['sum_amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmbi['sum_amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $moumissionyear['amount'] / 1000;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $mbioct = MBIService::getactualMBIDetail($condition['YearTo'] - $yearlist[$key], $ml);
                $mouoct = MouService::getMission($condition['YearTo'] - $yearlist[$key], $ml);
                $detail['collectoct'] += $mbioct['sum_amount'] / 1000;
                $detail['targetoct'] += $mouoct['amount'] / 1000;

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////จำหน่าย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การจำหน่ายน้ำนม';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลจำหน่ายน้ำนม', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $msi = MSIService::getactualMSIDetail($condition['YearTo'], $condition['MonthFrom']);
            $beforemsi = MSIService::getactualMSIDetail($year, $beforemonth);
            $beforeyearmsi = MSIService::getactualMSIDetail($condition['YearTo'] - 1, $condition['MonthFrom']);

            $detail['detailname'] = '2 การจำหน่ายน้ำนม (ตัน) ';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;



            $detail['beforemonth'] = $beforemsi['sum_amount'] / 1000;

            $detail['target'] = $avg[0]['amount'];
            $detail['collectmonth'] = $msi['sum_amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmsi['sum_amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);

                $msioct = MSIService::getactualMSIDetail($condition['YearTo'] - $yearlist[$key], $ml);

                $detail['collectoct'] += $msioct['sum_amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////ข้อมูลการผลิต
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $pro = ProductionInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
            $beforepro = ProductionInfoService::getDetailList2($year, $beforemonth);
            $beforeyearpro = ProductionInfoService::getDetailList2($condition['YearTo'] - 1, $condition['MonthFrom']);

            $data2 = [];
            $detail['name'] = '2. การผลิต';
            $detail['detailname'] = '2.1 น้ำนมดิบเข้ากระบวนการผลิต (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            $detail['beforemonth'] = $beforepro['amount'] / 1000;

            $detail['target'] = $avg[0]['amount'];
            $detail['collectmonth'] = $pro['amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;
            foreach ($monthList as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);

                $prooct = ProductionInfoService::getDetailList2($condition['YearTo'] - $yearlist[$key], $ml);

                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data2, $detail);
            /////ข้อมูลการขาย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการขาย', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $pro = ProductionSaleInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
            $beforepro = ProductionSaleInfoService::getDetailList2($year, $beforemonth);
            $beforeyearpro = ProductionSaleInfoService::getDetailList2($condition['YearTo'] - 1, $condition['MonthFrom']);

            $data3 = [];
            $detail['name'] = '3. การจำหน่าย';
            $detail['detailname'] = '3.1  ผลิตภัณฑ์ที่ผลิตได้ (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            $detail['beforemonth'] = $beforepro['amount'] / 1000;

            $detail['target'] = $avg[0]['amount'];
            $detail['collectmonth'] = $pro['amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;
            foreach ($monthList as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);

                $prooct = ProductionSaleInfoService::getDetailList2($condition['YearTo'] - $yearlist[$key], $ml);

                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data3, $detail);
        } else {
            $beforeQuarter = $condition['QuarterFrom'];
            $year = $condition['YearFrom'];
            $beforeyear = $condition['YearFrom'];
            $loop = [10, 11, 12];
            if ($condition['QuarterFrom'] == 1) {
                $montharr = [10, 11, 12];
                $beforemontharr = [7, 8, 9];
                $year--;
                $beforeyear--;
                $beforeQuarter = 4;
            } else if ($condition['QuarterFrom'] == 2) {
                $montharr = [1, 2, 3];
                $beforemontharr = [10, 11, 12];
                $beforeyear--;
                $loop = [10, 11, 12, 1, 2, 3];
                $beforeQuarter--;
            } else if ($condition['QuarterFrom'] == 3) {
                $montharr = [4, 5, 6];
                $beforemontharr = [1, 2, 3];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6];
                $beforeQuarter--;
            } else if ($condition['QuarterFrom'] == 4) {
                $montharr = [7, 8, 9];
                $beforemontharr = [4, 5, 6];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
                $beforeQuarter--;
            }

            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. ผลการดำเนินงานด้านอุตสาหกรรมนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ไตรมาสที่ ' . $beforeQuarter . ' ' . ($year + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');

            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมาย ไตรมาสที่1 - ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('J4:K4');
            $objPHPExcel->getActiveSheet()->setCellValue('J5', 'ไตรมาสที่1 - ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('K5', '%/เป้าหมายสะสม');
            $data = [];




            foreach ($beforemontharr as $bm) {
                $beforembi = MBIService::getactualMBIDetail($year, $bm);
                $detail['beforemonth'] += $beforembi['sum_amount'] / 1000;
            }
            foreach ($montharr as $ma) {
                $moumission = MouService::getMission($year, $ma);
                $detail['target'] += $moumission['amount'] / 1000;
                $mbi = MBIService::getactualMBIDetail($year, $ma);
                $detail['collectmonth'] += $mbi['sum_amount'] / 1000;
                $beforeyearmbi = MBIService::getactualMBIDetail($year - 1, $ma);
                $detail['beforeyear'] += $beforeyearmbi['sum_amount'] / 1000;
            }

            foreach ($loop as $key => $ml) {
                $mbioct = MBIService::getactualMBIDetail($year - $yearlist[$key], $ml);
                $mouoct = MouService::getMission($year - $yearlist[$key], $ml);
                $detail['collectoct'] += $mbioct['sum_amount'] / 1000;
                $detail['targetoct'] += $mouoct['amount'] / 1000;
            }



            $moumissionyear = MouService::getMissionyear($condition['YearFrom']);
            $detail['name'] = '1.การรับซื้อน้ำนม';
            $detail['detailname'] = '1 การรับซื้อน้ำนม (ตัน) ';



            $detail['permonth'] = 0;

            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $moumissionyear['amount'] / 1000;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;




            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////จำหน่าย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การจำหน่ายน้ำนม';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลจำหน่ายน้ำนม', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearFrom']);


            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);


            $detail['detailname'] = '2 การจำหน่ายน้ำนม (ตัน) ';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($beforemontharr as $bm) {
                $beforemsi = MSIService::getactualMSIDetail($year, $bm);
                $detail['beforemonth'] += $beforemsi['sum_amount'] / 1000;
            }
            foreach ($montharr as $ma) {

                $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year, $ma);
                $detail['target'] = $avg[0]['amount'];
                $msi = MSIService::getactualMSIDetail($year, $ma);
                $detail['collectmonth'] = $msi['sum_amount'] / 1000;
                $beforeyearmsi = MSIService::getactualMSIDetail($year - 1, $ma);
                $detail['beforeyear'] = $beforeyearmsi['sum_amount'] / 1000;
            }

            foreach ($loop as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $year - $yearlist[$key], $ml);

                $msioct = MSIService::getactualMSIDetail($year - $yearlist[$key], $ml);

                $detail['collectoct'] += $msioct['sum_amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];
            }


            $detail['yeartarget'] = $mission[0]['amount'];



            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////
            /////ข้อมูลการผลิต
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearFrom']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $data2 = [];
            $detail['name'] = '2. การผลิต';
            $detail['detailname'] = '2.1 น้ำนมดิบเข้ากระบวนการผลิต (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($beforemontharr as $bm) {
                $beforepro = ProductionInfoService::getDetailList2($year, $bm);
                $detail['beforemonth'] += $beforepro['amount'] / 1000;
            }
            foreach ($montharr as $ma) {

                $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year, $ma);
                $detail['target'] = $avg[0]['amount'];

                $pro = ProductionInfoService::getDetailList2($year, $ma);
                $detail['collectmonth'] = $pro['amount'] / 1000;
                $beforeyearpro = ProductionInfoService::getDetailList2($year - 1, $ma);
                $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            }

            foreach ($loop as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $year - $yearlist[$key], $ml);

                $prooct = ProductionInfoService::getDetailList2($year - $yearlist[$key], $ml);

                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];
            }


            $detail['yeartarget'] = $mission[0]['amount'];



            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data2, $detail);
            /////
            /////ข้อมูลการขาย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการขาย', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearFrom']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $data3 = [];
            $detail['name'] = '3. การจำหน่าย';
            $detail['detailname'] = '3.1 ผลิตภัณฑ์ที่ผลิตได้ (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($beforemontharr as $bm) {
                $beforepro = ProductionSaleInfoService::getDetailList2($year, $bm);
                $detail['beforemonth'] += $beforepro['amount'] / 1000;
            }
            foreach ($montharr as $ma) {

                $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year, $ma);
                $detail['target'] = $avg[0]['amount'];

                $pro = ProductionSaleInfoService::getDetailList2($year, $ma);
                $detail['collectmonth'] = $pro['amount'] / 1000;
                $beforeyearpro = ProductionSaleInfoService::getDetailList2($year - 1, $ma);
                $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            }

            foreach ($loop as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $year - $yearlist[$key], $ml);

                $prooct = ProductionSaleInfoService::getDetailList2($year - $yearlist[$key], $ml);

                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];
            }


            $detail['yeartarget'] = $mission[0]['amount'];



            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data3, $detail);
            /////
        }
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $data[0]['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        foreach ($data as $key => $itemdata) {


            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $itemdata['detailname']);

            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['peroct']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $data2[0]['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        foreach ($data2 as $key => $itemdata) {


            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $itemdata['detailname']);

            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['peroct']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $data3[0]['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        foreach ($data3 as $key => $itemdata) {


            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $itemdata['detailname']);

            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['peroct']);
            $row++;
        }
        // header style

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:L2');
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(14);

        $objPHPExcel->getActiveSheet()->getStyle('A4:L5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L5')->getFont()->setSize(16);


        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:K5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('B6:L' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . (6 + $row ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A6:L' . (6 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A4:K' . (6 + $row - 1 ))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    ),
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesheet4($objPHPExcel, $condition, $header) {
        $objPHPExcel->createSheet(3);
        $objPHPExcel->setActiveSheetIndex(3);
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $FactoryList = FactoryService::getList();
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 6");
        $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2.2 การสูญเสียน้ำนมสดและผลิตภัณฑ์นมของสำนักงานภาค');
        $data = [];
        $data2 = [];
        $data3 = [];



        if ($condition['DisplayType'] == 'annually') {
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ผลการดำเนินงาน ปี ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'สะสม ' . $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:N4');
            foreach ($FactoryList as $key => $fac) {

                $detail = [];
                $detail2 = [];
                $detail3 = [];
                foreach ($monthList as $key => $ml) {

                    $dataIN = LostInProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail['sum_amount'] += $dataIN['sum_amount'];
                    $detail['sum_baht'] += $dataIN['sum_baht'];
                    $dataOUT = LostOutProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail2['sum_amount'] += $dataOUT['sum_amount'];
                    $detail2['sum_baht'] += $dataOUT['sum_baht'];

                    $dataWAIT = LostWaitSaleService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail3['sum_amount'] += $dataWAIT['sum_amount'];
                    $detail3['sum_baht'] += $dataWAIT['sum_baht'];
                }
                array_push($data, $detail);
                array_push($data2, $detail2);
                array_push($data3, $detail3);
            }
            foreach ($FactoryList as $key => $fac) {

                $detail = [];
                $detail2 = [];
                $detail3 = [];
                foreach ($monthList as $key => $ml) {

                    $dataIN = LostInProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail['sum_amount'] += $dataIN['sum_amount'];
                    $detail['sum_baht'] += $dataIN['sum_baht'];
                    $dataOUT = LostOutProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail2['sum_amount'] += $dataOUT['sum_amount'];
                    $detail2['sum_baht'] += $dataOUT['sum_baht'];

                    $dataWAIT = LostWaitSaleService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail3['sum_amount'] += $dataWAIT['sum_amount'];
                    $detail3['sum_baht'] += $dataWAIT['sum_baht'];
                }
                array_push($data, $detail);
                array_push($data2, $detail2);
                array_push($data3, $detail3);
            }
        } else if ($condition['DisplayType'] == 'monthly') {
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ผลการดำเนินงาน เดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'สะสม ' . $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:N4');
            foreach ($FactoryList as $key => $fac) {

                $dataIN = LostInProcessService::getMainListreport($condition['YearTo'], $condition['MonthFrom'], $fac['id']);
                $dataOUT = LostOutProcessService::getMainListreport($condition['YearTo'], $condition['MonthFrom'], $fac['id']);
                $dataWAIT = LostWaitSaleService::getMainListreport($condition['YearTo'], $condition['MonthFrom'], $fac['id']);

                array_push($data, $dataIN);
                array_push($data2, $dataOUT);
                array_push($data3, $dataWAIT);
            }
            foreach ($FactoryList as $key => $fac) {
                $detail = [];
                $detail2 = [];
                $detail3 = [];
                foreach ($monthList as $key => $ml) {

                    $dataIN = LostInProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail['sum_amount'] += $dataIN['sum_amount'];
                    $detail['sum_baht'] += $dataIN['sum_baht'];
                    $dataOUT = LostOutProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail2['sum_amount'] += $dataOUT['sum_amount'];
                    $detail2['sum_baht'] += $dataOUT['sum_baht'];

                    $dataWAIT = LostWaitSaleService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail3['sum_amount'] += $dataWAIT['sum_amount'];
                    $detail3['sum_baht'] += $dataWAIT['sum_baht'];


                    if ($ml == $condition['MonthFrom']) {
                        break;
                    }
                }
                array_push($data, $detail);
                array_push($data2, $detail2);
                array_push($data3, $detail3);
            }
        } else {
            $year = $condition['YearFrom'];


            if ($condition['QuarterFrom'] == 1) {
                $montharr = [10, 11, 12];
                $year--;
                $loop = [10, 11, 12];
            } else if ($condition['QuarterFrom'] == 2) {
                $montharr = [1, 2, 3];

                $loop = [10, 11, 12, 1, 2, 3];
            } else if ($condition['QuarterFrom'] == 3) {
                $montharr = [4, 5, 6];

                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6];
            } else if ($condition['QuarterFrom'] == 4) {
                $montharr = [7, 8, 9];

                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ผลการดำเนินงาน ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'สะสม ไตรมาสที่ 1 - ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:N4');
            foreach ($FactoryList as $key => $fac) {

                $detail = [];
                $detail2 = [];
                $detail3 = [];
                foreach ($montharr as $key => $ml) {

                    $dataIN = LostInProcessService::getMainListreport($year, $ml, $fac['id']);
                    $detail['sum_amount'] += $dataIN['sum_amount'];
                    $detail['sum_baht'] += $dataIN['sum_baht'];
                    $dataOUT = LostOutProcessService::getMainListreport($year, $ml, $fac['id']);
                    $detail2['sum_amount'] += $dataOUT['sum_amount'];
                    $detail2['sum_baht'] += $dataOUT['sum_baht'];

                    $dataWAIT = LostWaitSaleService::getMainListreport($year, $ml, $fac['id']);
                    $detail3['sum_amount'] += $dataWAIT['sum_amount'];
                    $detail3['sum_baht'] += $dataWAIT['sum_baht'];
                }
                array_push($data, $detail);
                array_push($data2, $detail2);
                array_push($data3, $detail3);
            }
            foreach ($FactoryList as $key => $fac) {

                $detail = [];
                $detail2 = [];
                $detail3 = [];
                foreach ($loop as $key => $ml) {

                    $dataIN = LostInProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail['sum_amount'] += $dataIN['sum_amount'];
                    $detail['sum_baht'] += $dataIN['sum_baht'];
                    $dataOUT = LostOutProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail2['sum_amount'] += $dataOUT['sum_amount'];
                    $detail2['sum_baht'] += $dataOUT['sum_baht'];

                    $dataWAIT = LostWaitSaleService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail3['sum_amount'] += $dataWAIT['sum_amount'];
                    $detail3['sum_baht'] += $dataWAIT['sum_baht'];
                }
                array_push($data, $detail);
                array_push($data2, $detail2);
                array_push($data3, $detail3);
            }
        }




        $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
        $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
        $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
        $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', 'สภก.');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', 'สภต.');
        $objPHPExcel->getActiveSheet()->setCellValue('C5', 'สภอ.');
        $objPHPExcel->getActiveSheet()->setCellValue('D5', 'สภน.');
        $objPHPExcel->getActiveSheet()->setCellValue('E5', 'สภน.');
        $objPHPExcel->getActiveSheet()->setCellValue('F5', 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('I5', 'สภก.');
        $objPHPExcel->getActiveSheet()->setCellValue('J5', 'สภต.');
        $objPHPExcel->getActiveSheet()->setCellValue('K5', 'สภอ.');
        $objPHPExcel->getActiveSheet()->setCellValue('L5', 'สภน.');
        $objPHPExcel->getActiveSheet()->setCellValue('M5', 'สภน.');
        $objPHPExcel->getActiveSheet()->setCellValue('N5', 'รวม');


        $row = 6;
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '1.การสูญเสีย');
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setBold(true);
        $row++;

        $sumam = $data[0]['sum_amount'] + $data[1]['sum_amount'] + $data[2]['sum_amount'] + $data[3]['sum_amount'] + $data[4]['sum_amount'];
        $sumba = ($data[0]['sum_baht'] + $data[1]['sum_baht'] + $data[2]['sum_baht'] + $data[3]['sum_baht'] + $data[4]['sum_baht']) / 1000000;
        $bsumam = $data[5]['sum_amount'] + $data[6]['sum_amount'] + $data[7]['sum_amount'] + $data[8]['sum_amount'] + $data[9]['sum_amount'];
        $bsumba = ($data[5]['sum_baht'] + $data[6]['sum_baht'] + $data[7]['sum_baht'] + $data[8]['sum_baht'] + $data[9]['sum_baht']) / 1000000;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data[0]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[1]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[2]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[3]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[4]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '   1.1 สูญเสียในกระบวนการผลิต ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[5]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data[6]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data[7]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data[8]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data[9]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumam);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data[0]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[1]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[2]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[3]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[4]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumba);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           มูลค่า ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ล้านบาท');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[5]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data[6]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data[7]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data[8]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data[9]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumba);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           %สูญเสีย ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), '');
        $row++;
////
        $sumam2 = $data2[0]['sum_amount'] + $data2[1]['sum_amount'] + $data2[2]['sum_amount'] + $data2[3]['sum_amount'] + $data2[4]['sum_amount'];
        $sumba2 = ($data2[0]['sum_baht'] + $data2[1]['sum_baht'] + $data2[2]['sum_baht'] + $data2[3]['sum_baht'] + $data2[4]['sum_baht']) / 1000000;
        $bsumam2 = $data2[5]['sum_amount'] + $data2[6]['sum_amount'] + $data2[7]['sum_amount'] + $data2[8]['sum_amount'] + $data2[9]['sum_amount'];
        $bsumba2 = ($data2[5]['sum_baht'] + $data2[6]['sum_baht'] + $data2[7]['sum_baht'] + $data2[8]['sum_baht'] + $data2[9]['sum_baht']) / 1000000;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data2[0]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data2[1]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data2[2]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data2[3]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data2[4]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam2);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '    1.2 สูญเสียหลังกระบวนการผลิต  ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data2[5]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data2[6]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data2[7]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data2[8]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data2[9]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumam2);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data2[0]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data2[1]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data2[2]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data2[3]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data2[4]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumba2);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           มูลค่า ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ล้านบาท');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data2[5]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data2[6]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data2[7]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data2[8]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data2[9]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumba2);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           %สูญเสีย ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), '');
        $row++;
////
        $sumam3 = $data3[0]['sum_amount'] + $data3[1]['sum_amount'] + $data3[2]['sum_amount'] + $data3[3]['sum_amount'] + $data3[4]['sum_amount'];
        $sumba3 = ($data3[0]['sum_baht'] + $data3[1]['sum_baht'] + $data3[2]['sum_baht'] + $data3[3]['sum_baht'] + $data3[4]['sum_baht']) / 1000000;
        $bsumam3 = $data3[5]['sum_amount'] + $data3[6]['sum_amount'] + $data3[7]['sum_amount'] + $data3[8]['sum_amount'] + $data3[9]['sum_amount'];
        $bsumba3 = ($data3[5]['sum_baht'] + $data3[6]['sum_baht'] + $data3[7]['sum_baht'] + $data3[8]['sum_baht'] + $data3[9]['sum_baht']) / 1000000;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data3[0]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data3[1]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data3[2]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data3[3]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data3[4]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam3);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '   1.3 สูญเสียระหว่างรอจำหน่าย ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data3[5]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data3[6]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data3[7]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data3[8]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data3[9]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumam3);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data3[0]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data3[1]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data3[2]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data3[3]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data3[4]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumba3);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           มูลค่า ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ล้านบาท');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data3[5]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data3[6]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data3[7]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data3[8]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data3[9]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumba3);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           %สูญเสีย ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), '');
        $row++;
////
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data[0]['sum_amount'] + $data2[0]['sum_amount'] + $data3[0]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[1]['sum_amount'] + $data2[1]['sum_amount'] + $data3[1]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[2]['sum_amount'] + $data2[2]['sum_amount'] + $data3[2]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[3]['sum_amount'] + $data2[3]['sum_amount'] + $data3[3]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[4]['sum_amount'] + $data2[4]['sum_amount'] + $data3[4]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam + $sumam2 + $sumam3);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '   1.4 สูญเสียทั้งกระบวนการผลิต  ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[5]['sum_amount'] + $data2[5]['sum_amount'] + $data3[5]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data[6]['sum_amount'] + $data2[6]['sum_amount'] + $data3[6]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data[7]['sum_amount'] + $data2[7]['sum_amount'] + $data3[7]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data[8]['sum_amount'] + $data2[8]['sum_amount'] + $data3[8]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data[9]['sum_amount'] + $data2[9]['sum_amount'] + $data3[9]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumam + $bsumam2 + $bsumam3);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data[0]['sum_baht'] + $data2[0]['sum_baht'] + $data3[0]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[1]['sum_baht'] + $data2[1]['sum_baht'] + $data3[1]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[2]['sum_baht'] + $data2[2]['sum_baht'] + $data3[2]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[3]['sum_baht'] + $data2[3]['sum_baht'] + $data3[3]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[4]['sum_baht'] + $data2[4]['sum_baht'] + $data3[4]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumba + $sumba2 + $sumba3);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           มูลค่า ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ล้านบาท');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[5]['sum_baht'] + $data2[5]['sum_baht'] + $data3[5]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data[6]['sum_baht'] + $data2[6]['sum_baht'] + $data3[6]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data[7]['sum_baht'] + $data2[7]['sum_baht'] + $data3[7]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data[8]['sum_baht'] + $data2[8]['sum_baht'] + $data3[8]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data[9]['sum_baht'] + $data2[9]['sum_baht'] + $data3[9]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumba + $bsumba2 + $bsumba3);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           %สูญเสีย ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), '');
        $row++;
////
        // header style

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:L2');
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(16);

        $objPHPExcel->getActiveSheet()->getStyle('A4:N5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:N5')->getFont()->setSize(14);


        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:N5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);

        $objPHPExcel->getActiveSheet()->getStyle('A4:N' . ($row ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A6:N' . ($row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A4:N' . ($row - 1 ))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    ),
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );

////
        //  $postion = $_SESSION["postion"];
        $hrow = $_SESSION["row"];
        session_destroy();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($hrow), '  ' . 'ปริมาณสูญเสียทั้งกระบวนการ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($hrow), 'ตัน');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($hrow), '-');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($hrow), $bsumam + $bsumam2 + $bsumam3);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($hrow), '-');

        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($hrow + 1), '  ' . '% ของการสูญเสียของทั้ง 3 กระบวนการ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($hrow + 1), '%');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($hrow + 1), '-');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($hrow + 1), '');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($hrow + 1), '-');

        $objPHPExcel->getActiveSheet()->getStyle('A' . ($hrow) . ':E' . ($hrow + 1))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($hrow) . ':E' . ($hrow + 1))->getFont()->setBold(true);
////

        return $objPHPExcel;
    }

}
