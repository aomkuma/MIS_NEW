<?php

namespace App\Controller;

use App\Controller\TravelController;
use App\Controller\CowGroupController;
use App\Controller\MineralController;
use App\Controller\VeterinaryController;
use App\Controller\InseminationController;
use App\Controller\TrainingCowBreedController;
use App\Controller\SpermSaleController;
use App\Controller\CooperativeMilkController;
use App\Controller\PersonalController;
use App\Controller\MBIController;
use App\Controller\ProductionInfoController;
use App\Service\FactoryService;
use App\Service\SubProductMilkService;
use App\Service\ProductMilkService;
use App\Service\CooperativeService;
use App\Controller\ProductionSaleInfoController;
use App\Controller\LostInProcessController;
use App\Controller\LostOutProcessController;
use App\Controller\LostWaitSaleController;
use PHPExcel;

class QuarterReportController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public $st = 1;
    public $en = 3;
    public $showyear = 0;

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

    private function getquarter($quar) {


        if ($quar == 1) {
            $this->showyear = 1;
            $this->st = 10;
            $this->en = 12;
        } else if ($quar == 2) {
            $this->st = 1;
            $this->en = 3;
        } else if ($quar == 3) {
            $this->st = 4;
            $this->en = 6;
        } else {
            $this->st = 7;
            $this->en = 9;
        }
    }

    public function exportquarterreportExcel($request, $response) {
        try {
            $obj = $request->getParsedBody();

            $condition['YearFrom'] = $obj['obj']['condition']['Year'];
            $condition['YearTo'] = $obj['obj']['condition']['Year'];
            $condition['QuarterFrom'] = $obj['obj']['condition']['Quarter'];
            $condition['QuarterTo'] = $obj['obj']['condition']['Quarter'];
            $condition['MonthFrom'] = 1;
            $condition['MonthTo'] = 1;
            $region = CooperativeService::getRegionList();


            $objPHPExcel = new PHPExcel();
            $objPHPExcel = $this->generatePowerExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generatePower2Excel($objPHPExcel, $condition, $region);

            $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $region);

            $objPHPExcel = $this->generateInseminationExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateMineralExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateTrainingCowbreedExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateTravelExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateSpermSaleExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateSpermSale2Excel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCooperativeMilkExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCowgroupExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCowgroup2Excel($objPHPExcel, $condition, $region);
            // $objPHPExcel = $this->generateCowgroup3Excel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCowgroup4Excel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCowgroup5Excel($objPHPExcel, $condition, $region);
            // $objPHPExcel = $this->generateCowgroup6Excel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateMilkExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generatesaleMilkExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateproductMilkExcel($objPHPExcel, $condition, $region);
//            die();
            $objPHPExcel = $this->generatesaleproductMilkExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generatelostproductionExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generatelostINproductionExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generatelostOUTproductionExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generatelostOUTproductionExcel2($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generatelostWAITproductionExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generatelostWAITproductionExcel2($objPHPExcel, $condition, $region);
            // $filename = 'MIS_Report-รายงานรายเดือน' . '_' . date('YmdHis') . '.xlsx';
            $filename = 'MIS_Report-Quarter_' . '_' . date('YmdHis') . '.xlsx';
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

    private function generatePowerExcel($objPHPExcel, $condition, $region) {

        $objPHPExcel->setActiveSheetIndex(0);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthFrom'] = 12;
            $condition['MonthTo'] = 12;
            $condition['YearTo']--;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthFrom'] = 3;
            $condition['MonthTo'] = 3;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthFrom'] = 6;
            $condition['MonthTo'] = 6;
        } else if ($condition['QuarterFrom'] == 4) {
            $condition['MonthFrom'] = 9;
            $condition['MonthTo'] = 9;
        }

        $data = PersonalController::getMonthList($condition);

        $objPHPExcel->getActiveSheet()->setTitle("1. อัตรากำลัง");
        $objPHPExcel->getActiveSheet()->setCellValue('A2', '1. อัตรากำลังทั้งหมดของ อ.ส.ค.');

        $this->getquarter($condition['QuarterFrom']);
//tb header
     
        
         $objPHPExcel->getActiveSheet()->setCellValue('J2', 'หน่วย : คน');
        $objPHPExcel->getActiveSheet()->getStyle('J2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->mergeCells('J2:K2');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'หน่วยงาน');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('F3:I3');
        $objPHPExcel->getActiveSheet()->setCellValue('J3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('J3:K3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'พนักงาน');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ลูกจ้าง');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'Outsource');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'พนักงาน');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', 'ลูกจ้าง');
        $objPHPExcel->getActiveSheet()->setCellValue('H4', 'Outsource');
        $objPHPExcel->getActiveSheet()->setCellValue('I4', 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('J4', 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('K4', '% เพิ่ม,ลด');
        
        

        $row = 0;
        $SummaryCurrentsum = 0;
        $SummaryCurrentdirector = 0;
        $SummaryCurrent = 0;
        $SummaryBeforesum = 0;
        $SummaryBeforedirector = 0;
        $SummaryBefore = 0;
        $SummaryPercentage = 0;
        $SummarysumPercentage = 0;
        $SummaryCurrentout = 0;
        $SummaryBeforeout = 0;
       foreach ($data['DataList'] as $item) {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item['Position']);
          //  $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item['Month']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':I' . (5 + $row))->getFont()->setBold(true);
            $row++;
            foreach ($item['CurrentEmployee'] as $key => $itemcurrent) {
                $sumcurrent = $itemcurrent['director'] + $itemcurrent['summary'] + $itemcurrent['outsource'];
                $sumbefore = $item['BeforeEmployee'][$key]['director'] + $item['BeforeEmployee'][$key]['summary'] + $item['BeforeEmployee'][$key]['outsource'];
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $itemcurrent['department']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $itemcurrent['summary']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $itemcurrent['director']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $itemcurrent['outsource']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumcurrent);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item['BeforeEmployee'][$key]['summary']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item['BeforeEmployee'][$key]['director']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (5 + $row), $item['BeforeEmployee'][$key]['outsource']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (5 + $row), $sumbefore);

                $objPHPExcel->getActiveSheet()->setCellValue('J' . (5 + $row), $sumcurrent + $sumbefore);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . (5 + $row), $itemcurrent['percent']);

                $row++;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item['SummaryCurrentsum']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $item['SummaryCurrentdirector']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item['SummaryCurrentout']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $item['SummaryCurrent']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item['SummaryBeforesum']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item['SummaryBeforedirector']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (5 + $row), $item['SummaryBeforeout']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (5 + $row), $item['SummaryBefore']);

            $objPHPExcel->getActiveSheet()->setCellValue('J' . (5 + $row), $item ['SummaryPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (5 + $row), $item['SummarysumPercentage']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':I' . (5 + $row))->getFont()->setBold(true);
            $row++;
            $SummaryCurrentsum += $item['SummaryCurrentsum'];

            $SummaryCurrentdirector += $item['SummaryCurrentdirector'];
            $SummaryCurrent += $item['SummaryCurrent'];
            $SummaryCurrentout += $item['SummaryCurrentout'];
            $SummaryBeforesum += $item['SummaryBeforesum'];
            $SummaryBeforedirector += $item['SummaryBeforedirector'];
            $SummaryBefore += $item['SummaryBefore'];
            $SummaryBeforeout += $item['SummaryBeforeout'];
            $SummaryPercentage += $item ['SummaryPercentage'];
            $SummarysumPercentage += $item['SummarysumPercentage'];
        }
//        //summary
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $SummaryCurrentsum);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $SummaryCurrentdirector);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $SummaryCurrentout);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $SummaryCurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $SummaryBeforesum);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $SummaryBeforedirector);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (5 + $row), $SummaryBefore);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (5 + $row), $SummaryBeforeout);


        $objPHPExcel->getActiveSheet()->setCellValue('J' . (5 + $row), $SummaryPercentage);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . (5 + $row), $SummarysumPercentage);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':K' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':K' . (5 + $row))->getFont()->setBold(true);
