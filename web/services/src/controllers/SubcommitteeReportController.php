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
use App\Service\MaterialService;
use App\Service\ProductMilkService;
use App\Service\BeginEndingBalanceService;

use PHPExcel;

class SubcommitteeReportController extends Controller {

    protected $logger;
    protected $db;
    protected $total_loss_amount;
    protected $total_loss_amount_percent;

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

            $years = $condition['YearTo'];
            $months = $condition['MonthFrom'];
            $quarters = $condition['QuarterFrom'];
            $time_list = [];

            if ($condition['DisplayType'] == 'annually') {
                $time_list = [
                                [
                                    'year'=>$years - 1,
                                    'month'=>10
                                ],
                                [
                                    'year'=>$years - 1,
                                    'month'=>11
                                ],
                                [
                                    'year'=>$years - 1,
                                    'month'=>12
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>1
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>2
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>3
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>4
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>5
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>6
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>7
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>8
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>9
                                ]
                            ];
            }else if($condition['DisplayType'] == 'monthly'){
                $time_list = [
                                [
                                    'year'=>$years/*$months > 9 ? $years - 1 : $years*/,
                                    'month'=>$months
                                ]
                            ];
            }else{
                if($condition['QuarterFrom'] == 1){
                     $time_list = [
                                [
                                    'year'=>$years - 1,
                                    'month'=>10
                                ],
                                [
                                    'year'=>$years - 1,
                                    'month'=>11
                                ],
                                [
                                    'year'=>$years - 1,
                                    'month'=>12
                                ]
                            ];
                }else if($condition['QuarterFrom'] == 2){
                     $time_list = [
                                [
                                    'year'=>$years,
                                    'month'=>1
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>2
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>3
                                ]
                            ];
                }else if($condition['QuarterFrom'] == 3){
                     $time_list = [
                                [
                                    'year'=>$years,
                                    'month'=>4
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>5
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>6
                                ]
                            ];
                }else if($condition['QuarterFrom'] == 4){
                     $time_list = [
                                [
                                    'year'=>$years,
                                    'month'=>7
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>8
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>9
                                ]
                            ];
                }
            }

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
            $condition['YearFrom'] = $condition['YearTo'];

            $objPHPExcel = new PHPExcel();

            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'สรุปรายงานผลการดำเนินงานประจำ ปี ' . ($condition['YearTo'] + 543);
                    break;
                case 'monthly' :$header = 'สรุปรายงานผลการดำเนินงานประจำเดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ปี ' . ($condition['YearTo'] + 543);
                    //  $objPHPExcel = $this->generatesheet5($objPHPExcel, $condition, $header);
                    break;
                case 'quarter' :$header = 'สรุปรายงานผลการดำเนินงานประจำ ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearTo'] + 543);
                    //   $objPHPExcel = $this->generatesheet5($objPHPExcel, $condition, $header);
                    break;

                default : $result = null;
            }

            $objPHPExcel = $this->generatesheet1($objPHPExcel, $condition, $time_list, $header);
            $objPHPExcel = $this->generatesheet2($objPHPExcel, $condition, $header);
            $objPHPExcel = $this->generatesheet3($objPHPExcel, $condition, $header);
            $objPHPExcel = $this->generatesheet4($objPHPExcel, $condition, $time_list, $header);
            $objPHPExcel = $this->generatesheet5($objPHPExcel, $condition, $time_list, $header);