//        
        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('K2')->getFont()->setSize(18);

        $objPHPExcel->getActiveSheet()->getStyle('A3:K4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:K4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()
                ->getStyle("A3:K4")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A6:K' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A3:K' . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A2:A8')->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatePower2Excel($objPHPExcel, $condition, $region) {
        $data = PersonalController::getQuarterDataList($condition);
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->setTitle("1. อัตรากำลัง (2)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');

        $index = 1;
        $row = 0;
        $sum = 0;
        foreach ($data['Summary'][0]['current'] as $item) {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $index . '. ' . $item['detail']);
            for ($i = 1; $i < 10; $i++) {

                if ($item['lv' . $i] != '') {
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), 'พนักงานระดับ ' . $i . ' = ' . $item['lv' . $i] . ' ตำแหน่ง');
                    $sum += $item['lv' . $i];
                    $row++;
                }
            }
            $index++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543) . ' มีการเคลื่อนไหวของพนักงานและลูกจ้าง จำนวน  ' . $sum . '  ตำแหน่ง ดังนี้');
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A6:E50')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A2:E50')->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateVeterinaryExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $data = VeterinaryController::getQuarterDataList($condition, $region);

        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("2.1 สัตวแพทย์-ผสมเทียม (2)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  2.1 การบริการสัตวแพทย์และการบริการผสมเทียม');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '            2.1.1 การบริการสัตวแพทย์');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                       ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543) . ' มีโคเข้ารับการบริการสัตวแพทย์ จำนวน ' . number_format($data['Summary']['SummaryCurrentCow'], 2, '.', ',') . ' ตัว รายได้ ' . number_format($data['Summary']['SummaryCurrentService'], 2, '.', ',') . '  บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การบริการและมูลค่าลดลงคิดเป็นร้อยละ ' . number_format($data['Summary']['SummaryCowPercentage'], 2, '.', ','));
        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                  และ ' . number_format($data['Summary']['SummaryServicePercentage'], 2, '.', ',') . ' ตามลำดับ');
        $this->getquarter($condition['QuarterFrom']);
//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A9', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A9:A13');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B9:C9');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D9:E9');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('B10:C10');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('D10:E10');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F9:I10');
        $objPHPExcel->getActiveSheet()->setCellValue('B11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('C11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('D11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('E11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('F11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('G11', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('H11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('I11', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('B12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('C12', '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('D12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('E12', '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('F12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('G12', 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->setCellValue('H12', '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('I12', 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->setCellValue('B13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('C13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('D13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('E13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('F13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('G13', 'ลด,');
        $objPHPExcel->getActiveSheet()->setCellValue('H13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('I13', 'ลด,');
        $row = 0;
        $summarydiffcow = 0;
        $summarydiffservice = 0;
        foreach ($data['DataList'] as $item) {
            $summarydiffcow += $item['DiffCowData'];
            $summarydiffservice += $item['DiffServiceData'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (14 + $row), $item['RegionName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (14 + $row), $item['CurrentCowData']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (14 + $row), $item['CurrentServiceData']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (14 + $row), $item['BeforeCowData']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (14 + $row), $item['BeforeServiceData']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (14 + $row), $item['DiffCowData']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (14 + $row), $item['DiffCowDataPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (14 + $row), $item['DiffServiceData']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (14 + $row), $item['DiffServiceDataPercentage']);
            $row++;
        }
//        //summary
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (14 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (14 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (14 + $row), $data['Summary']['SummaryCurrentCow']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (14 + $row), $data['Summary']['SummaryCurrentService']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (14 + $row), $data['Summary']['SummaryBeforeCow']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (14 + $row), $data['Summary']['SummaryBeforeService']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (14 + $row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (14 + $row), $data['Summary']['SummaryCowPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (14 + $row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (14 + $row), $data['Summary']['SummaryServicePercentage']);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumm = $objPHPExcel->getActiveSheet()->getHighestColumn();
        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I13')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I13')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A9:I13")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);



        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B14:I' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A3:A8')->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        ///////tb 2
//        $tb2data = $data;
//        while ($condition['MonthFrom'] != 10) {
//            $condition['MonthFrom'] -= 1;
//            if ($condition['MonthFrom'] == 0) {
//                $condition['MonthFrom'] = 12;
//                $condition['MonthTo'] = 12;
//                $condition['YearTo'] -= 1;
//                $condition['YearFrom'] -= 1;
//                $newdata = VeterinaryController::getQuarterDataList($condition, $region);
//
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentCowData'] += $itemnewdata['CurrentCowData'];
//                    $tb2data['DataList'][$key]['CurrentServiceData'] += $itemnewdata['CurrentServiceData'];
//                    $tb2data['DataList'][$key]['BeforeCowData'] += $itemnewdata['BeforeCowData'];
//                    $tb2data['DataList'][$key]['BeforeServiceData'] += $itemnewdata['BeforeServiceData'];
//                    $tb2data['DataList'][$key]['DiffCowData'] += $itemnewdata['DiffCowData'];
//                    $tb2data['DataList'][$key]['DiffCowDataPercentage'] += $itemnewdata['DiffCowDataPercentage'];
//                    $tb2data['DataList'][$key]['DiffServiceData'] += $itemnewdata['DiffServiceData'];
//                    $tb2data['DataList'][$key]['DiffServiceDataPercentage'] += $itemnewdata['DiffServiceDataPercentage'];
//                    $tb2data['Summary']['SummaryCurrentCow'] += $newdata['Summary']['SummaryCurrentCow'];
//                    $tb2data['Summary']['SummaryCurrentService'] += $newdata['Summary']['SummaryCurrentService'];
//                    $tb2data['Summary']['SummaryBeforeCow'] += $newdata['Summary']['SummaryBeforeCow'];
//                    $tb2data['Summary']['SummaryBeforeService'] += $newdata['Summary']['SummaryBeforeService'];
//                    $tb2data['Summary']['SummaryCowPercentage'] += $newdata['Summary']['SummaryCowPercentage'];
//                    $tb2data['Summary']['SummaryServicePercentage'] += $newdata['Summary']['SummaryServicePercentage'];
//                }
//            } else {
//                $condition['MonthTo'] -= 1;
//
//                $newdata = VeterinaryController::getQuarterDataList($condition, $region);
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentCowData'] += $itemnewdata['CurrentCowData'];
//                    $tb2data['DataList'][$key]['CurrentServiceData'] += $itemnewdata['CurrentServiceData'];
//                    $tb2data['DataList'][$key]['BeforeCowData'] += $itemnewdata['BeforeCowData'];
//                    $tb2data['DataList'][$key]['BeforeServiceData'] += $itemnewdata['BeforeServiceData'];
//                    $tb2data['DataList'][$key]['DiffCowData'] += $itemnewdata['DiffCowData'];
//                    $tb2data['DataList'][$key]['DiffCowDataPercentage'] += $itemnewdata['DiffCowDataPercentage'];
//                    $tb2data['DataList'][$key]['DiffServiceData'] += $itemnewdata['DiffServiceData'];
//                    $tb2data['DataList'][$key]['DiffServiceDataPercentage'] += $itemnewdata['DiffServiceDataPercentage'];
//                    $tb2data['Summary']['SummaryCurrentCow'] += $newdata['Summary']['SummaryCurrentCow'];
//                    $tb2data['Summary']['SummaryCurrentService'] += $newdata['Summary']['SummaryCurrentService'];
//                    $tb2data['Summary']['SummaryBeforeCow'] += $newdata['Summary']['SummaryBeforeCow'];
//                    $tb2data['Summary']['SummaryBeforeService'] += $newdata['Summary']['SummaryBeforeService'];
//                    $tb2data['Summary']['SummaryCowPercentage'] += $newdata['Summary']['SummaryCowPercentage'];
//                    $tb2data['Summary']['SummaryServicePercentage'] += $newdata['Summary']['SummaryServicePercentage'];
//                }
//            }
//        }
//        $highestRow += 2;
//        $startrowtb2 = $highestRow;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' มีโคเข้ารับการบริการสัตวแพทย์ จำนวน ' . number_format($tb2data['Summary']['SummaryCurrentCow'], 2, '.', ',') . ' ตัว รายได้ ' . number_format($tb2data['Summary']['SummaryCurrentService'], 2, '.', ',') . '  บาท');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การบริการและมูลค่าลดลงคิดเป็นร้อยละ ' . number_format($tb2data['Summary']['SummaryCowPercentage'], 2, '.', ','));
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  และ ' . number_format($tb2data['Summary']['SummaryServicePercentage'], 2, '.', ',') . ' ตามลำดับ');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, 'รายการ');
//        $objPHPExcel->getActiveSheet()->mergeCells('A' . $highestRow . ':A' . ($highestRow + 3));
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543));
//        $objPHPExcel->getActiveSheet()->mergeCells('B' . $highestRow . ':C' . $highestRow);
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'ต.ค. ' . ($showm + 542) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 542));
//        $objPHPExcel->getActiveSheet()->mergeCells('D' . $highestRow . ':E' . $highestRow);
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'ผลต่าง');
//        $objPHPExcel->getActiveSheet()->mergeCells('F' . $highestRow . ':I' . $highestRow);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'โคที่รับ');
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, 'รายได้ค่าบริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'โคที่รับ');
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, 'รายได้ค่าบริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'โคที่รับ');
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, '%');
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, 'รายได้ค่าบริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, '%');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'บริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, '+เวชภัณฑ์+วัสดุฯ');
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'บริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, '+เวชภัณฑ์+วัสดุฯ');
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'บริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, 'เพิ่ม,');
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, '+เวชภัณฑ์+วัสดุฯ');
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, 'เพิ่ม,');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, '(ตัว)');
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, '(บาท)');
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, '(ตัว)');
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, '(บาท)');
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, '(ตัว)');
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, 'ลด,');
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, '(บาท)');
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, 'ลด,');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//
//
//
//        $objPHPExcel->getActiveSheet()->getStyle('A10:I13')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A10:I13')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle("A10:I13")
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//
//        //  print_r($data);
//        //   die();
//        $row = $highestRow;
//
//        foreach ($tb2data['DataList'] as $item2) {
//            $summarydiffcow += $item['DiffCowData'];
//            $summarydiffservice += $item['DiffServiceData'];
//            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $item2['RegionName']);
//            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $item2['CurrentCowData']);
//            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $item2['CurrentServiceData']);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $item2['BeforeCowData']);
//            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $item2['BeforeServiceData']);
//            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $item2['DiffCowData']);
//            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $item2['DiffCowDataPercentage']);
//            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $item2['DiffServiceData']);
//            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $item2['DiffServiceDataPercentage']);
//            $row++;
//        }
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รวมทั้งสิ้น');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tb2data['Summary']['SummaryCurrentCow']);
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $tb2data['Summary']['SummaryCurrentService']);
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $tb2data['Summary']['SummaryBeforeCow']);
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $tb2data['Summary']['SummaryBeforeService']);
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $summarydiffcow);
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $tb2data['Summary']['SummaryCowPercentage']);
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $summarydiffservice);
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $tb2data['Summary']['SummaryServicePercentage']);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':I' . ($row))->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)
//                ->getNumberFormat()
//                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($startrowtb2 + 3) . ':I' . $row)->applyFromArray(
//                array(
//                    'borders' => array(
//                        'allborders' => array(
//                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
//                        )
//                    ),
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)->applyFromArray(
//                array(
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
        return $objPHPExcel;
    }

    private function generateInseminationExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $data = InseminationController::getQuarterDataList($condition, $region);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("2.1 สัตวแพท-ผสมเทียม (3)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  2.1 การบริการสัตวแพทย์และการบริการผสมเทียม');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '            2.1.2 การบริการผสมเทียม');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                       ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543) . ' มีโคเข้ารับการบริการผสมเทียม จำนวน ' . number_format($data['Summary']['SummaryCurrentCowService'], 2, '.', ',') . ' ตัว รายได้ ' . number_format($data['Summary']['SummaryCurrentIncomeService'], 2, '.', ',') . '  บาท ');
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การบริการและมูลค่าลดลงคิดเป็นร้อยละ ' . number_format($data['Summary']['SummaryCowServicePercentage'], 2, '.', ','));
        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                  และ ' . number_format($data['Summary']['SummaryIncomeServicePercentage'], 2, '.', ',') . ' ตามลำดับ');

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A9', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A9:A13');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B9:C9');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D9:E9');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('B10:C10');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('D10:E10');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F9:I10');
        $objPHPExcel->getActiveSheet()->setCellValue('B11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('C11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('D11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('E11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('F11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('G11', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('H11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('I11', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('B12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('C12', '+ รายได้น้ำเชื้อและวัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('D12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('E12', '+ รายได้น้ำเชื้อและวัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('F12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('G12', 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->setCellValue('H12', '+ รายได้น้ำเชื้อและวัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('I12', 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->setCellValue('B13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('C13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('D13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('E13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('F13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('G13', 'ลด,');
        $objPHPExcel->getActiveSheet()->setCellValue('H13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('I13', 'ลด,');
        $row = 0;
        $summarydiffcow = 0;
        $summarydiffservice = 0;
        foreach ($data['DataList'] as $item) {
            $summarydiffcow += $item['DiffCowService'];
            $summarydiffservice += $item['DiffIncomeService'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (14 + $row), $item['RegionName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (14 + $row), $item['CurrentCowService']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (14 + $row), $item['CurrentIncomeService']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (14 + $row), $item['BeforeCowService']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (14 + $row), $item['BeforeIncomeService']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (14 + $row), $item['DiffCowService']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (14 + $row), $item['DiffCowServicePercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (14 + $row), $item['DiffIncomeService']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (14 + $row), $item['DiffIncomeServicePercentage']);
            $row++;
        }
        //summary
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (14 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (14 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (14 + $row), $data['Summary']['SummaryCurrentCowService']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (14 + $row), $data['Summary']['SummaryCurrentIncomeService']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (14 + $row), $data['Summary']['SummaryBeforeCowService']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (14 + $row), $data['Summary']['SummaryBeforeIncomeService']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (14 + $row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (14 + $row), $data['Summary']['SummaryCowServicePercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (14 + $row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (14 + $row), $data['Summary']['SummaryIncomeServicePercentage']);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumm = $objPHPExcel->getActiveSheet()->getHighestColumn();
        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I13')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I13')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A9:I13")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);



        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B14:I' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A3:A8')->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        ///////tb 2
//        $tb2data = $data;
//        while ($condition['MonthFrom'] != 10) {
//            $condition['MonthFrom'] -= 1;
//            if ($condition['MonthFrom'] == 0) {
//                $condition['MonthFrom'] = 12;
//                $condition['MonthTo'] = 12;
//                $condition['YearTo'] -= 1;
//                $condition['YearFrom'] -= 1;
//                $newdata = InseminationController::getQuarterDataList($condition, $region);
//
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentCowService'] += $itemnewdata['CurrentCowService'];
//                    $tb2data['DataList'][$key]['CurrentIncomeService'] += $itemnewdata['CurrentIncomeService'];
//                    $tb2data['DataList'][$key]['BeforeCowService'] += $itemnewdata['BeforeCowService'];
//                    $tb2data['DataList'][$key]['BeforeIncomeService'] += $itemnewdata['BeforeIncomeService'];
//                    $tb2data['DataList'][$key]['DiffCowService'] += $itemnewdata['DiffCowService'];
//                    $tb2data['DataList'][$key]['DiffCowServicePercentage'] += $itemnewdata['DiffCowServicePercentage'];
//                    $tb2data['DataList'][$key]['DiffIncomeService'] += $itemnewdata['DiffIncomeService'];
//                    $tb2data['DataList'][$key]['DiffIncomeServicePercentage'] += $itemnewdata['DiffIncomeServicePercentage'];
//                    $tb2data['Summary']['SummaryCurrentCowService'] += $newdata['Summary']['SummaryCurrentCowService'];
//                    $tb2data['Summary']['SummaryCurrentIncomeService'] += $newdata['Summary']['SummaryCurrentIncomeService'];
//                    $tb2data['Summary']['SummaryBeforeCowService'] += $newdata['Summary']['SummaryBeforeCowService'];
//                    $tb2data['Summary']['SummaryBeforeIncomeService'] += $newdata['Summary']['SummaryBeforeIncomeService'];
//                    $tb2data['Summary']['SummaryCowServicePercentage'] += $newdata['Summary']['SummaryCowServicePercentage'];
//                    $tb2data['Summary']['SummaryIncomeServicePercentage'] += $newdata['Summary']['SummaryIncomeServicePercentage'];
//                }
//            } else {
//                $condition['MonthTo'] -= 1;
//
//                $newdata = InseminationController::getQuarterDataList($condition, $region);
//
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentCowService'] += $itemnewdata['CurrentCowService'];
//                    $tb2data['DataList'][$key]['CurrentIncomeService'] += $itemnewdata['CurrentIncomeService'];
//                    $tb2data['DataList'][$key]['BeforeCowService'] += $itemnewdata['BeforeCowService'];
//                    $tb2data['DataList'][$key]['BeforeIncomeService'] += $itemnewdata['BeforeIncomeService'];
//                    $tb2data['DataList'][$key]['DiffCowService'] += $itemnewdata['DiffCowService'];
//                    $tb2data['DataList'][$key]['DiffCowServicePercentage'] += $itemnewdata['DiffCowServicePercentage'];
//                    $tb2data['DataList'][$key]['DiffIncomeService'] += $itemnewdata['DiffIncomeService'];
//                    $tb2data['DataList'][$key]['DiffIncomeServicePercentage'] += $itemnewdata['DiffIncomeServicePercentage'];
//                    $tb2data['Summary']['SummaryCurrentCowService'] += $newdata['Summary']['SummaryCurrentCowService'];
//                    $tb2data['Summary']['SummaryCurrentIncomeService'] += $newdata['Summary']['SummaryCurrentIncomeService'];
//                    $tb2data['Summary']['SummaryBeforeCowService'] += $newdata['Summary']['SummaryBeforeCowService'];
//                    $tb2data['Summary']['SummaryBeforeIncomeService'] += $newdata['Summary']['SummaryBeforeIncomeService'];
//                    $tb2data['Summary']['SummaryCowServicePercentage'] += $newdata['Summary']['SummaryCowServicePercentage'];
//                    $tb2data['Summary']['SummaryIncomeServicePercentage'] += $newdata['Summary']['SummaryIncomeServicePercentage'];
//                }
//            }
//        }
//
//        $highestRow += 2;
//        $startrowtb2 = $highestRow;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' มีโคเข้ารับการบริการผสมเทียม จำนวน ' . number_format($tb2data['Summary']['SummaryCurrentCowService'], 2, '.', ',') . ' ตัว รายได้ ' . number_format($tb2data['Summary']['SummaryCurrentIncomeService'], 2, '.', ',') . '  บาท');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การบริการและมูลค่าลดลงคิดเป็นร้อยละ ' . number_format($tb2data['Summary']['SummaryCowServicePercentage'], 2, '.', ','));
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  และ ' . number_format($tb2data['Summary']['SummaryIncomeServicePercentage'], 2, '.', ',') . ' ตามลำดับ');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, 'รายการ');
//        $objPHPExcel->getActiveSheet()->mergeCells('A' . $highestRow . ':A' . ($highestRow + 3));
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543));
//        $objPHPExcel->getActiveSheet()->mergeCells('B' . $highestRow . ':C' . $highestRow);
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'ต.ค. ' . ($showm + 542) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 542));
//        $objPHPExcel->getActiveSheet()->mergeCells('D' . $highestRow . ':E' . $highestRow);
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'ผลต่าง');
//        $objPHPExcel->getActiveSheet()->mergeCells('F' . $highestRow . ':I' . $highestRow);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'โคที่รับ');
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, 'รายได้ค่าบริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'โคที่รับ');
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, 'รายได้ค่าบริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'โคที่รับ');
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, '%');
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, 'รายได้ค่าบริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, '%');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'บริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, '+ รายได้น้ำเชื้อและวัสดุฯ');
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'บริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, '+ รายได้น้ำเชื้อและวัสดุฯ');
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'บริการ');
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, 'เพิ่ม,');
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, '+ รายได้น้ำเชื้อและวัสดุฯ');
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, 'เพิ่ม,');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, '(ตัว)');
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, '(บาท)');
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, '(ตัว)');
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, '(บาท)');
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, '(ตัว)');
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, 'ลด,');
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, '(บาท)');
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, 'ลด,');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//
//
//
//        $objPHPExcel->getActiveSheet()->getStyle('A10:I13')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A10:I13')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle("A10:I13")
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//
//        //  print_r($data);
//        //   die();
//        $row = $highestRow;
//
//        foreach ($tb2data['DataList'] as $item2) {
//            $summarydiffcow += $item2['DiffCowService'];
//            $summarydiffservice += $item2['DiffIncomeService'];
//            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $item2['RegionName']);
//            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $item2['CurrentCowService']);
//            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $item2['CurrentIncomeService']);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $item2['BeforeCowService']);
//            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $item2['BeforeIncomeService']);
//            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $item2['DiffCowService']);
//            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $item2['DiffCowServicePercentage']);
//            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $item2['DiffIncomeService']);
//            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $item2['DiffIncomeServicePercentage']);
//            $row++;
//        }
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รวมทั้งสิ้น');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tb2data['Summary']['SummaryCurrentCowService']);
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $tb2data['Summary']['SummaryCurrentIncomeService']);
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $tb2data['Summary']['SummaryBeforeCowService']);
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $tb2data['Summary']['SummaryBeforeIncomeService']);
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $summarydiffcow);
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $tb2data['Summary']['SummaryCowServicePercentage']);
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $summarydiffservice);
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $tb2data['Summary']['SummaryIncomeServicePercentage']);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':I' . ($row))->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)
//                ->getNumberFormat()
//                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($startrowtb2 + 3) . ':I' . $row)->applyFromArray(
//                array(
//                    'borders' => array(
//                        'allborders' => array(
//                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
//                        )
//                    ),
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)->applyFromArray(
//                array(
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );

        return $objPHPExcel;
    }

    private function generateMineralExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $data = MineralController::getQuarterDataListbyMaster($condition, $region);

        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("2.2 อาหารสัตว์");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  2.2 อาหารสัตว์');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                       ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543) . ' มีจำหน่ายอาหารสัตว์ ปริมาณ ' . number_format($data['Summary']['SummaryCurrentMineralAmount'], 2, '.', ',') . '  กิโลกรัม มูลค่า ' . number_format($data['Summary']['SummaryCurrentMineralIncome'], 2, '.', ',') . '  บาท ');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การปริมาณและมูลค่าลดลงคิดเป็นร้อยละ ' . number_format($data['Summary']['SummaryMineralAmountPercentage'], 2, '.', ','));
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                  และ ' . number_format($data['Summary']['SummaryMineralIncomePercentage'], 2, '.', ',') . ' ตามลำดับ');

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A8', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A8:A10');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B8:C8');
        $objPHPExcel->getActiveSheet()->setCellValue('D8', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D8:E8');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('B9:C9');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('D9:E9');
        $objPHPExcel->getActiveSheet()->setCellValue('F8', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F8:I8');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F10', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G10', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I10', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('A11', 'จำหน่าย');
        $objPHPExcel->getActiveSheet()->getStyle('A11')->getFont()->setBold(true);
        $row = 0;
        $summarydiffcow = 0;
        $summarydiffservice = 0;
        foreach ($data['DataList'] as $item) {
            $summarydiffcow += $item['DiffWeight'];
            $summarydiffservice += $item['DiffBaht'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), $item['RegionName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $item['CurrentWeight']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $item['BeforeWeight']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $item['DiffWeight']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $item['DiffWeightPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $item['DiffBahtPercentage']);
            $row++;
        }
        //summary
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $data['Summary']['SummaryCurrentMineralAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $data['Summary']['SummaryCurrentMineralIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $data['Summary']['SummaryBeforMineralAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $data['Summary']['SummaryBeforeMineralIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $data['Summary']['SummaryMineralAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $data['Summary']['SummaryMineralIncomePercentage']);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumm = $objPHPExcel->getActiveSheet()->getHighestColumn();
        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A8:I10')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A8:I10')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A8:I10")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);



        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B12:I' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A8:I' . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A3:A8')->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        ///////tb 2
//        $tb2data = $data;
//        while ($condition['MonthFrom'] != 10) {
//            $condition['MonthFrom'] -= 1;
//            if ($condition['MonthFrom'] == 0) {
//                $condition['MonthFrom'] = 12;
//                $condition['MonthTo'] = 12;
//                $condition['YearTo'] -= 1;
//                $condition['YearFrom'] -= 1;
//                $newdata = MineralController::getQuarterDataListByMaster($condition, $region);
//
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentWeight'] += $itemnewdata['CurrentWeight'];
//                    $tb2data['DataList'][$key]['CurrentBaht'] += $itemnewdata['CurrentBaht'];
//                    $tb2data['DataList'][$key]['BeforeWeight'] += $itemnewdata['BeforeWeight'];
//                    $tb2data['DataList'][$key]['BeforeBaht'] += $itemnewdata['BeforeBaht'];
//                    $tb2data['DataList'][$key]['DiffWeight'] += $itemnewdata['DiffWeight'];
//                    $tb2data['DataList'][$key]['DiffWeightPercentage'] += $itemnewdata['DiffWeightPercentage'];
//                    $tb2data['DataList'][$key]['DiffBaht'] += $itemnewdata['DiffBaht'];
//                    $tb2data['DataList'][$key]['DiffBahtPercentage'] += $itemnewdata['DiffBahtPercentage'];
//                    $tb2data['Summary']['SummaryCurrentMineralAmount'] += $newdata['Summary']['SummaryCurrentMineralAmount'];
//                    $tb2data['Summary']['SummaryCurrentMineralIncome'] += $newdata['Summary']['SummaryCurrentMineralIncome'];
//                    $tb2data['Summary']['SummaryBeforMineralAmount'] += $newdata['Summary']['SummaryBeforMineralAmount'];
//                    $tb2data['Summary']['SummaryBeforeMineralIncome'] += $newdata['Summary']['SummaryBeforeMineralIncome'];
//                    $tb2data['Summary']['SummaryMineralAmountPercentage'] += $newdata['Summary']['SummaryMineralAmountPercentage'];
//                    $tb2data['Summary']['SummaryMineralIncomePercentage'] += $newdata['Summary']['SummaryMineralIncomePercentage'];
//                }
//            } else {
//                $condition['MonthTo'] -= 1;
//
//                $newdata = MineralController::getQuarterDataListByMaster($condition, $region);
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentWeight'] += $itemnewdata['CurrentWeight'];
//                    $tb2data['DataList'][$key]['CurrentBaht'] += $itemnewdata['CurrentBaht'];
//                    $tb2data['DataList'][$key]['BeforeWeight'] += $itemnewdata['BeforeWeight'];
//                    $tb2data['DataList'][$key]['BeforeBaht'] += $itemnewdata['BeforeBaht'];
//                    $tb2data['DataList'][$key]['DiffWeight'] += $itemnewdata['DiffWeight'];
//                    $tb2data['DataList'][$key]['DiffWeightPercentage'] += $itemnewdata['DiffWeightPercentage'];
//                    $tb2data['DataList'][$key]['DiffBaht'] += $itemnewdata['DiffBaht'];
//                    $tb2data['DataList'][$key]['DiffBahtPercentage'] += $itemnewdata['DiffBahtPercentage'];
//                    $tb2data['Summary']['SummaryCurrentMineralAmount'] += $newdata['Summary']['SummaryCurrentMineralAmount'];
//                    $tb2data['Summary']['SummaryCurrentMineralIncome'] += $newdata['Summary']['SummaryCurrentMineralIncome'];
//                    $tb2data['Summary']['SummaryBeforMineralAmount'] += $newdata['Summary']['SummaryBeforMineralAmount'];
//                    $tb2data['Summary']['SummaryBeforeMineralIncome'] += $newdata['Summary']['SummaryBeforeMineralIncome'];
//                    $tb2data['Summary']['SummaryMineralAmountPercentage'] += $newdata['Summary']['SummaryMineralAmountPercentage'];
//                    $tb2data['Summary']['SummaryMineralIncomePercentage'] += $newdata['Summary']['SummaryMineralIncomePercentage'];
//                }
//            }
//        }
//        $highestRow += 2;
//        $startrowtb2 = $highestRow;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . '  จำหน่ายอาหารสัตว์ จำนวน ' . number_format($tb2data['Summary']['SummaryCurrentMineralAmount'], 2, '.', ',') . ' ตัว รายได้ ' . number_format($tb2data['Summary']['SummaryCurrentMineralIncome'], 2, '.', ',') . '  บาท');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา  จำหน่ายอาหารสัตว์คิดเป็นร้อยละ ' . number_format($tb2data['Summary']['SummaryMineralAmountPercentage'], 2, '.', ','));
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  และ ' . number_format($tb2data['Summary']['SummaryMineralIncomePercentage'], 2, '.', ',') . ' ตามลำดับ');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, 'รายการ');
//        $objPHPExcel->getActiveSheet()->mergeCells('A' . $highestRow . ':A' . ($highestRow + 1));
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543));
//        $objPHPExcel->getActiveSheet()->mergeCells('B' . $highestRow . ':C' . $highestRow);
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'ต.ค. ' . ($showm + 542) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 542));
//        $objPHPExcel->getActiveSheet()->mergeCells('D' . $highestRow . ':E' . $highestRow);
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'ผลต่าง');
//        $objPHPExcel->getActiveSheet()->mergeCells('F' . $highestRow . ':I' . $highestRow);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//
//
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'กิโลกรัม');
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, 'บาท');
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'กิโลกรัม');
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, 'บาท');
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'กิโลกรัม');
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, '% เพิ่ม,ลด');
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, 'บาท');
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, '% เพิ่ม,ลด');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, 'จำหน่าย');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setBold(true);
//        $highestRow++;
//        $row = $highestRow;
//
//        foreach ($tb2data['DataList'] as $item2) {
//            $summarydiffcow += $item2['DiffCowService'];
//            $summarydiffservice += $item2['DiffIncomeService'];
//            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $item2['MineralName']);
//            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $item2['CurrentWeight']);
//            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $item2['CurrentBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $item2['BeforeWeight']);
//            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $item2['BeforeBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $item2['DiffWeight']);
//            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $item2['DiffWeightPercentage']);
//            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $item2['DiffBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $item2['DiffBahtPercentage']);
//
//            $row++;
//        }
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รวมทั้งสิ้น');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tb2data['Summary']['SummaryCurrentMineralAmount']);
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $tb2data['Summary']['SummaryCurrentMineralIncome']);
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $tb2data['Summary']['SummaryBeforMineralAmount']);
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $tb2data['Summary']['SummaryBeforeMineralIncome']);
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $summarydiffcow);
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $tb2data['Summary']['SummaryMineralAmountPercentage']);
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $summarydiffservice);
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $tb2data['Summary']['SummaryMineralIncomePercentage']);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':I' . ($row))->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)
//                ->getNumberFormat()
//                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($startrowtb2 + 3) . ':I' . $row)->applyFromArray(
//                array(
//                    'borders' => array(
//                        'allborders' => array(
//                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
//                        )
//                    ),
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)->applyFromArray(
//                array(
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );

        return $objPHPExcel;
    }

    private function generateTrainingCowbreedExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $data = TrainingCowBreedController::getQuarterDataList($condition, $region);


        $objPHPExcel->getActiveSheet()->setTitle("2.3 การฝึกอบรม");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  2.3 การฝึกอบรมการเลี้ยงโคนม');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                       ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543) . ' มีการฝึกอบรมทั้งสิ้น  ' . number_format($data['Summary']['SummaryCurrentCowBreedAmount'], 2, '.', ',') . '  ราย มูลค่า ' . number_format($data['Summary']['SummaryCurrentCowBreedIncome'], 2, '.', ',') . '  บาท ');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา จำนวนผู้เข้ารับการอบรมและมูลค่าการบริการคิดเป็นร้อยละ ' . number_format($data['Summary']['SummaryCowBreedAmountPercentage'], 2, '.', ','));
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                  และ ' . number_format($data['Summary']['SummaryCowBreedIncomePercentage'], 2, '.', ',') . ' ตามลำดับ');

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A8', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A8:A10');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', ' ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B8:C8');
        $objPHPExcel->getActiveSheet()->setCellValue('D8', ' ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D8:E8');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('B9:C9');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('D9:E9');
        $objPHPExcel->getActiveSheet()->setCellValue('F8', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F8:I8');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('C10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('E10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F10', 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('G10', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I10', '% เพิ่ม,ลด');
        $row = 0;
        $summarydiffcow = 0;
        $summarydiffservice = 0;
        foreach ($data['DataList'] as $item) {
            $summarydiffcow += $item['DiffAmount'];
            $summarydiffservice += $item['DiffBaht'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), $item['CowBreedName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $item['DiffBahtPercentage']);
            $row++;
        }
        //summary
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $data['Summary']['SummaryCurrentCowBreedAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $data['Summary']['SummaryCurrentCowBreedIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $data['Summary']['SummaryBeforeCowBreedAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $data['Summary']['SummaryBeforeCowBreedIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $data['Summary']['SummaryCowBreedAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $data['Summary']['SummaryCowBreedIncomePercentage']);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumm = $objPHPExcel->getActiveSheet()->getHighestColumn();

        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A8:I10')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A8:I10')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A8:I10")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);



        $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B12:I' . (12 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A8:I' . (12 + $row))->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A3:A8')->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        ///////tb 2
//        $tb2data = $data;
//        while ($condition['MonthFrom'] != 10) {
//            $condition['MonthFrom'] -= 1;
//            if ($condition['MonthFrom'] == 0) {
//                $condition['MonthFrom'] = 12;
//                $condition['MonthTo'] = 12;
//                $condition['YearTo'] -= 1;
//                $condition['YearFrom'] -= 1;
//                $newdata = TrainingCowBreedController::getQuarterDataList($condition, $region);
//
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentAmount'] += $itemnewdata['CurrentAmount'];
//                    $tb2data['DataList'][$key]['CurrentBaht'] += $itemnewdata['CurrentBaht'];
//                    $tb2data['DataList'][$key]['BeforeAmount'] += $itemnewdata['BeforeAmount'];
//                    $tb2data['DataList'][$key]['BeforeBaht'] += $itemnewdata['BeforeBaht'];
//                    $tb2data['DataList'][$key]['DiffAmount'] += $itemnewdata['DiffAmount'];
//                    $tb2data['DataList'][$key]['DiffAmountPercentage'] += $itemnewdata['DiffAmountPercentage'];
//                    $tb2data['DataList'][$key]['DiffBaht'] += $itemnewdata['DiffBaht'];
//                    $tb2data['DataList'][$key]['DiffBahtPercentage'] += $itemnewdata['DiffBahtPercentage'];
//                    $tb2data['Summary']['SummaryCurrentCowBreedAmount'] += $newdata['Summary']['SummaryCurrentCowBreedAmount'];
//                    $tb2data['Summary']['SummaryCurrentCowBreedIncome'] += $newdata['Summary']['SummaryCurrentCowBreedIncome'];
//                    $tb2data['Summary']['SummaryBeforeCowBreedAmount'] += $newdata['Summary']['SummaryBeforeCowBreedAmount'];
//                    $tb2data['Summary']['SummaryBeforeCowBreedIncome'] += $newdata['Summary']['SummaryBeforeCowBreedIncome'];
//                    $tb2data['Summary']['SummaryCowBreedAmountPercentage'] += $newdata['Summary']['SummaryCowBreedAmountPercentage'];
//                    $tb2data['Summary']['SummaryCowBreedIncomePercentage'] += $newdata['Summary']['SummaryCowBreedIncomePercentage'];
//                }
//            } else {
//                $condition['MonthTo'] -= 1;
//
//                $newdata = TrainingCowBreedController::getQuarterDataList($condition, $region);
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentAmount'] += $itemnewdata['CurrentAmount'];
//                    $tb2data['DataList'][$key]['CurrentBaht'] += $itemnewdata['CurrentBaht'];
//                    $tb2data['DataList'][$key]['BeforeAmount'] += $itemnewdata['BeforeAmount'];
//                    $tb2data['DataList'][$key]['BeforeBaht'] += $itemnewdata['BeforeBaht'];
//                    $tb2data['DataList'][$key]['DiffAmount'] += $itemnewdata['DiffAmount'];
//                    $tb2data['DataList'][$key]['DiffAmountPercentage'] += $itemnewdata['DiffAmountPercentage'];
//                    $tb2data['DataList'][$key]['DiffBaht'] += $itemnewdata['DiffBaht'];
//                    $tb2data['DataList'][$key]['DiffBahtPercentage'] += $itemnewdata['DiffBahtPercentage'];
//                    $tb2data['Summary']['SummaryCurrentCowBreedAmount'] += $newdata['Summary']['SummaryCurrentCowBreedAmount'];
//                    $tb2data['Summary']['SummaryCurrentCowBreedIncome'] += $newdata['Summary']['SummaryCurrentCowBreedIncome'];
//                    $tb2data['Summary']['SummaryBeforeCowBreedAmount'] += $newdata['Summary']['SummaryBeforeCowBreedAmount'];
//                    $tb2data['Summary']['SummaryBeforeCowBreedIncome'] += $newdata['Summary']['SummaryBeforeCowBreedIncome'];
//                    $tb2data['Summary']['SummaryCowBreedAmountPercentage'] += $newdata['Summary']['SummaryCowBreedAmountPercentage'];
//                    $tb2data['Summary']['SummaryCowBreedIncomePercentage'] += $newdata['Summary']['SummaryCowBreedIncomePercentage'];
//                }
//            }
//        }
//        $highestRow += 2;
//        $startrowtb2 = $highestRow;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' มีโคเข้ารับการบริการผสมเทียม จำนวน ' . number_format($tb2data['Summary']['SummaryCurrentCowBreedAmount'], 2, '.', ',') . ' ตัว รายได้ ' . number_format($tb2data['Summary']['SummaryCurrentCowBreedIncome'], 2, '.', ',') . '  บาท');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การบริการและมูลค่าลดลงคิดเป็นร้อยละ ' . number_format($tb2data['Summary']['SummaryCowBreedAmountPercentage'], 2, '.', ','));
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  และ ' . number_format($tb2data['Summary']['SummaryCowBreedIncomePercentage'], 2, '.', ',') . ' ตามลำดับ');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, 'รายการ');
//        $objPHPExcel->getActiveSheet()->mergeCells('A' . $highestRow . ':A' . ($highestRow + 1));
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543));
//        $objPHPExcel->getActiveSheet()->mergeCells('B' . $highestRow . ':C' . $highestRow);
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'ต.ค. ' . ($showm + 542) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 542));
//        $objPHPExcel->getActiveSheet()->mergeCells('D' . $highestRow . ':E' . $highestRow);
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'ผลต่าง');
//        $objPHPExcel->getActiveSheet()->mergeCells('F' . $highestRow . ':I' . $highestRow);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//
//
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'ราย');
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, 'บาท');
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'ราย');
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, 'บาท');
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'ราย');
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, '% เพิ่ม,ลด');
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, 'บาท');
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, '% เพิ่ม,ลด');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//        $row = $highestRow;
//
//        foreach ($tb2data['DataList'] as $item2) {
//            $summarydiffcow += $item2['DiffCowService'];
//            $summarydiffservice += $item2['DiffIncomeService'];
//            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $item2['CowBreedName']);
//            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $item2['CurrentAmount']);
//            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $item2['CurrentBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $item2['BeforeAmount']);
//            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $item2['BeforeBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $item2['DiffAmount']);
//            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $item2['DiffAmountPercentage']);
//            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $item2['DiffBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $item2['DiffBahtPercentage']);
//
//            $row++;
//        }
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รวมทั้งสิ้น');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tb2data['Summary']['SummaryCurrentCowBreedAmount']);
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $tb2data['Summary']['SummaryCurrentCowBreedIncome']);
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $tb2data['Summary']['SummaryBeforeCowBreedAmount']);
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $tb2data['Summary']['SummaryBeforeCowBreedIncome']);
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $summarydiffcow);
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $tb2data['Summary']['SummaryCowBreedAmountPercentage']);
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $summarydiffservice);
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $tb2data['Summary']['SummaryCowBreedIncomePercentage']);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':I' . ($row))->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)
//                ->getNumberFormat()
//                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($startrowtb2 + 3) . ':I' . $row)->applyFromArray(
//                array(
//                    'borders' => array(
//                        'allborders' => array(
//                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
//                        )
//                    ),
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)->applyFromArray(
//                array(
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
        return $objPHPExcel;
    }

    private function generateTravelExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $data = TravelController::getQuarterDataList($condition, $region);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("2.4 โครงการท่องเที่ยว");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  2.4 ท่องเที่ยวเชิงเกษตร');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                       ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543) . '  มีผู้ท่องเที่ยวเชิงเกษตร จำนวน ' . number_format($data['Summary']['SummaryCurrentTravelAmount'], 2, '.', ',') . '  ราย มูลค่า ' . number_format($data['Summary']['SummaryCurrentTravelIncome'], 2, '.', ',') . '  บาท ');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา ปรากฏว่าจำนวนผู้ท่องเที่ยวฯ และมูลค่าการบริการคิดเป็นร้อยละ ' . number_format($data['Summary']['SummaryTravelAmountPercentage'], 2, '.', ','));
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                  และ ' . number_format($data['Summary']['SummaryTravelIncomePercentage'], 2, '.', ',') . ' ตามลำดับ');

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A9', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A9:A11');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B9:C9');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D9:E9');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('B10:C10');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('D10:E10');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F9:I10');
        $objPHPExcel->getActiveSheet()->setCellValue('B11', 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('C11', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D11', 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('E11', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F11', 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('G11', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H11', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I11', '% เพิ่ม,ลด');

        $row = 0;
        $summarydiffcow = 0;
        $summarydiffservice = 0;
        foreach ($data['DataList'] as $item) {
            $summarydiffcow += $item['DiffAmount'];
            $summarydiffservice += $item['DiffBaht'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), $item['RegionName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $item['DiffBahtPercentage']);
            $row++;
        }


        //summary
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $data['Summary']['SummaryCurrentTravelAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $data['Summary']['SummaryCurrentTravelIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $data['Summary']['SummaryBeforTravelAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $data['Summary']['SummaryBeforeTravelIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $data['Summary']['SummaryTravelAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $data['Summary']['SummaryTravelIncomePercentage']);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumm = $objPHPExcel->getActiveSheet()->getHighestColumn();
        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I11')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I11')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A9:I11")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);



        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B12:I' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A3:A8')->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        ///////tb 2
//        $tb2data = $data;
//        while ($condition['MonthFrom'] != 10) {
//            $condition['MonthFrom'] -= 1;
//            if ($condition['MonthFrom'] == 0) {
//                $condition['MonthFrom'] = 12;
//                $condition['MonthTo'] = 12;
//                $condition['YearTo'] -= 1;
//                $condition['YearFrom'] -= 1;
//                $newdata = TravelController::getQuarterDataList($condition, $region);
//
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentAmount'] += $itemnewdata['CurrentAmount'];
//                    $tb2data['DataList'][$key]['CurrentBaht'] += $itemnewdata['CurrentBaht'];
//                    $tb2data['DataList'][$key]['BeforeAmount'] += $itemnewdata['BeforeAmount'];
//                    $tb2data['DataList'][$key]['BeforeBaht'] += $itemnewdata['BeforeBaht'];
//                    $tb2data['DataList'][$key]['DiffAmount'] += $itemnewdata['DiffAmount'];
//                    $tb2data['DataList'][$key]['DiffAmountPercentage'] += $itemnewdata['DiffAmountPercentage'];
//                    $tb2data['DataList'][$key]['DiffBaht'] += $itemnewdata['DiffBaht'];
//                    $tb2data['DataList'][$key]['DiffBahtPercentage'] += $itemnewdata['DiffBahtPercentage'];
//                    $tb2data['Summary']['SummaryCurrentTravelAmount'] += $newdata['Summary']['SummaryCurrentTravelAmount'];
//                    $tb2data['Summary']['SummaryCurrentTravelIncome'] += $newdata['Summary']['SummaryCurrentTravelIncome'];
//                    $tb2data['Summary']['SummaryBeforTravelAmount'] += $newdata['Summary']['SummaryBeforTravelAmount'];
//                    $tb2data['Summary']['SummaryBeforeTravelIncome'] += $newdata['Summary']['SummaryBeforeTravelIncome'];
//                    $tb2data['Summary']['SummaryTravelAmountPercentage'] += $newdata['Summary']['SummaryTravelAmountPercentage'];
//                    $tb2data['Summary']['SummaryTravelIncomePercentage'] += $newdata['Summary']['SummaryTravelIncomePercentage'];
//                }
//            } else {
//                $condition['MonthTo'] -= 1;
//
//                $newdata = TravelController::getQuarterDataList($condition, $region);
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentAmount'] += $itemnewdata['CurrentAmount'];
//                    $tb2data['DataList'][$key]['CurrentBaht'] += $itemnewdata['CurrentBaht'];
//                    $tb2data['DataList'][$key]['BeforeAmount'] += $itemnewdata['BeforeAmount'];
//                    $tb2data['DataList'][$key]['BeforeBaht'] += $itemnewdata['BeforeBaht'];
//                    $tb2data['DataList'][$key]['DiffAmount'] += $itemnewdata['DiffAmount'];
//                    $tb2data['DataList'][$key]['DiffAmountPercentage'] += $itemnewdata['DiffAmountPercentage'];
//                    $tb2data['DataList'][$key]['DiffBaht'] += $itemnewdata['DiffBaht'];
//                    $tb2data['DataList'][$key]['DiffBahtPercentage'] += $itemnewdata['DiffBahtPercentage'];
//                    $tb2data['Summary']['SummaryCurrentTravelAmount'] += $newdata['Summary']['SummaryCurrentTravelAmount'];
//                    $tb2data['Summary']['SummaryCurrentTravelIncome'] += $newdata['Summary']['SummaryCurrentTravelIncome'];
//                    $tb2data['Summary']['SummaryBeforTravelAmount'] += $newdata['Summary']['SummaryBeforTravelAmount'];
//                    $tb2data['Summary']['SummaryBeforeTravelIncome'] += $newdata['Summary']['SummaryBeforeTravelIncome'];
//                    $tb2data['Summary']['SummaryTravelAmountPercentage'] += $newdata['Summary']['SummaryTravelAmountPercentage'];
//                    $tb2data['Summary']['SummaryTravelIncomePercentage'] += $newdata['Summary']['SummaryTravelIncomePercentage'];
//                }
//            }
//        }
//        $highestRow += 2;
//        $startrowtb2 = $highestRow;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . '  มีผู้ท่องเที่ยวเชิงเกษตร จำนวน ' . number_format($tb2data['Summary']['SummaryCurrentTravelAmount'], 2, '.', ',') . ' ราย รายได้ ' . number_format($tb2data['Summary']['SummaryCurrentTravelIncome'], 2, '.', ',') . '  บาท');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา  ปรากฏว่าจำนวนผู้ท่องเที่ยวฯ และมูลค่าการบริการคิดเป็นร้อยละ ' . number_format($tb2data['Summary']['SummaryTravelAmountPercentage'], 2, '.', ','));
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  และ ' . number_format($tb2data['Summary']['SummaryTravelIncomePercentage'], 2, '.', ',') . ' ตามลำดับ');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
//        $highestRow++;
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, 'รายการ');
//        $objPHPExcel->getActiveSheet()->mergeCells('A' . $highestRow . ':A' . ($highestRow + 1));
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543));
//        $objPHPExcel->getActiveSheet()->mergeCells('B' . $highestRow . ':C' . $highestRow);
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'ต.ค. ' . ($showm + 542) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 542));
//        $objPHPExcel->getActiveSheet()->mergeCells('D' . $highestRow . ':E' . $highestRow);
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'ผลต่าง');
//        $objPHPExcel->getActiveSheet()->mergeCells('F' . $highestRow . ':I' . $highestRow);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $highestRow++;
//
//
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'ราย');
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, 'บาท');
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'ราย');
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, 'บาท');
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'ราย');
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, '% เพิ่ม,ลด');
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, 'บาท');
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, '% เพิ่ม,ลด');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A' . $highestRow . ':I' . $highestRow)
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//
//        $highestRow++;
//        $row = $highestRow;
//
//        foreach ($tb2data['DataList'] as $item2) {
//            $summarydiffcow += $item2['DiffCowService'];
//            $summarydiffservice += $item2['DiffIncomeService'];
//            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $item2['RegionName']);
//            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $item2['CurrentAmount']);
//            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $item2['CurrentBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $item2['BeforeAmount']);
//            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $item2['BeforeBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $item2['DiffAmount']);
//            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $item2['DiffAmountPercentage']);
//            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $item2['DiffBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $item2['DiffBahtPercentage']);
//
//            $row++;
//        }
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รวมทั้งสิ้น');
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tb2data['Summary']['SummaryCurrentTravelAmount']);
//        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $tb2data['Summary']['SummaryCurrentTravelIncome']);
//        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $tb2data['Summary']['SummaryBeforTravelAmount']);
//        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $tb2data['Summary']['SummaryBeforeTravelIncome']);
//        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $summarydiffcow);
//        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $tb2data['Summary']['SummaryTravelAmountPercentage']);
//        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $summarydiffservice);
//        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $tb2data['Summary']['SummaryTravelIncomePercentage']);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':I' . ($row))->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)
//                ->getNumberFormat()
//                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($startrowtb2 + 3) . ':I' . $row)->applyFromArray(
//                array(
//                    'borders' => array(
//                        'allborders' => array(
//                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
//                        )
//                    ),
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
//        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)->applyFromArray(
//                array(
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
        return $objPHPExcel;
    }

    private function generateSpermSaleExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $data = SpermSaleController::getQuarterDataListreport($condition, $region);
        $objPHPExcel->getActiveSheet()->setTitle("2.5 ปัจจัยการผลิต");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '   2.5 ปัจจัยการผลิต');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '            ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543) . ' อ.ส.ค.มีการดำเนินงาน ดังนี้ ');
//        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                         ผลิตน้ำเชื้อแช่แข็ง จำนวน  12,410  หลอด มูลค่า  414,200 บาท เมื่อเปรียบเทียบกับเดือนเดียวกัน');
//        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                    ของปีที่ผ่านมา ปรากฏว่าทั้งปริมาณและมูลค่าเพิ่มขึ้นคิดเป็นร้อยละ  144.77 และ  104.24 ตามลำดับ');
//        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                         การจำหน่ายน้ำเชื้อแช่แข็ง จำนวน  4,767  หลอด มูลค่า  392,540  บาท เมื่อเปรียบเทียบกับ');
//        $objPHPExcel->getActiveSheet()->setCellValue('A9', '                   เดือนเดียวกันของปีที่ผ่านมา ปรากฏว่าปริมาณและมูลค่าลดลงคิดเป็นร้อยละ  48.28  และ  38.49  ตามลำดับ');
//        $objPHPExcel->getActiveSheet()->setCellValue('A10', '                        การจำหน่ายไนโตรเจนเหลว ปริมาณ  3,390  กิโลกรัม มูลค่า  84,750  บาท เมื่อเปรียบเทียบกับ');
//        $objPHPExcel->getActiveSheet()->setCellValue('A11', '                    เดือนเดียวกันของปีที่ผ่านมา ปรากฏว่าทั้งปริมาณและมูลค่าลดลงคิดเป็นร้อยละ  17.14');
//        $objPHPExcel->getActiveSheet()->setCellValue('A12', '                        การจำหน่ายวัสดุผสมเทียมและอื่น ๆ มูลค่า  5,397  บาท เมื่อเปรียบเทียบกับเดือนเดียวกัน');
//        $objPHPExcel->getActiveSheet()->setCellValue('A13', '                   ของปีที่ผ่านมา ปรากฏว่ามูลค่าลดลงคิดเป็นร้อยละ  13.99');
//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A14', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A14:A16');
        $objPHPExcel->getActiveSheet()->setCellValue('B14', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B14:C14');
        $objPHPExcel->getActiveSheet()->setCellValue('D14', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D14:E14');
        $objPHPExcel->getActiveSheet()->setCellValue('B15', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('B15:C15');
        $objPHPExcel->getActiveSheet()->setCellValue('D15', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('D15:E15');
        $objPHPExcel->getActiveSheet()->setCellValue('F14', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F14:I15');
        $objPHPExcel->getActiveSheet()->setCellValue('B16', 'ปริมาณ');
        $objPHPExcel->getActiveSheet()->setCellValue('C16', 'มูลค่า');
        $objPHPExcel->getActiveSheet()->setCellValue('D16', 'ปริมาณ');
        $objPHPExcel->getActiveSheet()->setCellValue('E16', 'มูลค่า');
        $objPHPExcel->getActiveSheet()->setCellValue('F16', 'ปริมาณ');
        $objPHPExcel->getActiveSheet()->setCellValue('G16', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H16', 'มูลค่า');
        $objPHPExcel->getActiveSheet()->setCellValue('I16', '% เพิ่ม,ลด');

        $row = 0;
        $rowhead = 0;
        $summarydiffcow = 0;
        $summarydiffservice = 0;


        foreach ($data['DataList'] as $item) {
            $summarydiffcow += $item['DiffAmount'];
            $summarydiffservice += $item['DiffBaht'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $rowhead), '                         ' . $item['RegionName'] . ' จำนวน  ' . number_format($item['CurrentAmount'], 2, '.', ',') . '  หลอด มูลค่า  ' . number_format($item['CurrentBaht'], 2, '.', ',') . ' บาท เมื่อเปรียบเทียบกับเดือนเดียวกัน');
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $rowhead), '                    ของปีที่ผ่านมา ปรากฏว่าทั้งปริมาณและมูลค่าคิดเป็นร้อยละ  ' . number_format($item['DiffAmountPercentage'], 2, '.', ',') . ' และ  ' . number_format($item['DiffBahtPercentage'], 2, '.', ',') . ' ตามลำดับ');
            $rowhead += 2;

            $objPHPExcel->getActiveSheet()->setCellValue('A' . (17 + $row), ' - ' . $item['RegionName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (17 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (17 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (17 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (17 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (17 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (17 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (17 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (17 + $row), $item['DiffBahtPercentage']);
            $row++;
        }
        $row--;
        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A13')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A14:I16')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A14:I16')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A14:I16")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getStyle('A17:I' . (17 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A14:I' . (17 + $row))->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . (17 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateSpermSale2Excel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(8);
        $objPHPExcel->setActiveSheetIndex(8);
        $objPHPExcel->getActiveSheet()->setTitle("2.5 ปัจจัยการผลิต (2)");
//        $data = SpermSaleController::getQuarterDataListreport($condition, $region);
//        $showm = 0;
//        $showy = $condition['YearFrom'];
//        $start = $condition['MonthTo'];
//        if ($condition['MonthTo'] < 10) {
//            $showm = $condition['YearFrom'] - 1;
//        } else {
//            $showm = $condition['YearFrom'];
//        }
//        $objPHPExcel->getActiveSheet()->setTitle("2.5 ปัจจัยการผลิต");
//        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
//        $objPHPExcel->getActiveSheet()->setCellValue('A4', '   2.5 ปัจจัยการผลิต');
//        $objPHPExcel->getActiveSheet()->setCellValue('A5', '            เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' อ.ส.ค.มีการดำเนินงาน ดังนี้ ');
////        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                         ผลิตน้ำเชื้อแช่แข็ง จำนวน  12,410  หลอด มูลค่า  414,200 บาท เมื่อเปรียบเทียบกับเดือนเดียวกัน');
////        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                    ของปีที่ผ่านมา ปรากฏว่าทั้งปริมาณและมูลค่าเพิ่มขึ้นคิดเป็นร้อยละ  144.77 และ  104.24 ตามลำดับ');
////        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                         การจำหน่ายน้ำเชื้อแช่แข็ง จำนวน  4,767  หลอด มูลค่า  392,540  บาท เมื่อเปรียบเทียบกับ');
////        $objPHPExcel->getActiveSheet()->setCellValue('A9', '                   เดือนเดียวกันของปีที่ผ่านมา ปรากฏว่าปริมาณและมูลค่าลดลงคิดเป็นร้อยละ  48.28  และ  38.49  ตามลำดับ');
////        $objPHPExcel->getActiveSheet()->setCellValue('A10', '                        การจำหน่ายไนโตรเจนเหลว ปริมาณ  3,390  กิโลกรัม มูลค่า  84,750  บาท เมื่อเปรียบเทียบกับ');
////        $objPHPExcel->getActiveSheet()->setCellValue('A11', '                    เดือนเดียวกันของปีที่ผ่านมา ปรากฏว่าทั้งปริมาณและมูลค่าลดลงคิดเป็นร้อยละ  17.14');
////        $objPHPExcel->getActiveSheet()->setCellValue('A12', '                        การจำหน่ายวัสดุผสมเทียมและอื่น ๆ มูลค่า  5,397  บาท เมื่อเปรียบเทียบกับเดือนเดียวกัน');
////        $objPHPExcel->getActiveSheet()->setCellValue('A13', '                   ของปีที่ผ่านมา ปรากฏว่ามูลค่าลดลงคิดเป็นร้อยละ  13.99');
////tb header
//        $objPHPExcel->getActiveSheet()->setCellValue('A15', 'รายการ');
//        $objPHPExcel->getActiveSheet()->mergeCells('A15:A16');
//        $objPHPExcel->getActiveSheet()->setCellValue('B15', 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543));
//        $objPHPExcel->getActiveSheet()->mergeCells('B15:C15');
//        $objPHPExcel->getActiveSheet()->setCellValue('D15', 'ต.ค. ' . ($showm + 542) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 542));
//        $objPHPExcel->getActiveSheet()->mergeCells('D15:E15');
//        $objPHPExcel->getActiveSheet()->setCellValue('F15', 'ผลต่าง');
//        $objPHPExcel->getActiveSheet()->mergeCells('F15:I15');
//        $objPHPExcel->getActiveSheet()->setCellValue('B16', 'ปริมาณ');
//        $objPHPExcel->getActiveSheet()->setCellValue('C16', 'มูลค่า');
//        $objPHPExcel->getActiveSheet()->setCellValue('D16', 'ปริมาณ');
//        $objPHPExcel->getActiveSheet()->setCellValue('E16', 'มูลค่า');
//        $objPHPExcel->getActiveSheet()->setCellValue('F16', 'ปริมาณ');
//        $objPHPExcel->getActiveSheet()->setCellValue('G16', '% เพิ่ม,ลด');
//        $objPHPExcel->getActiveSheet()->setCellValue('H16', 'มูลค่า');
//        $objPHPExcel->getActiveSheet()->setCellValue('I16', '% เพิ่ม,ลด');
//        // header style
//        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
//        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
//        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A5:A13')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A15:I16')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A15:I16')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle("A15:I16")
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//
//        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
//
//        $row = 0;
//
//        ///////tb 2
//        $tb2data = $data;
//        while ($condition['MonthFrom'] != 10) {
//            $condition['MonthFrom'] -= 1;
//            if ($condition['MonthFrom'] == 0) {
//                $condition['MonthFrom'] = 12;
//                $condition['MonthTo'] = 12;
//                $condition['YearTo'] -= 1;
//                $condition['YearFrom'] -= 1;
//                $newdata = SpermSaleController::getQuarterDataListreport($condition, $region);
//
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentAmount'] += $itemnewdata['CurrentAmount'];
//                    $tb2data['DataList'][$key]['CurrentBaht'] += $itemnewdata['CurrentBaht'];
//                    $tb2data['DataList'][$key]['BeforeAmount'] += $itemnewdata['BeforeAmount'];
//                    $tb2data['DataList'][$key]['BeforeBaht'] += $itemnewdata['BeforeBaht'];
//                    $tb2data['DataList'][$key]['DiffAmount'] += $itemnewdata['DiffAmount'];
//                    $tb2data['DataList'][$key]['DiffAmountPercentage'] += $itemnewdata['DiffAmountPercentage'];
//                    $tb2data['DataList'][$key]['DiffBaht'] += $itemnewdata['DiffBaht'];
//                    $tb2data['DataList'][$key]['DiffBahtPercentage'] += $itemnewdata['DiffBahtPercentage'];
//                }
//            } else {
//                $condition['MonthTo'] -= 1;
//
//                $newdata = SpermSaleController::getQuarterDataListreport($condition, $region);
//                foreach ($newdata['DataList'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][$key]['CurrentAmount'] += $itemnewdata['CurrentAmount'];
//                    $tb2data['DataList'][$key]['CurrentBaht'] += $itemnewdata['CurrentBaht'];
//                    $tb2data['DataList'][$key]['BeforeAmount'] += $itemnewdata['BeforeAmount'];
//                    $tb2data['DataList'][$key]['BeforeBaht'] += $itemnewdata['BeforeBaht'];
//                    $tb2data['DataList'][$key]['DiffAmount'] += $itemnewdata['DiffAmount'];
//                    $tb2data['DataList'][$key]['DiffAmountPercentage'] += $itemnewdata['DiffAmountPercentage'];
//                    $tb2data['DataList'][$key]['DiffBaht'] += $itemnewdata['DiffBaht'];
//                    $tb2data['DataList'][$key]['DiffBahtPercentage'] += $itemnewdata['DiffBahtPercentage'];
//                }
//            }
//        }
//        $rowhead = 0;
//        foreach ($tb2data['DataList'] as $item2) {
//            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $rowhead), '                         ' . $item2['RegionName'] . ' จำนวน  ' . number_format($item2['CurrentAmount'], 2, '.', ',') . '  หลอด มูลค่า  ' . number_format($item2['CurrentBaht'], 2, '.', ',') . ' บาท เมื่อเปรียบเทียบกับเดือนเดียวกัน');
//            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $rowhead), '                    ของปีที่ผ่านมา ปรากฏว่าทั้งปริมาณและมูลค่าคิดเป็นร้อยละ  ' . number_format($item2['DiffAmountPercentage'], 2, '.', ',') . ' และ  ' . number_format($item2['DiffBahtPercentage'], 2, '.', ',') . ' ตามลำดับ');
//            $rowhead += 2;
//            $objPHPExcel->getActiveSheet()->setCellValue('A' . (17 + $row), ' - ' . $item2['RegionName']);
//            $objPHPExcel->getActiveSheet()->setCellValue('B' . (17 + $row), $item2['CurrentAmount']);
//            $objPHPExcel->getActiveSheet()->setCellValue('C' . (17 + $row), $item2['CurrentBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . (17 + $row), $item2['BeforeAmount']);
//            $objPHPExcel->getActiveSheet()->setCellValue('E' . (17 + $row), $item2['BeforeBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('F' . (17 + $row), $item2['DiffAmount']);
//            $objPHPExcel->getActiveSheet()->setCellValue('G' . (17 + $row), $item2['DiffAmountPercentage']);
//            $objPHPExcel->getActiveSheet()->setCellValue('H' . (17 + $row), $item2['DiffBaht']);
//            $objPHPExcel->getActiveSheet()->setCellValue('I' . (17 + $row), $item2['DiffBahtPercentage']);
//
//            $row++;
//        }
//        $row--;
//        $objPHPExcel->getActiveSheet()->getStyle('A17:I' . (17 + $row))
//                ->getNumberFormat()
//                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
//        $objPHPExcel->getActiveSheet()->getStyle('A15:I' . (17 + $row))->applyFromArray(
//                array(
//                    'borders' => array(
//                        'allborders' => array(
//                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
//                        )
//                    ),
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
//        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . (17 + $row))->applyFromArray(
//                array(
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
        return $objPHPExcel;
    }

    private function generateCooperativeMilkExcel($objPHPExcel, $condition) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $region = [0 => ['RegionID' => 1, 'RegionName' => 'อ.ส.ค. สำนักงานใหญ่ มวกเหล็ก'], 1 => ['RegionID' => 2, 'RegionName' => 'อ.ส.ค. สำนักงานกรุงเทพฯ Office'],
            2 => ['RegionID' => 3, 'RegionName' => 'อ.ส.ค. สำนักงานภาคกลาง'], 3 => ['RegionID' => 4, 'RegionName' => 'อ.ส.ค. ภาคใต้ (ประจวบคีรีขันธ์)'],
            4 => ['RegionID' => 5, 'RegionName' => 'อ.ส.ค. ภาคตะวันออกเฉียงเหนือ (ขอนแก่น)'], 5 => ['RegionID' => 6, 'RegionName' => 'อ.ส.ค. ภาคเหนือตอนล่าง (สุโขทัย)'],
            6 => ['RegionID' => 7, 'RegionName' => 'อ.ส.ค. ภาคเหนือตอนบน (เชียงใหม่)']];

        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก ");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '3. การดำเนินงานด้านกิจการโคนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  3.1 จำนวนเกษตรกร/สมาชิกผู้เลี้ยงโคนม, จำนวนโค และปริมาณน้ำนม');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                       ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' )' . ' มีจำนวนเกษตรกร/สมาชิก, จำนวนโค และการรับซื้อน้ำนม มีรายละเอียด ดังนี้ ');


//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A9');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'เกษตรกร/สมาชิก');
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', '(ราย)');
        $objPHPExcel->getActiveSheet()->mergeCells('B8:C8');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('C9', 'ส่งนม');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'โค (ตัว)');
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E8');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('E9', 'ส่งนม');

        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:F8');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('G7', 'มูลค่า (บาท)');
        $objPHPExcel->getActiveSheet()->mergeCells('G7:G8');
        $objPHPExcel->getActiveSheet()->setCellValue('G9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('H7', 'ราคาเฉลี่ย ');
        $objPHPExcel->getActiveSheet()->mergeCells('H7:H8');
        $objPHPExcel->getActiveSheet()->setCellValue('H9', '(บาท/กก.)');
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:H9')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        // // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A13')->getFont()->setSize(16);

        $row = 0;
        $tsumTotalPerson = 0;
        $tsumTotalPersonSent = 0;
        $tsumTotalCow = 0;
        $tsumTotalCowSent = 0;
        $tsumTotalMilkAmount = 0;
        $tsumTotalValues = 0;
        $tsumAverageValues = 0;
        foreach ($region as $reg) {
            $sumTotalPerson = 0;
            $sumTotalPersonSent = 0;
            $sumTotalCow = 0;
            $sumTotalCowSent = 0;
            $sumTotalMilkAmount = 0;
            $sumTotalValues = 0;
            $sumAverageValues = 0;
            $line = 0;
            $data = CooperativeMilkController::getQuarterData($condition, $reg);
            if ($data['DataList'][0]['RegionName'] != '') {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $data['DataList'][0]['RegionName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getFont()->setBold(true);
                $line = 10 + $row;
                $row++;
            }

            foreach ($data['DataList'] as $item) {
                $sumTotalPerson += $item['TotalPerson'];
                $sumTotalPersonSent += $item['TotalPersonSent'];
                $sumTotalCow += $item['TotalCow'];
                $sumTotalCowSent += $item['TotalCowSent'];
                $sumTotalMilkAmount += $item['TotalMilkAmount'];
                $sumTotalValues += $item['TotalValues'];
                $sumAverageValues += $item['AverageValues'];
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $item['CooperativeName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $item['TotalPerson']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $item['TotalPersonSent']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $item['TotalCow']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $item['TotalCowSent']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $item['TotalMilkAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $item['TotalValues']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $item['AverageValues']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setSize(14);
                $row++;
            }
            $tsumTotalPerson += $sumTotalPerson;
            $tsumTotalPersonSent += $sumTotalPersonSent;
            $tsumTotalCow += $sumTotalCow;
            $tsumTotalCowSent += $sumTotalCowSent;
            $tsumTotalMilkAmount += $sumTotalMilkAmount;
            $tsumTotalValues += $sumTotalValues;
            $tsumAverageValues += $sumAverageValues;

            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($line), $sumTotalPerson);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($line), $sumTotalPersonSent);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($line), $sumTotalCow);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($line), $sumTotalCowSent);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($line), $sumTotalMilkAmount);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($line), $sumTotalValues);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($line), $sumAverageValues);
            $objPHPExcel->getActiveSheet()->getStyle('B' . ($line) . ':H' . ($line))->getFont()->setBold(true);
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $tsumTotalPerson);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $tsumTotalPersonSent);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $tsumTotalCow);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $tsumTotalCowSent);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $tsumTotalMilkAmount);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $tsumTotalValues);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $tsumAverageValues);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H' . (10 + $row))->applyFromArray(
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
        $row += 2;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), 'หมายเหตุ');
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), '           1. จำนวนสมาชิกและจำนวนโค เป็นข้อมูลจากเกษตรกรที่เป็นสมาชิกศูนย์ส่งเสริมการเลี้ยงโคนมของ อ.ส.ค., สหกรณ์โคนม');
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), '             ไทย - เดนมาร์ค จำกัด, สหกรณ์โคนมอื่น ๆ และฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม');
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), '           2. น้ำนมเป็นการรับซื้อน้ำนมจากสมาชิกศูนย์ส่งเสริมการเลี้ยงโคนมของ อ.ส.ค., สหกรณ์โคนมไทย - เดนมาร์ค จำกัด,');
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), '             ฟาร์มสาธิต และรับซื้อหน้าโรงงานจากหน่วยงานภายนอกที่ไม่ได้เป็นสมาชิกของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':A' . (10 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':A' . (10 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                        'size' => 14
                    )
                )
        );
        $objPHPExcel = $this->generateCooperativeMilk2Excel($objPHPExcel, $condition, $region[2]);
        $objPHPExcel = $this->generateCooperativeMilk3Excel($objPHPExcel, $condition, $region[0]);
        $objPHPExcel = $this->generateCooperativeMilk4Excel($objPHPExcel, $condition, $region[3]);
        $objPHPExcel = $this->generateCooperativeMilk5Excel($objPHPExcel, $condition, $region[4]);
        $objPHPExcel = $this->generateCooperativeMilk6Excel($objPHPExcel, $condition, $region[5]);
        $objPHPExcel = $this->generateCooperativeMilk7Excel($objPHPExcel, $condition, $region[6]);
        return $objPHPExcel;
    }

    private function generateCooperativeMilk2Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (2)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '3. การดำเนินงานด้านกิจการโคนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  3.1 จำนวนเกษตรกร/สมาชิกผู้เลี้ยงโคนม, จำนวนโค และปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '      3.1.1 ภาคกลาง');