//            
//            die();
            // $filename = 'MIS_Report-รายงานรายเดือน' . '_' . date('YmdHis') . '.xlsx';

            // set total loss value is summary sheet
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValue('A19', '14. ปริมาณสูญเสียทั้งกระบวนการ');
            $objPHPExcel->getActiveSheet()->setCellValue('B19', ' ตัน');
            $objPHPExcel->getActiveSheet()->setCellValue('D19', number_format($this->total_loss_amount, 2, '.', ''));
            $objPHPExcel->getActiveSheet()->setCellValue('A20', '15. % ของการสูญเสีย');
            $objPHPExcel->getActiveSheet()->setCellValue('B20', ' %');
            $objPHPExcel->getActiveSheet()->setCellValue('D20', number_format($this->total_loss_amount_percent, 2, '.', ''));

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

    private function generatesheet1($objPHPExcel, $condition, $time_list, $header) {
        $objPHPExcel->getActiveSheet()->setTitle("สรุป");
        $mastername = ['การบริการสัตวแพทย์', 'การบริการผสมเทียม', 'การผลิตน้ำนมของฟาร์ม อ.ส.ค.', 'ปริมาณการจำหน่ายแร่ธาตุ', 'ปริมาณการจำหน่ายอาหารสัตว์อื่นๆ', 'การฝึกอบรม', 'รายได้จากน้ำเชื้อแช่แข็ง', 'รายได้อื่นๆ จากการจำหน่ายไนโตรเจนเหลวและปัจจัยการเลี้ยงโคนม', 'บริการชมฟาร์มโคนมฯ', 'ปริมาณการรับซื้อน้ำนม', 'ปริมาณน้ำนมดิบเข้ากระบวนการผลิต', 'ปริมาณการผลิตผลิตภัณฑ์นม', 'ปริมาณการจำหน่าย'];

        $this->logger->info('begin log sheet 1');

        $row = 0;
        $position = 1;

        if($condition['DisplayType'] == 'annually'){
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'เป้าหมาย');
            // $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ปี ' . ($condition['YearTo'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ปี ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('C4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');

        }else if($condition['DisplayType'] == 'monthly'){

            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'เป้าหมาย');
            // $objPHPExcel->getActiveSheet()->setCellValue('C5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('C4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');

        }else{

            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('C4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');
            

        }
        $index = 0;
        // sheet 1 : 1. การบริการสัตวแพทย์
        $index++;
        $menu_type = 'บริการสัตวแพทย์และผสมเทียม';
        $dairy_farming_id = [1,4,20];
        $goal_name = ['ควบคุมโรค','การบริการสัตว์แพทย์','การตรวจทางห้องปฏิบัติการ'];
        $item_type_amount_list = ['โคนม','ปริมาณงาน'];

        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $index. '. การบริการสัตวแพทย์');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ครั้ง');

        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuTypeAndGoalName($menu_type, $goal_name, $m > 9?$y + 1:$y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = VeterinaryService::getDetailmonth($y, $m, '','', $dairy_farming_id, $item_type_amount_list);
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $goal_amount);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $result_amount);
        
        if(!empty($goal_amount)){
            $percent = number_format((($result_amount * 100) / $goal_amount), 2, '.', '');   
            // $percent = ($result_amount * 100) / $goal_amount; 
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 2. การบริการผสมเทียม
        $row++;
        $index++;
        $menu_type = 'บริการสัตวแพทย์และผสมเทียม';
        $goal_name = ['การบริการผสมเทียม','ทะเบียนประวัติโค'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. การบริการผสมเทียม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ครั้ง');

        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuTypeAndGoalName($menu_type, $goal_name, $m > 9?$y + 1:$y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = VeterinaryService::getDetailmonthInsemination($y, $m, '','');
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $goal_amount);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $result_amount);

        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');   
        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 3. การผลิตน้ำนมของฟาร์ม อ.ส.ค.
        $row++;
        $index++;
        $menu_type = 'ข้อมูลฝูงโค';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. การผลิตน้ำนมของฟาร์ม อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = CowGroupService::getDetailmonth($y, $m, '','');
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');    
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 4. ปริมาณการจำหน่ายแร่ธาตุ
        $row++;
        $index++;
        $menu_type = 'แร่ธาตุ พรีมิกซ์ และอาหาร';
        $sub_goal_type_arr = ['พรีมิกซ์','แร่ธาตุ'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณการจำหน่ายแร่ธาตุ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $m > 9?$y + 1:$y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = MineralService::getDetailmonth($y, $m, '','');
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 5. ปริมาณการจำหน่ายอาหารสัตว์อื่นๆ
        $row++;
        $index++;
        $menu_type = 'แร่ธาตุ พรีมิกซ์ และอาหาร';
        $sub_goal_type_arr = ['อาหาร'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณการจำหน่ายอาหารสัตว์อื่นๆ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $m > 9?$y + 1:$y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = MineralService::getDetailmonthFood($y, $m, '','');
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format(($goal_amount / 1000), 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format(($result_amount / 1000), 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', ''); 
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 6. การฝึกอบรม
        $row++;
        $index++;
        $menu_type = 'ฝึกอบรม';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. การฝึกอบรม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ราย');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = TrainingCowBreedService::getDetailmonth($y, $m, '','');
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $goal_amount);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $result_amount);
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 7. รายได้น้ำเชื้อแช่แข็ง
        $row++;
        $index++;
        $menu_type = 'จำหน่ายน้ำเชื้อแช่แข็ง';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. รายได้น้ำเชื้อแช่แข็ง');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ล้านบาท');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['price'];

            $result_data = SpermSaleService::getDetailmonth($y, $m, '','');
            $result_amount += $result_data['price']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 8. รายได้อื่นๆ จากการจำหน่ายไนโตรเจนเหลวและปัจจัยการเลี้ยงโคนม
        $row++;
        $index++;
        
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. รายได้อื่นๆ จากการจำหน่ายไนโตรเจนเหลวและปัจจัยการเลี้ยงโคนม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ล้านบาท');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $this->logger->info('month : '. $m);
            $this->logger->info('year : '. $y);

            $menu_type = 'วัสดุผสมเทียมและเวชภัณฑ์ยาสัตว์';
            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['price'];

            $result_data = MaterialService::getDetailmonth($y, $m, '','');
            $this->logger->info('Nitro material : '. $result_data['price']);
            $result_amount += $result_data['price']; 

            $menu_type = 'ปัจจัยการเลี้ยงดูโค (เคมีภัณฑ์)';
            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['price'];

            $result_data = CowBreedService::getDetailmonth($y, $m, '','');
            $this->logger->info('Nitro cow breed : '. $result_data['price']);
            $result_amount += $result_data['price']; 

        }
        // exit;
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 9. บริการชมฟาร์มโคนมฯ
        $row++;
        $index++;
        $menu_type = 'ท่องเที่ยว';
        $goal_id_list_data = MasterGoalService::getGoalIDByKeyword('รายได้ชมฟาร์ม');

        $goal_id_list = [];
        foreach ($goal_id_list_data as $key => $value) {
            $goal_id_list[] = $value['id'];
        }
        // $goal_id_list = [365,366];//[391,392,393,320,321,322];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. บริการชมฟาร์มโคนมฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ราย');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuTypeAndGoalID($menu_type, $goal_id_list, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = TravelService::getDetailmonth($y, $m, $goal_id_list);
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $goal_amount);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $result_amount);
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 10. ปริมาณการรับซื้อน้ำนม
        $row++;
        $index++;
        $menu_type = 'รับซื้อน้ำนมดิบ (ERP)';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณการรับซื้อน้ำนม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = MBIService::getListMBIreoprt2($y, $m);
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 11. ปริมาณน้ำนมดิบเข้ากระบวนการผลิต
        $row++;
        $index++;
        $menu_type = 'จำหน่ายน้ำนมดิบ (ERP)';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณน้ำนมดิบเข้ากระบวนการผลิต');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $master_list = MasterGoalService::getGoalIDListByName('น้ำนมดิบรับเข้า', 'การสูญเสียในกระบวนการ');

        $id_list = [];
        foreach ($master_list as $master_key => $master_value) {
            $id_list[] = $master_value['id'];
        }

        $mission_list = GoalMissionService::getMissionList($id_list, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
        $goal_id_list = [];
        foreach ($mission_list as $mission_key => $mission_value) {
            $goal_id_list[] = $mission_value['id'];
        }

        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgList($goal_id_list, $y, $m);//GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data[0]['amount'];

            $result_data = LostInProcessService::getAmountList($y, $m, $id_list);//MSIService::getListMSIreoprt2($y, $m);
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $goal_amount / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $result_amount / 1000);
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 12sheet 1 : 13. . ปริมาณการผลิตผลิตภัณฑ์นม
        $row++;
        $index++;
        $menu_type = 'ข้อมูลการผลิต';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณการผลิตผลิตภัณฑ์นม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;

        $FactoryList = FactoryService::getList();
        

        foreach ($FactoryList as $factory_k => $factory_v) {

            $ProductMilkList = ProductMilkService::getList('Y', '', '', $factory_v['id']);
        
            foreach ($ProductMilkList as $prod_k => $prod_v) {
                foreach ($time_list as $t_key => $t_value) {

                    $y = $t_value['year'];
                    $m = $t_value['month'];

                    $avg_data = GoalMissionService::getMissionavgByMenuTypeAndGoalNameKeyword($menu_type, $prod_v['name'], $y, $m, $factory_v['id']);
                    $goal_amount += $avg_data['amount'];
                    
                    $result_data = ProductionInfoService::getDetail($y, $m, $factory_v['id'], $prod_v['id']);
                    $result_amount += $result_data['amount'];
                }
            }

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // sheet 1 : 13. ปริมาณการจำหน่าย
        $row++;
        $index++;
        $menu_type = 'ข้อมูลการขาย';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณการจำหน่าย');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];
            // echo "$menu_type, " . ($m > 9?$y + 1:$y) . ", $m";exit;
            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = ProductionSaleInfoService::getDetailList2($y, $m);
            $result_amount += $result_data['amount']; 

        }
        // echo $goal_amount;exit;
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);


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

        $row++;
        $_SESSION["row"] = 6 + $row;
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('A6:E' . (6 + $row + 1))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E' . (6 + $row + 1 ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C6:E' . (6 + $row + 1))
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
        
        $mastername = ['การบริการสัตวแพทย์'
                , 'การบริการผสมเทียม'
                , 'การบริการและจัดการฟาร์มสหกรณ์'
                , 'การผลิตน้ำนมของฟาร์ม อ.ส.ค.'
                , 'การจำหน่ายอาหารสัตว์'];

        $this->logger->info('begin log sheet 2');

        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $objPHPExcel->createSheet(1);
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 2-3");
        $row = 0;
        $data = [];
        $detail = [];

        $display_year = $condition['YearTo'];

        if ($condition['MonthFrom'] < 10) {
            $display_year--;
            // $year--;
        }

        if ($condition['DisplayType'] == 'monthly') {
            $beforemonth = $condition['MonthFrom'];
            if ($condition['MonthFrom'] == 1) {
                $beforemonth = 12;
                $beforeYear = $condition['YearTo'] - 1;
            } else {
                $beforemonth--;
                $beforeYear = $condition['YearTo'];
            }

            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '1.ผลการดำเนินงานด้านกิจการโคนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', $this->getMonthName($beforemonth) . ' ' . ($beforeYear + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            
            $objPHPExcel->getActiveSheet()->setCellValue('B4',  'เดือน' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('B4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('B5',  'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5',  'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->setCellValue('D5',  '% เป้าหมาย');

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
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ' . $this->getMonthName(10) . ' ' . ($display_year + 543) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');
            $objPHPExcel->getActiveSheet()->setCellValue('K5', $this->getMonthName(10) . ' ' . ($display_year + 543) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');
            
            // sheet 2 : 1. การบริการสัตวแพทย์

            $menu_type = 'บริการสัตวแพทย์และผสมเทียม';
            $detail['name'] = 'การบริการสัตวแพทย์';
            $dairy_farming_id = [1,4,20];
            $goal_name = ['ควบคุมโรค','การบริการสัตว์แพทย์','การตรวจทางห้องปฏิบัติการ'];
            $item_type_amount_list = ['โคนม','ปริมาณงาน'];
            $item_type_price_list = ['ค่าเวชภัณฑ์','ค่าบริการ', 'ค่าวัสดุ', 'ค่าน้ำเชื้อ'];

            // result before selected month 
            $result = VeterinaryService::getDetailmonth($beforeYear, $beforemonth, '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($beforeYear, $beforemonth, '', '', $dairy_farming_id, $item_type_price_list);
            $detail['beforemonth']['price_value'] = empty($result['amount'])?0:$result['amount'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = VeterinaryService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_price_list);
            $detail['collectmonth']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = VeterinaryService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_price_list);
            $detail['beforeyear']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }
            
            $result = GoalMissionService::getMissionYearByMenuTypeAndGoalName($menu_type, $goal_name, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1:$condition['YearTo'];
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = VeterinaryService::getDetailmonth($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_amount_list);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $result = VeterinaryService::getDetailmonthPrice($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_price_list);
                $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            $result = VeterinaryService::getDetailmonth($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_price_list);
            $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ครั้ง';
            $detail['unit_price'] = 'ล้านบาท';
            $detail['type_amount'] = 'จำนวน';
            $detail['type_price'] = 'รายได้';
            array_push($data, $detail);
            $detail = [];

            // sheet 2 : 2. การบริการผสมเทียม

            $menu_type = 'บริการสัตวแพทย์และผสมเทียม';
            $detail['name'] = 'การบริการผสมเทียม';
            $dairy_farming_id = [17, 29];
            $goal_name = ['การบริการผสมเทียม','ทะเบียนประวัติโค'];
            $item_type_amount_list = ['โคนม','ปริมาณงาน'];
            $item_type_price_list = ['ค่าเวชภัณฑ์','ค่าบริการ', 'ค่าวัสดุ', 'ค่าน้ำเชื้อ'];
            // result before selected month 
            $result = VeterinaryService::getDetailmonth($beforeYear, $beforemonth, '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($beforeYear, $beforemonth, '', '', $dairy_farming_id, $item_type_price_list);
            $detail['beforemonth']['price_value'] = empty($result['amount'])?0:$result['amount'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = VeterinaryService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_price_list);
            $detail['collectmonth']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = VeterinaryService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_price_list);
            $detail['beforeyear']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            
            $result = GoalMissionService::getMissionYearByMenuTypeAndGoalName($menu_type, $goal_name, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1:$condition['YearTo'];
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = VeterinaryService::getDetailmonth($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_amount_list);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $result = VeterinaryService::getDetailmonthPrice($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_price_list);
                $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            $result = VeterinaryService::getDetailmonth($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_price_list);
            $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ครั้ง';
            $detail['unit_price'] = 'ล้านบาท';
            $detail['type_amount'] = 'จำนวน';
            $detail['type_price'] = 'รายได้';
            array_push($data, $detail);

            $detail = [];

            // sheet 2 : 3. การบริการจัดการฟาร์มและสหกรณ์

            $menu_type = 'บริการสัตวแพทย์และผสมเทียม';
            $detail['name'] = 'การบริการจัดการฟาร์มและสหกรณ์';
            $dairy_farming_id = [13];
            $goal_name = ['DIP'];
            $item_type_amount_list = ['โคนม','สมาชิก','ปริมาณงาน'];
            $item_type_price_list = ['ค่าเวชภัณฑ์','ค่าบริการ', 'ค่าวัสดุ', 'ค่าน้ำเชื้อ'];

            // result before selected month 
            $result = VeterinaryService::getDetailmonth($beforeYear, $beforemonth, '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($beforeYear, $beforemonth, '', '', $dairy_farming_id, $item_type_price_list);
            $detail['beforemonth']['price_value'] = empty($result['amount'])?0:$result['amount'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = VeterinaryService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_price_list);
            $detail['collectmonth']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = VeterinaryService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id, $item_type_price_list);
            $detail['beforeyear']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            $result = GoalMissionService::getMissionYearByMenuTypeAndGoalName($menu_type, $goal_name, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1 : $condition['YearTo'];
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = VeterinaryService::getDetailmonth($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_amount_list);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $result = VeterinaryService::getDetailmonthPrice($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_price_list);
                $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            $result = VeterinaryService::getDetailmonth($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_amount_list);
            $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($loop_year, $loop_month, '', '', $dairy_farming_id, $item_type_price_list);
            $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ครั้ง';
            $detail['unit_price'] = 'ล้านบาท';
            $detail['type_amount'] = 'ปริมาณ';
            $detail['type_price'] = 'รายได้';
            array_push($data, $detail);

            $detail = [];

            // sheet 2 : 4. การผลิตน้ำนมของฟาร์ม อ.ส.ค.

            $menu_type = 'ข้อมูลฝูงโค';
            $detail['name'] = 'การผลิตน้ำนมของฟาร์ม อ.ส.ค.';
            $dairy_farming_id = [13];
            // result before selected month 
            $result = CowGroupService::getDetailmonth($beforeYear, $beforemonth);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = CowGroupService::getDetailmonth($condition['YearTo'], $condition['MonthFrom']);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = CowGroupService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom']);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            
            $result = GoalMissionService::getMissionYearByMenuType($menu_type, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1 : $condition['YearTo'];
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = CowGroupService::getDetailmonth($loop_year, $loop_month);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            $result = CowGroupService::getDetailmonth($loop_year, $loop_month);
            $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
            $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ตัน';
            $detail['unit_price'] = 'ล้านบาท';

            $detail['type_amount'] = 'ปริมาณ';
            $detail['type_price'] = 'มูลค่า';
            array_push($data, $detail);

            $detail = [];

            // sheet 2 : 5. การจำหน่ายอาหารสัตว์

            $menu_type = 'แร่ธาตุ พรีมิกซ์ และอาหาร';
            $detail['name'] = 'การจำหน่ายอาหารสัตว์ (แร่ธาตุ พรีมิกซ์)';
            $dairy_farming_id = [13];
            $sub_goal_type_arr = ['พรีมิกซ์','แร่ธาตุ'];
            // result before selected month 
            $result = MineralService::getDetailmonth($beforeYear, $beforemonth);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = MineralService::getDetailmonth($condition['YearTo'], $condition['MonthFrom']);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = MineralService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom']);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            
            $result = GoalMissionService::getMissionYearByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1:$condition['YearTo'];
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = MineralService::getDetailmonth($loop_year, $loop_month);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            $result = MineralService::getDetailmonth($loop_year, $loop_month);
            $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
            $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            
            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ตัน';
            $detail['unit_price'] = 'ล้านบาท';

            $detail['type_amount'] = 'ปริมาณการจำหน่ายแร่ธาตุ';
            $detail['type_price'] = 'รายได้การจำหน่ายแร่ธาตุ';
            array_push($data, $detail);

            $detail = [];

            // การจำหน่ายอาหารสัตว์

            $menu_type = 'แร่ธาตุ พรีมิกซ์ และอาหาร';
            $detail['name'] = 'การจำหน่ายอาหารสัตว์ (อาหาร)';
            $dairy_farming_id = [13];
            $sub_goal_type_arr = ['อาหาร'];
            // result before selected month 
            $result = MineralService::getDetailmonthFood($beforeYear, $beforemonth);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = MineralService::getDetailmonthFood($condition['YearTo'], $condition['MonthFrom']);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = MineralService::getDetailmonthFood($condition['YearTo'] - 1, $condition['MonthFrom']);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            
            $result = GoalMissionService::getMissionYearByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1:$condition['YearTo'];
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = MineralService::getDetailmonthFood($loop_year, $loop_month);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            $result = MineralService::getDetailmonthFood($loop_year, $loop_month);
            $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
            $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ตัน';
            $detail['unit_price'] = 'ล้านบาท';

            $detail['type_amount'] = 'ปริมาณการจำหน่ายอาหารอื่นๆ';
            $detail['type_price'] = 'รายได้การจำหน่ายอาหารอื่นๆ';
            array_push($data, $detail);

            // sheet 2 : 7. การฝึกอบรม

            $detail = [];

            $menu_type = 'ฝึกอบรม';
            $detail['name'] = 'การฝึกอบรม';
            // result before selected month 
            $result = TrainingCowBreedService::getDetailmonth($beforeYear, $beforemonth);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = TrainingCowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom']);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = TrainingCowBreedService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom']);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            
            $result = GoalMissionService::getMissionYearByMenuType($menu_type, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['MonthFrom'] < 10 ? $condition['YearTo'] - 1 : $condition['YearTo'];
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = TrainingCowBreedService::getDetailmonth($loop_year, $loop_month);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            $result = TrainingCowBreedService::getDetailmonth($loop_year, $loop_month);
            $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
            $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }
            

            $detail['unit_amount'] = 'ราย';
            $detail['unit_price'] = 'ล้านบาท';

            $detail['type_amount'] = 'ปริมาณ';
            $detail['type_price'] = 'รายได้';
            array_push($data, $detail);

        }
// print

        foreach ($data as $key => $itemdata) {
            $index = $position + $key;
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), ($position + $key) . '.' . $itemdata['name']);
            $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
            $row++;

            if($itemdata['unit_amount'] == 'ตัน'){
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($itemdata['beforemonth']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($itemdata['target']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($itemdata['collectmonth']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format($itemdata['beforeyear']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   ' . $itemdata['type_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ' . $itemdata['unit_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($itemdata['yeartarget']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($itemdata['targetoct']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($itemdata['collectoct']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['amount']);
            }else{
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   ' . $itemdata['type_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ' . $itemdata['unit_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['amount']);
            }
            
            $row++;
            if($itemdata['unit_price'] == 'ล้านบาท'){

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($itemdata['beforemonth']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($itemdata['target']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($itemdata['collectmonth']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format($itemdata['beforeyear']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   ' . $itemdata['type_price']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ' . $itemdata['unit_price']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($itemdata['yeartarget']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($itemdata['targetoct']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($itemdata['collectoct']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['price_value']);

            }else{
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   ' . $itemdata['type_price']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ' . $itemdata['unit_price']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['price_value']);
            }
            

            $row++;
        }

        $detail = [];

        // sheet 2 : 8. ปัจจัยการผลิต
        $index++;
        $menu_type = 'จำหน่ายน้ำเชื้อแช่แข็ง';
        $detail['name'] = 'การจำหน่ายปัจจัยการผลิต';
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), ($index) . '.' . $detail['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        // result before selected month 
        $result = SpermSaleService::getDetailmonth($beforeYear, $beforemonth);
        $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($detail['beforemonth']['price_value'] / 1000000, 2, '.', ''));
            
        // current month goal 
        $result = GoalMissionService::getMissionavgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
        $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($detail['target']['price_value'] / 1000000, 2, '.', ''));

        // result selected month 
        $result = SpermSaleService::getDetailmonth($condition['YearTo'], $condition['MonthFrom']);
        $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($detail['collectmonth']['price_value'] / 1000000, 2, '.', ''));

        if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
        $detail['permonth']['price_value'] = ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($detail['permonth']['price_value'], 2, '.', ''));

        $result = SpermSaleService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom']);
        $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format($detail['beforeyear']['price_value'] / 1000000, 2, '.', ''));
        
        if(!empty($detail['beforeyear']['price_value'])){
            $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
        }else{
            $detail['perbeforeyear']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $detail['perbeforeyear']['price_value']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    รายได้น้ำเชื้อแช่แข็ง');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $result = GoalMissionService::getMissionYearByMenuType($menu_type, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
        $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($detail['yeartarget']['price_value'] / 1000000, 2, '.', ''));

        $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($detail['targetoct']['price_value'] / 1000000, 2, '.', ''));
            
        $loop_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1 : $condition['YearTo'];
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = SpermSaleService::getDetailmonth($loop_year, $loop_month);
            $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $result = SpermSaleService::getDetailmonth($loop_year, $loop_month);
        $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];     

        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($detail['collectoct']['price_value'] / 1000000, 2, '.', ''));
            
        if(!empty($detail['targetoct']['price_value'])){
            $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
        }else{
            $detail['peroct']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail['peroct']['price_value']);
        
        $row++;

        // ไนโตรเจนเหลว
        $detail_nitro = [];
        
        $menu_type = 'วัสดุผสมเทียมและเวชภัณฑ์ยาสัตว์';
        $detail_nitro['name'] = 'ไนโตรเจนเหลว';
        $type_id = [359, 160];

        $result = MaterialService::getDetailmonth($beforeYear, $beforemonth, $type_id);
        $detail_nitro['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];

        $result = GoalMissionService::getMissionavgByMenuTypeAndGoalID($menu_type, $type_id, $condition['YearTo'], $condition['MonthFrom']);
        $detail_nitro['target']['price_value'] = empty($result['price'])?0:$result['price'];

        $result = MaterialService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $type_id);
        $detail_nitro['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];

        $detail_nitro['permonth']['amount'] = ($detail_nitro['collectmonth']['amount'] / $detail_nitro['target']['amount']) * 100;
        $detail_nitro['permonth']['price_value'] += ($detail_nitro['collectmonth']['price_value'] * 100) / $detail_nitro['target']['price_value'];

        $result = MaterialService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $type_id);
        $detail_nitro['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

        if(!empty($detail_nitro['beforeyear']['price_value'])){
            $detail_nitro['perbeforeyear']['price_value'] = (($detail_nitro['collectmonth']['price_value'] - $detail_nitro['beforeyear']['price_value']) * 100) / $detail_nitro['beforeyear']['price_value'];
        }else{
            $detail_nitro['perbeforeyear']['price_value'] = 0;
        }

        $result = GoalMissionService::getMissionYearByMenuTypeAndGoalID($menu_type, $type_id, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
        $detail_nitro['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];
        
        $loop_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1 : $condition['YearTo'];
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = MaterialService::getDetailmonth($loop_year, $loop_month, $type_id);
            $detail_nitro['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $result = MaterialService::getDetailmonth($loop_year, $loop_month, $type_id);
        $detail_nitro['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];

        $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalID($menu_type, $type_id, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
        $detail_nitro['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        
        if(!empty($detail_nitro['targetoct']['price_value'])){
            $detail_nitro['peroct']['price_value'] = ($detail_nitro['collectoct']['price_value'] * 100) / $detail_nitro['targetoct']['price_value'];
        }else{
            $detail_nitro['peroct']['price_value'] = 0;
        }




        // ปัจจัยการเลี้ยงโคนม
        $detail_cowbreed = [];
        
        $menu_type = 'วัสดุผสมเทียมและเวชภัณฑ์ยาสัตว์';
        $detail_cowbreed['name'] = 'ปัจจัยการเลี้ยงโคนม';
        $type_id = [359, 160];

        $result = MaterialService::getDetailmonthExcept($beforeYear, $beforemonth, $type_id);
        $detail_cowbreed['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];

        $result = GoalMissionService::getMissionavgByMenuTypeAndGoalIDNotIn($menu_type, $type_id, $condition['YearTo'], $condition['MonthFrom']);
        $detail_cowbreed['target']['price_value'] = empty($result['price'])?0:$result['price'];
        $result = GoalMissionService::getMissionavgByMenuType('ปัจจัยการเลี้ยงดูโค (เคมีภัณฑ์)', $condition['YearTo'], $condition['MonthFrom']);
        $detail_cowbreed['target']['price_value'] += empty($result['price'])?0:$result['price'];

        $result = MaterialService::getDetailmonthExcept($condition['YearTo'], $condition['MonthFrom'], $type_id);
        $detail_cowbreed['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $result = CowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom']);
        $detail_cowbreed['collectmonth']['price_value'] += empty($result['price'])?0:$result['price'];


        $detail_cowbreed['permonth']['amount'] = ($detail_cowbreed['collectmonth']['amount'] / $detail_cowbreed['target']['amount']) * 100;
        $detail_cowbreed['permonth']['price_value'] += ($detail_cowbreed['collectmonth']['price_value'] * 100) / $detail_cowbreed['target']['price_value'];

        $result = MaterialService::getDetailmonthExcept($condition['YearTo'] - 1, $condition['MonthFrom'], $type_id);
        $detail_cowbreed['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];
        $result = CowBreedService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom']);
        $detail_cowbreed['beforeyear']['price_value'] += empty($result['price'])?0:$result['price'];

        if(!empty($detail_cowbreed['beforeyear']['price_value'])){
            $detail_cowbreed['perbeforeyear']['price_value'] = (($detail_cowbreed['collectmonth']['price_value'] - $detail_cowbreed['beforeyear']['price_value']) * 100) / $detail_cowbreed['beforeyear']['price_value'];
        }else{
            $detail_cowbreed['perbeforeyear']['price_value'] = 0;
        }

        $result = GoalMissionService::getMissionYearByMenuTypeAndGoalIDNotIn($menu_type, $type_id, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
        $detail_cowbreed['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

        $result = GoalMissionService::getMissionYearByMenuType('ปัจจัยการเลี้ยงดูโค (เคมีภัณฑ์)', $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
        $detail_cowbreed['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];
        
        $loop_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1 : $condition['YearTo'];
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = CowBreedService::getDetailmonth($loop_year, $loop_month);
            $detail_cowbreed['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            $result = MaterialService::getDetailmonthExcept($loop_year, $loop_month, $type_id);
            $detail_cowbreed['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $result = CowBreedService::getDetailmonth($loop_year, $loop_month);
        $detail_cowbreed['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
        $result = MaterialService::getDetailmonthExcept($loop_year, $loop_month, $type_id);
        $detail_cowbreed['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];

        $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalIDNotIn($menu_type, $type_id, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
        $detail_cowbreed['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $result = GoalMissionService::getMissionOctAvgByMenuType('ปัจจัยการเลี้ยงดูโค (เคมีภัณฑ์)', $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
        $detail_cowbreed['targetoct']['price_value'] += empty($result['price'])?0:$result['price'];

        if(!empty($detail_cowbreed['targetoct']['price_value'])){
            $detail_cowbreed['peroct']['price_value'] = ($detail_cowbreed['collectoct']['price_value'] * 100) / $detail_cowbreed['targetoct']['price_value'];
        }else{
            $detail_cowbreed['peroct']['price_value'] = 0;
        }

        //$index++;
        
        // $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), 'รายได้อื่นๆ');
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        // $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        // $row++;
        // result before selected month 
        
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format(($detail_nitro['beforemonth']['price_value'] + $detail_cowbreed['beforemonth']['price_value']) / 1000000, 2, '.', ''));
            
        // current month goal 
        
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format(($detail_nitro['target']['price_value'] + $detail_cowbreed['target']['price_value']) / 1000000, 2, '.', ''));

        // result selected month 
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format(($detail_nitro['collectmonth']['price_value'] + $detail_cowbreed['collectmonth']['price_value']) / 1000000, 2, '.', ''));

        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($detail_cowbreed['permonth']['price_value'], 2, '.', ''));
        
        
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format(($detail_nitro['beforeyear']['price_value'] + $detail_cowbreed['beforeyear']['price_value']) / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $detail_nitro['perbeforeyear']['price_value'] + $detail_cowbreed['perbeforeyear']['price_value']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    รายได้อื่นๆ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format(($detail_nitro['yeartarget']['price_value'] + $detail_cowbreed['yeartarget']['price_value']) / 1000000, 2, '.', ''));

        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format(($detail_nitro['targetoct']['price_value'] + $detail_cowbreed['targetoct']['price_value']) / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format(($detail_nitro['collectoct']['price_value'] + $detail_cowbreed['collectoct']['price_value']) / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail_nitro['peroct']['price_value'] + $detail_cowbreed['peroct']['price_value']);
        
        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $detail_nitro['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        // $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        // $row++;
        // result before selected month 
        
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($detail_nitro['beforemonth']['price_value'] / 1000000, 2, '.', ''));
            
        // current month goal 
        
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($detail_nitro['target']['price_value'] / 1000000, 2, '.', ''));

        // result selected month 
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($detail_nitro['collectmonth']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($detail_nitro['permonth']['price_value'], 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format($detail_nitro['beforeyear']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $detail_nitro['perbeforeyear']['price_value']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    ไนโตรเจนเหลว');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($detail_nitro['yeartarget']['price_value'] / 1000000, 2, '.', ''));

        $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($detail_nitro['targetoct']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($detail_nitro['collectoct']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail_nitro['peroct']['price_value']);
        
        $row++;

        //$index++;

        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $detail_cowbreed['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        // $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        // $row++;
        // result before selected month 
        
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($detail_cowbreed['beforemonth']['price_value'] / 1000000, 2, '.', ''));
            
        // current month goal 
        
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($detail_cowbreed['target']['price_value'] / 1000000, 2, '.', ''));

        // result selected month 
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($detail_cowbreed['collectmonth']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($detail_cowbreed['permonth']['price_value'], 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format($detail_cowbreed['beforeyear']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $detail_cowbreed['perbeforeyear']['price_value']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '        ปัจจัยการเลี้ยงโคนม');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($detail_cowbreed['yeartarget']['price_value'] / 1000000, 2, '.', ''));

        $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($detail_cowbreed['targetoct']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($detail_cowbreed['collectoct']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail_cowbreed['peroct']['price_value']);
        
        $row++;

        $detail = [];

        // sheet 2 : 9. ท่องเที่ยวฟาร์มโคนมไทย - เดนมาร์ค
        $index++;
        $menu_type = 'ท่องเที่ยว';
        // $goal_id_list = [391,392,393,320,321,322];

        // $goal_id_list = [365,366];
        $goal_id_list_data = MasterGoalService::getGoalIDByKeyword('รายได้ชมฟาร์ม');

        $goal_id_list = [];
        foreach ($goal_id_list_data as $key => $value) {
            $goal_id_list[] = $value['id'];
        }

        $detail['name'] = 'ท่องเที่ยวฟาร์มโคนมไทย - เดนมาร์ค';
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), ($index) . '.' . $detail['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        // result before selected month 
        $result = TravelService::getDetailmonth($beforeYear, $beforemonth, $goal_id_list);
        $detail['beforemonth']['price_value'] = empty($result['amount'])?0:$result['amount'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $detail['beforemonth']['price_value']);
            
        // current month goal 
        $result = GoalMissionService::getMissionavgByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['YearTo'], $condition['MonthFrom']);
        $detail['target']['price_value'] = empty($result['amount'])?0:$result['amount'];
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $detail['target']['price_value']);

        // result selected month 
        $result = TravelService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $goal_id_list);
        $detail['collectmonth']['price_value'] = empty($result['amount'])?0:$result['amount'];
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $detail['collectmonth']['price_value']);

        if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }


        $detail['permonth']['price_value'] = ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $detail['permonth']['price_value']);

        $result = TravelService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $goal_id_list);
        $detail['beforeyear']['price_value'] = empty($result['amount'])?0:$result['amount'];

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $detail['beforeyear']['price_value']);
        
        if(!empty($detail['beforeyear']['price_value'])){
            $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
        }else{
            $detail['perbeforeyear']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $detail['perbeforeyear']['price_value']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    บริการชมฟาร์มโคนม');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ราย');
            
        $result = GoalMissionService::getMissionavgByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['YearTo']);
        $detail['yeartarget']['price_value'] = empty($result['amount'])?0:$result['amount'];
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $detail['yeartarget']['price_value']);

        $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['amount'])?0:$result['amount'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $detail['targetoct']['price_value']);
            
        $loop_year = $condition['MonthFrom'] < 10 ? $condition['YearTo'] - 1 : $condition['YearTo'];
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = TravelService::getDetailmonth($loop_year, $loop_month, $goal_id_list);
            $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $result = TravelService::getDetailmonth($loop_year, $loop_month, $goal_id_list);
        $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];

        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $detail['collectoct']['price_value']);
            
        if(!empty($detail['targetoct']['price_value'])){
            $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
        }else{
            $detail['peroct']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail['peroct']['price_value']);
        
        $row++;

        // result before selected month 
        $detail = [];
        // get goal id by keyword
        $goal_id_list_data = MasterGoalService::getGoalIDByKeyword('รายได้ชมฟาร์ม');

        $goal_id_list = [];
        foreach ($goal_id_list_data as $key => $value) {
            $goal_id_list[] = $value['id'];
        }

        // $goal_id_list_data = MasterGoalService::getGoalIDByKeyword('บริการขี่ม้า');

        // foreach ($goal_id_list_data as $key => $value) {
        //     $goal_id_list[] = $value['id'];
        // }

        $result = TravelService::getDetailmonth($beforeYear, $beforemonth, $goal_id_list);
        $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($detail['beforemonth']['price_value'] / 1000000, 2, '.', ''));
            
        // current month goal 
        $result = GoalMissionService::getMissionavgByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['YearTo'], $condition['MonthFrom']);
        $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($detail['target']['price_value'] / 1000000, 2, '.', ''));

        // result selected month 
        $result = TravelService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $goal_id_list);
        $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($detail['collectmonth']['price_value'] / 1000000, 2, '.', ''));

        if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
        $detail['permonth']['price_value'] = ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $detail['permonth']['price_value']);

        $result = TravelService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $goal_id_list);
        $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $detail['beforeyear']['price_value']);
        
        if(!empty($detail['beforeyear']['price_value'])){
            $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
        }else{
            $detail['perbeforeyear']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), number_format($detail['perbeforeyear']['price_value'] / 1000000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    รายได้เข้าชมฟาร์มโคนม');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $result = GoalMissionService::getMissionYearByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
        $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($detail['yeartarget']['price_value'] / 1000000, 2, '.', ''));

        $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($detail['targetoct']['price_value'] / 1000000, 2, '.', ''));
            
        $loop_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1 : $condition['YearTo'];
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = TravelService::getDetailmonth($loop_year, $loop_month, $goal_id_list);
            $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $result = TravelService::getDetailmonth($loop_year, $loop_month, $goal_id_list);
        $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];

        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($detail['collectoct']['price_value'] / 1000000, 2, '.', ''));
            
        if(!empty($detail['targetoct']['price_value'])){
            $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
        }else{
            $detail['peroct']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail['peroct']['price_value']);
        
        $row++;

        // result before selected month 
        $detail = [];
        $result = TravelService::getDetailmonthExcept($beforeYear, $beforemonth, $goal_id_list);
        $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($detail['beforemonth']['price_value'] / 1000000, 2, '.', ''));
            
        // current month goal 
        $result = GoalMissionService::getMissionavgByMenuTypeAndGoalIDNotIn($menu_type, $goal_id_list, $condition['YearTo'], $condition['MonthFrom']);
        $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($detail['target']['price_value'] / 1000000, 2, '.', ''));

        // result selected month 
        $result = TravelService::getDetailmonthExcept($condition['YearTo'], $condition['MonthFrom'], $goal_id_list);
        $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($detail['collectmonth']['price_value'] / 1000000, 2, '.', ''));

        if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
        
        $detail['permonth']['price_value'] = ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($detail['permonth']['price_value'], 2, '.', ''));


        $result = TravelService::getDetailmonthExcept($condition['YearTo'] - 1, $condition['MonthFrom'], $goal_id_list);
        $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $detail['beforeyear']['price_value']);
        
        if(!empty($detail['beforeyear']['price_value'])){
            $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
        }else{
            $detail['perbeforeyear']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), number_format($detail['perbeforeyear']['price_value'] / 1000000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    รายได้กิจกรรมอื่นๆ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $result = GoalMissionService::getMissionYearByMenuTypeAndGoalIDNotIn($menu_type, $goal_id_list, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
        $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($detail['yeartarget']['price_value'] / 1000000, 2, '.', ''));

        $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalIDNotIn($menu_type, $goal_id_list, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($detail['targetoct']['price_value'] / 1000000, 2, '.', ''));
            
        $loop_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1 : $condition['YearTo'];
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = TravelService::getDetailmonthExcept($loop_year, $loop_month, $goal_id_list);
            $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $result = TravelService::getDetailmonthExcept($loop_year, $loop_month, $goal_id_list);
        $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];

        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($detail['collectoct']['price_value'] / 1000000, 2, '.', ''));
            
        if(!empty($detail['targetoct']['price_value'])){
            $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
        }else{
            $detail['peroct']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail['peroct']['price_value']);
        
        $row++;

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
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . (6 + $row))->getFont()->setSize(14);
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

        if($condition['MonthFrom'] < 10){
            $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        }else{
            $yearlist = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        }
        
        $objPHPExcel->createSheet(2);
        $objPHPExcel->setActiveSheetIndex(2);
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 4");

        $this->logger->info('begin log sheet 3');

        $row = 0;
        if ($condition['DisplayType'] == 'monthly') {

            $display_year = $condition['YearTo'];

            if ($condition['MonthFrom'] < 10) {
                $display_year--;
                // $year--;
            }
            $beforemonth = $condition['MonthFrom'];
            $ma = $condition['MonthFrom'];
            $year = $condition['YearTo'];
            if ($condition['MonthFrom'] == 1) {
                $beforemonth = 12;
                $year--;
            } else {
                $beforemonth--;
            }
            $this->logger->info('sheet 4 : monthly');
            $position = 1;
            //   $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. ผลการดำเนินงานด้านอุตสาหกรรมนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', $this->getMonthName($beforemonth) . ' ' . ($year + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงาน');
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
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ' . $this->getMonthName(10) . ' ' . ($display_year + 543) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม ');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');

            $objPHPExcel->getActiveSheet()->setCellValue('K5', $this->getMonthName(10) . ' ' . ($display_year + 543) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');
            $data = [];
////////รับซื้อ

            // sheet 3 : 1., 1.1 การรับซื้อน้ำนม
            $this->logger->info('sheet 4 : 1., 1.1');

            /*
            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = MBIService::getListMBIreoprt2($y, $m);
            $result_amount += $result_data['amount'];
            */
            $menu_type = 'รับซื้อน้ำนมดิบ (ERP)';
            $moumission = GoalMissionService::getMissionavgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
            $mbi = MBIService::getactualMBIDetail($condition['YearTo'], $condition['MonthFrom']);
            $beforembi = MBIService::getactualMBIDetail($year, $beforemonth);
            $beforeyearmbi = MBIService::getactualMBIDetail($condition['YearTo'] - 1, $condition['MonthFrom']);
            $moumissionyear = GoalMissionService::getMissionByMenuType($menu_type, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            $detail['name'] = '1.การรับซื้อน้ำนม';
            $detail['unit'] = 'ตัน';
            $detail['detailname'] = '1.1 การรับซื้อน้ำนม ';
            $detail['beforemonth'] = $beforembi['sum_amount'];
            $detail['target'] = $moumission['amount'];
            $detail['collectmonth'] = $mbi['sum_amount'];
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmbi['sum_amount'];
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $moumissionyear[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $mbioct = MBIService::getactualMBIDetail($condition['YearTo'] - $yearlist[$key], $ml);
                $mouoct = GoalMissionService::getMissionavgByMenuType($menu_type, $condition['YearTo'] - $yearlist[$key], $ml);//MouService::getMission($condition['YearTo'] - $yearlist[$key], $ml);
                $detail['collectoct'] += $mbioct['sum_amount'];
                $detail['targetoct'] += $mouoct['amount'];

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
            // $data[0]
            array_push($data, $detail);


            $mbi = MBIService::getactualMBIDetailByVendor($condition['YearTo'], $condition['MonthFrom'], 'สหกรณ์');
            $beforembi = MBIService::getactualMBIDetailByVendor($year, $beforemonth, 'สหกรณ์');
            $beforeyearmbi = MBIService::getactualMBIDetailByVendor($condition['YearTo'] - 1, $condition['MonthFrom'], 'สหกรณ์');

            $detail['unit'] = 'ตัน';
            $detail['detailname'] = '     - รับซื้อน้ำนมจากสหกรณ์ทั้งหมด';
            $detail['beforemonth'] = $beforembi['sum_amount'];
            $detail['target'] = 0;
            $detail['collectmonth'] = $mbi['sum_amount'];
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmbi['sum_amount'];
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $mbioct = MBIService::getactualMBIDetailByVendor($condition['YearTo'] - $yearlist[$key], $ml, 'สหกรณ์');
                $detail['collectoct'] += $mbioct['sum_amount'];
                $detail['targetoct'] = 0;

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
            // $data[1]
            array_push($data, $detail);


            $this->logger->info('sheet 4 : 1.. 1.1.1');
            $mbi = MBIService::getactualMBIDetailByVendor($condition['YearTo'], $condition['MonthFrom'], 'ศูนย์');
            $beforembi = MBIService::getactualMBIDetailByVendor($year, $beforemonth, 'ศูนย์');
            $beforeyearmbi = MBIService::getactualMBIDetailByVendor($condition['YearTo'] - 1, $condition['MonthFrom'], 'ศูนย์');

            $detail['unit'] = 'ตัน';
            $detail['detailname'] = '     - รับซื้อน้ำนมจากศูนย์รับน้ำนม อ.ส.ค.';
            $detail['beforemonth'] = $beforembi['sum_amount'];
            $detail['target'] = 0;
            $detail['collectmonth'] = $mbi['sum_amount'];
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmbi['sum_amount'];
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $mbioct = MBIService::getactualMBIDetailByVendor($condition['YearTo'] - $yearlist[$key], $ml, 'ศูนย์');
                $detail['collectoct'] += $mbioct['sum_amount'];
                $detail['targetoct'] = 0;

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
            // $data[2]
            array_push($data, $detail);


            /////จำหน่าย
            // sheet 3 : 1., 1.2 การจำหน่ายน้ำนม
            $this->logger->info('sheet 4 : monthly1.,1.1.2');
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การจำหน่ายน้ำนม';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการจำหน่ายน้ำนม', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $msi = MSIService::getactualMSIDetail($condition['YearTo'], $condition['MonthFrom']);
            $beforemsi = MSIService::getactualMSIDetail($year, $beforemonth);
            $beforeyearmsi = MSIService::getactualMSIDetail($condition['YearTo'] - 1, $condition['MonthFrom']);

            $detail['detailname'] = '1.2 การจำหน่ายน้ำนม ';
            $detail['unit'] = 'ตัน';
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



            $detail['beforemonth'] = $beforemsi['sum_amount'];

            $detail['target'] = $avg[0]['amount'];
            $detail['collectmonth'] = $msi['sum_amount'];
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmsi['sum_amount'];
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);

                $msioct = MSIService::getactualMSIDetail($condition['YearTo'] - $yearlist[$key], $ml);

                $detail['collectoct'] += $msioct['sum_amount'];
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
            // $data[3]
            array_push($data, $detail);

            // sheet 3 : 1., 1.3 น้ำนมคงเหลือ
            $this->logger->info('sheet 4 : monthly 1.. 1.1.3');
            $detail['detailname'] = '1.3 น้ำนมคงเหลือ ';
            $detail['unit'] = 'ตัน';
            $detail['beforemonth'] = $data[0]['beforemonth'] - $data[3]['beforemonth'];
            $detail['target'] = 0;
            $detail['collectmonth'] = $data[0]['collectmonth'] - $data[3]['collectmonth'];
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $data[0]['beforeyear'] - $data[3]['beforeyear'];
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = $data[0]['collectoct'] - $data[3]['collectoct'];
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

            /////ข้อมูลการผลิต

            // sheet 3 : 2., 2.1 น้ำนมเข้ากระบวนการผลิต

            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';  // น้ำนมเข้ากระบวนการผลิต
            /*
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);

            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $pro = ProductionInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
            $beforepro = ProductionInfoService::getDetailList2($year, $beforemonth);
            $beforeyearpro = ProductionInfoService::getDetailList2($condition['YearTo'] - 1, $condition['MonthFrom']);
            */
            $master_list = MasterGoalService::getGoalIDListByName('น้ำนมดิบรับเข้า', 'การสูญเสียในกระบวนการ');

            $id_list = [];
            foreach ($master_list as $master_key => $master_value) {
                $id_list[] = $master_value['id'];
            }

            // $id_list = implode(',', $id_list);
            // print_r($id_list);
            $mission_list = GoalMissionService::getMissionList($id_list, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            // exit;
            $goal_id_list = [];
            foreach ($mission_list as $mission_key => $mission_value) {
                $goal_id_list[] = $mission_value['id'];
            }

            // $goal_id_list = implode(',', $goal_id_list);

            $beforeavg = GoalMissionService::getMissionavgList($goal_id_list, $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavgList($goal_id_list, $condition['YearTo'], $condition['MonthFrom']);

            $pro = LostInProcessService::getAmountList($condition['YearTo'], $condition['MonthFrom'], $id_list);
            // print_r($pro);exit;
            $beforepro = LostInProcessService::getAmountList($year, $beforemonth, $id_list);
            $beforeyearpro = LostInProcessService::getAmountList($condition['YearTo'] - 1, $condition['MonthFrom'], $id_list);


            // exit;
            $this->logger->info('sheet 4 : monthly 2., 2.1');
            $data2 = [];
            $detail['name'] = '2. การผลิต';
            $detail['detailname'] = '2.1 น้ำนมเข้ากระบวนการผลิต';
            $detail['unit'] = 'ตัน';
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

            $detail['beforemonth'] = $beforepro['amount'];

            $detail['target'] = $avg[0]['amount'];
            $detail['collectmonth'] = $pro['amount'];
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearpro['amount'];
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;
            foreach ($monthList as $key => $ml) {
                $octavg = GoalMissionService::getMissionavgList($goal_id_list, $condition['YearTo'] - $yearlist[$key], $ml);

                $prooct = LostInProcessService::getAmountList($condition['YearTo'] - $yearlist[$key], $ml, $id_list);

                $detail['collectoct'] += $prooct['amount'];
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


            $master_list = MasterGoalService::getGoalIDListByName('น้ำนมดิบรับเข้า', 'การสูญเสียในกระบวนการ');

            $id_list = [];
            foreach ($master_list as $master_key => $master_value) {
                $id_list[] = $master_value['id'];
            }

            // $id_list = implode(',', $id_list);
            // print_r($id_list);
            $mission_list = GoalMissionService::getMissionList($id_list, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            // exit;
            $goal_id_list = [];
            $goal_sum_amount = 0;
            foreach ($mission_list as $mission_key => $mission_value) {
                $goal_id_list[] = $mission_value['id'];
                $goal_sum_amount += floatval($mission_value['amount']);
            }

            // $goal_id_list = implode(',', $goal_id_list);

            $beforeavg = GoalMissionService::getMissionavgList($goal_id_list, $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavgList($goal_id_list, $condition['YearTo'], $condition['MonthFrom']);

            $pro = LostInProcessService::getAmountList($condition['YearTo'], $condition['MonthFrom'], $id_list);
            // print_r($pro);exit;
            $beforepro = LostInProcessService::getAmountList($year, $beforemonth, $id_list);
            $beforeyearpro = LostInProcessService::getAmountList($condition['YearTo'] - 1, $condition['MonthFrom'], $id_list);


            // exit;
            // sheet 3 : 2., 2.1 น้ำนมเข้ากระบวนการผลิต
            $data2 = [];
            $detail['name'] = '2. การผลิต';
            $detail['detailname'] = '2.1 น้ำนมเข้ากระบวนการผลิต';
            $detail['unit'] = 'ตัน';
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

            $detail['beforemonth'] = $beforepro['amount'];

            $detail['target'] = $avg[0]['amount'];
            $detail['collectmonth'] = $pro['amount'];
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearpro['amount'];
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $goal_sum_amount;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;
            foreach ($monthList as $key => $ml) {
                $octavg = GoalMissionService::getMissionavgList($goal_id_list, $condition['YearTo'] - $yearlist[$key], $ml);

                $prooct = LostInProcessService::getAmountList($condition['YearTo'] - $yearlist[$key], $ml, $id_list);

                $detail['collectoct'] += $prooct['amount'];
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


            // sheet 3 : 2., 2.2 น้ำนมปรุงแต่งเข้ากระบวนการผลิต

            // exclude 'การสูญเสียในกระบวนการ'
            
            // exit;
            // $data2 = [];
            $detail = [];
            $detail['name'] = '2. การผลิต';
            $detail['detailname'] = '2.2 น้ำนมปรุงแต่งเข้ากระบวนการผลิต';
            $detail['unit'] = 'ตัน';

            $request_condition = ['MonthFrom' => $beforemonth, 'YearTo' => $condition['YearTo']];
            $LostInProcessData = new LostInProcessController;
            $result = $LostInProcessData->getMonthDataListAll($request_condition);
            $detail['beforemonth'] = $result['DataList'][count($result['DataList']) - 4]['CurrentAmount'];
            $check_before_month = $detail['beforemonth'];
            
            $detail['target'] = 0;  // นมเข้ากระบวนการแปรรูป (รวบรวม) - แปรรูปน้ำนม(หักลบ)

            $target = GoalMissionService::getMissionBySubGoalType('การสูญเสียในกระบวนการ', $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom'], 'การแปรรูปน้ำนม (รวบรวม)');
            $target1 = $target['amount'];

            $target = GoalMissionService::getMissionBySubGoalType('การสูญเสียในกระบวนการ', $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom'], 'การแปรรูปน้ำนม (หักลบ)');
            $target2 = $target['amount'];
            $detail['target'] = $target1 - $target2;  

            $request_condition = ['MonthFrom' => $condition['MonthFrom'], 'YearTo' => $condition['YearTo']];
            $LostInProcessData = new LostInProcessController;
            $result = $LostInProcessData->getMonthDataListAll($request_condition);
            $detail['collectmonth'] = $result['DataList'][count($result['DataList']) - 4]['CurrentAmount'];
            $check_collect_month = $detail['collectmonth'];

            $detail['permonth'] = 0;

            $request_condition = ['MonthFrom' => $condition['MonthFrom'], 'YearTo' => $condition['YearTo'] - 1];
            $LostInProcessData = new LostInProcessController;
            $result = $LostInProcessData->getMonthDataListAll($request_condition);
            $detail['beforeyear'] = $result['DataList'][count($result['DataList']) - 4]['CurrentAmount'];
            $check_before_year = $detail['beforeyear'];
            $detail['perbeforeyear'] = 0;

            $targetoct = GoalMissionService::getMissionOctAVGSumAmount('การสูญเสียในกระบวนการ', $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], 9, 'การแปรรูปน้ำนม (รวบรวม)');
            $targetoct1 = $targetoct['amount'];

            $targetoct = GoalMissionService::getMissionOctAVGSumAmount('การสูญเสียในกระบวนการ', $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], 9, 'การแปรรูปน้ำนม (หักลบ)');
            $targetoct2 = $targetoct['amount'];
            $detail['yeartarget'] = $targetoct1 - $targetoct2;



            $targetoct = GoalMissionService::getMissionOctAVGSumAmount('การสูญเสียในกระบวนการ', $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'],$condition['MonthFrom'], 'การแปรรูปน้ำนม (รวบรวม)');
            $targetoct1 = $targetoct['amount'];

            $targetoct = GoalMissionService::getMissionOctAVGSumAmount('การสูญเสียในกระบวนการ', $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo'], $condition['MonthFrom'], 'การแปรรูปน้ำนม (หักลบ)');
            $targetoct2 = $targetoct['amount'];

            $detail['targetoct'] = $targetoct1 - $targetoct2;

            foreach ($monthList as $key => $ml) {

                $request_condition = ['MonthFrom' => $ml, 'YearTo' => $year - $yearlist[$key]];
                $result = $LostInProcessData->getMonthDataListAll($request_condition);
                $detail['collectoct'] += $result['DataList'][count($result['DataList']) - 4]['CurrentAmount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }

            $check_collect_cot = $detail['collectoct'];

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

            array_push($data2, $detail);

            // sheet 3 : 2., 2.3 ผลิตภัณฑ์ที่ผลิตได้

            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            // $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
            // $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearFrom']);
            // $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            // $data2 = [];
            $detail = [];
            $detail['name'] = '2. การผลิต';
            $detail['detailname'] = '2.3 ผลิตภัณฑ์ที่ผลิตได้';
            $detail['unit'] = 'ตัน';
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

            // $factory_id_list = [1,2,3,4,5];
            $production_info = ProductionInfoService::getMonthList($year, $beforemonth);
            $detail['beforemonth'] = floatval($production_info['sum_amount']);//$beforepro['amount'];
            $tmp_before_month = $detail['beforemonth'];

            $FactoryList = FactoryService::getList();
        
            foreach ($FactoryList as $factory_k => $factory_v) {

                $ProductMilkList = ProductMilkService::getList('Y', '', '', $factory_v['id']);
            
                foreach ($ProductMilkList as $prod_k => $prod_v) {

                    $avg_data = GoalMissionService::getMissionavgByMenuTypeAndGoalNameKeyword('ข้อมูลการผลิต', $prod_v['name'], $year, $ma, $factory_v['id']);
                    $goal_amount += $avg_data['amount'];
                
                }

            }

            // $avg = GoalMissionService::getMissionavgByMenuType('ข้อมูลการผลิต', $year, $ma);
            $detail['target'] = $goal_amount;
            
            $production_info = ProductionInfoService::getMonthList($year, $ma);
            $detail['collectmonth'] =  floatval($production_info['sum_amount']);//$pro['amount'];
            $tmp_collect_month = $detail['collectmonth'];

            $production_info = ProductionInfoService::getMonthList($year - 1, $ma);
            $detail['beforeyear'] = floatval($production_info['sum_amount']);//$beforeyearpro['amount'];

            foreach ($monthList as $key => $ml) {

                $octavg = GoalMissionService::getMissionavgByMenuType('ข้อมูลการผลิต', $year - $yearlist[$key], $ml);

                $production_info = ProductionInfoService::getMonthList($year - $yearlist[$key], $ml);
                
                $detail['collectoct'] += floatval($production_info['sum_amount']);//$prooct['amount'];
                $detail['targetoct'] += $octavg['amount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }

            $tmp_collect_oct = $detail['collectoct'];

            $mission_year = GoalMissionService::getMissionYearByMenuType('ข้อมูลการผลิต', $condition['MonthFrom'] > 9?$year + 1:$year);

            $detail['yeartarget'] = $mission_year['amount'];
            // $mission_year = GoalMissionService::getMissionYearByMenuType('ข้อมูลการขาย', $condition['MonthFrom'] > 9?$year + 1:$year);
            // $detail['yeartarget'] = $mission_year['amount'];

            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }

            $production_result_arr = $detail;
            array_push($data2, $detail);

            // sheet 3 : 2., 2.4 ผลต่างในขบวนการผลิต

            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            // $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
            // $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearFrom']);
            // $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            // $data2 = [];
            $detail = [];
            $detail['name'] = '2. การผลิต';
            $detail['detailname'] = '2.4 ผลต่างในขบวนการผลิต';
            $detail['unit'] = 'ตัน';
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

            // $beforepro = ProductionInfoService::getDetailList2($year, $beforemonth);
            $detail['beforemonth'] = $check_before_month - $tmp_before_month;//$beforepro['amount'];

            $avg = GoalMissionService::getMissionavgByMenuType('การสูญเสียในกระบวนการ', $year, $ma);
            $detail['target'] = $avg['amount'];
            // exit;
            
            // $pro = ProductionInfoService::getDetailList2($year, $ma);
            $detail['collectmonth'] = $check_collect_month - $tmp_collect_month;//$pro['amount'];

            $before_production_info_amount = 0;

            $production_info = ProductionInfoService::getMonthList($year - 1, $ma);
            $before_production_info_amount += floatval($production_info['sum_amount']);

            // $beforeyearpro = ProductionInfoService::getDetailList2($year - 1, $ma);
            $detail['beforeyear'] = $tmp_before_month - $check_before_month;//$beforeyearpro['amount'];

            $current_production_info_amount = 0;
            foreach ($monthList as $key => $ml) {

                $octavg = GoalMissionService::getMissionavgByMenuType('การสูญเสียในกระบวนการ', $year - $yearlist[$key], $ml);

                $detail['targetoct'] += $octavg['amount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }

            $detail['collectoct'] = $check_collect_cot - $tmp_collect_oct;//$prooct['amount'];

            $mission_year = GoalMissionService::getMissionYearByMenuType('การสูญเสียในกระบวนการ', $condition['MonthFrom'] > 9?$year + 1:$year);
            $detail['yeartarget'] = $mission_year['amount'];

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
            $this->logger->info('sheet 4 : monthly 3., 3.1');
            // sheet 3 : 3., 3.1 ยอดผลิตภัณฑ์ยกมา
            
            $data3 = [];
            $detail['name'] = '3. การจำหน่าย';
            $detail['detailname'] = '3.1  ยอดผลิตภัณฑ์ยกมา';
            $detail['unit'] = 'ตัน';
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

            $before_month_data = BeginEndingBalanceService::getBeforeSumByDate($year, $beforemonth);
            $detail['beforemonth'] = $before_month_data['sum_amount'];
            // echo "$year, $ma";exit;
            $collect_month_data = BeginEndingBalanceService::getBeforeSumByDate($year, $ma);
            // print_r($collect_month_data);exit;
            $detail['collectmonth'] = $collect_month_data['sum_amount'];
            $ending_balance = $detail['collectmonth'];

            $before_year_data = BeginEndingBalanceService::getBeforeSumByDate($year - 1, $ma);
            $detail['beforeyear'] = $before_year_data['sum_amount'];

            // foreach ($monthList as $key => $ml) {

            //     $octavg = BeginEndingBalanceService::getBeforeSumByDate($year - $yearlist[$key], $ml);
            //     $detail['collectoct'] += $octavg['sum_amount'];

            //     if ($ml == $condition['MonthFrom']) {
            //         break;
            //     }
            // }
            $detail['collectoct'] = $ending_balance;

            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }

            // print_r($detail);exit;
            array_push($data3, $detail);

            // sheet 3 : 3., 3.2 ผลิตภัณฑ์ทร่ผลิตได้
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการขาย', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $pro = ProductionSaleInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
            $beforepro = ProductionSaleInfoService::getDetailList2($year, $beforemonth);
            $beforeyearpro = ProductionSaleInfoService::getDetailList2($condition['YearTo'] - 1, $condition['MonthFrom']);

            $detail['name'] = '3. การจำหน่าย';
            $production_result_arr['detailname'] = '3.2  ผลิตภัณฑ์ที่ผลิตได้';
            $detail['unit'] = 'ตัน';
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

            array_push($data3, $production_result_arr);

            // sheet 3 : 3., 3.3 หักนมตัวอย่างตรวจคุณภาพ
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการขาย', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['MonthFrom'] > 9?$condition['YearTo'] + 1:$condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $pro = ProductionSaleInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
            $beforepro = ProductionSaleInfoService::getDetailList2($year, $beforemonth);
            $beforeyearpro = ProductionSaleInfoService::getDetailList2($condition['YearTo'] - 1, $condition['MonthFrom']);

            $detail['name'] = '3. การจำหน่าย';
            $detail['detailname'] = '3.3  หักนมตัวอย่างตรวจคุณภาพ';
            $detail['unit'] = 'ตัน';
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

            $lost_out_process = LostOutProcessService::getMonthList($year, $beforemonth);
            $detail['beforemonth'] = floatval($lost_out_process['sum_amount']);//$beforepro['amount'];
            
            $avg = GoalMissionService::getMissionavgByMenuType('การสูญเสียหลังกระบวนการ', $year, $ma);
            $detail['target'] = $avg['amount'];
            // exit;
            
            $lost_out_process = LostOutProcessService::getMonthList($year, $ma);
            $detail['collectmonth'] = floatval($lost_out_process['sum_amount']);//$pro['amount'];
            
            $lost_out_process = LostOutProcessService::getMonthList($year - 1, $ma);
            $detail['beforeyear'] = floatval($lost_out_process['sum_amount']);//$beforeyearpro['amount'];

            $current_production_info_amount = 0;
            foreach ($monthList as $key => $ml) {

                $octavg = GoalMissionService::getMissionavgByMenuType('การสูญเสียหลังกระบวนการ', $year - $yearlist[$key], $ml);

                $lost_out_process = LostOutProcessService::getMonthList($year - $yearlist[$key], $ml);
                
                $detail['collectoct'] += floatval($lost_out_process['sum_amount']);//$prooct['amount'];
                $detail['targetoct'] += $octavg['amount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }

            $tmp_collect_oct = $detail['collectoct'];

            $mission_year = GoalMissionService::getMissionYearByMenuType('การสูญเสียหลังกระบวนการ', $condition['MonthFrom'] > 9?$year + 1:$year);
            $detail['yeartarget'] = $mission_year['amount'];

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

            // sheet 3 : 3., 3.4 หักสูญเสียระหว่างรอจำหน่าย
            
            $detail['name'] = '3. การจำหน่าย';
            $detail['detailname'] = '3.4  หักสูญเสียระหว่างรอจำหน่าย';
            $detail['unit'] = 'ตัน';
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

            $lost_wait_sale = LostWaitSaleService::getMonthList($year, $beforemonth);
            $detail['beforemonth'] = floatval($lost_wait_sale['sum_amount']);//$beforepro['amount'];
            
            $avg = GoalMissionService::getMissionavgByMenuType('การสูญเสียรอจำหน่าย', $year, $ma);
            $detail['target'] = $avg['amount'];
            // exit;
            
            $lost_wait_sale = LostWaitSaleService::getMonthList($year, $ma);
            $detail['collectmonth'] = floatval($lost_wait_sale['sum_amount']);//$pro['amount'];
            
            $lost_wait_sale = LostWaitSaleService::getMonthList($year - 1, $ma);
            $detail['beforeyear'] = floatval($lost_wait_sale['sum_amount']);//$beforeyearpro['amount'];

            foreach ($monthList as $key => $ml) {

                $octavg = GoalMissionService::getMissionavgByMenuType('การสูญเสียรอจำหน่าย', $year - $yearlist[$key], $ml);

                $lost_wait_sale = LostWaitSaleService::getMonthList($year - $yearlist[$key], $ml);
                
                $detail['collectoct'] += floatval($lost_wait_sale['sum_amount']);//$prooct['amount'];
                $detail['targetoct'] += $octavg['amount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }

            $tmp_collect_oct = $detail['collectoct'];

            $mission_year = GoalMissionService::getMissionYearByMenuType('การสูญเสียรอจำหน่าย', $condition['MonthFrom'] > 9?$year + 1:$year);
            $detail['yeartarget'] = $mission_year['amount'];

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

            // sheet 3 : 3., 3.5 ผลิตภัณฑ์ที่จำหน่าย (รวมแถม)
            
            $detail['name'] = '3. การจำหน่าย';
            $detail['detailname'] = '3.5  ผลิตภัณฑ์ที่จำหน่าย (รวมแถม)';
            $detail['unit'] = 'ตัน';
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

            $production_sale_info = ProductionSaleInfoService::getMonthList($year, $beforemonth);
            $detail['beforemonth'] = floatval($production_sale_info['sum_amount']);//$beforepro['amount'];
            
            $avg = GoalMissionService::getMissionavgByMenuType('ข้อมูลการขาย', $year, $ma);
            $detail['target'] = $avg['amount'];
            // exit;
            
            $production_sale_info = ProductionSaleInfoService::getMonthList($year, $ma);
            $detail['collectmonth'] = floatval($production_sale_info['sum_amount']);//$pro['amount'];
            
            $production_sale_info = ProductionSaleInfoService::getMonthList($year - 1, $ma);
            $detail['beforeyear'] = floatval($production_sale_info['sum_amount']);//$beforeyearpro['amount'];

            foreach ($monthList as $key => $ml) {

                $octavg = GoalMissionService::getMissionavgByMenuType('ข้อมูลการขาย', $year - $yearlist[$key], $ml);

                $production_sale_info = ProductionSaleInfoService::getMonthList($year - $yearlist[$key], $ml);
                
                $detail['collectoct'] += floatval($production_sale_info['sum_amount']);//$prooct['amount'];
                $detail['targetoct'] += $octavg['amount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }

            $tmp_collect_oct = $detail['collectoct'];

            $mission_year = GoalMissionService::getMissionYearByMenuType('ข้อมูลการขาย', $condition['MonthFrom'] > 9?$year + 1:$year);
            $detail['yeartarget'] = $mission_year['amount'];

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

            // sheet 3 : 3., 3.6 ผลิตภัณฑ์นมคงเหลือ (ยกไป)
            
            $detail['name'] = '3. การจำหน่าย';
            $detail['detailname'] = '3.6  ผลิตภัณฑ์นมคงเหลือ (ยกไป)';
            $detail['unit'] = 'ตัน';
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

            $before_month_data = BeginEndingBalanceService::getEndingSumByDate($year, $beforemonth);
            $detail['beforemonth'] = $before_month_data['sum_amount'];
            // echo "$year, $ma";exit;
            $collect_month_data = BeginEndingBalanceService::getEndingSumByDate($year, $ma);
            // print_r($collect_month_data);exit;
            $detail['collectmonth'] = $collect_month_data['sum_amount'];
            $ending_balance = $detail['collectmonth'];

            $before_year_data = BeginEndingBalanceService::getEndingSumByDate($year - 1, $ma);
            $detail['beforeyear'] = $before_year_data['sum_amount'];

            // foreach ($monthList as $key => $ml) {

            //     $octavg = BeginEndingBalanceService::getEndingSumByDate($year - $yearlist[$key], $ml);
            //     $detail['collectoct'] += $octavg['sum_amount'];

            //     if ($ml == $condition['MonthFrom']) {
            //         break;
            //     }
            // }

            $detail['collectoct'] = $ending_balance;

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

            /////tb2
            $condition['MonthTo'] = $condition['MonthFrom'];
            $FactoryList = FactoryService::getList();
            $detail = [];
            foreach ($FactoryList as $id) {
                $data_fac = ProductionInfoController::getMonthreportforsubcom($condition, 1);
                array_push($detail, $data_fac);
            }

            $this->logger->info('sheet 4 : monthly finish');
            //   print_r($detail);
        }
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $data[0]['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        foreach ($data as $key => $itemdata) {


            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $itemdata['detailname']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['unit']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $data2[0]['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        foreach ($data2 as $key => $itemdata) {


            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $itemdata['detailname']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['unit']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $data3[0]['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        foreach ($data3 as $key => $itemdata) {


            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $itemdata['detailname']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['unit']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']);
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A6:L' . (6 + $row))->getFont()->setSize(14);
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
          $row+=7;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'หมายเหตุ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), 'เป้าหมายจากแผนปฏิบัติงานปี ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . $row . ':D' . $row);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), 'ข้อมูล ณ วันที่ ' . ($condition['date'] ));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . $row . ':D' . $row);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesheet4($objPHPExcel, $condition, $time_list, $header) {
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        if($condition['MonthFrom'] < 10){
            $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        }else{
            $yearlist = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        }

        $objPHPExcel->createSheet(3);
        $objPHPExcel->setActiveSheetIndex(3);
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 5");
        $FactoryList = FactoryService::getList();
        $row = 6;
        $detail = [];
        $detail2 = [];
        $this->logger->info('begin log sheet 4');
    
        if ($condition['DisplayType'] == 'monthly') {

            $this->logger->info('type monthly');
        
            $beforemonth = $condition['MonthFrom'];
            $year = $condition['YearTo'];
            $display_year = $condition['YearTo'];

            if ($condition['MonthFrom'] < 10) {
                $display_year--;
                // $year--;
            }

            $beforemonth--;

            if($beforemonth < 1){
                $year--;
                $beforemonth = 12;
            }

            $position = 1;
            //  $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2.1 ผลการดำเนินงานด้านอุตสาหกรรมนม รายภาค');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', $this->getMonthName($beforemonth) . ' ' . ($year + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');

            $objPHPExcel->getActiveSheet()->setCellValue('B4',  'เดือน' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('B4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('B5',  'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5',  'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->setCellValue('D5',  '% เป้าหมาย');

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
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ' . $this->getMonthName(10) . ' ' . ($display_year + 543) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม ');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');

            $objPHPExcel->getActiveSheet()->setCellValue('K5', $this->getMonthName(10) . ' ' . ($display_year + 543) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');

            $condition['MonthTo'] = $condition['MonthFrom'];

        }
        // ผลิต
        $menu_type = 'ข้อมูลการผลิต';

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '1. การผลิต');
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setBold(true);
        $row++;
        
        $List = [];
        foreach ($FactoryList as $factory_k => $factory_v) {
            
            $FacList = [];
            $FacList['beforemonth']['amount'] = 0;
            $FacList['target']['amount'] = 0;
            $FacList['collectmonth']['amount'] = 0;
            $FacList['permonth']['amount'] = 0;
            $FacList['beforeyear']['amount'] = 0;
            $FacList['perbeforeyear']['amount'] = 0;
            $FacList['activity']['amount'] = $factory_v['factory_name'];
            $FacList['unit']['amount'] = 'ตัน';
            $FacList['yeartarget']['amount'] = 0;
            $FacList['yearocttarget']['amount'] = 0;
            $FacList['collectoct']['amount'] = 0;
            $FacList['peroct']['amount'] = 0;
            $FacList['detail'] = [];

            $ProductMilkList = ProductMilkService::getList('Y', '', '', $factory_v['id']);

            foreach ($ProductMilkList as $prod_k => $prod_v) {

                $detail = [];
                $detail['activity']['amount'] = $prod_v['name'];
                $detail['unit']['amount'] = 'ตัน';

                foreach ($time_list as $time_k => $time_v) {

                    $before_month = $time_v['month'];
                    $year = $time_v['year'];

                    if($condition['DisplayType'] == 'quarter'){
                        $before_month -= 3;
                        if($before_month == -2){
                            $before_month = 10;
                            $year -= 1;
                        }else if($before_month == -1){
                            $before_month = 11;
                            $year -= 1;
                        }else if($before_month == 0){
                            $before_month = 12;
                            $year -= 1;
                        }
                    }else if($condition['DisplayType'] == 'annually'){
                        $year -= 1;
                    }
                    else if($condition['DisplayType'] == 'monthly'){
                        
                        $before_month--;
                        if($before_month < 1){
                            $year -= 1;
                            $before_month = 12;
                        }
                    }
                 
                    $result = ProductionInfoService::getDetail($year, $before_month, $factory_v['id'], $prod_v['id']);
                    $detail['beforemonth']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = GoalMissionService::getMissionavgByMenuTypeAndGoalNameKeyword($menu_type, $prod_v['name'], $time_v['year'], $time_v['month'], $factory_v['id']);
                    $detail['target']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = ProductionInfoService::getDetail($time_v['year'], $time_v['month'], $factory_v['id'], $prod_v['id']);
                    $detail['collectmonth']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $detail['permonth']['amount'] = 0;

                    $result = ProductionInfoService::getDetail($time_v['year'] - 1, $time_v['month'], $factory_v['id'], $prod_v['id']);
                    $detail['beforeyear']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = GoalMissionService::getMissionYearByMenuTypeAndGoalNameKeyword($menu_type, $prod_v['name'], $time_v['month'] > 9?$time_v['year'] + 1:$time_v['year'], $factory_v['id']);
                    $detail['yeartarget']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalNameKeyword($menu_type, $prod_v['name'], $time_v['month'] > 9?$time_v['year'] + 1:$time_v['year'], $time_v['month'], $factory_v['id']);
                    $detail['yearocttarget']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $loop_year = $time_v['month'] < 10?$time_v['year'] - 1 : $time_v['year'];
                    $loop_month = 10;

                    while($loop_month != $time_v['month']){

                        $result = ProductionInfoService::getDetail($loop_year, $loop_month, $factory_v['id'], $prod_v['id']);
                        $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                        $loop_month++;

                        if($loop_month > 12){
                            $loop_month = 1;
                            $loop_year += 1;
                        }
                    }

                    $result = ProductionInfoService::getDetail($loop_year, $loop_month, $factory_v['id'], $prod_v['id']);
                    $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
        
                }

                if(!empty($detail['collectmonth']['amount'])){
                    $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] / $detail['target']['amount']) * 100;
                }else{
                    $detail['permonth']['amount'] = 0;
                }

                if(!empty($detail['beforeyear']['amount'])){
                    $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
                }else{
                    $detail['perbeforeyear']['amount'] = 0;
                }

                if(!empty($detail['yearocttarget']['amount'])){
                    $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['yearocttarget']['amount'];
                }else{
                    $detail['peroct']['amount'] = 0;
                }

                $FacList['beforemonth']['amount'] += $detail['beforemonth']['amount'];
                $FacList['target']['amount'] += $detail['target']['amount'];
                $FacList['collectmonth']['amount'] += $detail['collectmonth']['amount'];
                $FacList['beforeyear']['amount'] += $detail['beforeyear']['amount'];
                $FacList['yeartarget']['amount'] += $detail['yeartarget']['amount'];
                $FacList['yearocttarget']['amount'] += $detail['yearocttarget']['amount'];
                $FacList['collectoct']['amount'] += $detail['collectoct']['amount'];
                
                array_push($FacList['detail'], $detail);

            }

            if(!empty($FacList['collectmonth']['amount'])){
                $FacList['permonth']['amount'] = ($FacList['collectmonth']['amount'] / $FacList['target']['amount']) * 100;
            }else{
                $FacList['permonth']['amount'] = 0;
            }

            if(!empty($FacList['beforeyear']['amount'])){
                $FacList['perbeforeyear']['amount'] = (($FacList['collectmonth']['amount'] - $FacList['beforeyear']['amount']) * 100) / $FacList['beforeyear']['amount'];
            }else{
                $FacList['perbeforeyear']['amount'] = 0;
            }

            if(!empty($FacList['yearocttarget']['amount'])){
                $FacList['peroct']['amount'] = ($FacList['collectoct']['amount'] * 100) / $FacList['yearocttarget']['amount'];
            }else{
                $FacList['peroct']['amount'] = 0;
            }

            array_push($List, $FacList);
            
        }

        $Summary = [];
        $Summary['beforemonth']['amount'] = 0;
        $Summary['target']['amount'] = 0;
        $Summary['collectmonth']['amount'] = 0;
        $Summary['permonth']['amount'] = 0;
        $Summary['beforeyear']['amount'] = 0;
        $Summary['perbeforeyear']['amount'] = 0;
        $Summary['activity']['amount'] = 'รวม';
        $Summary['unit']['amount'] = 'ตัน';
        $Summary['yeartarget']['amount'] = 0;
        $Summary['yearocttarget']['amount'] = 0;
        $Summary['collectoct']['amount'] = 0;
        $Summary['peroct']['amount'] = 0;
        
        $TotalByProd = [];
        $TotalByProd['milk1'] = [];
        $TotalByProd['milk1']['beforemonth']['amount'] = 0;
        $TotalByProd['milk1']['target']['amount'] = 0;
        $TotalByProd['milk1']['collectmonth']['amount'] = 0;
        $TotalByProd['milk1']['permonth']['amount'] = 0;
        $TotalByProd['milk1']['beforeyear']['amount'] = 0;
        $TotalByProd['milk1']['perbeforeyear']['amount'] = 0;
        $TotalByProd['milk1']['activity']['amount'] = 'นมพานิชย์';
        $TotalByProd['milk1']['unit']['amount'] = 'ตัน';
        $TotalByProd['milk1']['yeartarget']['amount'] = 0;
        $TotalByProd['milk1']['yearocttarget']['amount'] = 0;
        $TotalByProd['milk1']['collectoct']['amount'] = 0;
        $TotalByProd['milk1']['peroct']['amount'] = 0;

        $TotalByProd['milk2'] = [];
        $TotalByProd['milk2']['beforemonth']['amount'] = 0;
        $TotalByProd['milk2']['target']['amount'] = 0;
        $TotalByProd['milk2']['collectmonth']['amount'] = 0;
        $TotalByProd['milk2']['permonth']['amount'] = 0;
        $TotalByProd['milk2']['beforeyear']['amount'] = 0;
        $TotalByProd['milk2']['perbeforeyear']['amount'] = 0;
        $TotalByProd['milk2']['activity']['amount'] = 'นมโรงเรียน';
        $TotalByProd['milk2']['unit']['amount'] = 'ตัน';
        $TotalByProd['milk2']['yeartarget']['amount'] = 0;
        $TotalByProd['milk2']['yearocttarget']['amount'] = 0;
        $TotalByProd['milk2']['collectoct']['amount'] = 0;
        $TotalByProd['milk2']['peroct']['amount'] = 0;

        $cnt = 1;
        foreach ($List as $l_key => $l_value) {

            $Summary['beforemonth']['amount'] += $l_value['beforemonth']['amount'];
            $Summary['target']['amount'] += $l_value['target']['amount'];
            $Summary['collectmonth']['amount'] += $l_value['collectmonth']['amount'];
            $Summary['beforeyear']['amount'] += $l_value['beforeyear']['amount'];
            $Summary['yeartarget']['amount'] += $l_value['yeartarget']['amount'];
            $Summary['yearocttarget']['amount'] += $l_value['yearocttarget']['amount'];
            $Summary['collectoct']['amount'] += $l_value['collectoct']['amount'];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $l_value['beforemonth']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $l_value['target']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $l_value['collectmonth']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $l_value['permonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $l_value['beforeyear']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $l_value['perbeforeyear']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '   1.' . $cnt . ' ' . $l_value['activity']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $l_value['unit']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $l_value['yeartarget']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $l_value['yearocttarget']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $l_value['collectoct']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $l_value['peroct']['amount']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
            $row++;

            foreach ($l_value['detail'] as $d_key => $d_value) {

                if($d_value['activity']['amount'] == 'นมพาณิชย์'){

                    $TotalByProd['milk1']['beforemonth']['amount'] += $d_value['beforemonth']['amount'];
                    $TotalByProd['milk1']['target']['amount'] += $d_value['target']['amount'];
                    $TotalByProd['milk1']['collectmonth']['amount'] += $d_value['collectmonth']['amount'];
                    $TotalByProd['milk1']['beforeyear']['amount'] += $d_value['beforeyear']['amount'];
                    $TotalByProd['milk1']['yeartarget']['amount'] += $d_value['yeartarget']['amount'];
                    $TotalByProd['milk1']['yearocttarget']['amount'] += $d_value['yearocttarget']['amount'];
                    $TotalByProd['milk1']['collectoct']['amount'] += $d_value['collectoct']['amount'];

                }else if($d_value['activity']['amount'] == 'นมโรงเรียน'){

                    $TotalByProd['milk2']['beforemonth']['amount'] += $d_value['beforemonth']['amount'];
                    $TotalByProd['milk2']['target']['amount'] += $d_value['target']['amount'];
                    $TotalByProd['milk2']['collectmonth']['amount'] += $d_value['collectmonth']['amount'];
                    $TotalByProd['milk2']['beforeyear']['amount'] += $d_value['beforeyear']['amount'];
                    $TotalByProd['milk2']['yeartarget']['amount'] += $d_value['yeartarget']['amount'];
                    $TotalByProd['milk2']['yearocttarget']['amount'] += $d_value['yearocttarget']['amount'];
                    $TotalByProd['milk2']['collectoct']['amount'] += $d_value['collectoct']['amount'];

                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $d_value['beforemonth']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $d_value['target']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $d_value['collectmonth']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $d_value['permonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $d_value['beforeyear']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $d_value['perbeforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         - '.$d_value['activity']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $d_value['unit']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $d_value['yeartarget']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $d_value['yearocttarget']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $d_value['collectoct']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $d_value['peroct']['amount']);
                $row++;

            }

            $cnt++;

        }

        if(!empty($Summary['collectmonth']['amount'])){
            $Summary['permonth']['amount'] = ($Summary['collectmonth']['amount'] / $Summary['target']['amount']) * 100;
        }else{
            $Summary['permonth']['amount'] = 0;
        }

        if(!empty($Summary['beforeyear']['amount'])){
            $Summary['perbeforeyear']['amount'] = (($Summary['collectmonth']['amount'] - $Summary['beforeyear']['amount']) * 100) / $Summary['beforeyear']['amount'];
        }else{
            $Summary['perbeforeyear']['amount'] = 0;
        }

        if(!empty($Summary['yearocttarget']['amount'])){
            $Summary['peroct']['amount'] = ($Summary['collectoct']['amount'] * 100) / $Summary['yearocttarget']['amount'];
        }else{
            $Summary['peroct']['amount'] = 0;
        }





        if(!empty($TotalByProd['milk1']['collectmonth']['amount'])){
            $TotalByProd['milk1']['permonth']['amount'] = ($TotalByProd['milk1']['collectmonth']['amount'] / $TotalByProd['milk1']['target']['amount']) * 100;
        }else{
            $TotalByProd['milk1']['permonth']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk1']['beforeyear']['amount'])){
            $TotalByProd['milk1']['perbeforeyear']['amount'] = (($Summary['collectmonth']['amount'] - $Summary['beforeyear']['amount']) * 100) / $Summary['beforeyear']['amount'];
        }else{
            $TotalByProd['milk1']['perbeforeyear']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk1']['yearocttarget']['amount'])){
            $TotalByProd['milk1']['peroct']['amount'] = ($TotalByProd['milk1']['collectoct']['amount'] * 100) / $TotalByProd['milk1']['yearocttarget']['amount'];
        }else{
            $TotalByProd['milk1']['peroct']['amount'] = 0;
        }





        if(!empty($TotalByProd['milk2']['collectmonth']['amount'])){
            $TotalByProd['milk2']['permonth']['amount'] = ($TotalByProd['milk2']['collectmonth']['amount'] / $TotalByProd['milk2']['target']['amount']) * 100;
        }else{
            $TotalByProd['milk2']['permonth']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk2']['beforeyear']['amount'])){
            $TotalByProd['milk2']['perbeforeyear']['amount'] = (($TotalByProd['milk2']['collectmonth']['amount'] - $TotalByProd['milk2']['beforeyear']['amount']) * 100) / $TotalByProd['milk2']['beforeyear']['amount'];
        }else{
            $TotalByProd['milk2']['perbeforeyear']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk2']['yearocttarget']['amount'])){
            $TotalByProd['milk2']['peroct']['amount'] = ($TotalByProd['milk2']['collectoct']['amount'] * 100) / $TotalByProd['milk2']['yearocttarget']['amount'];
        }else{
            $TotalByProd['milk2']['peroct']['amount'] = 0;
        }


        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $Summary['beforemonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $Summary['target']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $Summary['collectmonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $Summary['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $Summary['beforeyear']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $Summary['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '   '. $Summary['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $Summary['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $Summary['yeartarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $Summary['yearocttarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $Summary['collectoct']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $Summary['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $TotalByProd['milk1']['beforemonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $TotalByProd['milk1']['target']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $TotalByProd['milk1']['collectmonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $TotalByProd['milk1']['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $TotalByProd['milk1']['beforeyear']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $TotalByProd['milk1']['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         '. $TotalByProd['milk1']['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $TotalByProd['milk1']['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $TotalByProd['milk1']['yeartarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $TotalByProd['milk1']['yearocttarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $TotalByProd['milk1']['collectoct']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $TotalByProd['milk1']['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $TotalByProd['milk2']['beforemonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $TotalByProd['milk2']['target']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $TotalByProd['milk2']['collectmonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $TotalByProd['milk2']['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $TotalByProd['milk2']['beforeyear']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $TotalByProd['milk2']['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         '. $TotalByProd['milk2']['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $TotalByProd['milk2']['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $TotalByProd['milk2']['yeartarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $TotalByProd['milk2']['yearocttarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $TotalByProd['milk2']['collectoct']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $TotalByProd['milk2']['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

        // ขาย
        $menu_type = 'ข้อมูลการขาย';

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '2. การจำหน่ายผลิตภัณฑ์นม (ตัน)');
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setBold(true);
        $row++;
        
        $List = [];
        foreach ($FactoryList as $factory_k => $factory_v) {
            
            $FacList = [];
            $FacList['beforemonth']['amount'] = 0;
            $FacList['target']['amount'] = 0;
            $FacList['collectmonth']['amount'] = 0;
            $FacList['permonth']['amount'] = 0;
            $FacList['beforeyear']['amount'] = 0;
            $FacList['perbeforeyear']['amount'] = 0;
            $FacList['activity']['amount'] = $factory_v['factory_name'];
            $FacList['unit']['amount'] = 'ตัน';
            $FacList['yeartarget']['amount'] = 0;
            $FacList['yearocttarget']['amount'] = 0;
            $FacList['collectoct']['amount'] = 0;
            $FacList['peroct']['amount'] = 0;
            $FacList['detail'] = [];

            $ProductMilkList = ProductMilkService::getList('Y', '', '', $factory_v['id']);

            foreach ($ProductMilkList as $prod_k => $prod_v) {

                $detail = [];
                $detail['activity']['amount'] = $prod_v['name'];
                $detail['unit']['amount'] = 'ตัน';

                foreach ($time_list as $time_k => $time_v) {

                    $before_month = $time_v['month'];
                    $year = $time_v['year'];

                    if($condition['DisplayType'] == 'quarter'){
                        $before_month -= 3;
                        if($before_month == -2){
                            $before_month = 10;
                            $year -= 1;
                        }else if($before_month == -1){
                            $before_month = 11;
                            $year -= 1;
                        }else if($before_month == 0){
                            $before_month = 12;
                            $year -= 1;
                        }
                    }else if($condition['DisplayType'] == 'annually'){
                        $year -= 1;
                    }
                    else if($condition['DisplayType'] == 'monthly'){
                        
                        $before_month--;
                        if($before_month < 1){
                            $year -= 1;
                            $before_month = 12;
                        }
                    }
                 
                    $result = ProductionSaleInfoService::getDetail($year, $before_month, $factory_v['id'], $prod_v['id']);
                    $detail['beforemonth']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = GoalMissionService::getMissionavgByMenuTypeAndGoalNameKeyword($menu_type, $prod_v['name'], $time_v['year'], $time_v['month'], $factory_v['id']);
                    $detail['target']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = ProductionSaleInfoService::getDetail($time_v['year'], $time_v['month'], $factory_v['id'], $prod_v['id']);
                    $detail['collectmonth']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $detail['permonth']['amount'] = 0;

                    $result = ProductionSaleInfoService::getDetail($time_v['year'] - 1, $time_v['month'], $factory_v['id'], $prod_v['id']);
                    $detail['beforeyear']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = GoalMissionService::getMissionYearByMenuTypeAndGoalNameKeyword($menu_type, $prod_v['name'], $time_v['month'] > 9?$time_v['year'] + 1:$time_v['year'], $factory_v['id']);
                    $detail['yeartarget']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalNameKeyword($menu_type, $prod_v['name'], $time_v['month'] > 9?$time_v['year'] + 1:$time_v['year'], $time_v['month'], $factory_v['id']);
                    $detail['yearocttarget']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $loop_year = $time_v['month'] < 10?$time_v['year'] - 1 : $time_v['year'];
                    $loop_month = 10;

                    while($loop_month != $time_v['month']){

                        $result = ProductionSaleInfoService::getDetail($loop_year, $loop_month, $factory_v['id'], $prod_v['id']);
                        $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                        //echo $detail['collectoct']['amount'] . ' ';
                        $loop_month++;

                        if($loop_month > 12){
                            $loop_month = 1;
                            $loop_year += 1;
                        }
                    }

                    $result = ProductionSaleInfoService::getDetail($loop_year, $loop_month, $factory_v['id'], $prod_v['id']);
                    $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                    
                }

                if(!empty($detail['collectmonth']['amount'])){
                    $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] / $detail['target']['amount']) * 100;
                }else{
                    $detail['permonth']['amount'] = 0;
                }

                if(!empty($detail['beforeyear']['amount'])){
                    $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
                }else{
                    $detail['perbeforeyear']['amount'] = 0;
                }

                if(!empty($detail['yearocttarget']['amount'])){
                    $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['yearocttarget']['amount'];
                }else{
                    $detail['peroct']['amount'] = 0;
                }

                $FacList['beforemonth']['amount'] += $detail['beforemonth']['amount'];
                $FacList['target']['amount'] += $detail['target']['amount'];
                $FacList['collectmonth']['amount'] += $detail['collectmonth']['amount'];
                $FacList['beforeyear']['amount'] += $detail['beforeyear']['amount'];
                $FacList['yeartarget']['amount'] += $detail['yeartarget']['amount'];
                $FacList['yearocttarget']['amount'] += $detail['yearocttarget']['amount'];
                $FacList['collectoct']['amount'] += $detail['collectoct']['amount'];
                
                array_push($FacList['detail'], $detail);

            }

            if(!empty($FacList['collectmonth']['amount'])){
                $FacList['permonth']['amount'] = ($FacList['collectmonth']['amount'] / $FacList['target']['amount']) * 100;
            }else{
                $FacList['permonth']['amount'] = 0;
            }

            if(!empty($FacList['beforeyear']['amount'])){
                $FacList['perbeforeyear']['amount'] = (($FacList['collectmonth']['amount'] - $FacList['beforeyear']['amount']) * 100) / $FacList['beforeyear']['amount'];
            }else{
                $FacList['perbeforeyear']['amount'] = 0;
            }

            if(!empty($FacList['yearocttarget']['amount'])){
                $FacList['peroct']['amount'] = ($FacList['collectoct']['amount'] * 100) / $FacList['yearocttarget']['amount'];
            }else{
                $FacList['peroct']['amount'] = 0;
            }

            array_push($List, $FacList);
            
        }

        $Summary = [];
        $Summary['beforemonth']['amount'] = 0;
        $Summary['target']['amount'] = 0;
        $Summary['collectmonth']['amount'] = 0;
        $Summary['permonth']['amount'] = 0;
        $Summary['beforeyear']['amount'] = 0;
        $Summary['perbeforeyear']['amount'] = 0;
        $Summary['activity']['amount'] = 'รวม';
        $Summary['unit']['amount'] = 'ตัน';
        $Summary['yeartarget']['amount'] = 0;
        $Summary['yearocttarget']['amount'] = 0;
        $Summary['collectoct']['amount'] = 0;
        $Summary['peroct']['amount'] = 0;
        
        $TotalByProd = [];
        $TotalByProd['milk1'] = [];
        $TotalByProd['milk1']['beforemonth']['amount'] = 0;
        $TotalByProd['milk1']['target']['amount'] = 0;
        $TotalByProd['milk1']['collectmonth']['amount'] = 0;
        $TotalByProd['milk1']['permonth']['amount'] = 0;
        $TotalByProd['milk1']['beforeyear']['amount'] = 0;
        $TotalByProd['milk1']['perbeforeyear']['amount'] = 0;
        $TotalByProd['milk1']['activity']['amount'] = 'นมพานิชย์';
        $TotalByProd['milk1']['unit']['amount'] = 'ตัน';
        $TotalByProd['milk1']['yeartarget']['amount'] = 0;
        $TotalByProd['milk1']['yearocttarget']['amount'] = 0;
        $TotalByProd['milk1']['collectoct']['amount'] = 0;
        $TotalByProd['milk1']['peroct']['amount'] = 0;

        $TotalByProd['milk2'] = [];
        $TotalByProd['milk2']['beforemonth']['amount'] = 0;
        $TotalByProd['milk2']['target']['amount'] = 0;
        $TotalByProd['milk2']['collectmonth']['amount'] = 0;
        $TotalByProd['milk2']['permonth']['amount'] = 0;
        $TotalByProd['milk2']['beforeyear']['amount'] = 0;
        $TotalByProd['milk2']['perbeforeyear']['amount'] = 0;
        $TotalByProd['milk2']['activity']['amount'] = 'นมโรงเรียน';
        $TotalByProd['milk2']['unit']['amount'] = 'ตัน';
        $TotalByProd['milk2']['yeartarget']['amount'] = 0;
        $TotalByProd['milk2']['yearocttarget']['amount'] = 0;
        $TotalByProd['milk2']['collectoct']['amount'] = 0;
        $TotalByProd['milk2']['peroct']['amount'] = 0;

        $cnt = 1;
        foreach ($List as $l_key => $l_value) {

            $Summary['beforemonth']['amount'] += $l_value['beforemonth']['amount'];
            $Summary['target']['amount'] += $l_value['target']['amount'];
            $Summary['collectmonth']['amount'] += $l_value['collectmonth']['amount'];
            $Summary['beforeyear']['amount'] += $l_value['beforeyear']['amount'];
            $Summary['yeartarget']['amount'] += $l_value['yeartarget']['amount'];
            $Summary['yearocttarget']['amount'] += $l_value['yearocttarget']['amount'];
            $Summary['collectoct']['amount'] += $l_value['collectoct']['amount'];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $l_value['beforemonth']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $l_value['target']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $l_value['collectmonth']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $l_value['permonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $l_value['beforeyear']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $l_value['perbeforeyear']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '   2.' . $cnt . ' ' . $l_value['activity']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $l_value['unit']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $l_value['yeartarget']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $l_value['yearocttarget']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $l_value['collectoct']['amount'] / 1000);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $l_value['peroct']['amount']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
            $row++;

            foreach ($l_value['detail'] as $d_key => $d_value) {

                if($d_value['activity']['amount'] == 'นมพาณิชย์'){

                    $TotalByProd['milk1']['beforemonth']['amount'] += $d_value['beforemonth']['amount'];
                    $TotalByProd['milk1']['target']['amount'] += $d_value['target']['amount'];
                    $TotalByProd['milk1']['collectmonth']['amount'] += $d_value['collectmonth']['amount'];
                    $TotalByProd['milk1']['beforeyear']['amount'] += $d_value['beforeyear']['amount'];
                    $TotalByProd['milk1']['yeartarget']['amount'] += $d_value['yeartarget']['amount'];
                    $TotalByProd['milk1']['yearocttarget']['amount'] += $d_value['yearocttarget']['amount'];
                    $TotalByProd['milk1']['collectoct']['amount'] += $d_value['collectoct']['amount'];

                }else if($d_value['activity']['amount'] == 'นมโรงเรียน'){

                    $TotalByProd['milk2']['beforemonth']['amount'] += $d_value['beforemonth']['amount'];
                    $TotalByProd['milk2']['target']['amount'] += $d_value['target']['amount'];
                    $TotalByProd['milk2']['collectmonth']['amount'] += $d_value['collectmonth']['amount'];
                    $TotalByProd['milk2']['beforeyear']['amount'] += $d_value['beforeyear']['amount'];
                    $TotalByProd['milk2']['yeartarget']['amount'] += $d_value['yeartarget']['amount'];
                    $TotalByProd['milk2']['yearocttarget']['amount'] += $d_value['yearocttarget']['amount'];
                    $TotalByProd['milk2']['collectoct']['amount'] += $d_value['collectoct']['amount'];

                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $d_value['beforemonth']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $d_value['target']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $d_value['collectmonth']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $d_value['permonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $d_value['beforeyear']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $d_value['perbeforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         - '.$d_value['activity']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $d_value['unit']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $d_value['yeartarget']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $d_value['yearocttarget']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $d_value['collectoct']['amount'] / 1000);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $d_value['peroct']['amount']);
                $row++;

            }

            $cnt++;

        }

        if(!empty($Summary['collectmonth']['amount'])){
            $Summary['permonth']['amount'] = ($Summary['collectmonth']['amount'] / $Summary['target']['amount']) * 100;
        }else{
            $Summary['permonth']['amount'] = 0;
        }

        if(!empty($Summary['beforeyear']['amount'])){
            $Summary['perbeforeyear']['amount'] = (($Summary['collectmonth']['amount'] - $Summary['beforeyear']['amount']) * 100) / $Summary['beforeyear']['amount'];
        }else{
            $Summary['perbeforeyear']['amount'] = 0;
        }

        if(!empty($Summary['yearocttarget']['amount'])){
            $Summary['peroct']['amount'] = ($Summary['collectoct']['amount'] * 100) / $Summary['yearocttarget']['amount'];
        }else{
            $Summary['peroct']['amount'] = 0;
        }





        if(!empty($TotalByProd['milk1']['collectmonth']['amount'])){
            $TotalByProd['milk1']['permonth']['amount'] = ($TotalByProd['milk1']['collectmonth']['amount'] / $TotalByProd['milk1']['target']['amount']) * 100;
        }else{
            $TotalByProd['milk1']['permonth']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk1']['beforeyear']['amount'])){
            $TotalByProd['milk1']['perbeforeyear']['amount'] = (($Summary['collectmonth']['amount'] - $Summary['beforeyear']['amount']) * 100) / $Summary['beforeyear']['amount'];
        }else{
            $TotalByProd['milk1']['perbeforeyear']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk1']['yearocttarget']['amount'])){
            $TotalByProd['milk1']['peroct']['amount'] = ($TotalByProd['milk1']['collectoct']['amount'] * 100) / $TotalByProd['milk1']['yearocttarget']['amount'];
        }else{
            $TotalByProd['milk1']['peroct']['amount'] = 0;
        }





        if(!empty($TotalByProd['milk2']['collectmonth']['amount'])){
            $TotalByProd['milk2']['permonth']['amount'] = ($TotalByProd['milk2']['collectmonth']['amount'] / $TotalByProd['milk2']['target']['amount']) * 100;
        }else{
            $TotalByProd['milk2']['permonth']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk2']['beforeyear']['amount'])){
            $TotalByProd['milk2']['perbeforeyear']['amount'] = (($TotalByProd['milk2']['collectmonth']['amount'] - $TotalByProd['milk2']['beforeyear']['amount']) * 100) / $TotalByProd['milk2']['beforeyear']['amount'];
        }else{
            $TotalByProd['milk2']['perbeforeyear']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk2']['yearocttarget']['amount'])){
            $TotalByProd['milk2']['peroct']['amount'] = ($TotalByProd['milk2']['collectoct']['amount'] * 100) / $TotalByProd['milk2']['yearocttarget']['amount'];
        }else{
            $TotalByProd['milk2']['peroct']['amount'] = 0;
        }


        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $Summary['beforemonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $Summary['target']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $Summary['collectmonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $Summary['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $Summary['beforeyear']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $Summary['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '   '. $Summary['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $Summary['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $Summary['yeartarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $Summary['yearocttarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $Summary['collectoct']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $Summary['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $TotalByProd['milk1']['beforemonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $TotalByProd['milk1']['target']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $TotalByProd['milk1']['collectmonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $TotalByProd['milk1']['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $TotalByProd['milk1']['beforeyear']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $TotalByProd['milk1']['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         '. $TotalByProd['milk1']['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $TotalByProd['milk1']['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $TotalByProd['milk1']['yeartarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $TotalByProd['milk1']['yearocttarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $TotalByProd['milk1']['collectoct']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $TotalByProd['milk1']['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $TotalByProd['milk2']['beforemonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $TotalByProd['milk2']['target']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $TotalByProd['milk2']['collectmonth']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $TotalByProd['milk2']['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $TotalByProd['milk2']['beforeyear']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $TotalByProd['milk2']['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         '. $TotalByProd['milk2']['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $TotalByProd['milk2']['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $TotalByProd['milk2']['yeartarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $TotalByProd['milk2']['yearocttarget']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $TotalByProd['milk2']['collectoct']['amount'] / 1000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $TotalByProd['milk2']['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

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
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . ($row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . ($row ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A6:L' . ($row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . ( $row - 1 ))->applyFromArray(
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

    private function generatesheet5($objPHPExcel, $condition, $time_list, $header){
        $objPHPExcel->createSheet(4);
        $objPHPExcel->setActiveSheetIndex(4);
        $this->logger->info('begin log sheet 5');
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        if($condition['MonthFrom'] < 10){
            $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        }else{
            $yearlist = [0, 0, 0];
        }

        $display_year = $condition['YearTo'];

        if ($condition['MonthFrom'] < 10) {
            $display_year--;
            // $year--;
        }

        $FactoryList = FactoryService::getList();
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 6");
        // $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2.2 การสูญเสียน้ำนมสดและผลิตภัณฑ์นมของสำนักงานภาค');

        $data_produce = [];
        $data = [];
        $detail2 = [];
        $detail3 = [];
        $divide_amount = 1000;
        $divide_value = 1000000;

        $start_year = $condition['MonthFrom'] < 10?$condition['YearTo'] - 1: $condition['YearTo'];

        if ($condition['DisplayType'] == 'monthly') {

            $this->logger->info('sheet 4 : monthly');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ผลการดำเนินงาน เดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'สะสม ' . $this->getMonthName(10) . ' ' . ($display_year + 543) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:N4');

            $column_list = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];

            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');

            $objPHPExcel->getActiveSheet()->setCellValue('A5', "สภก.\nสระบุรี");
            $objPHPExcel->getActiveSheet()->setCellValue('B5', "สภต.\nปราณบุรี");
            $objPHPExcel->getActiveSheet()->setCellValue('C5', "สภอ.\nขอนแก่น");
            $objPHPExcel->getActiveSheet()->setCellValue('D5', "สภน.\nสุโขทัย");
            $objPHPExcel->getActiveSheet()->setCellValue('E5', "สภน.\nเชียงใหม่");
            $objPHPExcel->getActiveSheet()->setCellValue('F5', "รวม");
            $objPHPExcel->getActiveSheet()->setCellValue('I5', "สภก.\nสระบุรี");
            $objPHPExcel->getActiveSheet()->setCellValue('J5', "สภต.\nปราณบุรี");
            $objPHPExcel->getActiveSheet()->setCellValue('K5', "สภอ.\nขอนแก่น");
            $objPHPExcel->getActiveSheet()->setCellValue('L5', "สภน.\nสุโขทัย");
            $objPHPExcel->getActiveSheet()->setCellValue('M5', "สภน.\nเชียงใหม่");
            $objPHPExcel->getActiveSheet()->setCellValue('N5', 'รวม');

            // A5 - N5
            $objPHPExcel->getActiveSheet()
                    ->getStyle('A4:N5')
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                            )
            );
            $row = 6;
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '1.การสูญเสีย');
            $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setSize(12);
            $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setBold(true);
            
            $SUMMARY_DATA = [];
            $IN_PRODUCTION_DATA = [];
            $PRODUCTION_DATA = [];
            // LOST IN PROCESS
            $LostInProcessData = new LostInProcessController;
            // get amount
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            $total_production_amount = 0;
            $in_total_production_amount = 0;
            foreach ($FactoryList as $key => $fac) {
                
                $request_condition = ['MonthFrom' => $condition['MonthFrom'], 'YearTo' => $condition['YearTo'], 'Factory' => $fac['id']];
                $result = $LostInProcessData->getMonthDataListAll($request_condition);
                $amount = $result['DataList'][count($result['DataList']) - 2]['CurrentAmount'];
                $SUMMARY_DATA[$fac['id']]['amount'] += $amount;

                $IN_PRODUCTION_DATA[$fac['id']]['amount'] = $result['DataList'][count($result['DataList']) - 4]['CurrentAmount'];
                $PRODUCTION_DATA[$fac['id']]['amount'] = $result['DataList'][count($result['DataList']) - 3]['CurrentAmount'];

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_amount);

                $in_total_production_amount += $result['DataList'][count($result['DataList']) - 4]['CurrentAmount'];
                $total_production_amount += $PRODUCTION_DATA[$fac['id']]['amount'];
                $total_loss_amount += $amount;

                $col++;
            }

            $IN_PRODUCTION_DATA['total']['amount'] = $in_total_production_amount;
            $PRODUCTION_DATA['total']['amount'] = $total_production_amount;

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_amount);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   1.1 สูญเสียในกระบวนการผลิต ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, 'ตัน');
            $col++;

            $total_loss_amount = 0;
            $total_production_amount = 0;
            $in_total_production_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $amount = 0;
                $amount_collect = 0;
                $in_amount_collect = 0;

                foreach ($monthList as $key => $ml) {
                    $request_condition = ['MonthFrom' => $ml, 'YearTo' => $condition['YearTo'] - $yearlist[$key], 'Factory' => $fac['id']];
                    $result = $LostInProcessData->getMonthDataListAll($request_condition);
                    $amount += $result['DataList'][count($result['DataList']) - 2]['CurrentAmount'];
                    $amount_collect += $result['DataList'][count($result['DataList']) - 3]['CurrentAmount'];
                    $in_amount_collect += $result['DataList'][count($result['DataList']) - 4]['CurrentAmount'];
                    if($ml == $condition['MonthFrom']){
                        break;
                    }
                    
                }

                $IN_PRODUCTION_DATA[$fac['id']]['amount_collect'] = $in_amount_collect;
                $PRODUCTION_DATA[$fac['id']]['amount_collect'] = $amount_collect;
                $SUMMARY_DATA[$fac['id']]['amount_collect'] += $amount;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_amount);

                $in_total_production_amount += $IN_PRODUCTION_DATA[$fac['id']]['amount_collect'];
                $total_production_amount += $PRODUCTION_DATA[$fac['id']]['amount_collect'];
                $total_loss_amount += $amount;

                $col++;
            }

            $IN_PRODUCTION_DATA['total']['amount_collect'] = $in_total_production_amount;
            $PRODUCTION_DATA['total']['amount_collect'] = $total_production_amount;

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_amount);

            // get value
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {
                $request_condition = ['months' => $condition['MonthFrom'], 'years' => $condition['YearTo'], 'factory_id' => $fac['id']];
                $result = LostInProcessService::checkDuplicateValue($request_condition);
                $amount = $result['values'];
                $SUMMARY_DATA[$fac['id']]['value'] += $amount;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_value);

                $total_loss_amount += $amount;
                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_value);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   มูลค่า ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, 'ล้านบาท');
            $col++;

            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $amount = 0;
                foreach ($monthList as $key => $ml) {
                    $request_condition = ['months' => $ml, 'years' => $condition['YearTo'] - $yearlist[$key], 'factory_id' => $fac['id']];
                    $result = LostInProcessService::checkDuplicateValue($request_condition);
                    $amount += $result['values'];
                    if($ml == $condition['MonthFrom']){
                        break;
                    }
                    
                }

                $SUMMARY_DATA[$fac['id']]['value_collect'] += $amount;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_value);

                $total_loss_amount += $amount;

                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_value);

            // percent
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {
                
                $request_condition = ['MonthFrom' => $condition['MonthFrom'], 'YearTo' => $condition['YearTo'], 'Factory' => $fac['id']];
                $result = $LostInProcessData->getMonthDataListAll($request_condition);
                $amount = $result['DataList'][count($result['DataList']) - 1]['CurrentAmount'];

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount);

                $total_loss_amount += $amount;
                $col++;
            }

            $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $IN_PRODUCTION_DATA['total']['amount'];

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   % สูญเสีย ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '');
            $col++;

            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $IN_PRODUCTION_DATA[$fac['id']]['amount_collect'];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);

                $total_loss_amount += $amount;

                $col++;
            }

            $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $IN_PRODUCTION_DATA['total']['amount_collect'];
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);

            // LOST OUT PROCESS
            //get amount
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {
                $lost_out_process = LostOutProcessService::getMonthList($condition['YearTo'], $condition['MonthFrom'], $fac['id']);
                $amount = floatval($lost_out_process['sum_amount']);
                $SUMMARY_DATA[$fac['id']]['amount'] += $amount;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_amount);
                $total_loss_amount += $amount;
                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_amount);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   1.2 สูญเสียหลังกระบวนการผลิต ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, 'ตัน');
            $col++;

            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $amount = 0;
                foreach ($monthList as $key => $ml) {
                    $request_condition = ['MonthFrom' => $ml, 'YearTo' => $condition['YearTo'] - $yearlist[$key], 'Factory' => $fac['id']];
                    $lost_out_process = LostOutProcessService::getMonthList($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $amount += floatval($lost_out_process['sum_amount']);

                    if($ml == $condition['MonthFrom']){
                        break;
                    }
                    
                }

                $SUMMARY_DATA[$fac['id']]['amount_collect'] += $amount;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_amount);

                $total_loss_amount += $amount;

                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_amount);

            // get value
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {
                $lost_out_process = LostOutProcessService::getMonthList($condition['YearTo'], $condition['MonthFrom'], $fac['id']);
                $amount = floatval($lost_out_process['sum_baht']);
                $SUMMARY_DATA[$fac['id']]['value'] += $amount;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_value);
                $total_loss_amount += $amount;
                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_value);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   มูลค่า ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, 'ล้านบาท');
            $col++;

            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $amount = 0;
                foreach ($monthList as $key => $ml) {
                    $request_condition = ['MonthFrom' => $ml, 'YearTo' => $condition['YearTo'] - $yearlist[$key], 'Factory' => $fac['id']];
                    $lost_out_process = LostOutProcessService::getMonthList($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $amount += floatval($lost_out_process['sum_baht']);

                    if($ml == $condition['MonthFrom']){
                        break;
                    }
                    
                }

                $SUMMARY_DATA[$fac['id']]['value_collect'] += $amount;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_value);

                $total_loss_amount += $amount;

                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_value);

            // percent
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $PRODUCTION_DATA[$fac['id']]['amount'];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);
                
                $col++;
            }

            $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $PRODUCTION_DATA['total']['amount'];
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   % สูญเสีย ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '');
            $col++;

            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $PRODUCTION_DATA[$fac['id']]['amount_collect'];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);

                $total_loss_amount += $amount;

                $col++;
            }

            $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $PRODUCTION_DATA['total']['amount_collect'];
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);


            // LOST WAIT SALE
            //get amount
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {
                $lost_out_process = LostWaitSaleService::getMonthList($condition['YearTo'], $condition['MonthFrom'], $fac['id']);
                $amount = floatval($lost_out_process['sum_amount']);
                $SUMMARY_DATA[$fac['id']]['amount'] += $amount;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_amount);
                $total_loss_amount += $amount;
                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_amount);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   1.3 สูญเสียระหว่างรอจำหน่าย ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, 'ตัน');
            $col++;

            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $amount = 0;
                foreach ($monthList as $key => $ml) {
                    $request_condition = ['MonthFrom' => $ml, 'YearTo' => $condition['YearTo'] - $yearlist[$key], 'Factory' => $fac['id']];
                    $lost_out_process = LostWaitSaleService::getMonthList($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $amount += floatval($lost_out_process['sum_amount']);

                    if($ml == $condition['MonthFrom']){
                        break;
                    }
                    
                }
                $SUMMARY_DATA[$fac['id']]['amount_collect'] += $amount;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_amount);

                $total_loss_amount += $amount;

                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_amount);

            // get value
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {
                $lost_out_process = LostWaitSaleService::getMonthList($condition['YearTo'], $condition['MonthFrom'], $fac['id']);
                $amount = floatval($lost_out_process['sum_baht']);
                $SUMMARY_DATA[$fac['id']]['value'] += $amount;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_value);
                $total_loss_amount += $amount;
                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_value);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   มูลค่า ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, 'ล้านบาท');
            $col++;

            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $amount = 0;
                foreach ($monthList as $key => $ml) {
                    $request_condition = ['MonthFrom' => $ml, 'YearTo' => $condition['YearTo'] - $yearlist[$key], 'Factory' => $fac['id']];
                    $lost_out_process = LostWaitSaleService::getMonthList($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $amount += floatval($lost_out_process['sum_baht']);

                    if($ml == $condition['MonthFrom']){
                        break;
                    }
                    
                }

                $SUMMARY_DATA[$fac['id']]['value_collect'] += $amount;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_value);

                $total_loss_amount += $amount;

                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_value);

            // percent
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $PRODUCTION_DATA[$fac['id']]['amount'];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);
                
                $col++;
            }

            $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $PRODUCTION_DATA['total']['amount'];
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   % สูญเสีย ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '');
            $col++;

            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $PRODUCTION_DATA[$fac['id']]['amount_collect'];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);

                $total_loss_amount += $amount;

                $col++;
            }

            $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $PRODUCTION_DATA['total']['amount_collect'];
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);

            // Summary
            // amount
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {
                $amount = floatval($SUMMARY_DATA[$fac['id']]['amount']);
                
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_amount);
                $total_loss_amount += $amount;
                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_amount);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   1.4 สูญเสียทั้งกระบวนการผลิต ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, 'ตัน');
            $col++;

            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $amount = floatval($SUMMARY_DATA[$fac['id']]['amount_collect']);

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_amount);

                $total_loss_amount += $amount;

                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_amount);

            // get value
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {
                $amount = floatval($SUMMARY_DATA[$fac['id']]['value']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_value);
                $total_loss_amount += $amount;
                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_value);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   มูลค่า ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, 'ล้านบาท');
            $col++;

            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $amount = floatval($SUMMARY_DATA[$fac['id']]['value_collect']);

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $amount / $divide_value);

                $total_loss_amount += $amount;

                $col++;
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $total_loss_amount / $divide_value);

            // percent
            $row++;
            $col = 0;
            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {
                
                $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $IN_PRODUCTION_DATA[$fac['id']]['amount'];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);
                $col++;

            }


            $this->total_loss_amount = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue();
            $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $IN_PRODUCTION_DATA['total']['amount'];
            $this->total_loss_amount_percent = $percent;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '   % สูญเสีย ');
            $col++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, '');
            $col++;

            $total_loss_amount = 0;
            foreach ($FactoryList as $key => $fac) {

                $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $IN_PRODUCTION_DATA[$fac['id']]['amount_collect'];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);

                $total_loss_amount += $amount;

                $col++;
            }

            $percent = (($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row - 2)->getValue() * $divide_amount) * 100) / $IN_PRODUCTION_DATA['total']['amount_collect'];
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col , $row, $percent);


        }

        $row++;

        $this->logger->info('sheet 4 : 4.4');


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

        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getStyle('A4:N' . ($row))->getFont()->setSize(14);
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

        return $objPHPExcel;        

    }


}