//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A9');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'เกษตรกร/สมาชิก');
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', '(ราย)');
        $objPHPExcel->getActiveSheet()->mergeCells('B8:C8');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('C9', 'ส่งนม');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'โค (ตัว)');
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E8');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('E9', 'ส่งนม');

        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:F8');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('G7', 'มูลค่า (บาท)');
        $objPHPExcel->getActiveSheet()->mergeCells('G7:G8');
        $objPHPExcel->getActiveSheet()->setCellValue('G9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('H7', 'ราคาเฉลี่ย ');
        $objPHPExcel->getActiveSheet()->mergeCells('H7:H8');
        $objPHPExcel->getActiveSheet()->setCellValue('H9', '(บาท/กก.)');
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:H9')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        // // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A13')->getFont()->setSize(16);

        $row = 0;
        $sumTotalPerson = 0;
        $sumTotalPersonSent = 0;
        $sumTotalCow = 0;
        $sumTotalCowSent = 0;
        $sumTotalMilkAmount = 0;
        $sumTotalValues = 0;
        $sumAverageValues = 0;
        $data = CooperativeMilkController::getQuarterData($condition, $region);
        if ($data['DataList'][0]['RegionName'] != '') {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $data['DataList'][0]['RegionName']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getFont()->setBold(true);
            $row++;
        }

        foreach ($data['DataList'] as $item) {
            $sumTotalPerson += $item['TotalPerson'];
            $sumTotalPersonSent += $item['TotalPersonSent'];
            $sumTotalCow += $item['TotalCow'];
            $sumTotalCowSent += $item['TotalCowSent'];
            $sumTotalMilkAmount += $item['TotalMilkAmount'];
            $sumTotalValues += $item['TotalValues'];
            $sumAverageValues += $item['AverageValues'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $item['CooperativeName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $item['TotalPerson']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $item['TotalPersonSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $item['TotalCow']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $item['TotalCowSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $item['TotalMilkAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $item['TotalValues']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $item['AverageValues']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setSize(14);
            $row++;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), 'รวม (ภาคกลาง)');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $sumTotalPerson);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $sumTotalPersonSent);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $sumTotalCow);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $sumTotalCowSent);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $sumTotalMilkAmount);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $sumTotalValues);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $sumAverageValues);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H' . (10 + $row))->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                        'size' => 14
                    )
                )
        );


        return $objPHPExcel;
    }

    private function generateCooperativeMilk3Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (3)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '3. การดำเนินงานด้านกิจการโคนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  3.1 จำนวนเกษตรกร/สมาชิกผู้เลี้ยงโคนม, จำนวนโค และปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '      3.1.1 ภาคกลาง (ต่อ)');
        //tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A9');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'เกษตรกร/สมาชิก');
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', '(ราย)');
        $objPHPExcel->getActiveSheet()->mergeCells('B8:C8');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('C9', 'ส่งนม');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'โค (ตัว)');
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E8');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('E9', 'ส่งนม');

        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:F8');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('G7', 'มูลค่า (บาท)');
        $objPHPExcel->getActiveSheet()->mergeCells('G7:G8');
        $objPHPExcel->getActiveSheet()->setCellValue('G9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('H7', 'ราคาเฉลี่ย ');
        $objPHPExcel->getActiveSheet()->mergeCells('H7:H8');
        $objPHPExcel->getActiveSheet()->setCellValue('H9', '(บาท/กก.)');
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:H9')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        // // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A13')->getFont()->setSize(16);

        $row = 0;
        $sumTotalPerson = 0;
        $sumTotalPersonSent = 0;
        $sumTotalCow = 0;
        $sumTotalCowSent = 0;
        $sumTotalMilkAmount = 0;
        $sumTotalValues = 0;
        $sumAverageValues = 0;
        $data = CooperativeMilkController::getQuarterData($condition, $region);
        if ($data['DataList'][0]['RegionName'] != '') {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $data['DataList'][0]['RegionName']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getFont()->setBold(true);
            $row++;
        }

        foreach ($data['DataList'] as $item) {
            $sumTotalPerson += $item['TotalPerson'];
            $sumTotalPersonSent += $item['TotalPersonSent'];
            $sumTotalCow += $item['TotalCow'];
            $sumTotalCowSent += $item['TotalCowSent'];
            $sumTotalMilkAmount += $item['TotalMilkAmount'];
            $sumTotalValues += $item['TotalValues'];
            $sumAverageValues += $item['AverageValues'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $item['CooperativeName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $item['TotalPerson']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $item['TotalPersonSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $item['TotalCow']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $item['TotalCowSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $item['TotalMilkAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $item['TotalValues']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $item['AverageValues']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setSize(14);
            $row++;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), 'รวม(ภาคกลาง)');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $sumTotalPerson);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $sumTotalPersonSent);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $sumTotalCow);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $sumTotalCowSent);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $sumTotalMilkAmount);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $sumTotalValues);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $sumAverageValues);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H' . (10 + $row))->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                        'size' => 14
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateCooperativeMilk4Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (4)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '3. การดำเนินงานด้านกิจการโคนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  3.1 จำนวนเกษตรกร/สมาชิกผู้เลี้ยงโคนม, จำนวนโค และปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '      3.1.2 ภาคใต้');
        //tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A9');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'เกษตรกร/สมาชิก');
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', '(ราย)');
        $objPHPExcel->getActiveSheet()->mergeCells('B8:C8');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('C9', 'ส่งนม');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'โค (ตัว)');
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E8');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('E9', 'ส่งนม');

        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:F8');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('G7', 'มูลค่า (บาท)');
        $objPHPExcel->getActiveSheet()->mergeCells('G7:G8');
        $objPHPExcel->getActiveSheet()->setCellValue('G9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('H7', 'ราคาเฉลี่ย ');
        $objPHPExcel->getActiveSheet()->mergeCells('H7:H8');
        $objPHPExcel->getActiveSheet()->setCellValue('H9', '(บาท/กก.)');
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:H9')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        // // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A13')->getFont()->setSize(16);

        $row = 0;
        $sumTotalPerson = 0;
        $sumTotalPersonSent = 0;
        $sumTotalCow = 0;
        $sumTotalCowSent = 0;
        $sumTotalMilkAmount = 0;
        $sumTotalValues = 0;
        $sumAverageValues = 0;
        $data = CooperativeMilkController::getQuarterData($condition, $region);
        if ($data['DataList'][0]['RegionName'] != '') {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $data['DataList'][0]['RegionName']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getFont()->setBold(true);
            $row++;
        }

        foreach ($data['DataList'] as $item) {
            $sumTotalPerson += $item['TotalPerson'];
            $sumTotalPersonSent += $item['TotalPersonSent'];
            $sumTotalCow += $item['TotalCow'];
            $sumTotalCowSent += $item['TotalCowSent'];
            $sumTotalMilkAmount += $item['TotalMilkAmount'];
            $sumTotalValues += $item['TotalValues'];
            $sumAverageValues += $item['AverageValues'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $item['CooperativeName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $item['TotalPerson']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $item['TotalPersonSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $item['TotalCow']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $item['TotalCowSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $item['TotalMilkAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $item['TotalValues']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $item['AverageValues']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setSize(14);
            $row++;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), 'รวม(ภาคใต้)');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $sumTotalPerson);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $sumTotalPersonSent);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $sumTotalCow);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $sumTotalCowSent);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $sumTotalMilkAmount);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $sumTotalValues);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $sumAverageValues);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H' . (10 + $row))->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                        'size' => 14
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateCooperativeMilk5Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (5)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '3. การดำเนินงานด้านกิจการโคนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  3.1 จำนวนเกษตรกร/สมาชิกผู้เลี้ยงโคนม, จำนวนโค และปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '      3.1.3 ภาคตะวันออกเฉียงเหนือ');
        //tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A9');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'เกษตรกร/สมาชิก');
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', '(ราย)');
        $objPHPExcel->getActiveSheet()->mergeCells('B8:C8');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('C9', 'ส่งนม');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'โค (ตัว)');
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E8');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('E9', 'ส่งนม');

        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:F8');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('G7', 'มูลค่า (บาท)');
        $objPHPExcel->getActiveSheet()->mergeCells('G7:G8');
        $objPHPExcel->getActiveSheet()->setCellValue('G9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('H7', 'ราคาเฉลี่ย ');
        $objPHPExcel->getActiveSheet()->mergeCells('H7:H8');
        $objPHPExcel->getActiveSheet()->setCellValue('H9', '(บาท/กก.)');
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:H9')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        // // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A13')->getFont()->setSize(16);

        $row = 0;
        $sumTotalPerson = 0;
        $sumTotalPersonSent = 0;
        $sumTotalCow = 0;
        $sumTotalCowSent = 0;
        $sumTotalMilkAmount = 0;
        $sumTotalValues = 0;
        $sumAverageValues = 0;
        $data = CooperativeMilkController::getQuarterData($condition, $region);
        if ($data['DataList'][0]['RegionName'] != '') {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $data['DataList'][0]['RegionName']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getFont()->setBold(true);
            $row++;
        }

        foreach ($data['DataList'] as $item) {
            $sumTotalPerson += $item['TotalPerson'];
            $sumTotalPersonSent += $item['TotalPersonSent'];
            $sumTotalCow += $item['TotalCow'];
            $sumTotalCowSent += $item['TotalCowSent'];
            $sumTotalMilkAmount += $item['TotalMilkAmount'];
            $sumTotalValues += $item['TotalValues'];
            $sumAverageValues += $item['AverageValues'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $item['CooperativeName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $item['TotalPerson']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $item['TotalPersonSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $item['TotalCow']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $item['TotalCowSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $item['TotalMilkAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $item['TotalValues']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $item['AverageValues']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setSize(14);
            $row++;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), 'รวม(ภาคตะวันออกเฉียงเหนือ)');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $sumTotalPerson);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $sumTotalPersonSent);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $sumTotalCow);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $sumTotalCowSent);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $sumTotalMilkAmount);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $sumTotalValues);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $sumAverageValues);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H' . (10 + $row))->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                        'size' => 14
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateCooperativeMilk6Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (6)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '3. การดำเนินงานด้านกิจการโคนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  3.1 จำนวนเกษตรกร/สมาชิกผู้เลี้ยงโคนม, จำนวนโค และปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '      3.1.4 ภาคเหนือตอนล่าง');
        //tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A9');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'เกษตรกร/สมาชิก');
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', '(ราย)');
        $objPHPExcel->getActiveSheet()->mergeCells('B8:C8');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('C9', 'ส่งนม');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'โค (ตัว)');
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E8');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('E9', 'ส่งนม');

        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:F8');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('G7', 'มูลค่า (บาท)');
        $objPHPExcel->getActiveSheet()->mergeCells('G7:G8');
        $objPHPExcel->getActiveSheet()->setCellValue('G9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('H7', 'ราคาเฉลี่ย ');
        $objPHPExcel->getActiveSheet()->mergeCells('H7:H8');
        $objPHPExcel->getActiveSheet()->setCellValue('H9', '(บาท/กก.)');
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:H9')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        // // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A13')->getFont()->setSize(16);

        $row = 0;
        $sumTotalPerson = 0;
        $sumTotalPersonSent = 0;
        $sumTotalCow = 0;
        $sumTotalCowSent = 0;
        $sumTotalMilkAmount = 0;
        $sumTotalValues = 0;
        $sumAverageValues = 0;
        $data = CooperativeMilkController::getQuarterData($condition, $region);
        if ($data['DataList'][0]['RegionName'] != '') {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $data['DataList'][0]['RegionName']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getFont()->setBold(true);
            $row++;
        }

        foreach ($data['DataList'] as $item) {
            $sumTotalPerson += $item['TotalPerson'];
            $sumTotalPersonSent += $item['TotalPersonSent'];
            $sumTotalCow += $item['TotalCow'];
            $sumTotalCowSent += $item['TotalCowSent'];
            $sumTotalMilkAmount += $item['TotalMilkAmount'];
            $sumTotalValues += $item['TotalValues'];
            $sumAverageValues += $item['AverageValues'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $item['CooperativeName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $item['TotalPerson']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $item['TotalPersonSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $item['TotalCow']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $item['TotalCowSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $item['TotalMilkAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $item['TotalValues']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $item['AverageValues']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setSize(14);
            $row++;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), 'รวม(ภาคเหนือตอนล่าง)');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $sumTotalPerson);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $sumTotalPersonSent);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $sumTotalCow);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $sumTotalCowSent);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $sumTotalMilkAmount);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $sumTotalValues);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $sumAverageValues);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H' . (10 + $row))->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                        'size' => 14
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateCooperativeMilk7Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (7)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '3. การดำเนินงานด้านกิจการโคนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  3.1 จำนวนเกษตรกร/สมาชิกผู้เลี้ยงโคนม, จำนวนโค และปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '      3.1.5 ภาคเหนือตอนบน');
        //tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A9');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'เกษตรกร/สมาชิก');
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', '(ราย)');
        $objPHPExcel->getActiveSheet()->mergeCells('B8:C8');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('C9', 'ส่งนม');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'โค (ตัว)');
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E8');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', 'ทั้งหมด');
        $objPHPExcel->getActiveSheet()->setCellValue('E9', 'ส่งนม');

        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ปริมาณน้ำนม');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:F8');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('G7', 'มูลค่า (บาท)');
        $objPHPExcel->getActiveSheet()->mergeCells('G7:G8');
        $objPHPExcel->getActiveSheet()->setCellValue('G9', '(กิโลกรัม)');
        $objPHPExcel->getActiveSheet()->setCellValue('H7', 'ราคาเฉลี่ย ');
        $objPHPExcel->getActiveSheet()->mergeCells('H7:H8');
        $objPHPExcel->getActiveSheet()->setCellValue('H9', '(บาท/กก.)');
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H9')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:H9')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        // // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A13')->getFont()->setSize(16);

        $row = 0;
        $sumTotalPerson = 0;
        $sumTotalPersonSent = 0;
        $sumTotalCow = 0;
        $sumTotalCowSent = 0;
        $sumTotalMilkAmount = 0;
        $sumTotalValues = 0;
        $sumAverageValues = 0;
        $data = CooperativeMilkController::getQuarterData($condition, $region);
        if ($data['DataList'][0]['RegionName'] != '') {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $data['DataList'][0]['RegionName']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getFont()->setBold(true);
            $row++;
        }

        foreach ($data['DataList'] as $item) {
            $sumTotalPerson += $item['TotalPerson'];
            $sumTotalPersonSent += $item['TotalPersonSent'];
            $sumTotalCow += $item['TotalCow'];
            $sumTotalCowSent += $item['TotalCowSent'];
            $sumTotalMilkAmount += $item['TotalMilkAmount'];
            $sumTotalValues += $item['TotalValues'];
            $sumAverageValues += $item['AverageValues'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), $item['CooperativeName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $item['TotalPerson']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $item['TotalPersonSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $item['TotalCow']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $item['TotalCowSent']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $item['TotalMilkAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $item['TotalValues']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $item['AverageValues']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setSize(14);
            $row++;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $row), 'รวม(ภาคเหนือตอนบน)');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $row), $sumTotalPerson);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $row), $sumTotalPersonSent);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $row), $sumTotalCow);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $row), $sumTotalCowSent);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $row), $sumTotalMilkAmount);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $row), $sumTotalValues);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $row), $sumAverageValues);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (10 + $row) . ':H' . (10 + $row))->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H' . (10 + $row))->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A10:H' . (10 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                        'size' => 14
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateCowgroupExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $data = CowGroupController::getDataListquar($condition, $region);
        $objPHPExcel->getActiveSheet()->setTitle("3.2 โค");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '3. การดำเนินงานด้านกิจการโคนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', ' 3.2 ฝูงโค อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', ' ฝูงโค อ.ส.ค. ณ 31 ' . ' ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543) . ' จำนวน ' . $data['Summary']['SummaryCurrentCowService'] . ' ตัว เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                       ปรากฏว่าเพิ่มขึ้น จำนวน 12 ตัว หรือเพิ่มขึ้นคิดเป็นร้อยละ 2.68  และในระหว่างเดือนมีการจำหน่ายโค');
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                   จำนวน  20 ตัว มูลค่า 135,625  บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                  ในเดือนนี้มีการผลิตน้ำนมทั้งสิ้น ปริมาณ 61,047.89  กิโลกรัม มูลค่า 1,145,043.59  บาท ราคาเฉลี่ย');
        $objPHPExcel->getActiveSheet()->setCellValue('A9', '                  18.76  บาท/กก. ซึ่งมีโครีดนม จำนวน 139 ตัว คิดเป็นผลผลิตเฉลี่ยรวม 15.30  กก./ตัว/วัน');

        //tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A10', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A10:A12');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B10:C10');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D10:E10');
        $objPHPExcel->getActiveSheet()->setCellValue('B11', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 543 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('B11:C11');
        $objPHPExcel->getActiveSheet()->setCellValue('D11', '(' . $this->getMonthshName($this->st) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' - ' . $this->getMonthshName($this->en) . ' ' . ($condition['YearFrom'] + 542 - $this->showyear) . ' )');
        $objPHPExcel->getActiveSheet()->mergeCells('D11:E11');
        $objPHPExcel->getActiveSheet()->setCellValue('F10', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F10:G11');
        $objPHPExcel->getActiveSheet()->setCellValue('B12', 'หน่วย');
        $objPHPExcel->getActiveSheet()->setCellValue('C12', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D12', 'หน่วย');
        $objPHPExcel->getActiveSheet()->setCellValue('E12', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F12', 'หน่วย');
        $objPHPExcel->getActiveSheet()->setCellValue('G12', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (13 + $row), $data['DataList'][0]['MainItem']);
        $row++;
        foreach ($data['DataList'][0]['SubItem'] as $item) {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . (13 + $row), $item['SubItem']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (13 + $row), $item['CurrentUnit']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (13 + $row), $item['CurrentPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (13 + $row), $item['BeforeUnit']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (13 + $row), $item['BeforePercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (13 + $row), $item['DiffUnit']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (13 + $row), $item['DiffPercentage']);

            $row++;
        }
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A9')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A13')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A13')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A10:G12')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A10:G12')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A10:G12')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A13:G' . (13 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A10:G' . (13 + $row - 1))->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . (13 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );


        return $objPHPExcel;
    }

    private function generateCowgroup2Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $data = CowGroupController::getDataListquar($condition, $region);
        $objPHPExcel->getActiveSheet()->setTitle("3.2 โค (2)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '3. การดำเนินงานด้านกิจการโคนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', ' 3.2 ฝูงโค อ.ส.ค.');


        //tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A6', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A6:A7');
        $objPHPExcel->getActiveSheet()->setCellValue('B6', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B6:C6');
        $objPHPExcel->getActiveSheet()->setCellValue('D6', 'ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D6:E6');
        $objPHPExcel->getActiveSheet()->setCellValue('F6', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F6:G6');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'หน่วย');
        $objPHPExcel->getActiveSheet()->setCellValue('C7', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'หน่วย');
        $objPHPExcel->getActiveSheet()->setCellValue('E7', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'หน่วย');
        $objPHPExcel->getActiveSheet()->setCellValue('G7', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (8 + $row), $data['DataList'][1]['MainItem']);
        $row++;
        foreach ($data['DataList'][1]['SubItem'] as $item) {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . (8 + $row), $item['SubItem']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (8 + $row), $item['CurrentUnit']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (8 + $row), $item['CurrentPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (8 + $row), $item['BeforeUnit']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (8 + $row), $item['BeforePercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (8 + $row), $item['DiffUnit']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (8 + $row), $item['DiffPercentage']);

            $row++;
        }
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A8')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A6:G7')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A6:G7')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A6:G7')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A6:G' . (8 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A6:G' . (8 + $row - 1))->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . (8 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );


        return $objPHPExcel;
    }

    private function generateCowgroup3Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $data = CowGroupController::getDataListquar($condition, $region);
        $objPHPExcel->getActiveSheet()->setTitle("3.2 โค (3)");
        return $objPHPExcel;
    }

    private function generateCowgroup4Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $data = CowGroupController::getDataListquar($condition, $region);
        $objPHPExcel->getActiveSheet()->setTitle("3.2 โค (4)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '3. การดำเนินงานด้านกิจการโคนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', ' 3.2 ฝูงโค อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '  ฝูงโค อ.ส.ค. ต.ค.60-ม.ค.61  มีการจำหน่ายโคทั้งสิ้น จำนวน 0  ตัว มูลค่า 0  บาท แบ่งเป็น');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                        1. โคเพศผู้ จำนวน 0 ตัว มูลค่า 0  บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                        2. โคหมดสภาพ จำนวน 0  ตัว มูลค่า 0  บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                        3. โคตาย จำนวน 0 ตัว มูลค่า 0  บาท');


        //tb header
//        $objPHPExcel->getActiveSheet()->setCellValue('A11', 'รายการ');
//        $objPHPExcel->getActiveSheet()->mergeCells('A11:A12');
//        $objPHPExcel->getActiveSheet()->setCellValue('B11', 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543));
//        $objPHPExcel->getActiveSheet()->mergeCells('B11:C11');
//        $objPHPExcel->getActiveSheet()->setCellValue('D11', 'ต.ค. ' . ($showm + 542) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 542));
//        $objPHPExcel->getActiveSheet()->mergeCells('D11:E11');
//        $objPHPExcel->getActiveSheet()->setCellValue('F11', 'ผลต่าง');
//        $objPHPExcel->getActiveSheet()->mergeCells('F11:G11');
//        $objPHPExcel->getActiveSheet()->setCellValue('B12', 'หน่วย');
//        $objPHPExcel->getActiveSheet()->setCellValue('C12', '%');
//        $objPHPExcel->getActiveSheet()->setCellValue('D12', 'หน่วย');
//        $objPHPExcel->getActiveSheet()->setCellValue('E12', '%');
//        $objPHPExcel->getActiveSheet()->setCellValue('F12', 'หน่วย');
//        $objPHPExcel->getActiveSheet()->setCellValue('G12', '% เพิ่ม,ลด');
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . (13 + $row), $data['DataList'][0]['MainItem']);
//        $row++;
        ///////tb 2
//        $tb2data = $data;
//        while ($condition['MonthFrom'] != 10) {
//            $condition['MonthFrom'] -= 1;
//            if ($condition['MonthFrom'] == 0) {
//                $condition['MonthFrom'] = 12;
//                $condition['MonthTo'] = 12;
//                $condition['YearTo'] -= 1;
//                $condition['YearFrom'] -= 1;
//                $newdata = CowGroupController::getDataListquar($condition, $region);
//
//                foreach ($newdata['DataList'][0]['SubItem'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][0]['SubItem'][$key]['CurrentPercentage'] += $itemnewdata['CurrentPercentage'];
//                    $tb2data['DataList'][0]['SubItem'][$key]['BeforePercentage'] += $itemnewdata['BeforePercentage'];
//                    $tb2data['DataList'][0]['SubItem'][$key]['DiffPercentage'] += $itemnewdata['DiffPercentage'];
//                }
//            } else {
//                $condition['MonthTo'] -= 1;
//
//                $newdata = CowGroupController::getDataListquar($condition, $region);
//                foreach ($newdata['DataList'][0]['SubItem'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][0]['SubItem'][$key]['CurrentPercentage'] += $itemnewdata['CurrentPercentage'];
//                    $tb2data['DataList'][0]['SubItem'][$key]['BeforePercentage'] += $itemnewdata['BeforePercentage'];
//                    $tb2data['DataList'][0]['SubItem'][$key]['DiffPercentage'] += $itemnewdata['DiffPercentage'];
//                }
//            }
//        }
//        foreach ($tb2data['DataList'][0]['SubItem'] as $item2) {
//
//            $objPHPExcel->getActiveSheet()->setCellValue('A' . (13 + $row), $item2['SubItem']);
//            $objPHPExcel->getActiveSheet()->setCellValue('B' . (13 + $row), $item2['CurrentUnit']);
//            $objPHPExcel->getActiveSheet()->setCellValue('C' . (13 + $row), $item2['CurrentPercentage']);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . (13 + $row), $item2['BeforeUnit']);
//            $objPHPExcel->getActiveSheet()->setCellValue('E' . (13 + $row), $item2['BeforePercentage']);
//            $objPHPExcel->getActiveSheet()->setCellValue('F' . (13 + $row), $item2['DiffUnit']);
//            $objPHPExcel->getActiveSheet()->setCellValue('G' . (13 + $row), $item2['DiffPercentage']);
//
//            $row++;
//        }
//// // header style
//        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
//        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
//        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A5:A9')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A13')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A13')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
//
//        $objPHPExcel->getActiveSheet()->getStyle('A11:G12')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A11:G12')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A11:G12')
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $objPHPExcel->getActiveSheet()->getStyle('A13:G' . (13 + $row))
//                ->getNumberFormat()
//                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
//        $objPHPExcel->getActiveSheet()->getStyle('A11:G' . (13 + $row - 1))->applyFromArray(
//                array(
//                    'borders' => array(
//                        'allborders' => array(
//                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
//                        )
//                    ),
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
//
//        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . (13 + $row))->applyFromArray(
//                array(
//                    'font' => array(
//                        'name' => 'AngsanaUPC',
//                    )
//                )
//        );
        return $objPHPExcel;
    }

    private function generateCowgroup5Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $data = CowGroupController::getDataListquar($condition, $region);
        $objPHPExcel->getActiveSheet()->setTitle("3.2 โค (5)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '3. การดำเนินงานด้านกิจการโคนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', ' 3.2 ฝูงโค อ.ส.ค.');
//        $objPHPExcel->getActiveSheet()->setCellValue('A5', '  ฝูงโค อ.ส.ค. ต.ค.60-ม.ค.61  มีการจำหน่ายโคทั้งสิ้น จำนวน 0  ตัว มูลค่า 0  บาท แบ่งเป็น');
//        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                        1. โคเพศผู้ จำนวน 0 ตัว มูลค่า 0  บาท');
//        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                        2. โคหมดสภาพ จำนวน 0  ตัว มูลค่า 0  บาท');
//        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                        3. โคตาย จำนวน 0 ตัว มูลค่า 0  บาท');
        //tb header
//        $objPHPExcel->getActiveSheet()->setCellValue('A6', 'รายการ');
//        $objPHPExcel->getActiveSheet()->mergeCells('A6:A7');
//        $objPHPExcel->getActiveSheet()->setCellValue('B6', 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543));
//        $objPHPExcel->getActiveSheet()->mergeCells('B6:C6');
//        $objPHPExcel->getActiveSheet()->setCellValue('D6', 'ต.ค. ' . ($showm + 542) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 542));
//        $objPHPExcel->getActiveSheet()->mergeCells('D6:E6');
//        $objPHPExcel->getActiveSheet()->setCellValue('F6', 'ผลต่าง');
//        $objPHPExcel->getActiveSheet()->mergeCells('F6:G6');
//        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'หน่วย');
//        $objPHPExcel->getActiveSheet()->setCellValue('C7', '%');
//        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'หน่วย');
//        $objPHPExcel->getActiveSheet()->setCellValue('E7', '%');
//        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'หน่วย');
//        $objPHPExcel->getActiveSheet()->setCellValue('G7', '% เพิ่ม,ลด');
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . (8 + $row), $data['DataList'][1]['MainItem']);
//        $row++;
        ///////tb 2
//        $tb2data = $data;
//        while ($condition['MonthFrom'] != 10) {
//            $condition['MonthFrom'] -= 1;
//            if ($condition['MonthFrom'] == 0) {
//                $condition['MonthFrom'] = 12;
//                $condition['MonthTo'] = 12;
//                $condition['YearTo'] -= 1;
//                $condition['YearFrom'] -= 1;
//                $newdata = CowGroupController::getQuarterDataList($condition, $region);
//
//                foreach ($newdata['DataList'][1]['SubItem'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][1]['SubItem'][$key]['CurrentPercentage'] += $itemnewdata['CurrentPercentage'];
//                    $tb2data['DataList'][1]['SubItem'][$key]['BeforePercentage'] += $itemnewdata['BeforePercentage'];
//                    $tb2data['DataList'][1]['SubItem'][$key]['DiffPercentage'] += $itemnewdata['DiffPercentage'];
//                }
//            } else {
//                $condition['MonthTo'] -= 1;
//
//                $newdata = CowGroupController::getQuarterDataList($condition, $region);
//                foreach ($newdata['DataList'][1]['SubItem'] as $key => $itemnewdata) {
//                    $tb2data['DataList'][1]['SubItem'][$key]['CurrentPercentage'] += $itemnewdata['CurrentPercentage'];
//                    $tb2data['DataList'][1]['SubItem'][$key]['BeforePercentage'] += $itemnewdata['BeforePercentage'];
//                    $tb2data['DataList'][1]['SubItem'][$key]['DiffPercentage'] += $itemnewdata['DiffPercentage'];
//                }
//            }
//        }
//        foreach ($tb2data['DataList'][1]['SubItem'] as $item2) {
//
//            $objPHPExcel->getActiveSheet()->setCellValue('A' . (8 + $row), $item2['SubItem']);
//            $objPHPExcel->getActiveSheet()->setCellValue('B' . (8 + $row), $item2['CurrentUnit']);
//            $objPHPExcel->getActiveSheet()->setCellValue('C' . (8 + $row), $item2['CurrentPercentage']);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . (8 + $row), $item2['BeforeUnit']);
//            $objPHPExcel->getActiveSheet()->setCellValue('E' . (8 + $row), $item2['BeforePercentage']);
//            $objPHPExcel->getActiveSheet()->setCellValue('F' . (8 + $row), $item2['DiffUnit']);
//            $objPHPExcel->getActiveSheet()->setCellValue('G' . (8 + $row), $item2['DiffPercentage']);
//
//            $row++;
//        }
//// // header style
//        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
//        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
//        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
//      //  $objPHPExcel->getActiveSheet()->getStyle('A5:A9')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A8')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A8')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
//
//        $objPHPExcel->getActiveSheet()->getStyle('A6:G7')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A6:G7')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()
//                ->getStyle('A6:G7')
//                ->applyFromArray(array(
//                    'alignment' => array(
//                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
//                    )
//                        )
//        );
//        $objPHPExcel->getActiveSheet()->getStyle('A8:G' . (8 + $row))
//                ->getNumberFormat()
//                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
//        $objPHPExcel->getActiveSheet()->getStyle('A6:G' . (8 + $row - 1))->applyFromArray(
//                array(
//                    'borders' => array(
//                        'allborders' => array(
//                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
//                        )
//                    ),
//                    'font' => array(
//                        'name' => 'AngsanaUPC'
//                    )
//                )
//        );
//
//        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . (8 + $row))->applyFromArray(
//                array(
//                    'font' => array(
//                        'name' => 'AngsanaUPC',
//                    )
//                )
//        );
        return $objPHPExcel;
    }

    private function generateCowgroup6Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $data = CowGroupController::getDataListquar($condition, $region);
        $objPHPExcel->getActiveSheet()->setTitle("3.2 โค (6)");
        return $objPHPExcel;
    }

    private function generateMilkExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        //  print_r($data2);

        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthTo'] = 10;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthTo'] = 3;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthTo'] = 6;
        } else {
            $condition['MonthTo'] = 9;
        }
        $showm = 0;
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        $data = MBIController::getListMBIMonthdata($condition, $region);
        $condition['MonthTo'] = 9;
        $data2 = MBIController::getListMBIMonthdataOther($condition, $region);
        $size = sizeof($data['DataList']);

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');




//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A10', 'เดือน');
        $objPHPExcel->getActiveSheet()->mergeCells('A10:A11');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B10:C10');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D10:E10');
        $objPHPExcel->getActiveSheet()->setCellValue('F10', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F10:I10');
        $objPHPExcel->getActiveSheet()->setCellValue('B11', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C11', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D11', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E11', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F11', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G11', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H11', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I11', '% เพิ่ม,ลด');
// // header style
        $showc = 0;
        $showb = 0;
        $showperc = 0;
        $showperb = 0;
        $row = 0;
        $sumca = 0;
        $sumcb = 0;
        $sumba = 0;
        $sumbb = 0;
        $pa = 0;
        $pb = 0;
        foreach ($data['DataList'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), $item['Month']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $item['DiffBahtPercentage']);
            $sumca += $item['CurrentAmount'];
            $sumcb += $item['CurrentBaht'];
            $sumba += $item['BeforeAmount'];
            $sumbb += $item['BeforeBaht'];
            $row++;
            if ($key == 2) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'Q1');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
                if ($condition['QuarterFrom'] == 1) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 5) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'Q2');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
                if ($condition['QuarterFrom'] == 2) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $row++;

                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 8) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'Q3');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
                if ($condition['QuarterFrom'] == 3) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 11) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'Q4');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
                if ($condition['QuarterFrom'] == 4) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            }
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $data['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $data['Summary']['SummaryBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $data['Summary']['SummaryBeforeAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $data['Summary']['SummaryBeforeBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $data['Summary']['SummaryDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $data['Summary']['DiffAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $data['Summary']['SummaryDiffBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $data['Summary']['DiffBahtPercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                        อ.ส.ค.รับซื้อน้ำนมไตรมาส' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . '  ปริมาณ ' . number_format($showc, 2, '.', ',') . '  กิโลกรัม มูลค่า ' . number_format($showb, 2, '.', ',') . '  บาท ');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา ปรากฏว่าปริมาณ และมูลค่าคิดเป็นร้อยละ ' . number_format($pa, 2, '.', ',') . ' และ ' . number_format($pb, 2, '.', ',') . ' ตามลำดับ');
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' มีปริมาณ ' . number_format($data['Summary']['SummaryAmount'], 2, '.', ',') . ' กิโลกรัม มูลค่า ' . number_format($data['Summary']['SummaryBaht'], 2, '.', ',') . '  บาท');

        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา  ปริมาณและมูลค่าคิดเป็นร้อยละ ' . number_format($data['Summary']['SummaryCowPercentage'], 2, '.', ',') . ' และ ' . number_format($data['Summary']['SummaryServicePercentage'], 2, '.', ',') . ' ตามลำดับ');



        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A12:I' . (12 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A5:I11')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A10:I11')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A10:I11')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A10:I' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        $objPHPExcel = $this->generateMilk2Excel($objPHPExcel, $condition, $data2['DataOthersheet'][0]);
        $objPHPExcel = $this->generateMilk3Excel($objPHPExcel, $condition, $data2['DataOthersheet'][0]);
        $objPHPExcel = $this->generateMilk4Excel($objPHPExcel, $condition, $data2['DataOthersheet'][0]);
        $objPHPExcel = $this->generateMilk5Excel($objPHPExcel, $condition, $data2['DataOthersheet'][1]);
        $objPHPExcel = $this->generateMilk6Excel($objPHPExcel, $condition, $data2['DataOthersheet'][1]);
        $objPHPExcel = $this->generateMilk7Excel($objPHPExcel, $condition, $data2['DataOthersheet'][1]);
        $objPHPExcel = $this->generateMilk8Excel($objPHPExcel, $condition, $data2['DataOthersheet'][2]);
        $objPHPExcel = $this->generateMilk9Excel($objPHPExcel, $condition, $data2['DataOthersheet'][2]);
        $objPHPExcel = $this->generateMilk10Excel($objPHPExcel, $condition, $data2['DataOthersheet'][2]);
        $objPHPExcel = $this->generateMilk11Excel($objPHPExcel, $condition, $data2['DataOthersheet'][3]);
        $objPHPExcel = $this->generateMilk12Excel($objPHPExcel, $condition, $data2['DataOthersheet'][3]);
        $objPHPExcel = $this->generateMilk13Excel($objPHPExcel, $condition, $data2['DataOthersheet'][3]);
        $objPHPExcel = $this->generateMilk14Excel($objPHPExcel, $condition, $data2['DataOthersheet'][4]);
        $objPHPExcel = $this->generateMilk15Excel($objPHPExcel, $condition, $data2['DataOthersheet'][4]);
        return $objPHPExcel;
    }

    private function generateMilk2Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);



        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (2)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                         4.1.1 ภาคกลาง');
        $objPHPExcel->getActiveSheet()->getStyle('A5')->getFont()->setSize(16);

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'เดือน');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A8');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E7');
        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:I7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G8', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I8', '% เพิ่ม,ลด');
        $showc = 0;
        $showb = 0;
        $showperc = 0;
        $showperb = 0;
        $row = 0;
        $sumca = 0;
        $sumcb = 0;
        $sumba = 0;
        $sumbb = 0;
        $pa = 0;
        $pb = 0;
        //print_r($data['data']);
        foreach ($data['data'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), $item['Month']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $item['DiffBahtPercentage']);

            $sumca += $item['CurrentAmount'];
            $sumcb += $item['CurrentBaht'];
            $sumba += $item['BeforeAmount'];
            $sumbb += $item['BeforeBaht'];
            $row++;
            if ($key == 2) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q1');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 1) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 5) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q2');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 2) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 8) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q3');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 3) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 11) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q4');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 4) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            }
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $data['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $data['Summary']['SummaryBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $data['Summary']['SummaryBeforeAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $data['Summary']['SummaryBeforeBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $data['Summary']['SummaryDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $data['Summary']['DiffAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $data['Summary']['SummaryDiffBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $data['Summary']['DiffBahtPercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);

// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:I8')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk3Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $size = sizeof($data['data']);
//print_r($data['data'][$size - 1]['CurrentAmount']);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (3)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                         4.1.1 ภาคกลาง');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A6')->getFont()->setSize(16);

        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                         ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . 'ภาคกลาง  ปริมาณ ' . number_format($data['data'][$size - 1]['CurrentAmount'], 2, '.', ',') . '  กิโลกรัม มูลค่า ' . number_format($data['data'][$size - 1]['CurrentBaht'], 2, '.', ',') . '  บาท ดังนี้');
        //   $objPHPExcel->getActiveSheet()->setCellValue('A7', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา ปรากฏว่าปริมาณ และมูลค่าคิดเป็นร้อยละ ' . number_format($data['DataList'][$size - 1]['DiffAmountPercentage'], 2, '.', ',') . ' และ ' . number_format($data['DataList'][$size - 1]['DiffBahtPercentage'], 2, '.', ',') . ' ตามลำดับ');
// // header style

        $row = 0;
        foreach ($data['subdetail'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), ($key + 1) . '. ' . $item['VENDOR_NAME'] . ' ปริมาณ ' . number_format($item['sum_amount'], 2, '.', ',') . ' มูลค่า ' . number_format($item['sum_baht'], 2, '.', ','));

            $row++;
        }
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . (7 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . (7 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk4Excel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (4)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                         4.1.1 ภาคกลาง');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A6')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' ภาคกลาง มีปริมาณ ' . number_format($data['Summary']['SummaryAmount'], 2, '.', ',') . ' กิโลกรัม มูลค่า ' . number_format($data['Summary']['SummaryBaht'], 2, '.', ',') . '  บาท ดังนี้');
        $row = 0;
        foreach ($data['collectsubdetail'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), ($key + 1) . '. ' . $item['VENDOR_NAME'] . ' ปริมาณ ' . number_format($item['sum_amount'], 2, '.', ',') . ' มูลค่า ' . number_format($item['sum_baht'], 2, '.', ','));

            $row++;
        }
//tb header
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . (7 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . (7 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk5Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);



        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (5)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                         4.1.2 ภาคใต้');
        $objPHPExcel->getActiveSheet()->getStyle('A5')->getFont()->setSize(16);

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'เดือน');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A8');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E7');
        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:I7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G8', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I8', '% เพิ่ม,ลด');

        $row = 0;
        $sumca = 0;
        $sumcb = 0;
        $sumba = 0;
        $sumbb = 0;
        $pa = 0;
        $pb = 0;
        foreach ($data['data'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), $item['Month']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $item['DiffBahtPercentage']);
            $row++;
            $sumca += $item['CurrentAmount'];
            $sumcb += $item['CurrentBaht'];
            $sumba += $item['BeforeAmount'];
            $sumbb += $item['BeforeBaht'];

            if ($key == 2) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q1');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 5) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q2');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;

                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 8) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q3');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 11) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q4');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            }
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $data['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $data['Summary']['SummaryBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $data['Summary']['SummaryBeforeAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $data['Summary']['SummaryBeforeBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $data['Summary']['SummaryDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $data['Summary']['DiffAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $data['Summary']['SummaryDiffBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $data['Summary']['DiffBahtPercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);


// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . (9 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:I8')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk6Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $size = sizeof($data['data']);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (6)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                         4.1.2 ภาคใต้');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A6')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                         ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . 'ภาคใต้  ปริมาณ ' . number_format($data['data'][$size - 1]['CurrentAmount'], 2, '.', ',') . '  กิโลกรัม มูลค่า ' . number_format($data['data'][$size - 1]['CurrentBaht'], 2, '.', ',') . '  บาท ดังนี้');
        $row = 0;
        foreach ($data['subdetail'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), ($key + 1) . '. ' . $item['VENDOR_NAME'] . ' ปริมาณ ' . number_format($item['sum_amount'], 2, '.', ',') . ' มูลค่า ' . number_format($item['sum_baht'], 2, '.', ','));

            $row++;
        }

        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        //  $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . (7 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . (7 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk7Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (7)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                         4.1.2 ภาคใต้');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A6')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' ภาคใต้ มีปริมาณ ' . number_format($data['Summary']['SummaryAmount'], 2, '.', ',') . ' กิโลกรัม มูลค่า ' . number_format($data['Summary']['SummaryBaht'], 2, '.', ',') . '  บาท ดังนี้');
        $row = 0;
        foreach ($data['collectsubdetail'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), ($key + 1) . '. ' . $item['VENDOR_NAME'] . ' ปริมาณ ' . number_format($item['sum_amount'], 2, '.', ',') . ' มูลค่า ' . number_format($item['sum_baht'], 2, '.', ','));

            $row++;
        }

// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . (7 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . (7 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk8Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);



        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (8)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                          4.1.3 ภาคตะวันออกเฉียงเหนือ');
        $objPHPExcel->getActiveSheet()->getStyle('A5')->getFont()->setSize(16);

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'เดือน');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A8');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E7');
        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:I7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G8', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I8', '% เพิ่ม,ลด');


        $row = 0;
        $sumca = 0;
        $sumcb = 0;
        $sumba = 0;
        $sumbb = 0;
        $pa = 0;
        $pb = 0;
        foreach ($data['data'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), $item['Month']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $item['DiffBahtPercentage']);
            $row++;
            $sumca += $item['CurrentAmount'];
            $sumcb += $item['CurrentBaht'];
            $sumba += $item['BeforeAmount'];
            $sumbb += $item['BeforeBaht'];

            if ($key == 2) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q1');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 5) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q2');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;

                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 8) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q3');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 11) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q4');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            }
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $data['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $data['Summary']['SummaryBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $data['Summary']['SummaryBeforeAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $data['Summary']['SummaryBeforeBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $data['Summary']['SummaryDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $data['Summary']['DiffAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $data['Summary']['SummaryDiffBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $data['Summary']['DiffBahtPercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);

// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . (9 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:I8')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk9Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $size = sizeof($data['data']);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (9)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                          4.1.3 ภาคตะวันออกเฉียงเหนือ');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A6')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                         ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . 'ภาคตะวันออกเฉียงเหนือ  ปริมาณ ' . number_format($data['data'][$size - 1]['CurrentAmount'], 2, '.', ',') . '  กิโลกรัม มูลค่า ' . number_format($data['data'][$size - 1]['CurrentBaht'], 2, '.', ',') . '  บาท ดังนี้');
        $row = 0;
        foreach ($data['subdetail'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), ($key + 1) . '. ' . $item['VENDOR_NAME'] . ' ปริมาณ ' . number_format($item['sum_amount'], 2, '.', ',') . ' มูลค่า ' . number_format($item['sum_baht'], 2, '.', ','));

            $row++;
        }
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . (7 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . (7 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk10Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (10)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                          4.1.3 ภาคตะวันออกเฉียงเหนือ');
        $objPHPExcel->getActiveSheet()->getStyle('A5')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' ภาคตะวันออกเฉียงเหนือ มีปริมาณ ' . number_format($data['Summary']['SummaryAmount'], 2, '.', ',') . ' กิโลกรัม มูลค่า ' . number_format($data['Summary']['SummaryBaht'], 2, '.', ',') . '  บาท ดังนี้');
        $row = 0;
        foreach ($data['collectsubdetail'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), ($key + 1) . '. ' . $item['VENDOR_NAME'] . ' ปริมาณ ' . number_format($item['sum_amount'], 2, '.', ',') . ' มูลค่า ' . number_format($item['sum_baht'], 2, '.', ','));

            $row++;
        }
//tb header
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . (7 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . (7 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk11Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);



        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (11)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                           4.1.4 ภาคเหนือตอนล่าง');
        $objPHPExcel->getActiveSheet()->getStyle('A5')->getFont()->setSize(16);

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'เดือน');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A8');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E7');
        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:I7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G8', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I8', '% เพิ่ม,ลด');

        $row = 0;
        $sumca = 0;
        $sumcb = 0;
        $sumba = 0;
        $sumbb = 0;
        $pa = 0;
        $pb = 0;
        foreach ($data['data'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), $item['Month']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $item['DiffBahtPercentage']);
            $row++;
            $sumca += $item['CurrentAmount'];
            $sumcb += $item['CurrentBaht'];
            $sumba += $item['BeforeAmount'];
            $sumbb += $item['BeforeBaht'];

            if ($key == 2) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q1');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 5) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q2');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;

                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 8) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q3');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 11) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q4');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            }
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $data['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $data['Summary']['SummaryBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $data['Summary']['SummaryBeforeAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $data['Summary']['SummaryBeforeBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $data['Summary']['SummaryDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $data['Summary']['DiffAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $data['Summary']['SummaryDiffBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $data['Summary']['DiffBahtPercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);

// // header style


        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:I8')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . (9 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk12Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $size = sizeof($data['data']);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (12)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                           4.1.4 ภาคเหนือตอนล่าง');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A6')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                         ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . 'ภาคเหนือตอนล่าง  ปริมาณ ' . number_format($data['data'][$size - 1]['CurrentAmount'], 2, '.', ',') . '  กิโลกรัม มูลค่า ' . number_format($data['data'][$size - 1]['CurrentBaht'], 2, '.', ',') . '  บาท ดังนี้');
        $row = 0;
        foreach ($data['subdetail'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), ($key + 1) . '. ' . $item['VENDOR_NAME'] . ' ปริมาณ ' . number_format($item['sum_amount'], 2, '.', ',') . ' มูลค่า ' . number_format($item['sum_baht'], 2, '.', ','));

            $row++;
        }
//tb header
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        //  $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . (7 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . (7 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk13Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (13)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                           4.1.4 ภาคเหนือตอนล่าง');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A6')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' ภาคเหนือตอนล่าง มีปริมาณ ' . number_format($data['Summary']['SummaryAmount'], 2, '.', ',') . ' กิโลกรัม มูลค่า ' . number_format($data['Summary']['SummaryBaht'], 2, '.', ',') . '  บาท ดังนี้');
        $row = 0;
        foreach ($data['collectsubdetail'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), ($key + 1) . '. ' . $item['VENDOR_NAME'] . ' ปริมาณ ' . number_format($item['sum_amount'], 2, '.', ',') . ' มูลค่า ' . number_format($item['sum_baht'], 2, '.', ','));

            $row++;
        }
//tb header
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . (7 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . (7 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk14Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);



        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (14)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                          4.1.5 ภาคเหนือตอนบน');
        $objPHPExcel->getActiveSheet()->getStyle('A5')->getFont()->setSize(16);

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'เดือน');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A8');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E7');
        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:I7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G8', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I8', '% เพิ่ม,ลด');

        $row = 0;
        $sumca = 0;
        $sumcb = 0;
        $sumba = 0;
        $sumbb = 0;
        $pa = 0;
        $pb = 0;
        foreach ($data['data'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), $item['Month']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $item['DiffBahtPercentage']);
            $row++;
            $sumca += $item['CurrentAmount'];
            $sumcb += $item['CurrentBaht'];
            $sumba += $item['BeforeAmount'];
            $sumbb += $item['BeforeBaht'];

            if ($key == 2) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q1');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 5) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q2');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;

                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 8) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q3');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 11) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q4');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            }
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $data['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $data['Summary']['SummaryBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $data['Summary']['SummaryBeforeAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $data['Summary']['SummaryBeforeBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $data['Summary']['SummaryDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $data['Summary']['DiffAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $data['Summary']['SummaryDiffBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $data['Summary']['DiffBahtPercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);

// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:I8')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . (9 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMilk15Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $size = sizeof($data['data']);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.1 รับน้ำนม (15)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  4.1 การรับซื้อน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                           4.1.5 ภาคเหนือตอนบน');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A7')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                         ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . 'ภาคเหนือตอนบน  ปริมาณ ' . number_format($data['data'][$size - 1]['CurrentAmount'], 2, '.', ',') . '  กิโลกรัม มูลค่า ' . number_format($data['data'][$size - 1]['CurrentBaht'], 2, '.', ',') . '  บาท ดังนี้');
        $row = 0;
        foreach ($data['subdetail'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), ($key + 1) . '. ' . $item['VENDOR_NAME'] . ' ปริมาณ ' . number_format($item['sum_amount'], 2, '.', ',') . ' มูลค่า ' . number_format($item['sum_baht'], 2, '.', ','));

            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' ภาคเหนือตอนบน มีปริมาณ ' . number_format($data['Summary']['SummaryAmount'], 2, '.', ',') . ' กิโลกรัม มูลค่า ' . number_format($data['Summary']['SummaryBaht'], 2, '.', ',') . '  บาท ดังนี้');
        $row++;
        foreach ($data['collectsubdetail'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), ($key + 1) . '. ' . $item['VENDOR_NAME'] . ' ปริมาณ ' . number_format($item['sum_amount'], 2, '.', ',') . ' มูลค่า ' . number_format($item['sum_baht'], 2, '.', ','));

            $row++;
        }
//tb header
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . (7 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . (7 + $row))->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generatesaleMilkExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        if ($condition['QuarterFrom'] == 1) {

            $condition['MonthTo'] = 10;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthTo'] = 3;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthTo'] = 6;
        } else {
            $condition['MonthTo'] = 9;
        }
        $data = MSIController::getListMSIMonthdata($condition, $region);
        $condition['MonthTo'] = 9;
        $data2 = MSIController::getListMSIMonthdataOther($condition, $region);


        $size = sizeof($data['DataList']);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.2 จำหน่ายน้ำนม");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '   4.2 การจำหน่ายน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' มีปริมาณ ' . number_format($data['Summary']['SummaryAmount'], 2, '.', ',') . ' กิโลกรัม มูลค่า ' . number_format($data['Summary']['SummaryBaht'], 2, '.', ',') . '  บาท');

        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา  ปริมาณและมูลค่าคิดเป็นร้อยละ ' . number_format($data['Summary']['SummaryCowPercentage'], 2, '.', ',') . ' และ ' . number_format($data['Summary']['SummaryServicePercentage'], 2, '.', ',') . ' ตามลำดับ');



//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A10', 'เดือน');
        $objPHPExcel->getActiveSheet()->mergeCells('A10:A11');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B10:C10');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D10:E10');
        $objPHPExcel->getActiveSheet()->setCellValue('F10', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F10:I10');
        $objPHPExcel->getActiveSheet()->setCellValue('B11', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C11', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D11', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E11', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F11', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G11', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H11', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I11', '% เพิ่ม,ลด');
// // header style
        $showc = 0;
        $showb = 0;
        $showperc = 0;
        $showperb = 0;
        $row = 0;
        $sumca = 0;
        $sumcb = 0;
        $sumba = 0;
        $sumbb = 0;
        $pa = 0;
        $pb = 0;
        foreach ($data['DataList'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), $item['Month']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $item['DiffBahtPercentage']);
            $row++;
            $sumca += $item['CurrentAmount'];
            $sumcb += $item['CurrentBaht'];
            $sumba += $item['BeforeAmount'];
            $sumbb += $item['BeforeBaht'];

            if ($key == 2) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'Q1');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 1) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 5) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'Q2');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 2) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 8) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'Q3');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 3) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 11) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'Q4');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 4) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            }
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                        อ.ส.ค.การจำหน่ายน้ำนมไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . '  ปริมาณ ' . number_format($showc, 2, '.', ',') . '  กิโลกรัม มูลค่า ' . number_format($showb, 2, '.', ',') . '  บาท ');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา ปรากฏว่าปริมาณ และมูลค่าคิดเป็นร้อยละ ' . number_format($pa, 2, '.', ',') . ' และ ' . number_format($pb, 2, '.', ',') . ' ตามลำดับ');

        $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $data['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $data['Summary']['SummaryBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $data['Summary']['SummaryBeforeAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $data['Summary']['SummaryBeforeBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $data['Summary']['SummaryDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $data['Summary']['DiffAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $data['Summary']['SummaryDiffBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $data['Summary']['DiffBahtPercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A12:I' . (12 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A5:I11')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A10:I11')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A10:I11')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A10:I' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        $objPHPExcel = $this->generatesaleMilk2Excel($objPHPExcel, $condition, $data2['DataOthersheet'][0]);
        $objPHPExcel = $this->generatesaleMilk3Excel($objPHPExcel, $condition, $data2['DataOthersheet'][1]);
        $objPHPExcel = $this->generatesaleMilk4Excel($objPHPExcel, $condition, $data2['DataOthersheet'][2]);
        return $objPHPExcel;
    }

    private function generatesaleMilk2Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $size = sizeof($data['data']);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.2 จำหน่ายน้ำนม (2)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '   4.2 การจำหน่ายน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                          4.2.1 โรงงานนมปราณบุรี');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A6')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'เดือน');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A8');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E7');
        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:I7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G8', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I8', '% เพิ่ม,ลด');
        $showc = 0;
        $showb = 0;
        $showperc = 0;
        $showperb = 0;
        $row = 0;
        $sumca = 0;
        $sumcb = 0;
        $sumba = 0;
        $sumbb = 0;
        $pa = 0;
        $pb = 0;
        foreach ($data['data'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), $item['Month']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $item['DiffBahtPercentage']);
            $row++;
            $sumca += $item['CurrentAmount'];
            $sumcb += $item['CurrentBaht'];
            $sumba += $item['BeforeAmount'];
            $sumbb += $item['BeforeBaht'];

            if ($key == 2) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q1');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 1) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 5) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q2');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 2) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }

                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 8) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q3');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 3) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 11) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q4');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 4) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            }
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $data['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $data['Summary']['SummaryBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $data['Summary']['SummaryBeforeAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $data['Summary']['SummaryBeforeBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $data['Summary']['SummaryDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $data['Summary']['DiffAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $data['Summary']['SummaryDiffBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $data['Summary']['DiffBahtPercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);

// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:I8')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . (9 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . $highestRow)->applyFromArray(
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
        $highestRow += 2;

        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($highestRow), '                        ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . ' โรงงานนมปราณบุรีจำน่ายน้ำนม  ปริมาณ ' . number_format($showc, 2, '.', ',') . '  กิโลกรัม มูลค่า ' . number_format($showb, 2, '.', ',') . '  บาท ');
        //  $objPHPExcel->getActiveSheet()->setCellValue('A6', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา ปรากฏว่าปริมาณ และมูลค่าคิดเป็นร้อยละ ' . number_format($data['DataList'][$size - 1]['DiffAmountPercentage'], 2, '.', ',') . ' และ ' . number_format($data['DataList'][$size - 1]['DiffBahtPercentage'], 2, '.', ',') . ' ตามลำดับ');
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($highestRow))->getFont()->setSize(16);
        $highestRow += 2;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($highestRow), '                        เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' โรงงานนมปราณบุรีจำน่ายน้ำนม มีปริมาณ ' . number_format($data['Summary']['SummaryAmount'], 2, '.', ',') . ' กิโลกรัม มูลค่า ' . number_format($data['Summary']['SummaryBaht'], 2, '.', ',') . '  บาท');
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($highestRow))->getFont()->setSize(16);
        // $objPHPExcel->getActiveSheet()->setCellValue('A8', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา  ปริมาณและมูลค่าคิดเป็นร้อยละ ' . number_format($data['Summary']['SummaryCowPercentage'], 2, '.', ',') . ' และ ' . number_format($data['Summary']['SummaryServicePercentage'], 2, '.', ',') . ' ตามลำดับ');

        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generatesaleMilk3Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $size = sizeof($data['data']);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.2 จำหน่ายน้ำนม (3)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '   4.2 การจำหน่ายน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                           4.2.2 โรงงานนมสุโขทัย');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A6')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'เดือน');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A8');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E7');
        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:I7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G8', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I8', '% เพิ่ม,ลด');
        $showc = 0;
        $showb = 0;
        $showperc = 0;
        $showperb = 0;
        $row = 0;
        $sumca = 0;
        $sumcb = 0;
        $sumba = 0;
        $sumbb = 0;
        $pa = 0;
        $pb = 0;
        foreach ($data['data'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), $item['Month']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $item['DiffBahtPercentage']);
            $row++;
            $sumca += $item['CurrentAmount'];
            $sumcb += $item['CurrentBaht'];
            $sumba += $item['BeforeAmount'];
            $sumbb += $item['BeforeBaht'];

            if ($key == 2) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q1');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 1) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 5) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q2');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 2) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 8) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q3');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 3) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 11) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q4');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 4) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            }
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $data['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $data['Summary']['SummaryBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $data['Summary']['SummaryBeforeAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $data['Summary']['SummaryBeforeBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $data['Summary']['SummaryDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $data['Summary']['DiffAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $data['Summary']['SummaryDiffBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $data['Summary']['DiffBahtPercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);

// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:I8')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . (9 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . $highestRow)->applyFromArray(
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
        $highestRow += 2;

        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($highestRow), '                        ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . ' โรงงานนมสุโขทัย จำน่ายน้ำนม  ปริมาณ ' . number_format($showc, 2, '.', ',') . '  กิโลกรัม มูลค่า ' . number_format($showb, 2, '.', ',') . '  บาท ');
        //  $objPHPExcel->getActiveSheet()->setCellValue('A6', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา ปรากฏว่าปริมาณ และมูลค่าคิดเป็นร้อยละ ' . number_format($data['DataList'][$size - 1]['DiffAmountPercentage'], 2, '.', ',') . ' และ ' . number_format($data['DataList'][$size - 1]['DiffBahtPercentage'], 2, '.', ',') . ' ตามลำดับ');
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($highestRow))->getFont()->setSize(16);
        $highestRow += 2;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($highestRow), '                        เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' โรงงานนมสุโขทัย จำน่ายน้ำนม มีปริมาณ ' . number_format($data['Summary']['SummaryAmount'], 2, '.', ',') . ' กิโลกรัม มูลค่า ' . number_format($data['Summary']['SummaryBaht'], 2, '.', ',') . '  บาท');
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($highestRow))->getFont()->setSize(16);
        // $objPHPExcel->getActiveSheet()->setCellValue('A8', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา  ปริมาณและมูลค่าคิดเป็นร้อยละ ' . number_format($data['Summary']['SummaryCowPercentage'], 2, '.', ',') . ' และ ' . number_format($data['Summary']['SummaryServicePercentage'], 2, '.', ',') . ' ตามลำดับ');

        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generatesaleMilk4Excel($objPHPExcel, $condition, $data) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $size = sizeof($data['data']);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        $objPHPExcel->getActiveSheet()->setTitle("4.2 จำหน่ายน้ำนม (4)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '4. การดำเนินงานด้านการรับซื้อและการจำหน่ายน้ำนมของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '   4.2 การจำหน่ายน้ำนมทั้งหมดของ อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                           4.2.3 โรงงานนมเชียงใหม่');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A6')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'เดือน');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A8');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B7:C7');
        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'ปีงบประมาณ ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D7:E7');
        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F7:I7');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F8', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G8', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H8', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I8', '% เพิ่ม,ลด');
        $showc = 0;
        $showb = 0;
        $showperc = 0;
        $showperb = 0;
        $row = 0;
        $sumca = 0;
        $sumcb = 0;
        $sumba = 0;
        $sumbb = 0;
        $pa = 0;
        $pb = 0;
        foreach ($data['data'] as $key => $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), $item['Month']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $item['DiffBahtPercentage']);
            $row++;
            $sumca += $item['CurrentAmount'];
            $sumcb += $item['CurrentBaht'];
            $sumba += $item['BeforeAmount'];
            $sumbb += $item['BeforeBaht'];

            if ($key == 2) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q1');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 1) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 5) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q2');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 2) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 8) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q3');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 3) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            } else if ($key == 11) {
                if ($sumba != 0) {
                    $pa = (($sumca - $sumba) / $sumba) * 100;
                } else if (empty($sumba) && !empty($sumca)) {
                    $pa = 100;
                }
                if ($sumbb != 0) {
                    $pb = (($sumcb - $sumbb) / $sumbb) * 100;
                } else if (empty($sumbb) && !empty($sumcb)) {
                    $pb = 100;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'Q4');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $sumca);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $sumcb);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $sumca - $sumba);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $pa);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $sumcb - $sumbb);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $pb);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);
                $row++;
                if ($condition['QuarterFrom'] == 4) {
                    $showc = $sumca;
                    $showb = $sumcb;
                    $showperc = $pa;
                    $showperb = $pb;
                }
                $sumca = 0;
                $sumcb = 0;
                $sumba = 0;
                $sumbb = 0;
                $pa = 0;
                $pb = 0;
            }
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $row), $data['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $row), $data['Summary']['SummaryBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $row), $data['Summary']['SummaryBeforeAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $row), $data['Summary']['SummaryBeforeBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $row), $data['Summary']['SummaryDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $row), $data['Summary']['DiffAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $row), $data['Summary']['SummaryDiffBaht']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $row), $data['Summary']['DiffBahtPercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (9 + $row) . ':I' . (9 + $row))->getFont()->setBold(true);

// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A7:I8')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A7:I8')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . (9 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A7:I' . $highestRow)->applyFromArray(
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
        $highestRow += 2;

        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($highestRow), '                        ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . ' โรงงานนมเชียงใหม่ จำน่ายน้ำนม  ปริมาณ ' . number_format($showc, 2, '.', ',') . '  กิโลกรัม มูลค่า ' . number_format($showb, 2, '.', ',') . '  บาท ');
        //  $objPHPExcel->getActiveSheet()->setCellValue('A6', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา ปรากฏว่าปริมาณ และมูลค่าคิดเป็นร้อยละ ' . number_format($data['DataList'][$size - 1]['DiffAmountPercentage'], 2, '.', ',') . ' และ ' . number_format($data['DataList'][$size - 1]['DiffBahtPercentage'], 2, '.', ',') . ' ตามลำดับ');
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($highestRow))->getFont()->setSize(16);
        $highestRow += 2;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($highestRow), '                        เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' โรงงานนมเชียงใหม่ จำน่ายน้ำนม มีปริมาณ ' . number_format($data['Summary']['SummaryAmount'], 2, '.', ',') . ' กิโลกรัม มูลค่า ' . number_format($data['Summary']['SummaryBaht'], 2, '.', ',') . '  บาท');
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($highestRow))->getFont()->setSize(16);
        // $objPHPExcel->getActiveSheet()->setCellValue('A8', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา  ปริมาณและมูลค่าคิดเป็นร้อยละ ' . number_format($data['Summary']['SummaryCowPercentage'], 2, '.', ',') . ' และ ' . number_format($data['Summary']['SummaryServicePercentage'], 2, '.', ',') . ' ตามลำดับ');

        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);

        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateproductMilkExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $FactoryList = FactoryService::getList();
        session_start();

        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }

        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthTo'] = 12;
            $condition['MonthFrom'] = 10;

            $condition['YearFrom'] --;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthTo'] = 3;
            $condition['MonthFrom'] = 1;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthTo'] = 6;
            $condition['MonthFrom'] = 4;
        } else if ($condition['QuarterFrom'] == 4) {
            $condition['MonthTo'] = 9;
            $condition['MonthFrom'] = 7;
        }
        ///จัดรูปแบบใหม่

        $detail = [];
        foreach ($FactoryList as $id) {
            $data = ProductionInfoController::getMonthDataListreport($condition, $id['id']);
            array_push($detail, $data);
        }
        $show = ProductionInfoController::getMonthDatasubproductreport($condition);

        $ProducMilkList = ProductMilkService::getList();
        $product = [];
        foreach ($ProducMilkList as $value) {
            $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);

            array_push($product, $SubProductMilkList);
        }
        //   $data = ProductionInfoController::getMonthDataListreport($condition, $FactoryList[2]['id']);
//         
        $objPHPExcel->getActiveSheet()->setTitle("5.1 ผลิตภัณฑ์นม");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '5. การดำเนินงานด้านอุตสาหกรรมนม');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', ' 5.1 การผลิตผลิตภัณฑ์นม อ.ส.ค.');


        $objPHPExcel->getActiveSheet()->setCellValue('B6', '                   แบ่งเป็น ' . sizeof($FactoryList) . ' โรงงาน ดังนี้');
        $total = 0;
        $row = 0;
        foreach ($detail as $key => $item) {


            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), ($key + 1) . '. ' . $item['DataList']['factory_name'] . ' ปริมาณ ' . number_format($item['Summary']['SummaryAmount'], 2, '.', ',') . ' ลิตร');
            $total += $item['Summary']['SummaryAmount'];
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('B5', '                        ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . ' .ผลิตผลิตภัณฑ์นมชนิดต่าง ๆ ได้รวมกันทั้งสิ้น ปริมาณ ' . number_format($total, 2, '.', ',') . '  ลิตร ');



        $ckrow = 7 + $row;
        $row += 2;
//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A' . (7 + $row) . ':A' . (7 + $row + 1));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), ' ไตรมาส' . $condition[QuarterFrom] . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . (7 + $row) . ':C' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), ' ไตรมาส' . $condition[QuarterFrom] . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . (7 + $row) . ':E' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F' . (7 + $row) . ':G' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row + 1), '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row + 1), '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row + 1), '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setBold(true);

        $strow = 7 + $row;
        $row++;
        $row++;
        $sumcurrent = 0;
        $sumbefore = 0;
        $sumpercurrent = 0;
        $sumperbefore = 0;
        $sumdiff = 0;
        $per = 0;
        $totalc = 0;
        $totalb = 0;

        foreach ($show['DataList'] as $showitem) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), $showitem['productname']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row))->getFont()->setBold(true);
            $row++;

            $percurrent = 0;
            $perbefore = 0;

            foreach ($showitem['item'] as $itemsub) {
                $sumcurrent += $itemsub['CurrentAmount'];
                $sumbefore += $itemsub['BeforeAmount'];
                $sumdiff += $itemsub['DiffAmount'];
                $totalc += $itemsub['CurrentAmount'];
                $totalb += $itemsub['BeforeAmount'];
                if ($show['Summary']['sumCurrentAmount'] != 0) {
                    $percurrent = ($itemsub['CurrentAmount'] * 100) / $show['Summary']['sumCurrentAmount'];
                }
                if ($show['Summary']['sumBeforeAmount'] != 0) {
                    $perbefore = ($itemsub['BeforeAmount'] * 100) / $show['Summary']['sumBeforeAmount'];
                }
                $sumpercurrent += $percurrent;
                $sumperbefore += $perbefore;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), $itemsub['subProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $itemsub['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), ($percurrent));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $itemsub['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), ($perbefore));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $itemsub['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $itemsub['DiffAmountPercentage']);
                $row++;
            }
            if ($sumbefore != 0) {
                $per += (($sumdiff) / $sumbefore) * 100;
            } else if (empty($sumbefore) && !empty($sumcurrent)) {
                $per = 100;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รวม');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $sumcurrent);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), $sumpercurrent);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $sumbefore);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), $sumperbefore);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $sumdiff);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $per);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setBold(true);
            $row++;
        }
        $totalper = 100;
        if ($totalb != 0) {
            $totalper = (($totalc - $totalb) / $totalb) * 100;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $totalc);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), 100);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $totalb);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), 100);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $totalc - $totalb);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $totalper);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setBold(true);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow), '                        เมื่อเปรียบเทียบกับไตรมาสเดียวกันกับปีก่อนซึ่งผลิตได้ ปริมาณ ' . number_format($totalb, 2, '.', ',') . '  ลิตร ปรากฏว่าการผลิต');
        $_SESSION["totalc"] = $totalc;
        $_SESSION["totalb"] = $totalb;
        if ($totalc - $totalb > 0) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow + 1), '                     เพิ่มขึ้น ปริมาณ ' . number_format($totalc - $totalb, 2, '.', ',') . '  ลิตร คิดเป็นร้อยละ ' . number_format($totalper));
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow + 1), '                     ลดลง ปริมาณ ' . number_format($totalc - $totalb, 2, '.', ',') . '  ลิตร คิดเป็นร้อยละ ' . number_format($totalper));
        }
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow) . ':G' . ($ckrow + 1))->getFont()->setSize(16);
        // $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow) . ':G' . ($ckrow + 1))->getFont()->setBold(true);
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
//
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A5:G11')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow + 2) . ':G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A' . ($strow) . ':G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        $objPHPExcel = $this->generateproductMilk2Excel($objPHPExcel, $condition, $detail[0], $detail[1], $totalc, $totalb);
        $objPHPExcel = $this->generateproductMilk3Excel($objPHPExcel, $condition, $detail[2], $detail[3], $totalc, $totalb);
        $objPHPExcel = $this->generateproductMilk4Excel($objPHPExcel, $condition, $detail[4], $totalc, $totalb);
        $collectdate['MonthFrom'] = 10;
        $collectdate['YearFrom'] = $condition['YearFrom'];
        $collectdate['MonthTo'] = $condition['MonthTo'];
        $collectdate['YearTo'] = $condition['YearTo'];
        $collectdate['QuarterFrom'] = $condition['QuarterFrom'];
//        if ($condition['MonthTo'] != 10 || $condition['MonthTo'] != 11 || $condition['MonthTo'] != 12) {
//            $collectdate['YearFrom'] --;
//        }
        $objPHPExcel = $this->generateproductMilk5Excel($objPHPExcel, $collectdate);
        return $objPHPExcel;
    }

    private function generateproductMilk2Excel($objPHPExcel, $condition, $detail1, $detail2, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.1 ผลิตภัณฑ์นม (2)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);


        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail2['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;
        $sumtotalc2 = 0;
        $sumtotalb2 = 0;
        foreach ($detail2['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc2 += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb2 += $pb;
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }

        $difper2 = 0;
        if ($detail2['Summary']['SummaryBefore'] != 0) {
            $difper2 = (($detail2['Summary']['SummaryAmount'] - $detail2['Summary']['SummaryBefore']) * 100) / $detail2['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail2['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc2);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail2['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail2['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper2);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row++;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateproductMilk3Excel($objPHPExcel, $condition, $detail1, $detail2, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.1 ผลิตภัณฑ์นม (3)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);


        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail2['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;
        $sumtotalc2 = 0;
        $sumtotalb2 = 0;
        foreach ($detail2['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc2 += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb2 += $pb;
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }

        $difper2 = 0;
        if ($detail2['Summary']['SummaryBefore'] != 0) {
            $difper2 = (($detail2['Summary']['SummaryAmount'] - $detail2['Summary']['SummaryBefore']) * 100) / $detail2['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail2['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc2);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail2['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail2['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper2);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row++;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateproductMilk4Excel($objPHPExcel, $condition, $detail1, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.1 ผลิตภัณฑ์นม (4)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ไตรมาส ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $row++;




        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateproductMilk5Excel($objPHPExcel, $condition) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $FactoryList = FactoryService::getList();


//        $showm = 0;
//        $showy = $condition['YearFrom'];
//        $start = $condition['MonthFrom'];
//        if ($condition['MonthTo'] < 10) {
//            $showm = $condition['YearFrom'] - 1;
//        } else {
//            $showm = $condition['YearFrom'];
//        }
        ///จัดรูปแบบใหม่

        $detail = [];
        foreach ($FactoryList as $id) {
            $data = ProductionInfoController::getMonthDataListreport($condition, $id['id']);
            array_push($detail, $data);
        }
        $show = ProductionInfoController::getMonthDatasubproductreport($condition);

        $ProducMilkList = ProductMilkService::getList();
        $product = [];
        foreach ($ProducMilkList as $value) {
            $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);

            array_push($product, $SubProductMilkList);
        }
        //   $data = ProductionInfoController::getMonthDataListreport($condition, $FactoryList[2]['id']);
//         
        $objPHPExcel->getActiveSheet()->setTitle("5.1 ผลิตภัณฑ์นม (5)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '5. การดำเนินงานด้านอุตสาหกรรมนม');


        $objPHPExcel->getActiveSheet()->setCellValue('B6', '                   แบ่งเป็น ' . sizeof($FactoryList) . ' โรงงาน ดังนี้');
        $total = 0;
        $row = 0;
        foreach ($detail as $key => $item) {


            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), ($key + 1) . '. ' . $item['DataList']['factory_name'] . ' ปริมาณ ' . number_format($item['Summary']['SummaryAmount'], 2, '.', ',') . ' ลิตร');
            $total += $item['Summary']['SummaryAmount'];
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('B5', '                        เดือน ' . $this->getMonthshName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearTo'] + 543) . ' ผลิตผลิตภัณฑ์นมชนิดต่าง ๆ ได้รวมกันทั้งสิ้น ปริมาณ ' . number_format($total, 2, '.', ',') . '  ลิตร ');



        $ckrow = 7 + $row;
        $row += 2;
//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A' . (7 + $row) . ':A' . (7 + $row + 1));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), 'ไตรมาส ที่ 1 ' . ($condition['YearFrom'] + 543) . ' - ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . (7 + $row) . ':C' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), 'ไตรมาส ที่ 1 ' . ($condition['YearFrom'] + 542) . ' - ไตรมาส ที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . (7 + $row) . ':E' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F' . (7 + $row) . ':G' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row + 1), '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row + 1), '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row + 1), '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setBold(true);

        $strow = 7 + $row;
        $row++;
        $row++;
        $sumcurrent = 0;
        $sumbefore = 0;
        $sumpercurrent = 0;
        $sumperbefore = 0;
        $sumdiff = 0;
        $per = 0;
        $totalc = 0;
        $totalb = 0;

        foreach ($show['DataList'] as $showitem) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), $showitem['productname']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row))->getFont()->setBold(true);
            $row++;

            $percurrent = 0;
            $perbefore = 0;

            foreach ($showitem['item'] as $itemsub) {
                $sumcurrent += $itemsub['CurrentAmount'];
                $sumbefore += $itemsub['BeforeAmount'];
                $sumdiff += $itemsub['DiffAmount'];
                $totalc += $itemsub['CurrentAmount'];
                $totalb += $itemsub['BeforeAmount'];
                if ($show['Summary']['sumCurrentAmount'] != 0) {
                    $percurrent = ($itemsub['CurrentAmount'] * 100) / $show['Summary']['sumCurrentAmount'];
                }
                if ($show['Summary']['sumBeforeAmount'] != 0) {
                    $perbefore = ($itemsub['BeforeAmount'] * 100) / $show['Summary']['sumBeforeAmount'];
                }
                $sumpercurrent += $percurrent;
                $sumperbefore += $perbefore;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), $itemsub['subProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $itemsub['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), ($percurrent));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $itemsub['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), ($perbefore));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $itemsub['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $itemsub['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (7 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
            if ($sumbefore != 0) {
                $per += (($sumdiff) / $sumbefore) * 100;
            } else if (empty($sumbefore) && !empty($sumcurrent)) {
                $per = 100;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รวม');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $sumcurrent);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), $sumpercurrent);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $sumbefore);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), $sumperbefore);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $sumdiff);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $per);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                    ->getStyle('A' . (7 + $row))
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $row++;
        }
        $totalper = 100;
        if ($totalb != 0) {
            $totalper = (($totalc - $totalb) / $totalb) * 100;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $totalc);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), 100);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $totalb);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), 100);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $totalc - $totalb);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $totalper);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (7 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $_SESSION["totalc2"] = $totalc;
        $_SESSION["totalb2"] = $totalb;
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow), '                        เมื่อเปรียบเทียบกับไตรมาสเดียวกันกับปีก่อนซึ่งผลิตได้ ปริมาณ ' . number_format($totalb, 2, '.', ',') . '  ลิตร ปรากฏว่าการผลิต');

        if ($totalc - $totalb > 0) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow + 1), '                     เพิ่มขึ้น ปริมาณ ' . number_format($totalc - $totalb, 2, '.', ',') . '  ลิตร คิดเป็นร้อยละ ' . number_format($totalper));
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow + 1), '                     ลดลง ปริมาณ ' . number_format($totalc - $totalb, 2, '.', ',') . '  ลิตร คิดเป็นร้อยละ ' . number_format($totalper));
        }
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow) . ':G' . ($ckrow + 1))->getFont()->setSize(16);
        // $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow) . ':G' . ($ckrow + 1))->getFont()->setBold(true);
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
//
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A5:G11')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow + 2) . ':G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A' . ($strow) . ':G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        $objPHPExcel = $this->generateproductMilk6Excel($objPHPExcel, $condition, $detail[0], $detail[1], $totalc, $totalb);
        $objPHPExcel = $this->generateproductMilk7Excel($objPHPExcel, $condition, $detail[2], $detail[3], $totalc, $totalb);
        $objPHPExcel = $this->generateproductMilk8Excel($objPHPExcel, $condition, $detail[4], $totalc, $totalb);
        //  $objPHPExcel = $this->generatesaleproductMilkExcel($objPHPExcel, $condition, $region, $totalc1, $totalb1, $totalc, $totalb);
        return $objPHPExcel;
    }

    private function generateproductMilk6Excel($objPHPExcel, $condition, $detail1, $detail2, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.1 ผลิตภัณฑ์นม (6)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'ไตรมาสที่ 1 ' . ($condition['YearFrom'] + 543) . ' - ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ไตรมาสที่ 1 ' . ($condition['YearFrom'] + 542) . ' - ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);


        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail2['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;
        $sumtotalc2 = 0;
        $sumtotalb2 = 0;
        foreach ($detail2['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc2 += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb2 += $pb;
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }

        $difper2 = 0;
        if ($detail2['Summary']['SummaryBefore'] != 0) {
            $difper2 = (($detail2['Summary']['SummaryAmount'] - $detail2['Summary']['SummaryBefore']) * 100) / $detail2['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail2['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc2);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail2['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail2['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper2);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row++;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateproductMilk7Excel($objPHPExcel, $condition, $detail1, $detail2, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.1 ผลิตภัณฑ์นม (7)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'ไตรมาสที่ 1 ' . ($condition['YearFrom'] + 543) . ' - ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ไตรมาสที่ 1 ' . ($condition['YearFrom'] + 542) . ' - ไตรมาสที่' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);


        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail2['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;
        $sumtotalc2 = 0;
        $sumtotalb2 = 0;
        foreach ($detail2['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc2 += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb2 += $pb;
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }

        $difper2 = 0;
        if ($detail2['Summary']['SummaryBefore'] != 0) {
            $difper2 = (($detail2['Summary']['SummaryAmount'] - $detail2['Summary']['SummaryBefore']) * 100) / $detail2['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail2['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc2);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail2['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail2['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper2);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row++;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateproductMilk8Excel($objPHPExcel, $condition, $detail1, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.1 ผลิตภัณฑ์นม (8)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'ไตรมาสที่ 1 ' . ($condition['YearFrom'] + 543) . ' - ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', ' ไตรมาสที่ 1 ' . ($condition['YearFrom'] + 542) . ' - ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $row++;




        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesaleproductMilkExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $FactoryList = FactoryService::getList();


        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthTo'] = 12;
            $condition['MonthFrom'] = 10;

            $condition['YearFrom'] --;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthTo'] = 3;
            $condition['MonthFrom'] = 1;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthTo'] = 6;
            $condition['MonthFrom'] = 4;
        } else if ($condition['QuarterFrom'] == 4) {
            $condition['MonthTo'] = 9;
            $condition['MonthFrom'] = 7;
        }

        ///จัดรูปแบบใหม่

        $detail = [];
        foreach ($FactoryList as $id) {
            $data = ProductionSaleInfoController::getMonthDataListreport($condition, $id['id']);
            array_push($detail, $data);
        }
        $show = ProductionSaleInfoController::getMonthDatasubproductreport($condition);

        $ProducMilkList = ProductMilkService::getList();
        $product = [];
        foreach ($ProducMilkList as $value) {
            $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);

            array_push($product, $SubProductMilkList);
        }
        //   $data = ProductionInfoController::getMonthDataListreport($condition, $FactoryList[2]['id']);
//         
        $objPHPExcel->getActiveSheet()->setTitle("5.2 จำหน่ายผลิตภัณฑ์นม");

        $objPHPExcel->getActiveSheet()->setCellValue('A4', '5.2 การจำหน่ายผลิตภัณฑ์นม อ.ส.ค.');

        $objPHPExcel->getActiveSheet()->setCellValue('B6', '                   แบ่งเป็น ' . sizeof($FactoryList) . ' โรงงาน ดังนี้');
        $total = 0;
        $row = 0;
        foreach ($detail as $key => $item) {


            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), ($key + 1) . '. ' . $item['DataList']['factory_name'] . ' ปริมาณ ' . number_format($item['Summary']['SummaryAmount'], 2, '.', ',') . ' ลิตร');
            $total += $item['Summary']['SummaryAmount'];
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('B5', '                        ไตรมาส ' . $condition['QuarterrFrom'] . ' ' . ($condition['YearFrom'] + 543) . ' จำหน่ายผลิตภัณฑ์นมชนิดต่าง ๆ ได้รวมกันทั้งสิ้น ปริมาณ ' . number_format($total, 2, '.', ',') . '  ลิตร ');



        $ckrow = 7 + $row;
        $row += 2;
//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A' . (7 + $row) . ':A' . (7 + $row + 1));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), 'ไตรมาส ' . $condition['QuarterFrom'] . '  ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . (7 + $row) . ':C' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), 'ไตรมาส ' . $condition['QuarterFrom'] . '  ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . (7 + $row) . ':E' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F' . (7 + $row) . ':G' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row + 1), '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row + 1), '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row + 1), '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setBold(true);

        $strow = 7 + $row;
        $row++;
        $row++;
        $sumcurrent = 0;
        $sumbefore = 0;
        $sumpercurrent = 0;
        $sumperbefore = 0;
        $sumdiff = 0;
        $per = 0;
        $totalc = 0;
        $totalb = 0;

        foreach ($show['DataList'] as $showitem) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), $showitem['productname']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row))->getFont()->setBold(true);
            $row++;

            $percurrent = 0;
            $perbefore = 0;

            foreach ($showitem['item'] as $itemsub) {
                $sumcurrent += $itemsub['CurrentAmount'];
                $sumbefore += $itemsub['BeforeAmount'];
                $sumdiff += $itemsub['DiffAmount'];
                $totalc += $itemsub['CurrentAmount'];
                $totalb += $itemsub['BeforeAmount'];
                if ($show['Summary']['sumCurrentAmount'] != 0) {
                    $percurrent = ($itemsub['CurrentAmount'] * 100) / $show['Summary']['sumCurrentAmount'];
                }
                if ($show['Summary']['sumBeforeAmount'] != 0) {
                    $perbefore = ($itemsub['BeforeAmount'] * 100) / $show['Summary']['sumBeforeAmount'];
                }
                $sumpercurrent += $percurrent;
                $sumperbefore += $perbefore;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), $itemsub['subProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $itemsub['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), ($percurrent));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $itemsub['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), ($perbefore));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $itemsub['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $itemsub['DiffAmountPercentage']);
                $row++;
            }
            if ($sumbefore != 0) {
                $per += (($sumdiff) / $sumbefore) * 100;
            } else if (empty($sumbefore) && !empty($sumcurrent)) {
                $per = 100;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รวม');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $sumcurrent);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), $sumpercurrent);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $sumbefore);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), $sumperbefore);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $sumdiff);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $per);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setBold(true);
            $row++;
        }
        $totalper = 100;
        if ($totalb != 0) {
            $totalper = (($totalc - $totalb) / $totalb) * 100;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $totalc);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), 100);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $totalb);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), 100);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $totalc - $totalb);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $totalper);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setBold(true);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow), '                        เมื่อเปรียบเทียบกับไตรมาสเดียวกันกับปีก่อนซึ่งผลิตได้ ปริมาณ ' . number_format($totalb, 2, '.', ',') . '  ลิตร ปรากฏว่าการผลิต');

        if ($totalc - $totalb > 0) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow + 1), '                     เพิ่มขึ้น ปริมาณ ' . number_format($totalc - $totalb, 2, '.', ',') . '  ลิตร คิดเป็นร้อยละ ' . number_format($totalper));
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow + 1), '                     ลดลง ปริมาณ ' . number_format($totalc - $totalb, 2, '.', ',') . '  ลิตร คิดเป็นร้อยละ ' . number_format($totalper));
        }
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow) . ':G' . ($ckrow + 1))->getFont()->setSize(16);
        // $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow) . ':G' . ($ckrow + 1))->getFont()->setBold(true);
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
//
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A5:G11')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow + 2) . ':G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A' . ($strow) . ':G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        $objPHPExcel = $this->generatesaleproductMilk2Excel($objPHPExcel, $condition, $detail[0], $detail[1], $totalc, $totalb);
        $objPHPExcel = $this->generatesaleproductMilk3Excel($objPHPExcel, $condition, $detail[2], $detail[3], $totalc, $totalb);
        $objPHPExcel = $this->generatesaleproductMilk4Excel($objPHPExcel, $condition, $detail[4], $totalc, $totalb);
        $collectdate['MonthFrom'] = 10;
        $collectdate['YearFrom'] = $condition['YearFrom'];
        $collectdate['MonthTo'] = $condition['MonthTo'];
        $collectdate['YearTo'] = $condition['YearTo'];
//        if ($condition['MonthTo'] != 10 || $condition['MonthTo'] != 11 || $condition['MonthTo'] != 12) {
//            $collectdate['YearFrom'] --;
//        }
        $objPHPExcel = $this->generatesaleproductMilk5Excel($objPHPExcel, $collectdate);
        return $objPHPExcel;
    }

    private function generatesaleproductMilk2Excel($objPHPExcel, $condition, $detail1, $detail2, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.2 จำหน่ายผลิตภัณฑ์นม (2)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);


        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail2['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;
        $sumtotalc2 = 0;
        $sumtotalb2 = 0;
        foreach ($detail2['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc2 += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb2 += $pb;
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }

        $difper2 = 0;
        if ($detail2['Summary']['SummaryBefore'] != 0) {
            $difper2 = (($detail2['Summary']['SummaryAmount'] - $detail2['Summary']['SummaryBefore']) * 100) / $detail2['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail2['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc2);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail2['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail2['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper2);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row++;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesaleproductMilk3Excel($objPHPExcel, $condition, $detail1, $detail2, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.2 จำหน่ายผลิตภัณฑ์นม (3)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);


        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail2['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;
        $sumtotalc2 = 0;
        $sumtotalb2 = 0;
        foreach ($detail2['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc2 += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb2 += $pb;
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }

        $difper2 = 0;
        if ($detail2['Summary']['SummaryBefore'] != 0) {
            $difper2 = (($detail2['Summary']['SummaryAmount'] - $detail2['Summary']['SummaryBefore']) * 100) / $detail2['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail2['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc2);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail2['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail2['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper2);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row++;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesaleproductMilk4Excel($objPHPExcel, $condition, $detail1, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.2 จำหน่ายผลิตภัณฑ์นม (4)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $row++;




        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesaleproductMilk5Excel($objPHPExcel, $condition) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $FactoryList = FactoryService::getList();


//        $showm = 0;
//        $showy = $condition['YearFrom'];
//        $start = $condition['MonthFrom'];
//        if ($condition['MonthTo'] < 10) {
//            $showm = $condition['YearFrom'] - 1;
//        } else {
//            $showm = $condition['YearFrom'];
//        }
        ///จัดรูปแบบใหม่

        $detail = [];
        foreach ($FactoryList as $id) {
            $data = ProductionSaleInfoController::getMonthDataListreport($condition, $id['id']);
            array_push($detail, $data);
        }
        $show = ProductionSaleInfoController::getMonthDatasubproductreport($condition);

        $ProducMilkList = ProductMilkService::getList();
        $product = [];
        foreach ($ProducMilkList as $value) {
            $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);

            array_push($product, $SubProductMilkList);
        }
        //   $data = ProductionInfoController::getMonthDataListreport($condition, $FactoryList[2]['id']);
//         
        $objPHPExcel->getActiveSheet()->setTitle("5.2 จำหน่ายผลิตภัณฑ์นม (5)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '5. การดำเนินงานด้านอุตสาหกรรมนม');


        $objPHPExcel->getActiveSheet()->setCellValue('B6', '                   แบ่งเป็น ' . sizeof($FactoryList) . ' โรงงาน ดังนี้');
        $total = 0;
        $row = 0;
        foreach ($detail as $key => $item) {


            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), ($key + 1) . '. ' . $item['DataList']['factory_name'] . ' ปริมาณ ' . number_format($item['Summary']['SummaryAmount'], 2, '.', ',') . ' ลิตร');
            $total += $item['Summary']['SummaryAmount'];
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('B5', '                        เดือน ' . $this->getMonthshName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearTo'] + 543) . ' อ.ส.ค.จำหน่ายผลิตภัณฑ์นมชนิดต่าง ๆ ได้รวมกันทั้งสิ้น ปริมาณ ' . number_format($total, 2, '.', ',') . '  ลิตร ');



        $ckrow = 7 + $row;
        $row += 2;
//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A' . (7 + $row) . ':A' . (7 + $row + 1));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $this->getMonthshName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearTo'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . (7 + $row) . ':C' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $this->getMonthshName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 542) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearTo'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . (7 + $row) . ':E' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F' . (7 + $row) . ':G' . (7 + $row));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row + 1), '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row + 1), '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row + 1), '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row + 1))->getFont()->setBold(true);

        $strow = 7 + $row;
        $row++;
        $row++;
        $sumcurrent = 0;
        $sumbefore = 0;
        $sumpercurrent = 0;
        $sumperbefore = 0;
        $sumdiff = 0;
        $per = 0;
        $totalc = 0;
        $totalb = 0;

        foreach ($show['DataList'] as $showitem) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), $showitem['productname']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row))->getFont()->setBold(true);
            $row++;

            $percurrent = 0;
            $perbefore = 0;

            foreach ($showitem['item'] as $itemsub) {
                $sumcurrent += $itemsub['CurrentAmount'];
                $sumbefore += $itemsub['BeforeAmount'];
                $sumdiff += $itemsub['DiffAmount'];
                $totalc += $itemsub['CurrentAmount'];
                $totalb += $itemsub['BeforeAmount'];
                if ($show['Summary']['sumCurrentAmount'] != 0) {
                    $percurrent = ($itemsub['CurrentAmount'] * 100) / $show['Summary']['sumCurrentAmount'];
                }
                if ($show['Summary']['sumBeforeAmount'] != 0) {
                    $perbefore = ($itemsub['BeforeAmount'] * 100) / $show['Summary']['sumBeforeAmount'];
                }
                $sumpercurrent += $percurrent;
                $sumperbefore += $perbefore;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), $itemsub['subProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $itemsub['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), ($percurrent));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $itemsub['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), ($perbefore));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $itemsub['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $itemsub['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (7 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
            if ($sumbefore != 0) {
                $per += (($sumdiff) / $sumbefore) * 100;
            } else if (empty($sumbefore) && !empty($sumcurrent)) {
                $per = 100;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รวม');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $sumcurrent);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), $sumpercurrent);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $sumbefore);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), $sumperbefore);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $sumdiff);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $per);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                    ->getStyle('A' . (7 + $row))
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $row++;
        }
        $totalper = 100;
        if ($totalb != 0) {
            $totalper = (($totalc - $totalb) / $totalb) * 100;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), $totalc);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), 100);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $totalb);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), 100);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $totalc - $totalb);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $row), $totalper);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row) . ':G' . (7 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (7 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow), '                        เมื่อเปรียบเทียบกับเดือนเดียวกันกับปีก่อนซึ่งผลิตได้ ปริมาณ ' . number_format($totalb, 2, '.', ',') . '  ลิตร ปรากฏว่าการผลิต');

        if ($totalc - $totalb > 0) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow + 1), '                     เพิ่มขึ้น ปริมาณ ' . number_format($totalc - $totalb, 2, '.', ',') . '  ลิตร คิดเป็นร้อยละ ' . number_format($totalper));
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($ckrow + 1), '                     ลดลง ปริมาณ ' . number_format($totalc - $totalb, 2, '.', ',') . '  ลิตร คิดเป็นร้อยละ ' . number_format($totalper));
        }
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow) . ':G' . ($ckrow + 1))->getFont()->setSize(16);
        // $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow) . ':G' . ($ckrow + 1))->getFont()->setBold(true);
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
//
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A5:G11')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($ckrow + 2) . ':G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A' . ($strow) . ':G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        $objPHPExcel = $this->generatesaleproductMilk6Excel($objPHPExcel, $condition, $detail[0], $detail[1], $totalc, $totalb);
        $objPHPExcel = $this->generatesaleproductMilk7Excel($objPHPExcel, $condition, $detail[2], $detail[3], $totalc, $totalb);
        $objPHPExcel = $this->generatesaleproductMilk8Excel($objPHPExcel, $condition, $detail[4], $totalc, $totalb);

        return $objPHPExcel;
    }

    private function generatesaleproductMilk6Excel($objPHPExcel, $condition, $detail1, $detail2, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.2 จำหน่ายผลิตภัณฑ์นม (6)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', $this->getMonthshName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearTo'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', $this->getMonthshName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 542) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearTo'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);


        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail2['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;
        $sumtotalc2 = 0;
        $sumtotalb2 = 0;
        foreach ($detail2['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc2 += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb2 += $pb;
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }

        $difper2 = 0;
        if ($detail2['Summary']['SummaryBefore'] != 0) {
            $difper2 = (($detail2['Summary']['SummaryAmount'] - $detail2['Summary']['SummaryBefore']) * 100) / $detail2['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail2['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc2);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail2['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail2['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper2);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row++;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesaleproductMilk7Excel($objPHPExcel, $condition, $detail1, $detail2, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.2 จำหน่ายผลิตภัณฑ์นม (7)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', $this->getMonthshName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearTo'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', $this->getMonthshName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 542) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearTo'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);


        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail2['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;
        $sumtotalc2 = 0;
        $sumtotalb2 = 0;
        foreach ($detail2['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc2 += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb2 += $pb;
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }

        $difper2 = 0;
        if ($detail2['Summary']['SummaryBefore'] != 0) {
            $difper2 = (($detail2['Summary']['SummaryAmount'] - $detail2['Summary']['SummaryBefore']) * 100) / $detail2['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail2['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc2);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail2['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail2['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper2);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row++;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesaleproductMilk8Excel($objPHPExcel, $condition, $detail1, $totalc, $totalb) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        //   print_r($detail1);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->getActiveSheet()->setTitle("5.2 จำหน่ายผลิตภัณฑ์นม (8)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', $this->getMonthshName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearTo'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('D3', $this->getMonthshName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 542) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearTo'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A3:G4')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:G4')->getFont()->setBold(true);
        $row = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $detail1['DataList']['factory_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $row++;

        $sumtotalc = 0;
        $sumtotalb = 0;
        foreach ($detail1['DataList'] as $item1) {


            foreach ($item1 as $item2) {

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
                $perc = 0;
                $perb = 0;
                foreach ($item2['sub'] as $item3) {
                    $pc = 0;
                    $pb = 0;
                    if ($totalc != 0) {
                        $pc = $item3['CurrentAmount'] * 100 / floatval($totalc);

                        $perc += $pc;

                        $sumtotalc += $pc;
                    }
                    if ($totalb != 0) {

                        $pb = $item3['BeforeAmount'] * 100 / floatval($totalb);

                        $perb += $pb;

                        $sumtotalb += $pb;
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item3['subProductionInfoName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item3['CurrentAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $pc);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item3['BeforeAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $pb);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item3['DiffAmount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item3['DiffAmountPercentage']);
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวม - ' . $item2['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $item2['summary'][0]['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $perc);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $item2['summary'][0]['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $perb);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $item2['summary'][0]['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $item2['summary'][0]['DiffAmountPercentage']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . (5 + $row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                                )
                );
                $row++;
            }
        }
        $difper1 = 0;
        if ($detail1['Summary']['SummaryBefore'] != 0) {
            $difper1 = (($detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']) * 100) / $detail1['Summary']['SummaryBefore'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมทั้งสิ้น ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), $detail1['Summary']['SummaryAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $sumtotalc);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumtotalb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $detail1['Summary']['SummaryAmount'] - $detail1['Summary']['SummaryBefore']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $row), $difper1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (5 + $row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $row++;




        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        $objPHPExcel->getActiveSheet()->getStyle('B3:G' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $highestRow)->applyFromArray(
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

        $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatelostproductionExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $totalc = $_SESSION["totalc"];
        $totalb = $_SESSION["totalb"];
        $totalc2 = $_SESSION["totalc2"];
        $totalb2 = $_SESSION["totalb2"];
        session_destroy();


        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthFrom'] = 10;
            $condition['MonthTo'] = 12;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthFrom'] = 1;
            $condition['MonthTo'] = 3;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthFrom'] = 4;
            $condition['MonthTo'] = 6;
        } else {
            $condition['MonthFrom'] = 7;
            $condition['MonthTo'] = 9;
        }

        $FactoryList = FactoryService::getList();
        $size = sizeof($FactoryList);
        $detail = [];
        $detailIN = [];
        $detailOUT = [];
        $detailWAIT = [];
        $tcurrent = 0;
        $tbaht = 0;
        $tbcurrent = 0;
        $tbbaht = 0;
        $tdiffc = 0;
        $tdiffb = 0;
        $tpc = 0;
        $tpb = 0;
        $byfactory = [];
        foreach ($FactoryList as $item) {
            $condition['Factory'] = $item['id'];
            $dataIN = LostInProcessController::getMonthDataList($condition);
            $detailIN['name'] = ' ในกระบวนการผลิต';
            $detailIN['current'] += $dataIN['Summary']['SummaryAmount'];
            $detailIN['before'] += $dataIN['Summary']['SummaryBeforeAmount'];
            $detailIN['currentbaht'] += $dataIN['Summary']['SummaryBaht'];
            $detailIN['beforebaht'] += $dataIN['Summary']['SummaryBeforeBaht'];

            $dataOUT = LostOutProcessController::getMonthDataList($condition);
            $detailOUT['name'] = ' หลังกระบวนการผลิต';
            $detailOUT['current'] += $dataOUT['Summary']['SummaryAmount'];
            $detailOUT['before'] += $dataOUT['Summary']['SummaryBeforeAmount'];
            $detailOUT['currentbaht'] += $dataOUT['Summary']['SummaryBaht'];
            $detailOUT['beforebaht'] += $dataOUT['Summary']['SummaryBeforeBaht'];

            $dataWAIT = LostWaitSaleController::getMonthDataList($condition);
            $detailWAIT['name'] = ' ระหว่างรอจำหน่าย';
            $detailWAIT['current'] += $dataWAIT['Summary']['SummaryAmount'];
            $detailWAIT['before'] += $dataWAIT['Summary']['SummaryBeforeAmount'];
            $detailWAIT['currentbaht'] += $dataWAIT['Summary']['SummaryBaht'];
            $detailWAIT['beforebaht'] += $dataWAIT['Summary']['SummaryBeforeBaht'];
            $_data['name'] = $item['factory_name'];
            $_data['amount'] = $dataIN['Summary']['SummaryAmount'] + $dataOUT['Summary']['SummaryAmount'] + $dataWAIT['Summary']['SummaryAmount'];
            $_data['baht'] = $dataIN['Summary']['SummaryBaht'] + $dataOUT['Summary']['SummaryBaht'] + $dataWAIT['Summary']['SummaryBaht'];
            array_push($byfactory, $_data);
        }
        //////
        $detailIN['diffamount'] = $detailIN['current'] - $detailIN['before'];
        $detailIN['diffbaht'] = $detailIN['currentbaht'] - $detailIN['beforebaht'];
        if ($detailIN['before'] != 0) {
            $detailIN['peramount'] = (($detailIN['diffamount']) / $detailIN['before']) * 100;
        } else {
            $detailIN['peramount'] = 0;
        }
        if ($detailIN['beforebaht'] != 0) {
            $detailIN['perbaht'] = (($detailIN['diffbaht']) / $detailIN['beforebaht']) * 100;
        } else {
            $detailIN['perbaht'] = 0;
        }
        ///////
        $detailWAIT['diffamount'] = $detailWAIT['current'] - $detailWAIT['before'];
        $detailWAIT['diffbaht'] = $detailWAIT['currentbaht'] - $detailWAIT['beforebaht'];
        if ($detailWAIT['before'] != 0) {
            $detailWAIT['peramount'] = (($detailWAIT['diffamount']) / $detailWAIT['before']) * 100;
        } else {
            $detailWAIT['peramount'] = 0;
        }
        if ($detailWAIT['beforebaht'] != 0) {
            $detailWAIT['perbaht'] = (($detailWAIT['diffbaht']) / $detailWAIT['beforebaht']) * 100;
        } else {
            $detailWAIT['perbaht'] = 0;
        }
        ///////
        $detailOUT['diffamount'] = $detailOUT['current'] - $detailOUT['before'];
        $detailOUT['diffbaht'] = $detailOUT['currentbaht'] - $detailOUT['beforebaht'];
        if ($detailOUT['before'] != 0) {
            $detailOUT['peramount'] = (($detailOUT['diffamount']) / $detailOUT['before']) * 100;
        } else {
            $detailOUT['peramount'] = 0;
        }
        if ($detailOUT['beforebaht'] != 0) {
            $detailOUT['perbaht'] = (($detailOUT['diffbaht']) / $detailOUT['beforebaht']) * 100;
        } else {
            $detailOUT['perbaht'] = 0;
        }
        array_push($detail, $detailIN);
        array_push($detail, $detailOUT);
        array_push($detail, $detailWAIT);
        ///////
        $objPHPExcel->getActiveSheet()->setTitle(" 5.3 การสูญเสียทั้งกระบวนการ");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '  5.3 การสูญเสียทั้งกระบวนการผลิต');





//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $size), 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A' . (7 + $size) . ':A' . (7 + $size + 1));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $size), 'ไตรมาสที่  ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . (7 + $size) . ':C' . (7 + $size));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $size), 'ไตรมาสที่  ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . (7 + $size) . ':E' . (7 + $size));
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $size), 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F' . (7 + $size) . ':I' . (7 + $size));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $size + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $size + 1), 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $size + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $size + 1), 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $size + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $size + 1), '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (7 + $size + 1), 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (7 + $size + 1), '% เพิ่ม,ลด');

        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $size) . ':I' . (7 + $size + 1))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . (7 + $size) . ':I' . (7 + $size + 1))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row = 7 + $size + 2;

        foreach ($detail as $ditem) {
            $tcurrent += $ditem['current'];
            $tbaht += $ditem['currentbaht'];
            $tbcurrent += $ditem['before'];
            $tbbaht += $ditem['beforebaht'];
            $tdiffc += $ditem['diffamount'];
            $tdiffb += $ditem['diffbaht'];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $ditem['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $ditem['current']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $ditem['currentbaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $ditem['before']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $ditem['beforebaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $ditem['diffamount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $ditem['peramount']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $ditem['diffbaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $ditem['perbaht']);
            $row++;
        }
        if ($tbcurrent != 0) {
            $tpc = (($tdiffc) / $tbcurrent) * 100;
        } else {
            $tpc = 0;
        }
        if ($tbbaht != 0) {
            $tpb = (($tdiffb) / $tbbaht) * 100;
        } else {
            $tpb = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $tbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $tbcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $tbbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $tdiffc);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $tpc);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $tdiffb);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $tpb);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ( $row), '% สูญเสีย');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tcurrent * 100 / $totalc);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ( $row), $tbcurrent * 100 / $totalb);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), '');
        $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('B4', '                        ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543) . ' อ.ส.ค.มีการสูญเสียทั้งกระบวนการผลิตทั้งสิ้น ปริมาณ  ' . number_format($tcurrent, 2, '.', ',') . '  ลิตร ');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', '                  มูลค่า ' . number_format($tbaht, 2, '.', ',') . ' คิดเป็นร้อยละ  ' . number_format($data['Summary']['SummaryTravelIncomePercentage'], 2, '.', ',') . ' แบ่งเป็น ' . sizeof($FactoryList) . ' โรงงาน ดังนี้');
        $st = 6;
        foreach ($byfactory as $key => $title) {
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $st, '             ' . ($key + 1) . '.' . $title['name'] . ' ปริมาณ ' . number_format($title['amount'], 2, '.', ',') . ' ลิตร มูลค่า  ' . number_format($title['baht'], 2, '.', ',') . ' บาท ');
            $st++;
        }
// // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('B' . (7 + $size) . ':I' . ($highestRow))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



        $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $size) . ':I' . $highestRow)->applyFromArray(
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
        $tb2row = $highestRow + 2;
        //////////table 2
//tb2 header
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($tb2row + 3 + $size), 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($tb2row + 3 + $size) . ':A' . ($tb2row + 3 + $size + 1));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($tb2row + 3 + $size), ' ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . ($tb2row + 3 + $size) . ':C' . ($tb2row + 3 + $size));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($tb2row + 3 + $size), ' ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . ($tb2row + 3 + $size) . ':E' . ($tb2row + 3 + $size));
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($tb2row + 3 + $size), 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F' . ($tb2row + 3 + $size) . ':I' . ($tb2row + 3 + $size));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($tb2row + 3 + $size + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($tb2row + 3 + $size + 1), 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($tb2row + 3 + $size + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($tb2row + 3 + $size + 1), 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($tb2row + 3 + $size + 1), 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($tb2row + 3 + $size + 1), '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($tb2row + 3 + $size + 1), 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($tb2row + 3 + $size + 1), '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($tb2row + 3 + $size) . ':I' . ($tb2row + 3 + $size + 1))->getFont()->setSize(16);

        $objPHPExcel->getActiveSheet()->getStyle('A' . ($tb2row + 3 + $size) . ':I' . ($tb2row + 3 + $size + 1))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . ($tb2row + 3 + $size) . ':I' . ($tb2row + 3 + $size + 1))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );



        $tcurrent = 0;
        $tbaht = 0;
        $tbcurrent = 0;
        $tbbaht = 0;
        $tdiffc = 0;
        $tdiffb = 0;
        $tpc = 0;
        $tpb = 0;
        $detailIN = [];
        $detailOUT = [];
        $detailWAIT = [];
        $detail = [];
        $Mlist = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $Ylist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $exit = $condition['MonthTo'];

        $byfactory = [];


        foreach ($FactoryList as $itemtb2) {
            $collect['MonthFrom'] = $condition['MonthTo'];
            $collect['YearTo'] = $condition['YearFrom'];
            foreach ($Mlist as $k => $m) {
                $collect['MonthFrom'] = $m;
                $collect['YearTo'] = $condition['YearFrom'] - $Ylist[$k];

                $collect['Factory'] = $itemtb2['id'];
                $dataIN = LostInProcessController::getMonthDataList($collect);
                //  print_r($dataIN);
                $detailIN['name'] = ' ในกระบวนการผลิต';
                $detailIN['current'] += $dataIN['Summary']['SummaryAmount'];
                $detailIN['before'] += $dataIN['Summary']['SummaryBeforeAmount'];
                $detailIN['currentbaht'] += $dataIN['Summary']['SummaryBaht'];
                $detailIN['beforebaht'] += $dataIN['Summary']['SummaryBeforeBaht'];

                $dataOUT = LostOutProcessController::getMonthDataList($collect);
                $detailOUT['name'] = ' หลังกระบวนการผลิต';
                $detailOUT['current'] += $dataOUT['Summary']['SummaryAmount'];
                $detailOUT['before'] += $dataOUT['Summary']['SummaryBeforeAmount'];
                $detailOUT['currentbaht'] += $dataOUT['Summary']['SummaryBaht'];
                $detailOUT['beforebaht'] += $dataOUT['Summary']['SummaryBeforeBaht'];

                $dataWAIT = LostWaitSaleController::getMonthDataList($collect);
                $detailWAIT['name'] = ' ระหว่างรอจำหน่าย';
                $detailWAIT['current'] += $dataWAIT['Summary']['SummaryAmount'];
                $detailWAIT['before'] += $dataWAIT['Summary']['SummaryBeforeAmount'];
                $detailWAIT['currentbaht'] += $dataWAIT['Summary']['SummaryBaht'];
                $detailWAIT['beforebaht'] += $dataWAIT['Summary']['SummaryBeforeBaht'];

                if ($exit == $m) {
                    break;
                }
            }
            $_data['name'] = $itemtb2['factory_name'];
            $_data['amount'] = $dataIN['Summary']['SummaryAmount'] + $dataOUT['Summary']['SummaryAmount'] + $dataWAIT['Summary']['SummaryAmount'];
            $_data['baht'] = $dataIN['Summary']['SummaryBaht'] + $dataOUT['Summary']['SummaryBaht'] + $dataWAIT['Summary']['SummaryBaht'];
            array_push($byfactory, $_data);
        }

        $detailIN['diffamount'] = $detailIN['current'] - $detailIN['before'];
        $detailIN['diffbaht'] = $detailIN['currentbaht'] - $detailIN['beforebaht'];
        if ($detailIN['before'] != 0) {
            $detailIN['peramount'] = (($detailIN['diffamount']) / $detailIN['before']) * 100;
        } else {
            $detailIN['peramount'] = 0;
        }
        if ($detailIN['beforebaht'] != 0) {
            $detailIN['perbaht'] = (($detailIN['diffbaht']) / $detailIN['beforebaht']) * 100;
        } else {
            $detailIN['perbaht'] = 0;
        }
        ///////
        $detailWAIT['diffamount'] = $detailWAIT['current'] - $detailWAIT['before'];
        $detailWAIT['diffbaht'] = $detailWAIT['currentbaht'] - $detailWAIT['beforebaht'];
        if ($detailWAIT['before'] != 0) {
            $detailWAIT['peramount'] = (($detailWAIT['diffamount']) / $detailWAIT['before']) * 100;
        } else {
            $detailWAIT['peramount'] = 0;
        }
        if ($detailWAIT['beforebaht'] != 0) {
            $detailWAIT['perbaht'] = (($detailWAIT['diffbaht']) / $detailWAIT['beforebaht']) * 100;
        } else {
            $detailWAIT['perbaht'] = 0;
        }
        ///////
        $detailOUT['diffamount'] = $detailOUT['current'] - $detailOUT['before'];
        $detailOUT['diffbaht'] = $detailOUT['currentbaht'] - $detailOUT['beforebaht'];
        if ($detailOUT['before'] != 0) {
            $detailOUT['peramount'] = (($detailOUT['diffamount']) / $detailOUT['before']) * 100;
        } else {
            $detailOUT['peramount'] = 0;
        }
        if ($detailOUT['beforebaht'] != 0) {
            $detailOUT['perbaht'] = (($detailOUT['diffbaht']) / $detailOUT['beforebaht']) * 100;
        } else {
            $detailOUT['perbaht'] = 0;
        }
        array_push($detail, $detailIN);
        array_push($detail, $detailOUT);
        array_push($detail, $detailWAIT);

        $row = $tb2row + $size + 5;
        foreach ($detail as $ditem) {
            $tcurrent += $ditem['current'];
            $tbaht += $ditem['currentbaht'];
            $tbcurrent += $ditem['before'];
            $tbbaht += $ditem['beforebaht'];
            $tdiffc += $ditem['diffamount'];
            $tdiffb += $ditem['diffbaht'];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $ditem['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $ditem['current']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $ditem['currentbaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $ditem['before']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $ditem['beforebaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $ditem['diffamount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $ditem['peramount']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $ditem['diffbaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $ditem['perbaht']);
            $row++;
        }
        if ($tbcurrent != 0) {
            $tpc = (($tdiffc) / $tbcurrent) * 100;
        } else {
            $tpc = 0;
        }
        if ($tbbaht != 0) {
            $tpb = (($tdiffb) / $tbbaht) * 100;
        } else {
            $tpb = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $tbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $tbcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $tbbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $tdiffc);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $tpc);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $tdiffb);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $tpb);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row . ':I' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ( $row), '% สูญเสีย');

        if ($totalc2 != 0) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tcurrent * 100 / $totalc2);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), 0);
        }
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), '');
        if ($totalb2 != 0) {
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $tbcurrent * 100 / $totalb2);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), 0);
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), '');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), '');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row . ':I' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($tb2row), '                        เดือน ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 543) . ' อ.ส.ค.มีการสูญเสียทั้งกระบวนการผลิตทั้งสิ้น ปริมาณ  ' . number_format($tcurrent, 2, '.', ',') . '  ลิตร ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($tb2row + 1), '                  มูลค่า ' . number_format($tbaht, 2, '.', ',') . ' คิดเป็นร้อยละ  ' . number_format($data['Summary']['SummaryTravelIncomePercentage'], 2, '.', ',') . ' แบ่งเป็น ' . sizeof($FactoryList) . ' โรงงาน ดังนี้');
        $st = $tb2row + 2;
        foreach ($byfactory as $key => $title) {
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $st, '             ' . ($key + 1) . '.' . $title['name'] . ' ปริมาณ ' . number_format($title['amount'], 2, '.', ',') . ' ลิตร มูลค่า  ' . number_format($title['baht'], 2, '.', ',') . ' บาท ');
            $st++;
        }
        $highestRow2 = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($tb2row + $size + 4) . ':I' . ($highestRow2))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



        $objPHPExcel->getActiveSheet()->getStyle('A' . ($tb2row + $size + 3) . ':I' . $highestRow2)->applyFromArray(
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
///////////////////////
        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow2)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generatelostINproductionExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->setTitle('สูญเสียใน (1)');
        $FactoryList = FactoryService::getList();
        $data = [];
        $datacollect = [];
        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthFrom'] = 10;
            $condition['MonthTo'] = 12;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthFrom'] = 1;
            $condition['MonthTo'] = 3;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthFrom'] = 4;
            $condition['MonthTo'] = 6;
        } else {
            $condition['MonthFrom'] = 7;
            $condition['MonthTo'] = 9;
        }
        foreach ($FactoryList as $key => $fac) {
            $condition['Factory'] = $fac['id'];
            $dataIN = LostInProcessController::getMonthDataList($condition);
            $dataIN['name'] = $fac['factory_name'];
            array_push($data, $dataIN);
            $datacollectIN = LostInProcessController::getMonthDataListreport($condition);
            array_push($datacollect, $datacollectIN);
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A3', '  5.3.1 สูญเสียในกระบวนการผลิต');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', $data[0]['name']);
        $objPHPExcel->getActiveSheet()->setCellValue('C4', 'หน่วย : ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', 'รายการ');

        $objPHPExcel->getActiveSheet()->setCellValue('B5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

        $objPHPExcel->getActiveSheet()->setCellValue('C5', ' ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 543));
//        print_r($data[0]['DataList']);

        $row = 6;
        $sum = 0;
        $size = sizeof($data[0]['DataList']);
        foreach ($datacollect[0]['DataList'] as $key => $item) {
            $sum += $item['CurrentAmount'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item['LostInProcessName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $data[0]['DataList'][$key]['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item['CurrentAmount']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $data[0]['DataList'][$size - 1]['CurrentAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item['CurrentAmount']);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A4:C5')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4:C5')->getFont()->setBold(true);



        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('B6:C' . ($row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()
                ->getStyle('A5:C5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A5:C' . $row)->applyFromArray(
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
///////////////////////
        $objPHPExcel->getActiveSheet()->getStyle('A1:C' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        $objPHPExcel = $this->generatelostINproductionExcel2($objPHPExcel, $condition, $data, $datacollect);
        $objPHPExcel = $this->generatelostINproductionExcel3($objPHPExcel, $condition, $data, $datacollect);
        return $objPHPExcel;
    }

    private function generatelostINproductionExcel2($objPHPExcel, $condition, $data1, $data2) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->setTitle('สูญเสียใน (2)');
        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthFrom'] = 10;
            $condition['MonthTo'] = 12;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthFrom'] = 1;
            $condition['MonthTo'] = 3;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthFrom'] = 4;
            $condition['MonthTo'] = 6;
        } else {
            $condition['MonthFrom'] = 7;
            $condition['MonthTo'] = 9;
        }


        $objPHPExcel->getActiveSheet()->setCellValue('A3', '  5.3.1 สูญเสียในกระบวนการผลิต');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', $data1[1]['name']);
        $objPHPExcel->getActiveSheet()->setCellValue('C4', 'หน่วย : ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', 'รายการ');

        $objPHPExcel->getActiveSheet()->setCellValue('B5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

        $objPHPExcel->getActiveSheet()->setCellValue('C5', ' ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 543));


        $row = 6;
        $sum = 0;
        $size = sizeof($data1);
        foreach ($data2[1]['DataList'] as $key => $item) {
            $sum += $item['CurrentAmount'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item['LostInProcessName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $data1[1]['DataList'][$key]['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item['CurrentAmount']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $data1[1]['DataList'][$size - 1]['CurrentAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item['CurrentAmount']);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A4:C5')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4:C5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B6:C' . ($row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()
                ->getStyle('A5:C5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A5:C' . ($row - 1))->applyFromArray(
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

        /////table2
        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data1[2]['name']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), 'หน่วย : ลิตร');
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':C' . ($row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':C' . ($row))->getFont()->setBold(true);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รายการ');

        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), ' ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':C' . $row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row . ':C' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $st = $row;
        $row++;


        $sum = 0;

        foreach ($data2[2]['DataList'] as $key => $item) {
            $sum += $item['CurrentAmount'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item['LostInProcessName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $data1[2]['DataList'][$key]['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item['CurrentAmount']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $data1[2]['DataList'][$size - 1]['CurrentAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item['CurrentAmount']);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;




        $objPHPExcel->getActiveSheet()->getStyle('A4:C5')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4:C5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $st . ':C' . ($row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



        $objPHPExcel->getActiveSheet()->getStyle('A' . $st . ':C' . ($row - 1))->applyFromArray(
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


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);

///////////////////////
        $objPHPExcel->getActiveSheet()->getStyle('A1:C' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatelostINproductionExcel3($objPHPExcel, $condition, $data1, $data2) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->setTitle('สูญเสียใน (3)');
        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthFrom'] = 10;
            $condition['MonthTo'] = 12;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthFrom'] = 1;
            $condition['MonthTo'] = 3;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthFrom'] = 4;
            $condition['MonthTo'] = 6;
        } else {
            $condition['MonthFrom'] = 7;
            $condition['MonthTo'] = 9;
        }


        $objPHPExcel->getActiveSheet()->setCellValue('A3', '  5.3.1 สูญเสียในกระบวนการผลิต');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', $data1[3]['name']);
        $objPHPExcel->getActiveSheet()->setCellValue('C4', 'หน่วย : ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', 'รายการ');

        $objPHPExcel->getActiveSheet()->setCellValue('B5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

        $objPHPExcel->getActiveSheet()->setCellValue('C5', ' ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 543));


        $row = 6;
        $sum = 0;
        $size = sizeof($data1);
        foreach ($data2[3]['DataList'] as $key => $item) {
            $sum += $item['CurrentAmount'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item['LostInProcessName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $data1[3]['DataList'][$key]['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item['CurrentAmount']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $data1[3]['DataList'][$size - 1]['CurrentAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item['CurrentAmount']);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A4:C5')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4:C5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B6:C' . ($row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()
                ->getStyle('A5:C5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A5:C' . ($row - 1))->applyFromArray(
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

        /////table2
        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data1[4]['name']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), 'หน่วย : ลิตร');
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':C' . ($row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':C' . ($row))->getFont()->setBold(true);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รายการ');

        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), ' ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':C' . $row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row . ':C' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $st = $row;
        $row++;


        $sum = 0;

        foreach ($data2[4]['DataList'] as $key => $item) {
            $sum += $item['CurrentAmount'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item['LostInProcessName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $data1[4]['DataList'][$key]['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item['CurrentAmount']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $data1[4]['DataList'][$size - 1]['CurrentAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $item['CurrentAmount']);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;




        $objPHPExcel->getActiveSheet()->getStyle('A4:C5')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4:C5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $st . ':C' . ($row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



        $objPHPExcel->getActiveSheet()->getStyle('A' . $st . ':C' . ($row - 1))->applyFromArray(
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


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);

///////////////////////
        $objPHPExcel->getActiveSheet()->getStyle('A1:C' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatelostOUTproductionExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthFrom'] = 10;
            $condition['MonthTo'] = 12;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthFrom'] = 1;
            $condition['MonthTo'] = 3;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthFrom'] = 4;
            $condition['MonthTo'] = 6;
        } else {
            $condition['MonthFrom'] = 7;
            $condition['MonthTo'] = 9;
        }
        $FactoryList = FactoryService::getList();
        $data = [];

        foreach ($FactoryList as $key => $fac) {
            $condition['Factory'] = $fac['id'];
            $dataOUT = LostOutProcessController::getMonthDataList($condition);
            $dataOUT['name'] = $fac['factory_name'];
            array_push($data, $dataOUT);
//            $datacollectIN = LostInProcessController::getMonthDataListreport($condition);
//            array_push($datacollect, $datacollectIN);
        }
        $tcurrent = 0;
        $tbaht = 0;
        $tbcurrent = 0;
        $tbbaht = 0;
        $tdiffc = 0;
        $tdiffb = 0;
        $tpc = 0;
        $tpb = 0;



        $objPHPExcel->getActiveSheet()->setTitle('สูญเสียหลัง (1)');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '  5.3.2 สูญเสียหลังกระบวนการผลิต');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', ' ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B4:C4');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', ' ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D4:E4');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F4:I4');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G5', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I5', '% เพิ่ม,ลด');
        $row = 6;
        foreach ($data as $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item['name']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setSize(16);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                    ->getStyle('A' . $row)
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $row++;
            foreach ($item['DataList'] as $value) {
                $pera = 0;
                $perb = 0;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $value['LostOutProcessName']);

                if ($value['Month'] == 'รวม') {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวม');
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setSize(14);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $row)
                            ->applyFromArray(array(
                                'alignment' => array(
                                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                )
                                    )
                    );
                    $tcurrent += $value['CurrentAmount'];
                    $tbaht += $value['CurrentBaht'];
                    $tbcurrent += $value['BeforeAmount'];
                    $tbbaht += $value['BeforeBaht'];
                    $tdiffc += $tcurrent - $tbcurrent;
                    $tdiffb += $tbaht - $tbbaht;
                }

                if ($value['BeforeAmount'] != 0) {
                    $pera = (($value['DiffAmount']) / $value['BeforeAmount']) * 100;
                } else {
                    $pera = 0;
                }
                if ($value['BeforeBaht'] != 0) {
                    $perb = (($value['DiffBaht']) / $value['BeforeBaht']) * 100;
                } else {
                    $perb = 0;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $value['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $value['CurrentBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $value['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $value['BeforeBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $value['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $pera);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $value['DiffBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $perb);
                $row++;
            }
        }
        if ($tbcurrent != 0) {
            $tpc = (($tdiffc) / $tbcurrent) * 100;
        } else {
            $tpc = 0;
        }
        if ($tbbaht != 0) {
            $tpb = (($tdiffb) / $tbbaht) * 100;
        } else {
            $tpb = 0;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $tcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $tbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tbcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tbbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tdiffc);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $pera);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tdiffb);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $perb);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:I5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A4:I5')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4:I5')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('B6:I' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



        $objPHPExcel->getActiveSheet()->getStyle('A4:I' . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );


        return $objPHPExcel;
    }

    private function generatelostOUTproductionExcel2($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthFrom'] = 10;
            $condition['MonthTo'] = 12;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthFrom'] = 1;
            $condition['MonthTo'] = 3;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthFrom'] = 4;
            $condition['MonthTo'] = 6;
        } else {
            $condition['MonthFrom'] = 7;
            $condition['MonthTo'] = 9;
        }
        $FactoryList = FactoryService::getList();
        $data = [];

        foreach ($FactoryList as $key => $fac) {
            $condition['Factory'] = $fac['id'];
            $dataOUT = LostOutProcessController::getMonthDataListreport($condition);
            $dataOUT['name'] = $fac['factory_name'];
            array_push($data, $dataOUT);
//            $datacollectIN = LostInProcessController::getMonthDataListreport($condition);
//            array_push($datacollect, $datacollectIN);
        }
        $tcurrent = 0;
        $tbaht = 0;
        $tbcurrent = 0;
        $tbbaht = 0;
        $tdiffc = 0;
        $tdiffb = 0;
        $tpc = 0;
        $tpb = 0;



        $objPHPExcel->getActiveSheet()->setTitle('สูญเสียหลัง (2)');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '  5.3.2 สูญเสียหลังกระบวนการผลิต');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', ' ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B4:C4');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', ' ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D4:E4');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F4:I4');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G5', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I5', '% เพิ่ม,ลด');
        $row = 6;
        foreach ($data as $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item['name']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setSize(16);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                    ->getStyle('A' . $row)
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $row++;
            foreach ($item['DataList'] as $value) {
                $pera = 0;
                $perb = 0;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $value['LostOutProcessName']);

                if ($value['Month'] == 'รวม') {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวม');
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setSize(14);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $row)
                            ->applyFromArray(array(
                                'alignment' => array(
                                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                )
                                    )
                    );
                    $tcurrent += $value['CurrentAmount'];
                    $tbaht += $value['CurrentBaht'];
                    $tbcurrent += $value['BeforeAmount'];
                    $tbbaht += $value['BeforeBaht'];
                }

                if ($value['BeforeAmount'] != 0) {
                    $pera = (($value['DiffAmount']) / $value['BeforeAmount']) * 100;
                } else {
                    $pera = 0;
                }
                if ($value['BeforeBaht'] != 0) {
                    $perb = (($value['DiffBaht']) / $value['BeforeBaht']) * 100;
                } else {
                    $perb = 0;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $value['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $value['CurrentBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $value['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $value['BeforeBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $value['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $pera);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $value['DiffBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $perb);
                $row++;
            }
        }
        $tdiffc = $tcurrent - $tbcurrent;
        $tdiffb = $tbaht - $tbbaht;
        if ($tbcurrent != 0) {
            $tpc = (($tdiffc) / $tbcurrent) * 100;
        } else {
            $tpc = 0;
        }
        if ($tbbaht != 0) {
            $tpb = (($tdiffb) / $tbbaht) * 100;
        } else {
            $tpb = 0;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $tcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $tbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tbcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tbbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tdiffc);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $pera);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tdiffb);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $perb);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:I5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A4:I5')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4:I5')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('B6:I' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



        $objPHPExcel->getActiveSheet()->getStyle('A4:I' . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );


        return $objPHPExcel;
    }

    private function generatelostWAITproductionExcel($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);
        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthFrom'] = 10;
            $condition['MonthTo'] = 12;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthFrom'] = 1;
            $condition['MonthTo'] = 3;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthFrom'] = 4;
            $condition['MonthTo'] = 6;
        } else {
            $condition['MonthFrom'] = 7;
            $condition['MonthTo'] = 9;
        }
        $FactoryList = FactoryService::getList();
        $data = [];

        foreach ($FactoryList as $key => $fac) {
            $condition['Factory'] = $fac['id'];
            $dataOUT = LostWaitSaleController::getMonthDataList($condition);
            $dataOUT['name'] = $fac['factory_name'];
            array_push($data, $dataOUT);
//            $datacollectIN = LostInProcessController::getMonthDataListreport($condition);
//            array_push($datacollect, $datacollectIN);
        }
        $tcurrent = 0;
        $tbaht = 0;
        $tbcurrent = 0;
        $tbbaht = 0;
        $tdiffc = 0;
        $tdiffb = 0;
        $tpc = 0;
        $tpb = 0;



        $objPHPExcel->getActiveSheet()->setTitle('สูญเสียรอ (1)');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '   5.3.3 สูญเสียระหว่างรอจำหน่าย');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', ' ไตรมาสที่ ' . $condition['QuarterFrom'] . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B4:C4');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', ' ไตรมาสที่ ' . $condition['QuarterFrom'] . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D4:E4');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F4:I4');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G5', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I5', '% เพิ่ม,ลด');
        $row = 6;
        foreach ($data as $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item['name']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setSize(16);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                    ->getStyle('A' . $row)
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $row++;
            foreach ($item['DataList'] as $value) {
                $pera = 0;
                $perb = 0;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $value['LostOutProcessName']);

                if ($value['Month'] == 'รวม') {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวม');
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setSize(14);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $row)
                            ->applyFromArray(array(
                                'alignment' => array(
                                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                )
                                    )
                    );
                    $tcurrent += $value['CurrentAmount'];
                    $tbaht += $value['CurrentBaht'];
                    $tbcurrent += $value['BeforeAmount'];
                    $tbbaht += $value['BeforeBaht'];
                    $tdiffc += $tcurrent - $tbcurrent;
                    $tdiffb += $tbaht - $tbbaht;
                }

                if ($value['BeforeAmount'] != 0) {
                    $pera = (($value['DiffAmount']) / $value['BeforeAmount']) * 100;
                } else {
                    $pera = 0;
                }
                if ($value['BeforeBaht'] != 0) {
                    $perb = (($value['DiffBaht']) / $value['BeforeBaht']) * 100;
                } else {
                    $perb = 0;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $value['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $value['CurrentBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $value['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $value['BeforeBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $value['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $pera);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $value['DiffBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $perb);
                $row++;
            }
        }
        if ($tbcurrent != 0) {
            $tpc = (($tdiffc) / $tbcurrent) * 100;
        } else {
            $tpc = 0;
        }
        if ($tbbaht != 0) {
            $tpb = (($tdiffb) / $tbbaht) * 100;
        } else {
            $tpb = 0;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $tcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $tbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tbcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tbbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tdiffc);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $pera);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tdiffb);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $perb);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:I5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A4:I5')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4:I5')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('B6:I' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



        $objPHPExcel->getActiveSheet()->getStyle('A4:I' . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );


        return $objPHPExcel;
    }

    private function generatelostWAITproductionExcel2($objPHPExcel, $condition, $region) {
        $sheet = $objPHPExcel->getSheetCount();
        $objPHPExcel->createSheet($sheet);
        $objPHPExcel->setActiveSheetIndex($sheet);

        $FactoryList = FactoryService::getList();
        $data = [];
        if ($condition['QuarterFrom'] == 1) {
            $condition['MonthFrom'] = 10;
            $condition['MonthTo'] = 12;
        } else if ($condition['QuarterFrom'] == 2) {
            $condition['MonthFrom'] = 1;
            $condition['MonthTo'] = 3;
        } else if ($condition['QuarterFrom'] == 3) {
            $condition['MonthFrom'] = 4;
            $condition['MonthTo'] = 6;
        } else {
            $condition['MonthFrom'] = 7;
            $condition['MonthTo'] = 9;
        }
        foreach ($FactoryList as $key => $fac) {
            $condition['Factory'] = $fac['id'];
            $dataOUT = LostWaitSaleController::getMonthDataListreport($condition);
            $dataOUT['name'] = $fac['factory_name'];
            array_push($data, $dataOUT);
//            $datacollectIN = LostInProcessController::getMonthDataListreport($condition);
//            array_push($datacollect, $datacollectIN);
        }
        $tcurrent = 0;
        $tbaht = 0;
        $tbcurrent = 0;
        $tbbaht = 0;
        $tdiffc = 0;
        $tdiffb = 0;
        $tpc = 0;
        $tpb = 0;



        $objPHPExcel->getActiveSheet()->setTitle('สูญเสียรอ (2)');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '   5.3.3 สูญเสียระหว่างรอจำหน่าย');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', ' ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B4:C4');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', ' ' . $this->getMonthshName(10) . ' - ' . $this->getMonthshName($condition['MonthTo']) . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D4:E4');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F4:I4');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('C5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('E5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F5', 'ลิตร');
        $objPHPExcel->getActiveSheet()->setCellValue('G5', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H5', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I5', '% เพิ่ม,ลด');
        $row = 6;
        foreach ($data as $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $item['name']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setSize(16);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                    ->getStyle('A' . $row)
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $row++;
            foreach ($item['DataList'] as $value) {
                $pera = 0;
                $perb = 0;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $value['LostOutProcessName']);

                if ($value['Month'] == 'รวม') {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวม');
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setSize(14);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $row)
                            ->applyFromArray(array(
                                'alignment' => array(
                                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                                )
                                    )
                    );
                    $tcurrent += $value['CurrentAmount'];
                    $tbaht += $value['CurrentBaht'];
                    $tbcurrent += $value['BeforeAmount'];
                    $tbbaht += $value['BeforeBaht'];
                }

                if ($value['BeforeAmount'] != 0) {
                    $pera = (($value['DiffAmount']) / $value['BeforeAmount']) * 100;
                } else {
                    $pera = 0;
                }
                if ($value['BeforeBaht'] != 0) {
                    $perb = (($value['DiffBaht']) / $value['BeforeBaht']) * 100;
                } else {
                    $perb = 0;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $value['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $value['CurrentBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $value['BeforeAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $value['BeforeBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $value['DiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $pera);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $value['DiffBaht']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $perb);
                $row++;
            }
        }
        $tdiffc = $tcurrent - $tbcurrent;
        $tdiffb = $tbaht - $tbbaht;
        if ($tbcurrent != 0) {
            $tpc = (($tdiffc) / $tbcurrent) * 100;
        } else {
            $tpc = 0;
        }
        if ($tbbaht != 0) {
            $tpb = (($tdiffb) / $tbbaht) * 100;
        } else {
            $tpb = 0;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $tcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $tbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tbcurrent);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tbbaht);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tdiffc);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $pera);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tdiffb);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $perb);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $row)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:I5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A4:I5')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A4:I5')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('B6:I' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



        $objPHPExcel->getActiveSheet()->getStyle('A4:I' . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . $highestRow)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC',
                    )
                )
        );


        return $objPHPExcel;
    }

}
