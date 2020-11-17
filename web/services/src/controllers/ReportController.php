<?php

namespace App\Controller;

use App\Service\FoodService;
use App\Service\MasterGoalService;
use App\Service\SpermService;
use App\Service\GoalMissionService;
use App\Service\TravelService;
use App\Service\CowBreedService;
use App\Service\CowGroupService;
use App\Service\CowGroupFatherService;
use App\Service\TrainingCowBreedService;
use App\Service\InseminationService;
use App\Service\VeterinaryService;
use PHPExcel;

class ReportController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8];
    public $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0];

    public function exportReport($request, $response, $args) {
        try {
            $params = $request->getParsedBody();
            $actives = $params['obj']['actives'];
            $_List = FoodService::getList($actives);

            $this->data_result['DATA']['List'] = $_List;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function exportVeterinaryExcel($request, $response) {
        // error_reporting(E_ERROR);
        // error_reporting(E_ALL);
        // ini_set('display_errors','On');      
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        try {
            $obj = $request->getParsedBody();

            $condition = $obj['obj']['condition'];
            $cooperative = $obj['obj']['CooperativeList'];
            $data = $obj['obj']['DetailList'];
            $description = $obj['obj']['data_description'];
            //$summary = $obj['summary'];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();
            $commentarr = [];

            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ปี ' . ($condition['YearFrom'] + 543);
                    foreach ($monthList as $key => $m) {
                        $comment = VeterinaryService::getcomment($data['Description']['years'] - $yearList[$key], $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $data, $cooperative, $header, $commentarr);
                    break;
                case 'monthly' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม เดือน ' . $this->getMonthName($description['months']) . ' ปี ' . ($description['years'] + 543);
                    $comment = VeterinaryService::getcomment($data['Description']['years'], $data['Description']['months'], $data['Description']['region_id']);
                    array_push($commentarr, $comment);
                    $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $data, $cooperative, $header, $commentarr);
                    break;
                case 'quarter' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ไตรมาสที่ ' . $description['quarter'] . ' ปี ' . ($condition['YearFrom'] + 543);
                    $month = [];
                    $year = $data['Description']['years'];
                    if ($data['Description']['quarter'] == 1) {
                        $textyear = $data['Description']['years'] + 1;
                        $month = [10, 11, 12];
                        $year--;
                    } else if ($data['Description']['quarter'] == 2) {
                        $month = [1, 2, 3];
                    } else if ($data['Description']['quarter'] == 3) {
                        $month = [4, 5, 6];
                    } else if ($data['Description']['quarter'] == 4) {
                        $month = [7, 8, 9];
                    }
                    foreach ($month as $m) {
                        $comment = VeterinaryService::getcomment($year, $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $data, $cooperative, $header, $commentarr);
                    break;

                default : $result = null;
            }

            $filename = 'Veterinary-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateVeterinaryExcel($objPHPExcel, $condition, $data, $cooperative, $header, $comment) {
        $objPHPExcel->getActiveSheet()->setTitle("สัตวแพท");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:A3');
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('B2:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'หน่วย');
           // echo $condition['region_name'];exit;
        $objPHPExcel->getActiveSheet()->setCellValue('D2', 'แผนกส่งเสริการเลี้ยงโคนม ' . $condition['region_name']);
        foreach ($cooperative as $key => $value) {

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $key, 3, $value['cooperative_name']);
        }
        $highestColumm = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $highestColumm++;
        $objPHPExcel->getActiveSheet()->setCellValue($highestColumm . '3', 'รวม');



        $con_row = 4;
        foreach ($data as $valuedata) {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $con_row, $valuedata['DairyFarmingName']);

            $styleArrayname = array(
                'font' => array(
                    'bold' => true,
                    'size' => 16,
                    'name' => 'AngsanaUPC'
            ));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->applyFromArray($styleArrayname);
            $con_row++;

            if ($valuedata['Data'] != '' || !is_null($valuedata['Data'])) {
                $sum = array_fill(0, sizeof($valuedata['Data'][0]['Dataset']), 0);
                $sumary = 0;

                foreach ($valuedata['Data'] as $item) {

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $con_row, $item['ItemName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $con_row, $item['Unit']);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $con_row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    foreach ($item['Dataset'] as $key => $itemdata) {

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $key, $con_row, $itemdata['Amount']);

                        if ($item['Unit'] == 'บาท') {

                            $sum[$key] += $itemdata['Amount'];
                        }
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue($highestColumm . $con_row, $item['Summary']);
                    if ($item['Unit'] == 'บาท') {

                        $sumary += $item['Summary'];
                    }
                    $con_row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $con_row, 'รวมจำนวนเงิน');
                $styleArraysum = array(
                    'font' => array(
                        'bold' => true,
                        'size' => 16,
                        'name' => 'AngsanaUPC'
                ));
                $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->applyFromArray($styleArraysum);
                // $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row . ':' . $highestColumm . $con_row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'F2DCDB')
                            )
                        )
                );

                foreach ($sum as $key => $sumarys) {

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $key, $con_row, $sumarys);
                }

                $objPHPExcel->getActiveSheet()->setCellValue($highestColumm . $con_row, $sumary);
                $con_row++;
            }
        }

        $styleArray = array(
//             'borders' => array(
//                'allborders' => array(
//                    'style' => (\PHPExcel_Style_Border::BORDER_THIN)
//                )
//            ),
            'font' => array(
                'name' => 'AngsanaUPC',
                'size' => '16'
        ));

//        $objPHPExcel->getDefaultStyle()
//                ->applyFromArray($styleArray);
        // header style
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $highestColumm . '1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A' . $con_row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A2:B3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2:B3')->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->mergeCells('D2:' . $highestColumm . '2');
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setSize(12);

        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getFont()->setSize(12);

        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getStyle('B3:' . $highestColumm . $con_row)->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle($highestColumm . '3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()
                ->getStyle("A2:" . $highestColumm . "3")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        // exit;
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColumm . '3')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'BFBFBF')
                    )
                )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColumm . ($con_row - 1))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColumm . ($con_row - 1))->applyFromArray(
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
        $row = $con_row + 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'หมายเหตุ :');
        $row++;

        foreach ($comment as $item) {
            foreach ($item as $com) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($com['cooperative_name']));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), ($this->getMonthName($com['months'])));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), ($com['user_comment']));
                $objPHPExcel->getActiveSheet()->mergeCells('C' . $row . ':E' . ($row ));
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->applyFromArray(
                        array(
                            'font' => array(
                                'name' => 'AngsanaUPC'
                            )
                        )
                );
                $row++;
            }
        }

        return $objPHPExcel;
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

    public function exportMineralExcel($request, $response) {
        // error_reporting(E_ERROR);
        // error_reporting(E_ALL);
        // ini_set('display_errors','On');    
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        try {
            $obj = $request->getParsedBody();
            // print_r($obj);

            $condition = $obj['obj']['condition'];
            $item = $obj['obj']['Item'];
            $itemunit = $obj['obj']['ItemUnit'];
            $data = $obj['obj']['DetailList'];
            $description = $obj['obj']['data_description'];
            //$summary = $obj['summary'];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();
            $commentarr = [];
            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ปี ' . ($condition['YearFrom'] + 543);
                    foreach ($monthList as $key => $m) {
                        $comment = MineralService::getcomment($data['Description']['years'] - $yearList[$key], $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateMineralExcel($objPHPExcel, $condition, $data, $header, $item, $itemunit, $commentarr);
                    break;
                case 'monthly' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม เดือน ' . $this->getMonthName($description['months']) . ' ปี ' . ($condition['YearFrom'] + 543);
                    $comment = SpermService::getcomment($data['Description']['years'], $data['Description']['months'], $data['Description']['region_id']);
                    array_push($commentarr, $comment);

                    $objPHPExcel = $this->generateMineralExcel($objPHPExcel, $condition, $data, $header, $item, $itemunit, $commentarr);
                    break;
                case 'quarter' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ไตรมาสที่ ' . $description['quarter'] . ' ปี ' . ($condition['YearFrom'] + 543);
                    $month = [];
                    $year = $data['Description']['years'];
                    if ($data['Description']['quarter'] == 1) {
                        $textyear = $data['Description']['years'] + 1;
                        $month = [10, 11, 12];
                        $year--;
                    } else if ($data['Description']['quarter'] == 2) {
                        $month = [1, 2, 3];
                    } else if ($data['Description']['quarter'] == 3) {
                        $month = [4, 5, 6];
                    } else if ($data['Description']['quarter'] == 4) {
                        $month = [7, 8, 9];
                    }
                    foreach ($month as $m) {
                        $comment = SpermService::getcomment($year, $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateMineralExcel($objPHPExcel, $condition, $data, $header, $item, $itemunit, $commentarr);
                    break;

                default : $result = null;
            }

            $filename = 'Mineral-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateMineralExcel($objPHPExcel, $condition, $data, $header, $item, $itemunit, $comment) {
        $objPHPExcel->getActiveSheet()->setTitle("แร่ธาตุ และอาหาร");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:A3');
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('B2:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'หน่วย');

        $objPHPExcel->getActiveSheet()->setCellValue('D2', 'แผนกส่งเสริการเลี้ยงโคนมภาคกลาง');
        foreach ($data as $key => $value) {

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $key, 3, $value['RegionName']);
        }
        $highestColumm = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $highestColumm++;
        $objPHPExcel->getActiveSheet()->setCellValue($highestColumm . '3', 'รวม');



        $con_row = 4;

        $sizesum = sizeof($data) + 1;
        $sum = array_fill(0, $sizesum, 0);
        $index = 1;
        $indexkg = 0;
        $sum = [];
        foreach ($item as $key => $valueitem) {
            $sumrow = 0;
            $sumkg = 0;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $con_row, $valueitem['label']);
            if ($itemunit[$key + $key]['label'] == 'กิโลกรัม') {
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $con_row, 'กก.');
                $objPHPExcel->getActiveSheet()->getStyle('B' . $con_row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $con_row, $itemunit[$key + $key]['label']);
            }

            foreach ($data as $keydata => $valueitemsdata) {
                $sumrow += $valueitemsdata['ValueList'][$index]['values'];
                $sumkg += $valueitemsdata['ValueList'][$indexkg]['values'];
                $sum[$keydata] += $valueitemsdata['ValueList'][$index]['values'];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $keydata, $con_row, $valueitemsdata['ValueList'][$index]['values']);
            }
            $sum[sizeof($data)] += $sumrow;
            //   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $key, $con_row, $data['ValueList'][$key + $key]['values']);
            $objPHPExcel->getActiveSheet()->setCellValue($highestColumm . $con_row, $sumrow);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $con_row, $sumkg);
            $con_row++;
            $index += 2;
            $indexkg += 2;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $con_row, 'รวมจำนวนเงิน');
        $styleArraysum = array(
            'font' => array(
                'bold' => true,
                'size' => 16,
                'name' => 'AngsanaUPC'
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->applyFromArray($styleArraysum);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $con_row, 'บาท');

        $objPHPExcel->getActiveSheet()->getStyle('B' . $con_row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        foreach ($sum as $keysum => $sums) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $keysum, $con_row, $sums);
        }
        // header style
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $highestColumm . '1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A' . $con_row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A2:B3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2:B3')->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->mergeCells('D2:' . $highestColumm . '2');
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setSize(12);

        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getFont()->setSize(12);

        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B3:' . $highestColumm . $con_row)->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle($highestColumm . '3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A2:" . $highestColumm . "3")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColumm . '3')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'BFBFBF')
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColumm . ($con_row ))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColumm . ($con_row ))->applyFromArray(
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
        $row = $con_row + 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'หมายเหตุ :');
        $row++;
        foreach ($comment as $item) {
            foreach ($item as $com) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($com['cooperative_name']));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), ($this->getMonthName($com['months'])));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), ($com['user_comment']));
                $objPHPExcel->getActiveSheet()->mergeCells('C' . $row . ':E' . ($row ));
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->applyFromArray(
                        array(
                            'font' => array(
                                'name' => 'AngsanaUPC'
                            )
                        )
                );
                $row++;
            }
        }
        return $objPHPExcel;
    }

    public function exportSpermExcel($request, $response) {
        // error_reporting(E_ERROR);
        // error_reporting(E_ALL);
        // ini_set('display_errors','On');        
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        try {

            $obj = $request->getParsedBody();
            $mastesgoallist = MasterGoalService::getList('Y', 'ผลิตน้ำเชื้อแช่แข็ง');
            $commentarr = [];

            $condition = $obj['obj']['condition'];
//            $cooperative = $obj['obj']['CooperativeList'];
            $data = $obj['obj']['data'];
//            $description = $obj['obj']['data_description'];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();

            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ปี ' . ($data['Description']['years'] + 543);
                    foreach ($monthList as $key => $m) {
                        $comment = SpermService::getcomment($data['Description']['years'] - $yearList[$key], $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }

                    $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'monthly' : $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
                    $comment = SpermService::getcomment($data['Description']['years'], $data['Description']['months'], $data['Description']['region_id']);
                    array_push($commentarr, $comment);

                    $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'quarter' :
                    $month = [];
                    $year = $data['Description']['years'];
                    if ($data['Description']['quarter'] == 1) {
                        $textyear = $data['Description']['years'] + 1;
                        $month = [10, 11, 12];
                        $year--;
                    } else if ($data['Description']['quarter'] == 2) {
                        $month = [1, 2, 3];
                    } else if ($data['Description']['quarter'] == 3) {
                        $month = [4, 5, 6];
                    } else if ($data['Description']['quarter'] == 4) {
                        $month = [7, 8, 9];
                    }
                    foreach ($month as $m) {
                        $comment = SpermService::getcomment($year, $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ไตรมาสที่ ' . $data['Description']['quarter'] . ' ปี ' . ($textyear + 543);
                    $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;

                default : $result = null;
            }
//            $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
//            $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data);
            $filename = 'Sperm-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data, $type, $comment) {
        $objPHPExcel->getActiveSheet()->setTitle("ผลิตน้ำเชื้อแช่แข็ง");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);



        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'การผลิตน้ำเชื้อแช่แข็ง');

        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'กิจกรรม/ผลิตภัณฑ์/สินค้า/บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'หน่วย');
        $objPHPExcel->getActiveSheet()->setCellValue('C3', 'เป้าหมายทั้งปี');

        $row = 0;
        //   $goal = GoalMissionService::getyearGoal($data['Description']['region_id'], $data['Description']['years']);


        if ($type == 'annually') {
            //  $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ผลการดำเนินงานประจำปี');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'เปรียบเทียบเป้าหมาย');
            foreach ($mastesgoallist as $key => $value) {
                $mission = GoalMissionService::getMission($value['id'], $data['Description']['region_id'], $data['Description']['years']);
                // print_r($mission);
                $spmonth = SpermService::getDetailmonth($data['Description']['years'], 9, $value['id'], $data['Description']['region_id']);

                foreach ($this->monthList as $key => $itemmonth) {
                    $detail = SpermService::getDetailmonth($data['Description']['years'] - $this->yearList[$key], $itemmonth, $value['id'], $data['Description']['region_id']);

                    $spmonth['amount'] += $detail['amount'];
                    $spmonth['price'] += $detail['price'];
                }

                if (sizeof($spmonth) > 0) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), $value['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        มูลค่า' . $value['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                    ///goal
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), $mission[0]['unit']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $mission[0]['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $mission[0]['price_value']);
//                $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), round($value['amount'] , 2));
//                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), round($value['price_value'] , 2));
/// month
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $spmonth['amount']);

                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $spmonth['price']);
//compare
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $spmonth['amount'] - round($mission['amount'], 2));

                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $spmonth['price'] - round($mission['price_value'], 2));
                    $row += 2;
                }
            }
        } else if ($type == 'monthly') {

            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'ผลการดำเนินงานประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'เปรียบเทียบเป้าหมาย');
            foreach ($mastesgoallist as $key => $value) {
                $mission = GoalMissionService::getMission($value['id'], $data['Description']['region_id'], $data['Description']['years']);
                $spmonth = SpermService::getDetailmonth($data['Description']['years'], $data['Description']['months'], $value['id'], $data['Description']['region_id']);
                $missionM = GoalMissionService::getMissionavg($mission[0]['id'], $data['Description']['years'], $data['Description']['months']);

                if (sizeof($spmonth) > 0) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), $value['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        มูลค่า' . $value['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                    ///goal
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), $mission[0]['unit']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $mission[0]['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $mission[0]['price_value']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), round($missionM[0]['amount'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), round($missionM[0]['price_value'], 2));
/// month
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $spmonth['amount']);

                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $spmonth['price']);
//compare
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $spmonth['amount'] - round($missionM[0]['amount'] / 12, 2));

                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $spmonth['price'] - round($missionM[0]['price_value'] / 12, 2));
                    $row += 2;
                }
            }
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'ผลการดำเนินงานประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'เปรียบเทียบเป้าหมาย');
            foreach ($mastesgoallist as $key => $value) {
                if ($data['Description']['quarter'] == 1) {
                    $y = $data['Description']['years'] + 1;

                    $mission = GoalMissionService::getMission($item['id'], $data['Description']['region_id'], $y);
                } else {
                    $mission = GoalMissionService::getMission($value['id'], $data['Description']['region_id'], $data['Description']['years']);
                }

                $spmonth = SpermService::getDetailquar($data['Description']['years'], $value['id'], $data['Description']['region_id'], $data['Description']['quarter']);
                $missionM = GoalMissionService::getMissionavgquar($mission[0]['id'], $data['Description']['years'], $data['Description']['quarter']);


                if (sizeof($spmonth) > 0) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), $value['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        มูลค่า' . $value['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                    ///goal
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), $mission[0]['unit']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $mission[0]['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $mission[0]['price_value']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), round($missionM['amount'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), round($missionM['price_value'], 2));
/// month
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $spmonth['amount']);

                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $spmonth['price']);
//compare
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $spmonth['amount'] - round($missionM['amount'], 2));

                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $spmonth['price'] - round($missionM['price_value'], 2));
                    $row += 2;
                }
            }
        }


        $highestRow = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
        $highestColum = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $highestColum . '1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:' . $highestColum . '2');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(18);


        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A4:A' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:A' . $highestRow)->getFont()->setSize(14);

        $objPHPExcel->getActiveSheet()->getStyle('B4:' . $highestColum . $highestRow)->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('B4:B' . $highestRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'BFBFBF')
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColum . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColum . $highestRow)->applyFromArray(
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
        $row = $highestRow + 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'หมายเหตุ :');
        $row++;
        foreach ($comment as $item) {
            foreach ($item as $com) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($com['cooperative_name']));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), ($this->getMonthName($com['months'])));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), ($com['user_comment']));
                $objPHPExcel->getActiveSheet()->mergeCells('C' . $row . ':E' . ($row ));
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->applyFromArray(
                        array(
                            'font' => array(
                                'name' => 'AngsanaUPC'
                            )
                        )
                );
                $row++;
            }
        }
        return $objPHPExcel;
    }

    public function exportTravelExcel($request, $response) {
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        try {
            $obj = $request->getParsedBody();
            $mastesgoallist = MasterGoalService::getList('Y', 'ท่องเที่ยว');

            $condition = $obj['obj']['condition'];
            $data = $obj['obj']['data'];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();
            $commentarr = [];
            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'รายงานผลการดำเนินงาน ฝ่ายท่องเที่ยวเชิงเกษตร ปี ' . ($data['Description']['years'] + 543);
                    foreach ($monthList as $key => $m) {
                        $comment = TravelService::getcomment($data['Description']['years'] - $yearList[$key], $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }

                    $objPHPExcel = $this->generateTravelExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'monthly' : $header = 'รายงานผลการดำเนินงาน ฝ่ายท่องเที่ยวเชิงเกษตร เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
                    $comment = TravelService::getcomment($data['Description']['years'], $data['Description']['months'], $data['Description']['region_id']);
                    array_push($commentarr, $comment);

                    $objPHPExcel = $this->generateTravelExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'quarter' :$header = 'รายงานผลการดำเนินงาน ฝ่ายท่องเที่ยวเชิงเกษตร ไตรมาสที่ ' . $data['Quarter'] . ' ปี ' . ($data['Description']['years'] + 543);
                    $month = [];
                    $year = $data['Description']['years'];
                    if ($data['Description']['quarter'] == 1) {
                        $textyear = $data['Description']['years'] + 1;
                        $month = [10, 11, 12];
                        $year--;
                    } else if ($data['Description']['quarter'] == 2) {
                        $month = [1, 2, 3];
                    } else if ($data['Description']['quarter'] == 3) {
                        $month = [4, 5, 6];
                    } else if ($data['Description']['quarter'] == 4) {
                        $month = [7, 8, 9];
                    }
                    foreach ($month as $m) {
                        $comment = TravelService::getcomment($year, $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateTravelExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;

                default : $result = null;
            }

            $filename = 'Travel-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateTravelExcel($objPHPExcel, $mastesgoallist, $header, $data, $type, $comment) {
        $objPHPExcel->getActiveSheet()->setTitle("ท่องเที่ยว");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:A4');
        $objPHPExcel->getActiveSheet()
                ->getStyle("A2:A4")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'เป้าหมายทั้งปี');
        $objPHPExcel->getActiveSheet()->mergeCells('B2:C3');
        $objPHPExcel->getActiveSheet()
                ->getStyle("B2:C3")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->setCellValue('A5', 'รายได้จากการท่องเที่ยว');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '  - บุคคลทั่วไป (ผู้ใหญ่)');
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '  - บุคคลทั่วไป (เด็ก)');
        $objPHPExcel->getActiveSheet()->setCellValue('A8', '  - นักศึกษา');
        $objPHPExcel->getActiveSheet()->setCellValue('A9', '  - จำนวนผู้เข้าชมที่ยกเว้น');
        $objPHPExcel->getActiveSheet()->setCellValue('A10', 'รวมรายได้ทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A6:A9')->getFont()->setSize(16);
        $row = 0;
        $mastesgoaladult = MasterGoalService::getmision($mastesgoallist[0]['goal_name']);
        $mastesgoalchild = MasterGoalService::getmision($mastesgoallist[1]['goal_name']);
        $mastesgoalstudent = MasterGoalService::getmision($mastesgoallist[2]['goal_name']);

        $missionad = GoalMissionService::getGoaltravel($mastesgoaladult[0]['id'], $data['Description']['years']);
        $missionch = GoalMissionService::getGoaltravel($mastesgoalchild[0]['id'], $data['Description']['years']);
        $missionst = GoalMissionService::getGoaltravel($mastesgoalstudent[0]['id'], $data['Description']['years']);

        if ($data['Description']['quarter'] == 1) {
            $y = $data['Description']['years'] + 1;

            $missionad = GoalMissionService::getGoaltravel($mastesgoaladult[0]['id'], $y);
            $missionch = GoalMissionService::getGoaltravel($mastesgoalchild[0]['id'], $y);
            $missionst = GoalMissionService::getGoaltravel($mastesgoalstudent[0]['id'], $y);
        }

        $avgad = GoalMissionService::getMissionavg($missionad[0]['id'], $data['Description']['years'], $data['Description']['months']);
//        


        $avgch = GoalMissionService::getMissionavg($missionch[0]['id'], $data['Description']['years'], $data['Description']['months']);
//       


        $avgst = GoalMissionService::getMissionavg($missionst[0]['id'], $data['Description']['years'], $data['Description']['months']);
//       

        if ($type == 'annually') {
            $tvmonth = TravelService::getDetailmonth($data['Description']['years'], 9, $mastesgoallist[0]['id']);
            foreach ($this->monthList as $key => $item) {
                $detail = TravelService::getDetailmonth($data['Description']['years'] - $this->yearList[$key], $item, $mastesgoallist[0]['id']);
                $tvmonth['apay'] += $detail['apay'];
                $tvmonth['p_adult'] += $detail['p_adult'];
                $tvmonth['cpay'] += $detail['cpay'];
                $tvmonth['p_child'] += $detail['p_child'];
                $tvmonth['spay'] += $detail['spay'];
                $tvmonth['p_student'] += $detail['p_student'];
            }

            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'มูลค่า (บาท)');



            $objPHPExcel->getActiveSheet()->setCellValue('D2', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->mergeCells('D2:E2');

            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('F2', 'เปรียบเทียบ % เป้าหมาย');
            $objPHPExcel->getActiveSheet()->mergeCells('F2:G2');

            $objPHPExcel->getActiveSheet()->setCellValue('F4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'มูลค่า (บาท)');
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ประจำปี ' . substr($data['Description']['years'] + 543, 2, 5));
            $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("D3:E3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ประจำปี');
            $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("F2:G3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
//            ///goal

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6), $missionad[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6), $missionad[0]['price_value']);


            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7), $missionch[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7), $missionch[0]['price_value']);


            $objPHPExcel->getActiveSheet()->setCellValue('B' . (8), $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (8), $missionst[0]['price_value']);


            $objPHPExcel->getActiveSheet()->setCellValue('D6', $tvmonth['apay']);
            $objPHPExcel->getActiveSheet()->setCellValue('E6', $tvmonth['p_adult']);
            $objPHPExcel->getActiveSheet()->setCellValue('D7', $tvmonth['cpay']);
            $objPHPExcel->getActiveSheet()->setCellValue('E7', $tvmonth['p_child']);
            $objPHPExcel->getActiveSheet()->setCellValue('D8', $tvmonth['spay']);
            $objPHPExcel->getActiveSheet()->setCellValue('E8', $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('D9', $tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['a_except']);


////compare
            $objPHPExcel->getActiveSheet()->setCellValue('F6', $tvmonth['apay'] - round($missionad[0]['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('G6', $tvmonth['p_adult'] - round($missionad[0]['price_value'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('F7', $tvmonth['cpay'] - round($missionch[0]['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('G7', $tvmonth['p_child'] - round($missionch[0]['price_value'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('F8', $tvmonth['spay'] - round($missionst[0]['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('G8', $tvmonth['p_student'] - round($missionst[0]['price_value'], 2));


//  sum
            $objPHPExcel->getActiveSheet()->setCellValue('B10', $missionad[0]['amount'] + $missionch[0]['amount'] + $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C10', $missionad[0]['price_value'] + $missionch[0]['price_value'] + $missionst[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D10', $tvmonth['apay'] + $tvmonth['cpay'] + $tvmonth['spay'] + $tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['a_except']);
            $objPHPExcel->getActiveSheet()->setCellValue('E10', $tvmonth['p_adult'] + $tvmonth['p_child'] + $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('F10', ($tvmonth['apay'] - round($missionad[0]['amount'], 2)) + ( $tvmonth['cpay'] - round($missionch[0]['amount'], 2)) + ($tvmonth['spay'] - round($missionst[0]['amount'], 2)));
            $objPHPExcel->getActiveSheet()->setCellValue('G10', ($tvmonth['p_adult'] - round($missionad[0]['price_value'], 2)) + ($tvmonth['p_child'] - round($missionch[0]['price_value'], 2)) + ($tvmonth['p_student'] - round($missionst[0]['price_value'], 2)));
        } else if ($type == 'monthly') {
            // echo $data['Description']['years']. $data['Description']['months'].' id '.$mastesgoallist[0]['id'];exit;
            $tvmonth = TravelService::getDetailmonth($data['Description']['years'], $data['Description']['months'], [$mastesgoallist[0]['id']]);

            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('F2', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->mergeCells('F2:G2');

            $objPHPExcel->getActiveSheet()->setCellValue('F4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('H2', 'เปรียบเทียบ % เป้าหมาย');
            $objPHPExcel->getActiveSheet()->mergeCells('H2:I2');

            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'มูลค่า (บาท)');
            $objPHPExcel->getActiveSheet()->setCellValue('D2', 'เป้าหมายประจำเดือน');
            $objPHPExcel->getActiveSheet()->mergeCells('D2:E3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("D2:E3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ประจำเดือน ' . $this->getMonthshName($data['Description']['months']) . ' ' . substr($data['Description']['years'] + 543, 2, 5));
            $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("F2:G3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $objPHPExcel->getActiveSheet()->setCellValue('H3', 'ประจำเดือน');
            $objPHPExcel->getActiveSheet()->mergeCells('H3:I3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("H2:I3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
//            ///goal

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6), number_format($missionad[0]['amount'], 2, '.', ','));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6), number_format($missionad[0]['price_value'], 2, '.', ','));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6), number_format($avgad[0]['amount'], 2, '.', ','));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6), number_format($avgad[0]['price_value'], 2, '.', ','));

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7), number_format($missionch[0]['amount'], 2, '.', ','));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7), number_format($missionch[0]['price_value'], 2, '.', ','));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7), number_format($avgch[0]['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (7), number_format($avgch[0]['price_value'], 2));

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (8), $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (8), $missionst[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (8), number_format($avgst[0]['amount'], 2, '.', ','));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (8), number_format($avgst[0]['price_value'], 2, '.', ','));
///// mont
            $objPHPExcel->getActiveSheet()->setCellValue('F6', $tvmonth['apay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G6', $tvmonth['p_adult']);
            $objPHPExcel->getActiveSheet()->setCellValue('F7', $tvmonth['cpay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G7', $tvmonth['p_child']);
            $objPHPExcel->getActiveSheet()->setCellValue('F8', $tvmonth['spay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G8', $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('F9', $tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['s_except']);


////compare
            $objPHPExcel->getActiveSheet()->setCellValue('H6', $tvmonth['apay'] - round($avgad[0]['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I6', $tvmonth['p_adult'] - round($avgad[0]['price_value'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('H7', $tvmonth['cpay'] - round($avgch[0]['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I7', $tvmonth['p_child'] - round($avgch[0]['price_value'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('H8', $tvmonth['spay'] - round($avgst[0]['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I8', $tvmonth['p_student'] - round($avgst[0]['price_value'], 2));


//  sum
            $objPHPExcel->getActiveSheet()->setCellValue('B10', $missionad[0]['amount'] + $missionch[0]['amount'] + $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C10', $missionad[0]['price_value'] + $missionch[0]['price_value'] + $missionst[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D10', round($avgad[0]['amount'], 2) + round($avgch[0]['amount'], 2) + round($avgst[0]['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E10', round($avgad[0]['price_value'], 2) + round($avgch[0]['price_value'], 2) + round($avgst[0]['price_value'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('F10', $tvmonth['apay'] + $tvmonth['cpay'] + $tvmonth['spay'] + $tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['a_except']);
            $objPHPExcel->getActiveSheet()->setCellValue('G10', $tvmonth['p_adult'] + $tvmonth['p_child'] + $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('H10', ($tvmonth['apay'] - round($avgad[0]['amount'], 2)) + ( $tvmonth['cpay'] - round($avgch[0]['amount'], 2)) + ($tvmonth['spay'] - round($avgst[0]['amount'], 2)));
            $objPHPExcel->getActiveSheet()->setCellValue('I10', ($tvmonth['p_adult'] - round($avgad[0]['price_value'], 2)) + ($tvmonth['p_child'] - round($avgch[0]['price_value'], 2)) + ($tvmonth['p_student'] - round($avgst[0]['price_value'], 2)));
        } else {
            $tvmonth = TravelService::getDetailquar($data['Description']['years'], $data['Description']['region_id'], $data['Quarter']);
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('F2', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->mergeCells('F2:G2');

            $objPHPExcel->getActiveSheet()->setCellValue('F4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('H2', 'เปรียบเทียบ % เป้าหมาย');
            $objPHPExcel->getActiveSheet()->mergeCells('H2:I2');

            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'มูลค่า (บาท)');
            $objPHPExcel->getActiveSheet()->setCellValue('D2', 'ประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->mergeCells('D2:E3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("D2:E3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ประจำไตรมาส ' . $this->getMonthshName($data['Description']['quarter']) . ' ' . substr($data['Description']['years'] + 543, 2, 5));
            $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("F2:G3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $objPHPExcel->getActiveSheet()->setCellValue('H3', 'ประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->mergeCells('H3:I3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("H2:I3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
//            ///goal
            $avgad = GoalMissionService::getMissionavgquar($missionad[0]['id'], $data['Description']['years'], $data['Quarter']);
            $avgch = GoalMissionService::getMissionavgquar($missionch[0]['id'], $data['Description']['years'], $data['Quarter']);
            $avgst = GoalMissionService::getMissionavgquar($missionst[0]['id'], $data['Description']['years'], $data['Quarter']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6), $missionad[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6), $missionad[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6), round($avgad['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6), round($avgad['price_value'], 2));

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7), $missionch[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7), $missionch[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7), round($avgch['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (7), round($avgch['price_value'], 2));

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (8), $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (8), $missionst[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (8), round($avgst['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (8), round($avgst['price_value'], 2));
///// month
            $objPHPExcel->getActiveSheet()->setCellValue('F6', $tvmonth['apay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G6', $tvmonth['p_adult']);
            $objPHPExcel->getActiveSheet()->setCellValue('F7', $tvmonth['cpay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G7', $tvmonth['p_child']);
            $objPHPExcel->getActiveSheet()->setCellValue('F8', $tvmonth['spay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G8', $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('F9', $tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['a_except']);


////compare
            $objPHPExcel->getActiveSheet()->setCellValue('H6', $tvmonth['apay'] - round($avgad['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I6', $tvmonth['p_adult'] - round($avgad['price_value'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('H7', $tvmonth['cpay'] - round($avgch['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I7', $tvmonth['p_child'] - round($avgch['price_value'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('H8', $tvmonth['spay'] - round($avgst['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I8', $tvmonth['p_student'] - round($avgst['price_value'], 2));


//  sum
            $objPHPExcel->getActiveSheet()->setCellValue('B10', $missionad[0]['amount'] + $missionch[0]['amount'] + $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C10', $missionad[0]['price_value'] + $missionch[0]['price_value'] + $missionst[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D10', round($avgad['amount'], 2) + round($avgch['amount'], 2) + round($avgst['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E10', round($avgad['price_value'], 2) + round($avgch['price_value'], 2) + round($avgst['price_value'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('F10', $tvmonth['apay'] + $tvmonth['cpay'] + $tvmonth['spay'] + $tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['a_except']);
            $objPHPExcel->getActiveSheet()->setCellValue('G10', $tvmonth['p_adult'] + $tvmonth['p_child'] + $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('H10', ($tvmonth['apay'] - round($avgad['amount'], 2)) + ( $tvmonth['cpay'] - round($avgch['amount'], 2)) + ($tvmonth['spay'] - round($avgst['amount'], 2)));
            $objPHPExcel->getActiveSheet()->setCellValue('I10', ($tvmonth['p_adult'] - round($avgad['price_value'], 2)) + ($tvmonth['p_child'] - round($avgch['price_value'], 2)) + ($tvmonth['p_student'] - round($avgst['price_value'], 2)));
        }



        $highestRow = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
        $highestColum = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $highestColum . '1');

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A5')->getFont()->setSize(18);


        $objPHPExcel->getActiveSheet()->getStyle('B2:' . $highestColum . '4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B2:' . $highestColum . '4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('B2:' . $highestColum . '4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B5:I9')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A10')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A10:I10')->getFont()->setSize(16);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A2:' . $highestColum . '4')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'BFBFBF')
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColum . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()
                ->getStyle("B6:I10")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->getStyle('B6:I10')
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $row = $highestRow + 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'หมายเหตุ :');
        $row++;
        foreach ($comment as $item) {
            foreach ($item as $com) {
                //  $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($com['cooperative_name']));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), ($this->getMonthName($com['months'])));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), ($com['user_comment']));
                $objPHPExcel->getActiveSheet()->mergeCells('C' . $row . ':E' . ($row ));
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->applyFromArray(
                        array(
                            'font' => array(
                                'name' => 'AngsanaUPC'
                            )
                        )
                );
                $row++;
            }
        }
        return $objPHPExcel;
    }

    public function exportCowbreedExcel($request, $response) {
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        try {
            $obj = $request->getParsedBody();
            $mastesgoallist = MasterGoalService::getList('Y', 'ปัจจัยการเลี้ยงโค');

            $condition = $obj['obj']['condition'];

            $data = $obj['obj']['data'];


            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();
            $commentarr = [];
            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ปี ' . ($data['Description']['years'] + 543);
                    foreach ($monthList as $key => $m) {
                        $comment = CowBreedService::getcomment($data['Description']['years'] - $yearList[$key], $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateCowbreedExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'monthly' : $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
                    $comment = CowBreedService::getcomment($data['Description']['years'], $data['Description']['months'], $data['Description']['region_id']);
                    array_push($commentarr, $comment);
                    $objPHPExcel = $this->generateCowbreedExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'quarter' :$header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ไตรมาสที่ ' . $data['Quarter'] . ' ปี ' . ($data['Description']['years'] + 543);
                    $month = [];
                    $year = $data['Description']['years'];
                    if ($data['Description']['quarter'] == 1) {
                        $textyear = $data['Description']['years'] + 1;
                        $month = [10, 11, 12];
                        $year--;
                    } else if ($data['Description']['quarter'] == 2) {
                        $month = [1, 2, 3];
                    } else if ($data['Description']['quarter'] == 3) {
                        $month = [4, 5, 6];
                    } else if ($data['Description']['quarter'] == 4) {
                        $month = [7, 8, 9];
                    }
                    foreach ($month as $m) {
                        $comment = CowBreedService::getcomment($year, $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateCowbreedExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;

                default : $result = null;
            }
//            $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
//            $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data);
            $filename = 'Cowbreed-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateCowbreedExcel($objPHPExcel, $mastesgoallist, $header, $data, $type, $comment) {

        $objPHPExcel->getActiveSheet()->setTitle("ปัจจัยการเลี้ยงโค");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'การจำหน่ายอาหารสัตว์ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'กิจกรรม/ผลิตภัณฑ์/สินค้า/บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'หน่วย');
        $objPHPExcel->getActiveSheet()->setCellValue('C3', 'เป้าหมายทั้งปี');
//  
        $row = 0;




        if ($type == 'annually') {

            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ผลการดำเนินงานประจำปี');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'เปรียบเทียบเป้าหมาย');
            $summisamt = 0;
            $summispri = 0;
            $sumcbamt = 0;
            $sumcbpri = 0;
            $sumcomamt = 0;
            $sumcompri = 0;
            $sumMamt = 0;
            $sumMpri = 0;
            foreach ($mastesgoallist as $item) {
                $cbmonth = CowBreedService::getDetailmonth($data['Description']['years'], 9, $item['id'], $data['Description']['region_id']);
                $mission = GoalMissionService::getMission($item['id'], $data['Description']['region_id'], $data['Description']['years']);

                $summisamt += $mission[0]['amount'];
                $summispri += $mission[0]['price_value'];
                foreach ($this->monthList as $key => $itemmonth) {
                    $detail = CowBreedService::getDetailmonth($data['Description']['years'] - $this->yearList[$key], $itemmonth, $item['id'], $data['Description']['region_id']);

                    $cbmonth['amount'] += $detail['amount'];
                    $cbmonth['price'] += $detail['price'];
                }
                $sumcbamt += $cbmonth['amount'];
                $sumcbpri += $cbmonth['price'];
                $sumcomamt += $cbmonth['amount'] - ($mission[0]['amount']);
                $sumcompri += $cbmonth['price'] - ($mission[0]['price_value'] );
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'ปริมาณ' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        รายได้' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'กก.');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $mission[0]['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $mission[0]['price_value']);

                $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $cbmonth['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $cbmonth['price']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $cbmonth['amount'] - ($mission[0]['amount']));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $cbmonth['price'] - ($mission[0]['price_value']));

                $row += 2;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'รวมการจำหน่ายอาหารสัตว์');
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมรายได้การจำหน่ายอาหารสัตว์');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'กก.');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $summisamt);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $summispri);

            $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $sumcbamt);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $sumcbpri);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $sumcomamt);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumcompri);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row) . ':E' . (4 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':E' . (5 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
        } else if ($type == 'monthly') {
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'ผลการดำเนินงานประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'เปรียบเทียบเป้าหมาย');
            $summisamt = 0;
            $summispri = 0;
            $sumcbamt = 0;
            $sumcbpri = 0;
            $sumcomamt = 0;
            $sumcompri = 0;
            $sumMamt = 0;
            $sumMpri = 0;
            foreach ($mastesgoallist as $item) {
                $cbmonth = CowBreedService::getDetailmonth($data['Description']['years'], $data['Description']['months'], $item['id'], $data['Description']['region_id']);
                $mission = GoalMissionService::getMission($item['id'], $data['Description']['region_id'], $data['Description']['years']);
                $avg = GoalMissionService::getMissionavg($mission[0]['id'], $data['Description']['years'], $data['Description']['months']);
                $summisamt += $mission[0]['amount'];
                $summispri += $mission[0]['price_value'];
                $sumMamt += $avg[0]['amount'];
                $sumMpri += $avg[0]['price_value'];
                $sumcbamt += $cbmonth['amount'];
                $sumcbpri += $cbmonth['price'];
                $sumcomamt += $cbmonth['amount'] - ($avg[0]['amount'] );
                $sumcompri += $cbmonth['price'] - ($avg[0]['price_value'] );
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'ปริมาณ' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        รายได้' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'กก.');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $mission[0]['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $mission[0]['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $avg[0]['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $avg[0]['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $cbmonth['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $cbmonth['price']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $cbmonth['amount'] - ($avg[0]['amount'] ));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $cbmonth['price'] - ($avg[0]['price_value'] ));

                $row += 2;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'รวมการจำหน่ายอาหารสัตว์');
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมรายได้การจำหน่ายอาหารสัตว์');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'กก.');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $summisamt);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $summispri);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $sumMamt);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $sumMpri);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $sumcbamt);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumcbpri);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $sumcomamt);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $sumcompri);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row) . ':F' . (4 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':F' . (5 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'ผลการดำเนินงานประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'เปรียบเทียบเป้าหมาย');
            $summisamt = 0;
            $summispri = 0;
            $sumcbamt = 0;
            $sumcbpri = 0;
            $sumcomamt = 0;
            $sumcompri = 0;
            $sumMamt = 0;
            $sumMpri = 0;

            foreach ($mastesgoallist as $item) {
                $cbmonth = CowBreedService::getDetailquar($data['Description']['years'], $item['id'], $data['Description']['region_id'], $data['Description']['quarter']);
                // $mission = GoalMissionService::getMission($item['id'], $data['Description']['region_id'], $data['Description']['years']);
                if ($data['Description']['quarter'] == 1) {
                    $y = $data['Description']['years'] + 1;

                    $mission = GoalMissionService::getMission($item['id'], $data['Description']['region_id'], $y);
                } else {
                    $mission = GoalMissionService::getMission($item['id'], $data['Description']['region_id'], $data['Description']['years']);
                }
                $missionM = GoalMissionService::getMissionavgquar($mission[0]['id'], $data['Description']['years'], $data['Description']['quarter']);

                $summisamt += $mission[0]['amount'];
                $summispri += $mission[0]['price_value'];
                $sumMamt += $missionM['amount'];
                $sumMpri += $missionM['price_value'];
                $sumcbamt += $cbmonth['amount'];
                $sumcbpri += $cbmonth['price'];
                $sumcomamt += $cbmonth['amount'] - ($missionM['amount'] );
                $sumcompri += $cbmonth['price'] - ($missionM['price_value'] );
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'ปริมาณ' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        รายได้' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'กก.');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $mission[0]['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $mission[0]['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $missionM['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $missionM['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $cbmonth['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $cbmonth['price']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $cbmonth['amount'] - ($missionM['amount'] ));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $cbmonth['price'] - ($missionM['price_value'] ));

                $row += 2;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'รวมการจำหน่ายอาหารสัตว์');
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมรายได้การจำหน่ายอาหารสัตว์');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'กก.');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $summisamt);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $summispri);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $sumMamt);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $sumMpri);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $sumcbamt);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumcbpri);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $sumcomamt);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $sumcompri);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row) . ':F' . (4 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':F' . (5 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
        }


        $highestRow = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
        $highestColum = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $highestColum . '1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:' . $highestColum . '2');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(18);


        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A4:A' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:A' . $highestRow)->getFont()->setSize(14);

        $objPHPExcel->getActiveSheet()->getStyle('B4:' . $highestColum . $highestRow)->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('B4:B' . $highestRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'BFBFBF')
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColum . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColum . $highestRow)->applyFromArray(
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
        $row = $highestRow + 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'หมายเหตุ :');
        $row++;
        foreach ($comment as $item) {
            foreach ($item as $com) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($com['cooperative_name']));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), ($this->getMonthName($com['months'])));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), ($com['user_comment']));
                $objPHPExcel->getActiveSheet()->mergeCells('C' . $row . ':E' . ($row ));
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->applyFromArray(
                        array(
                            'font' => array(
                                'name' => 'AngsanaUPC'
                            )
                        )
                );
                $row++;
            }
        }
        return $objPHPExcel;
    }

    public function exportCowgroupExcel($request, $response) {
        // error_reporting(E_ERROR);
        // error_reporting(E_ALL);
        // ini_set('display_errors','On'); 
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        try {
            $obj = $request->getParsedBody();
            $mastesgoallist = MasterGoalService::getList('Y', 'ข้อมูลฝูงโค');
            //  print_r($mastesgoallist->toArray());
            $data['Description']['years'] = $obj['obj']['data']['SubItem'][0]['Description']['years'];

            $data['Description']['months'] = $obj['obj']['data']['SubItem'][0]['Description']['months'];
//            $data['Quarter'] = 1;
            $data['Description']['region_id'] = $obj['obj']['data']['SubItem'][0]['Description']['region_id'];
            $condition = $obj['obj']['condition'];
            // $condition['DisplayType'] = 'annually';
//            $cooperative = $obj['obj']['CooperativeList'];
            //   $data = $obj['obj']['data'];
//            $description = $obj['obj']['data_description'];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();
            $commentarr = [];
            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'รายงานความเคลื่อนไหวโคนม ประจำปี ' . ($data['Description']['years'] + 543);
                    foreach ($monthList as $key => $m) {
                        $comment = CowGroupService::getcomment($data['Description']['years'] - $yearList[$key], $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateCowgroupExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'monthly' : $header = 'รายงานความเคลื่อนไหวโคนม ประจำเดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
                    $comment = CowGroupService::getcomment($data['Description']['years'], $data['Description']['months'], $data['Description']['region_id']);
                    array_push($commentarr, $comment);
                    $objPHPExcel = $this->generateCowgroupExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'quarter' :$header = 'รายงานความเคลื่อนไหวโคนม ประจำไตรมาสที่ ' . $data['Quarter'] . ' ปี ' . ($data['Description']['years'] + 543);
                    $month = [];
                    $year = $data['Description']['years'];
                    if ($data['Description']['quarter'] == 1) {
                        $textyear = $data['Description']['years'] + 1;
                        $month = [10, 11, 12];
                        $year--;
                    } else if ($data['Description']['quarter'] == 2) {
                        $month = [1, 2, 3];
                    } else if ($data['Description']['quarter'] == 3) {
                        $month = [4, 5, 6];
                    } else if ($data['Description']['quarter'] == 4) {
                        $month = [7, 8, 9];
                    }
                    foreach ($month as $m) {
                        $comment = CowGroupService::getcomment($year, $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateCowgroupExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;

                default : $result = null;
            }
//            $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
//            $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data);
            $filename = 'Cowgroup-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateCowgroupExcel($objPHPExcel, $mastesgoallist, $header, $data, $type, $commemt) {


        $objPHPExcel->getActiveSheet()->setTitle("ข้อมูลฝูงโค");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'แผนกฟาร์มอินทรีย์ ฝ่ายวิจัยและพัฒนาโคนม');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:K2');
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(18);

        $objPHPExcel->getActiveSheet()->setCellValue('A4', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(45);
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'จำนวนยกมา');
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', 'จำนวนโคเพิ่ม');
        $objPHPExcel->getActiveSheet()->mergeCells('C4:F4');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', 'จำนวนโคลด');
        $objPHPExcel->getActiveSheet()->mergeCells('G4:J4');
        $objPHPExcel->getActiveSheet()->setCellValue('K4', 'จำนวนโคยกไป');
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->mergeCells('K4:K5');
        $objPHPExcel->getActiveSheet()->setCellValue('C5', 'เกิด');
        $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ย้ายเข้า');
        $objPHPExcel->getActiveSheet()->setCellValue('E5', 'ซื้อเข้า');
        $objPHPExcel->getActiveSheet()->setCellValue('F5', 'รวม (+)');
        $objPHPExcel->getActiveSheet()->setCellValue('G5', 'ตาย');
        $objPHPExcel->getActiveSheet()->setCellValue('H5', 'ขาย');
        $objPHPExcel->getActiveSheet()->setCellValue('I5', 'ย้ายออก');
        $objPHPExcel->getActiveSheet()->setCellValue('J5', 'รวม (-)');

        $objPHPExcel->getActiveSheet()->getStyle('A4:K5')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Get sub_goal_type
        $MasterSubGoalType = MasterGoalService::getSubTypeList('ข้อมูลฝูงโค');

        $cnt = 6;
        foreach ($MasterSubGoalType as $key => $value) {

            $years = $data['Description']['years'];
            $months = $data['Description']['months'];
            
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, $value['sub_goal_type']);
            $cnt++;

            $MasterGoalList = MasterGoalService::getList('Y', 'ข้อมูลฝูงโค', [], $value['sub_goal_type'], '', []);

            $total = ['beginning_period' => 0, 
                        'total_born' => 0,
                        'total_movein' => 0,
                        'total_buy' => 0,
                        'total_in' => 0,
                        'total_die' => 0,
                        'total_sell' => 0,
                        'total_moveout' => 0,
                        'total_out' => 0,
                        'last_period' => 0
                    ];

            foreach ($MasterGoalList as $key1 => $value1) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, '   ' . $value1['goal_name']);
                //  Get summary data by goal_id
                // echo "$years, $months, " . $value1['id'];
                // exit;
                $data_result = CowGroupService::getReportData($years, $months, $value1['id']);

                $data_result['beginning_period'] = $data_result['beginning_period']?$data_result['beginning_period']:0;
                $data_result['total_born'] = $data_result['total_born']?$data_result['total_born']:0;
                $data_result['total_movein'] = $data_result['total_movein']?$data_result['total_movein']:0;
                $data_result['total_buy'] = $data_result['total_buy']?$data_result['total_buy']:0;
                $data_result['total_die'] = $data_result['total_die']?$data_result['total_die']:0;
                $data_result['total_sell'] = $data_result['total_sell']?$data_result['total_sell']:0;
                $data_result['total_moveout'] = $data_result['total_moveout']?$data_result['total_moveout']:0;
                $data_result['last_period'] = $data_result['last_period']?$data_result['last_period']:0;

                $objPHPExcel->getActiveSheet()->setCellValue('B' . $cnt, $data_result['beginning_period']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $cnt, $data_result['total_born']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $cnt, $data_result['total_movein']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $cnt, $data_result['total_buy']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $cnt, $data_result['total_born'] + $data_result['total_movein'] + $data_result['total_buy']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $cnt, $data_result['total_die']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $cnt, $data_result['total_sell']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $cnt, $data_result['total_moveout']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $cnt, $data_result['total_die'] + $data_result['total_sell'] + $data_result['total_moveout']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $cnt, $data_result['last_period']);

                $total['beginning_period'] += $data_result['beginning_period'];
                $total['total_born'] += $data_result['total_born'];
                $total['total_movein'] += $data_result['total_movein'];
                $total['total_buy'] += $data_result['total_buy'];
                $total['total_in'] += $data_result['total_born'] + $data_result['total_movein'] + $data_result['total_buy'];
                $total['total_die'] += $data_result['total_die'];
                $total['total_sell'] += $data_result['total_sell'];
                $total['total_moveout'] += $data_result['total_moveout'];
                $total['total_out'] += $data_result['total_die'] + $data_result['total_sell'] + $data_result['total_moveout'];
                $total['last_period'] += $data_result['last_period'];

                $cnt++;
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, 'รวม');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $cnt, $total['beginning_period']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $cnt, $total['total_born']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $cnt, $total['total_movein']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $cnt, $total['total_buy']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $cnt, $total['total_in']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $cnt, $total['total_die']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $cnt, $total['total_sell']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $cnt, $total['total_moveout']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $cnt, $total['total_out']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $cnt, $total['last_period']);

            $cnt++;

        }

        $objPHPExcel->getActiveSheet()->getStyle('A4:K' . ($cnt - 1))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    )
                )
        );

        $cnt++;
        $start_row = $cnt;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, 'รายการ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $cnt, 'ฝูงโค (1962)');
        $objPHPExcel->getActiveSheet()->mergeCells('B' . $cnt  . ':D' . $cnt);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $cnt, 'ฝูงโคฟาร์ม3 (ฝึกอบรม)');
        $objPHPExcel->getActiveSheet()->mergeCells('E' . $cnt . ':G' . $cnt);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $cnt, 'รวม 2 ฝูง');
        $objPHPExcel->getActiveSheet()->mergeCells('H' . $cnt . ':J' . $cnt);

        $cnt++;

        
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($cnt - 1) . ':A' . $cnt);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $cnt, "ปริมาณน้ำนมดิบ\n(กิโลกรัม)");
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $cnt, "ราคา\n(บาท/กิโลกร้ม)");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $cnt, "มูลค่า\n (บาท)");
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $cnt, "ปริมาณน้ำนมดิบ\n(กิโลกรัม)");
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $cnt, "ราคา\n(บาท/กิโลกร้ม)");
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $cnt, "มูลค่า\n(บาท)");
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $cnt, "ปริมาณน้ำนมดิบ\n(กิโลกรัม)");
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $cnt, "ราคา\n(บาท/กิโลกร้ม)");
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $cnt, "มูลค่า\n(บาท)");
        $objPHPExcel->getActiveSheet()->getStyle('B' . ($cnt - 1) . ':J'. $cnt)->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $start_row . ':J' . $cnt)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $cnt++;
        $start_row = $cnt;

        $total = [];

        $summary_data_1 = CowGroupService::getReportSummaryData($years, $months, 'ฝูงโค 1962');
        $summary_data_2 = CowGroupService::getReportSummaryData($years, $months, 'ฝูงโคฟาร์ม 3 (ฝึกอบรม)');

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, 'ส่งเข้าโรงงาน');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $cnt, $summary_data_1['go_factory_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $cnt, $summary_data_1['go_factory_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $cnt, $summary_data_1['go_factory_values']);

        $objPHPExcel->getActiveSheet()->setCellValue('E' . $cnt, $summary_data_2['go_factory_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $cnt, $summary_data_2['go_factory_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $cnt, $summary_data_2['go_factory_values']);

        $objPHPExcel->getActiveSheet()->setCellValue('H' . $cnt, $summary_data_1['go_factory_weight'] + $summary_data_2['go_factory_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $cnt, $summary_data_1['go_factory_price'] + $summary_data_2['go_factory_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $cnt, $summary_data_1['go_factory_values'] + $summary_data_2['go_factory_values']);

        $total['weight_1'] += $summary_data_1['go_factory_weight'];
        $total['price_1'] += $summary_data_1['go_factory_price'];
        $total['values_1'] += $summary_data_1['go_factory_values'];

        $total['weight_2'] += $summary_data_2['go_factory_weight'];
        $total['price_2'] += $summary_data_2['go_factory_price'];
        $total['values_2'] += $summary_data_2['go_factory_values'];

        $total['weight_12'] += $summary_data_1['go_factory_weight'] + $summary_data_2['go_factory_weight'];
        $total['price_12'] += $summary_data_1['go_factory_price'] + $summary_data_2['go_factory_price'];
        $total['values_12'] += $summary_data_1['go_factory_values'] + $summary_data_2['go_factory_values'];

        $cnt++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, 'เลี้ยงลูกโค');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $cnt, $summary_data_1['cow_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $cnt, $summary_data_1['cow_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $cnt, $summary_data_1['cow_values']);

        $objPHPExcel->getActiveSheet()->setCellValue('E' . $cnt, $summary_data_2['cow_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $cnt, $summary_data_2['cow_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $cnt, $summary_data_2['cow_values']);

        $objPHPExcel->getActiveSheet()->setCellValue('H' . $cnt, $summary_data_1['cow_weight'] + $summary_data_2['cow_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $cnt, $summary_data_1['cow_price'] + $summary_data_2['cow_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $cnt, $summary_data_1['cow_values'] + $summary_data_2['cow_values']);

        $total['weight_1'] += $summary_data_1['cow_weight'];
        $total['price_1'] += $summary_data_1['cow_price'];
        $total['values_1'] += $summary_data_1['cow_values'];

        $total['weight_2'] += $summary_data_2['cow_weight'];
        $total['price_2'] += $summary_data_2['cow_price'];
        $total['values_2'] += $summary_data_2['cow_values'];

        $total['weight_12'] += $summary_data_1['cow_weight'] + $summary_data_2['cow_weight'];
        $total['price_12'] += $summary_data_1['cow_price'] + $summary_data_2['cow_price'];
        $total['values_12'] += $summary_data_1['cow_values'] + $summary_data_2['cow_values'];

        $cnt++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, 'เสื่อมคุณภาพ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $cnt, $summary_data_1['decline_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $cnt, $summary_data_1['decline_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $cnt, $summary_data_1['decline_values']);

        $objPHPExcel->getActiveSheet()->setCellValue('E' . $cnt, $summary_data_2['decline_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $cnt, $summary_data_2['decline_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $cnt, $summary_data_2['decline_values']);

        $objPHPExcel->getActiveSheet()->setCellValue('H' . $cnt, $summary_data_1['decline_weight'] + $summary_data_2['decline_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $cnt, $summary_data_1['decline_price'] + $summary_data_2['decline_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $cnt, $summary_data_1['decline_values'] + $summary_data_2['decline_values']);

        $total['weight_1'] += $summary_data_1['decline_weight'];
        $total['price_1'] += $summary_data_1['decline_price'];
        $total['values_1'] += $summary_data_1['decline_values'];

        $total['weight_2'] += $summary_data_2['decline_weight'];
        $total['price_2'] += $summary_data_2['decline_price'];
        $total['values_2'] += $summary_data_2['decline_values'];

        $total['weight_12'] += $summary_data_1['decline_weight'] + $summary_data_2['decline_weight'];
        $total['price_12'] += $summary_data_1['decline_price'] + $summary_data_2['decline_price'];
        $total['values_12'] += $summary_data_1['decline_values'] + $summary_data_2['decline_values'];

        $cnt++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, 'นมน้ำเหลือง');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $cnt, $summary_data_1['yellow_milk_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $cnt, $summary_data_1['yellow_milk_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $cnt, $summary_data_1['yellow_milk_values']);

        $objPHPExcel->getActiveSheet()->setCellValue('E' . $cnt, $summary_data_2['yellow_milk_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $cnt, $summary_data_2['yellow_milk_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $cnt, $summary_data_2['yellow_milk_values']);

        $objPHPExcel->getActiveSheet()->setCellValue('H' . $cnt, $summary_data_1['yellow_milk_weight'] + $summary_data_2['yellow_milk_weight']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $cnt, $summary_data_1['yellow_milk_price'] + $summary_data_2['yellow_milk_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $cnt, $summary_data_1['yellow_milk_values'] + $summary_data_2['yellow_milk_values']);

        $total['weight_1'] += $summary_data_1['yellow_milk_weight'];
        $total['price_1'] += $summary_data_1['yellow_milk_price'];
        $total['values_1'] += $summary_data_1['yellow_milk_values'];

        $total['weight_2'] += $summary_data_2['yellow_milk_weight'];
        $total['price_2'] += $summary_data_2['yellow_milk_price'];
        $total['values_2'] += $summary_data_2['yellow_milk_values'];

        $total['weight_12'] += $summary_data_1['yellow_milk_weight'] + $summary_data_2['yellow_milk_weight'];
        $total['price_12'] += $summary_data_1['yellow_milk_price'] + $summary_data_2['yellow_milk_price'];
        $total['values_12'] += $summary_data_1['yellow_milk_values'] + $summary_data_2['yellow_milk_values'];

        $cnt++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $cnt, $total['weight_1']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $cnt, number_format($total['values_1'] / $total['weight_1'], 2));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $cnt, $total['values_1']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $cnt, $total['weight_2']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $cnt,  number_format($total['values_2'] / $total['weight_2'], 2));
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $cnt, $total['values_2']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $cnt, $total['weight_12']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $cnt,  number_format($total['values_12'] / $total['weight_12'], 2));
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $cnt, $total['values_12']);

        $objPHPExcel->getActiveSheet()->getStyle('A'. ($start_row - 2) .':J' . $cnt)->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    )
                )
        );

        return $objPHPExcel;
    }

    public function exportCowgroupFatherExcel($request, $response) {
        // error_reporting(E_ERROR);
        // error_reporting(E_ALL);
        // ini_set('display_errors','On'); 
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        try {
            $obj = $request->getParsedBody();
            $mastesgoallist = MasterGoalService::getList('Y', 'ข้อมูลฝูงโค');
            //  print_r($mastesgoallist->toArray());
            $data['Description']['years'] = $obj['obj']['data']['SubItem'][0]['Description']['years'];

            $data['Description']['months'] = $obj['obj']['data']['SubItem'][0]['Description']['months'];
//            $data['Quarter'] = 1;
            $data['Description']['region_id'] = $obj['obj']['data']['SubItem'][0]['Description']['region_id'];
            $condition = $obj['obj']['condition'];
            // $condition['DisplayType'] = 'annually';
//            $cooperative = $obj['obj']['CooperativeList'];
            //   $data = $obj['obj']['data'];
//            $description = $obj['obj']['data_description'];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();
            $commentarr = [];
            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ประจำปี ' . ($data['Description']['years'] + 543);
                    foreach ($monthList as $key => $m) {
                        $comment = CowGroupFatherService::getcomment($data['Description']['years'] - $yearList[$key], $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateCowgroupFatherExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'monthly' : $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ประจำเดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
                    $comment = CowGroupFatherService::getcomment($data['Description']['years'], $data['Description']['months'], $data['Description']['region_id']);
                    array_push($commentarr, $comment);
                    $objPHPExcel = $this->generateCowgroupFatherExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'quarter' :$header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ประจำไตรมาสที่ ' . $data['Quarter'] . ' ปี ' . ($data['Description']['years'] + 543);
                    $month = [];
                    $year = $data['Description']['years'];
                    if ($data['Description']['quarter'] == 1) {
                        $textyear = $data['Description']['years'] + 1;
                        $month = [10, 11, 12];
                        $year--;
                    } else if ($data['Description']['quarter'] == 2) {
                        $month = [1, 2, 3];
                    } else if ($data['Description']['quarter'] == 3) {
                        $month = [4, 5, 6];
                    } else if ($data['Description']['quarter'] == 4) {
                        $month = [7, 8, 9];
                    }
                    foreach ($month as $m) {
                        $comment = CowGroupFatherService::getcomment($year, $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateCowgroupFatherExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;

                default : $result = null;
            }
//            $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
//            $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data);
            $filename = 'Cowgroup-father-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateCowgroupFatherExcel($objPHPExcel, $mastesgoallist, $header, $data, $type, $commemt) {


        $objPHPExcel->getActiveSheet()->setTitle("ข้อมูลฝูงโคพ่อพันธุ์");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(18);

        $objPHPExcel->getActiveSheet()->setCellValue('A4', 'รายการ');
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(45);
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ต้นงวด');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เพิ่มขึ้น');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ลดลง');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ปลายงวด');

        $objPHPExcel->getActiveSheet()->getStyle('A4:E5')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Get sub_goal_type
        $MasterSubGoalType = MasterGoalService::getSubTypeList('ข้อมูลฝูงโคพ่อพันธุ์');

        $cnt = 5;
        foreach ($MasterSubGoalType as $key => $value) {

            $years = $data['Description']['years'];
            $months = $data['Description']['months'];
            
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, $value['sub_goal_type']);
            $cnt++;

            $MasterGoalList = MasterGoalService::getList('Y', 'ข้อมูลฝูงโคพ่อพันธุ์', [], $value['sub_goal_type'], '', []);

            $total = ['beginning_period' => 0, 
                        'total_born' => 0,
                        'total_movein' => 0,
                        'total_buy' => 0
                    ];

            foreach ($MasterGoalList as $key1 => $value1) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, '   ' . $value1['goal_name']);
                //  Get summary data by goal_id
                // echo "$years, $months, " . $value1['id'];
                // exit;
                $data_result = CowGroupFatherService::getReportData($years, $months, $value1['id']);

                $data_result['beginning_period'] = $data_result['beginning_period']?$data_result['beginning_period']:0;
                $data_result['total_born'] = $data_result['total_born']?$data_result['total_born']:0;
                $data_result['total_movein'] = $data_result['total_movein']?$data_result['total_movein']:0;
                $data_result['total_buy'] = $data_result['total_buy']?$data_result['total_buy']:0;
                
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $cnt, $data_result['beginning_period']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $cnt, $data_result['total_born']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $cnt, $data_result['total_movein']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $cnt, $data_result['total_buy']);
                
                $total['beginning_period'] += $data_result['beginning_period'];
                $total['total_born'] += $data_result['total_born'];
                $total['total_movein'] += $data_result['total_movein'];
                $total['total_buy'] += $data_result['total_buy'];
                
                $cnt++;
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $cnt, 'รวม');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $cnt, $total['beginning_period']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $cnt, $total['total_born']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $cnt, $total['total_movein']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $cnt, $total['total_buy']);
            
            $cnt++;

        }

        $objPHPExcel->getActiveSheet()->getStyle('A4:E' . ($cnt - 1))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    )
                )
        );

        return $objPHPExcel;
    }

    public function exportTrainingcowbreedExcel($request, $response) {
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        try {
            $obj = $request->getParsedBody();
            $mastesgoallist = MasterGoalService::getList('Y', 'ฝึกอบรม');
            //  print_r($mastesgoallist->toArray());
//            $data['Description']['years'] = 2018;
//            $condition['DisplayType'] = 'quarter';
//            $data['Description']['months'] = 1;
//            $data['Quarter'] = 1;
//            $data['Description']['region_id'] = 3;
            $condition = $obj['obj']['condition'];
//            $cooperative = $obj['obj']['CooperativeList'];
            $data = $obj['obj']['data'];

//            $description = $obj['obj']['data_description'];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();
            $commentarr = [];
            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ปี ' . ($data['Description']['years'] + 543);
                    foreach ($monthList as $key => $m) {
                        $comment = TrainingCowBreedService::getcomment($data['Description']['years'] - $yearList[$key], $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateTrainingcowbreedExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'monthly' : $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
                    $comment = TrainingCowBreedService::getcomment($data['Description']['years'], $data['Description']['months'], $data['Description']['region_id']);
                    array_push($commentarr, $comment);
                    $objPHPExcel = $this->generateTrainingcowbreedExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'quarter' :$header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ไตรมาสที่ ' . $data['Description']['quarter'] . ' ปี ' . ($data['Description']['years'] + 543);
                    $month = [];
                    $year = $data['Description']['years'];
                    if ($data['Description']['quarter'] == 1) {
                        $textyear = $data['Description']['years'] + 1;
                        $month = [10, 11, 12];
                        $year--;
                    } else if ($data['Description']['quarter'] == 2) {
                        $month = [1, 2, 3];
                    } else if ($data['Description']['quarter'] == 3) {
                        $month = [4, 5, 6];
                    } else if ($data['Description']['quarter'] == 4) {
                        $month = [7, 8, 9];
                    }
                    foreach ($month as $m) {
                        $comment = TrainingCowBreedService::getcomment($year, $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateTrainingcowbreedExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;

                default : $result = null;
            }
//            $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
//            $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data);
            $filename = 'Trainingcowbreed-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateTrainingcowbreedExcel($objPHPExcel, $mastesgoallist, $header, $data, $type, $comment) {

        $objPHPExcel->getActiveSheet()->setTitle("ฝึกอบรม");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'การฝึกอบรม');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'กิจกรรม/ผลิตภัณฑ์/สินค้า/บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'หน่วย');
        $objPHPExcel->getActiveSheet()->setCellValue('C3', 'เป้าหมายทั้งปี');
//  
        $row = 0;




        if ($type == 'annually') {

            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ผลการดำเนินงานประจำปี');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'เปรียบเทียบเป้าหมาย');
            $summisamt = 0;
            $summispri = 0;
            $sumcbamt = 0;
            $sumcbpri = 0;
            $sumcomamt = 0;
            $sumcompri = 0;
            $sumMamt = 0;
            $sumMpri = 0;
            foreach ($mastesgoallist as $item) {
                $cbmonth = TrainingCowBreedService::getDetailmonth($data['Description']['years'], 9, $item['id'], $data['Description']['region_id']);
                $mission = GoalMissionService::getGoaltravel($item['id'], $data['Description']['years']);

                $summisamt += $mission[0]['amount'];
                $summispri += $mission[0]['price_value'];
                foreach ($this->monthList as $key => $itemmonth) {
                    $detail = TrainingCowBreedService::getDetailmonth($data['Description']['years'], $itemmonth, $item['id'], $data['Description']['region_id']);
                    $cbmonth['amount'] += $detail['amount'];
                    $cbmonth['price'] += $detail['price'];
                }
                $sumcbamt += $cbmonth['amount'];
                $sumcbpri += $cbmonth['price'];
                $sumcomamt += $cbmonth['amount'] - ($mission[0]['amount']);
                $sumcompri += $cbmonth['price'] - ($mission[0]['price_value'] );
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'ปริมาณ' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        รายได้' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'ครั้ง');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $mission[0]['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $mission[0]['price_value']);

                $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $cbmonth['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $cbmonth['price']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $cbmonth['amount'] - ($mission[0]['amount']));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $cbmonth['price'] - ($mission[0]['price_value']));

                $row += 2;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'จำนวนรวม');
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมรายได้');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'ครั้ง');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $summisamt);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $summispri);

            $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $sumcbamt);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $sumcbpri);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $sumcomamt);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumcompri);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row) . ':E' . (4 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':E' . (5 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
        } else if ($type == 'monthly') {
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'ผลการดำเนินงานประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'เปรียบเทียบเป้าหมาย');
            $summisamt = 0;
            $summispri = 0;
            $sumcbamt = 0;
            $sumcbpri = 0;
            $sumcomamt = 0;
            $sumcompri = 0;
            $sumMamt = 0;
            $sumMpri = 0;
            foreach ($mastesgoallist as $item) {
                $cbmonth = TrainingCowBreedService::getDetailmonth($data['Description']['years'], $data['Description']['months'], $item['id'], $data['Description']['region_id']);
                $mission = GoalMissionService::getGoaltravel($item['id'], $data['Description']['years']);
                $avg = GoalMissionService::getMissionavg($mission[0]['id'], $data['Description']['years'], $data['Description']['months']);
                
                $summisamt += $mission[0]['amount'];
                $summispri += $mission[0]['price_value'];
                $sumMamt += $avg[0]['amount'];
                $sumMpri += $avg[0]['price_value'];
                $sumcbamt += $cbmonth['amount'];
                $sumcbpri += $cbmonth['price'];
                $sumcomamt += $cbmonth['amount'] - ($avg[0]['amount'] );
                $sumcompri += $cbmonth['price'] - ($avg[0]['price_value'] );
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'ปริมาณ' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        รายได้' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'ครั้ง');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $mission[0]['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $mission[0]['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $avg[0]['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $avg[0]['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $cbmonth['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $cbmonth['price']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $cbmonth['amount'] - ($avg[0]['amount'] ));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $cbmonth['price'] - ($avg[0]['price_value'] ));

                $row += 2;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'จำนวนรวม');
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมรายได้');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'ครั้ง');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $summisamt);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $summispri);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $sumMamt);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $sumMpri);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $sumcbamt);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumcbpri);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $sumcomamt);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $sumcompri);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row) . ':F' . (4 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':F' . (5 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'ผลการดำเนินงานประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'เปรียบเทียบเป้าหมาย');
            $summisamt = 0;
            $summispri = 0;
            $sumcbamt = 0;
            $sumcbpri = 0;
            $sumcomamt = 0;
            $sumcompri = 0;
            $sumMamt = 0;
            $sumMpri = 0;
            foreach ($mastesgoallist as $item) {
                $cbmonth = TrainingCowBreedService::getDetailquar($data['Description']['years'], $item['id'], $data['Description']['region_id'], $data['Description']['quarter']);

                if ($data['Description']['quarter'] == 1) {
                    $y = $data['Description']['years'] + 1;

                    $mission = GoalMissionService::getGoaltravel($item['id'], $y);
                } else {
                    $mission = GoalMissionService::getGoaltravel($item['id'], $data['Description']['years']);
                }
                $missionM = GoalMissionService::getMissionavgquar($mission[0]['id'], $data['Description']['years'], $data['Description']['quarter']);
                $summisamt += $mission[0]['amount'];
                $summispri += $mission[0]['price_value'];
                $sumMamt += $missionM['amount'];
                $sumMpri += $missionM['price_value'];
                $sumcbamt += $cbmonth['amount'];
                $sumcbpri += $cbmonth['price'];
                $sumcomamt += $cbmonth['amount'] - ($missionM['amount'] );
                $sumcompri += $cbmonth['price'] - ($missionM['price_value'] );
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'ปริมาณ' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        รายได้' . $item['goal_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'ครั้ง');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $mission[0]['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $mission[0]['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $missionM['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $missionM['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $cbmonth['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $cbmonth['price']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $cbmonth['amount'] - ($missionM['amount'] ));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $cbmonth['price'] - ($missionM['price_value'] ));

                $row += 2;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), 'จำนวนรวม');
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), 'รวมรายได้');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), 'ครั้ง');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $summisamt);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $summispri);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $sumMamt);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $sumMpri);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $sumcbamt);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $sumcbpri);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $sumcomamt);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $sumcompri);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row) . ':F' . (4 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row) . ':F' . (5 + $row))->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
        }


        $highestRow = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
        $highestColum = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $highestColum . '1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:' . $highestColum . '2');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(18);


        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A4:A' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:A' . $highestRow)->getFont()->setSize(14);

        $objPHPExcel->getActiveSheet()->getStyle('B4:' . $highestColum . $highestRow)->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('B4:B' . $highestRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'BFBFBF')
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColum . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColum . $highestRow)->applyFromArray(
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
        $row = $highestRow + 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'หมายเหตุ :');
        $row++;
        foreach ($comment as $item) {
            foreach ($item as $com) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($com['cooperative_name']));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), ($this->getMonthName($com['months'])));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), ($com['user_comment']));
                $objPHPExcel->getActiveSheet()->mergeCells('C' . $row . ':E' . ($row ));
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->applyFromArray(
                        array(
                            'font' => array(
                                'name' => 'AngsanaUPC'
                            )
                        )
                );
                $row++;
            }
        }
        return $objPHPExcel;
    }

    public function exportInseminationExcel($request, $response) {
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        try {
            $obj = $request->getParsedBody();
            $mastesgoallist = MasterGoalService::getList('Y', 'ผสมเทียม');

//            $data['Description']['years'] = 2018;
//            $condition['DisplayType'] = 'monthly';
//            $data['Description']['months'] = 1;
//            $data['Quarter'] = 1;
//            $data['Description']['region_id'] = 3;
            $condition = $obj['obj']['condition'];
//            $cooperative = $obj['obj']['CooperativeList'];
            $data = $obj['obj']['data'];
//            $description = $obj['obj']['data_description'];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();
            $commentarr = [];
            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ปี ' . ($data['Description']['years'] + 543);
                    foreach ($monthList as $key => $m) {
                        $comment = InseminationService::getcomment($data['Description']['years'] - $yearList[$key], $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateInseminationExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'monthly' : $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
                    $comment = InseminationService::getcomment($data['Description']['years'], $data['Description']['months'], $data['Description']['region_id']);
                    array_push($commentarr, $comment);
                    $objPHPExcel = $this->generateInseminationExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;
                case 'quarter' :$header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ไตรมาสที่ ' . $data['Quarter'];
                    $month = [];
                    $year = $data['Description']['years'];
                    if ($data['Description']['quarter'] == 1) {
                        $textyear = $data['Description']['years'] + 1;
                        $month = [10, 11, 12];
                        $year--;
                    } else if ($data['Description']['quarter'] == 2) {
                        $month = [1, 2, 3];
                    } else if ($data['Description']['quarter'] == 3) {
                        $month = [4, 5, 6];
                    } else if ($data['Description']['quarter'] == 4) {
                        $month = [7, 8, 9];
                    }
                    foreach ($month as $m) {
                        $comment = InseminationService::getcomment($year, $m, $data['Description']['region_id']);
                        array_push($commentarr, $comment);
                    }
                    $objPHPExcel = $this->generateInseminationExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType'], $commentarr);
                    break;

                default : $result = null;
            }
//            $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
//            $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data);
            $filename = 'Insemination-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateInseminationExcel($objPHPExcel, $mastesgoallist, $header, $data, $type, $comment) {

        $objPHPExcel->getActiveSheet()->setTitle("ผสมเทียม");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'ผสมเทียม');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'กิจกรรม/ผลิตภัณฑ์/สินค้า/บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'หน่วย');
        $objPHPExcel->getActiveSheet()->setCellValue('C3', 'เป้าหมายทั้งปี');

//  
        $row = 0;




        if ($type == 'annually') {

            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ผลการดำเนินงานประจำปี');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'เปรียบเทียบเป้าหมาย');
            $summisamt = 0;
            $summispri = 0;
            $sumcbamt = 0;
            $sumcbpri = 0;
            $sumcomamt = 0;
            $sumcompri = 0;
            $sumMamt = 0;
            $sumMpri = 0;
            foreach ($mastesgoallist as $item) {


                $mission = GoalMissionService::getMissionforinsem($item['id'], $data['Description']['years']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), $item['goal_name']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row))->getFont()->setSize(16);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item['goal_name'] . 'รายตัว');
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'ตัว');
                $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row))->getFont()->setSize(16);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
                foreach ($mission as $itemmission) {
                    $inmonth = InseminationService::getDetailmonth($data['Description']['years'], 9, $itemmission['region_id']);
                    $summisamt += $itemmission['amount'];
                    $summispri += $itemmission['price_value'];
//                    $sumMamt += $itemmission['amount'] ;
//                    $sumMpri += $itemmission['price_value'] ;
                    foreach ($this->monthList as $key => $itemmonth) {
                        $detail = InseminationService::getDetailmonth($data['Description']['years'], $itemmonth, $itemmission['region_id']);
                        $inmonth['amount'] += $detail['amount'];
                        $inmonth['price'] += $detail['price'];
                    }
                    $sumcbamt += $inmonth['amount'];
                    $sumcbpri += $inmonth['price'];
                    $sumcomamt += $inmonth['amount'] - ($itemmission['amount']);
                    $sumcompri += $inmonth['price'] - ($itemmission['price_value']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), '    - ' . $itemmission['RegionName']);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(16);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemmission['amount']);
//                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemmission['amount'] / 12);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $inmonth['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $inmonth['amount'] - ($itemmission['amount'] / 12));

                    $row ++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), 'รายได้ค่าบริการ ');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), 'บาท');
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row) . $highestRow)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row) . $highestRow)->getFont()->setSize(16);
                $row ++;
                foreach ($mission as $itemmission) {
                    $inmonth = InseminationService::getDetailmonth($data['Description']['years'], 9, $itemmission['region_id']);
                    foreach ($this->monthList as $key => $itemmonth) {
                        $detail = InseminationService::getDetailmonth($data['Description']['years'], $itemmonth, $itemmission['region_id']);
                        $inmonth['amount'] += $detail['amount'];
                        $inmonth['price'] += $detail['price'];
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), '    - ' . $itemmission['RegionName']);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(16);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemmission['price_value']);
                    //   $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemmission['price_value'] / 12);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $inmonth['price']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $inmonth['price'] - ($itemmission['price_value'] / 12));

                    $row ++;
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), 'รวมการบริการผสมเทียม');
            $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(16);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รวมรายได้ค่าบริการผสมเทียม');
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row))->getFont()->setSize(16);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), 'ตัว.');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), 'บาท');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $summisamt);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), $summispri);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $sumMamt);
//            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $sumMpri);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $sumcbamt);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $sumcbpri);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $sumcomamt);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), $sumcompri);

            $objPHPExcel->getActiveSheet()->getStyle('A3:E4')->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
        } else if ($type == 'monthly') {
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'ผลการดำเนินงานประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'เปรียบเทียบเป้าหมาย');
            $summisamt = 0;
            $summispri = 0;
            $sumcbamt = 0;
            $sumcbpri = 0;
            $sumcomamt = 0;
            $sumcompri = 0;
            $sumMamt = 0;
            $sumMpri = 0;
            foreach ($mastesgoallist as $item) {

                $mission = GoalMissionService::getMissionforinsem($item['id'], $data['Description']['years']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), $item['goal_name']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row))->getFont()->setSize(16);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item['goal_name'] . 'รายตัว');
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'ตัว');
                $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row))->getFont()->setSize(16);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
                foreach ($mission as $itemmission) {
                    $inmonth = InseminationService::getDetailmonth($data['Description']['years'], $data['Description']['months'], $itemmission['region_id']);
                    $missionM = GoalMissionService::getMissionavg($itemmission['id'], $data['Description']['years'], $data['Description']['months']);
                    $summisamt += $itemmission['amount'];
                    $summispri += $itemmission['price_value'];
                    $sumMamt += $missionM[0]['amount'];
                    $sumMpri += $missionM[0]['price_value'];
                    $sumcbamt += $inmonth['amount'];
                    $sumcbpri += $inmonth['price'];
                    $sumcomamt += $inmonth['amount'] - ($missionM[0]['amount']);
                    $sumcompri += $inmonth['price'] - ($missionM[0]['price_value'] );
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), '    - ' . $itemmission['RegionName']);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(16);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemmission['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $missionM[0]['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $inmonth['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $inmonth['amount'] - ($missionM[0]['amount']));

                    $row ++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), 'รายได้ค่าบริการ ');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), 'บาท');
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(16);
                $row ++;

                foreach ($mission as $itemmission) {
                    $inmonth = InseminationService::getDetailmonth($data['Description']['years'], $data['Description']['months'], $itemmission['region_id']);
                    $missionM = GoalMissionService::getMissionavg($itemmission['id'], $data['Description']['years'], $data['Description']['months']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), '    - ' . $itemmission['RegionName']);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(16);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemmission['price_value']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $missionM[0]['price_value']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $inmonth['price']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $inmonth['price'] - ($missionM[0]['price_value']));

                    $row ++;
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), 'รวมการบริการผสมเทียม');
            $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(16);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รวมรายได้ค่าบริการผสมเทียม');
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row))->getFont()->setSize(16);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), 'ตัว.');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), 'บาท');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $summisamt);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), $summispri);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $sumMamt);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $sumMpri);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $sumcbamt);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), $sumcbpri);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $sumcomamt);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $sumcompri);

            $objPHPExcel->getActiveSheet()->getStyle('A3:F4')->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'ผลการดำเนินงานประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'เปรียบเทียบเป้าหมาย');
            $summisamt = 0;
            $summispri = 0;
            $sumcbamt = 0;
            $sumcbpri = 0;
            $sumcomamt = 0;
            $sumcompri = 0;
            $sumMamt = 0;
            $sumMpri = 0;


            foreach ($mastesgoallist as $item) {

                if ($data['Description']['quarter'] == 1) {
                    $y = $data['Description']['years'] + 1;

                    $mission = GoalMissionService::getMissionforinsem($item['id'], $y);
                } else {
                    $mission = GoalMissionService::getMissionforinsem($item['id'], $data['Description']['years']);
                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), $item['goal_name']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row))->getFont()->setSize(16);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), $item['goal_name'] . 'รายตัว');
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'ตัว');
                $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $row))->getFont()->setSize(16);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (5 + $row))->getFont()->setSize(16);
//                print_r($mission);
                foreach ($mission as $itemmission) {
                    $inmonth = InseminationService::getDetailquar($data['Description']['years'], $itemmission['region_id'], $data['Description']['quarter']);
                    $missionM = GoalMissionService::getMissionavgquar($itemmission['id'], $data['Description']['years'], $data['Description']['quarter']);

                    $summisamt += $itemmission['amount'];
                    $summispri += $itemmission['price_value'];
                    $sumMamt += $missionM['amount'];
                    $sumMpri += $missionM['price_value'];
                    $sumcbamt += $inmonth['amount'];
                    $sumcbpri += $inmonth['price'];
                    $sumcomamt += $inmonth['amount'] - ($missionM[0]['amount'] );
                    $sumcompri += $inmonth['price'] - ($missionM[0]['price_value']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), '    - ' . $itemmission['RegionName']);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(16);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemmission['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $missionM['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $inmonth['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $inmonth['amount'] - ($missionM['amount'] ));

                    $row ++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), 'รายได้ค่าบริการ ');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), 'บาท');
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(16);
                $row ++;
                foreach ($mission as $itemmission) {
                    $inmonth = InseminationService::getDetailquar($data['Description']['years'], $itemmission['region_id'], $data['Description']['quarter']);
                    $missionM = GoalMissionService::getMissionavgquar($itemmission['id'], $data['Description']['years'], $data['Description']['quarter']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), '    - ' . $itemmission['RegionName']);
                    $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(16);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemmission['price_value']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $missionM['price_value']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $inmonth['price']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $inmonth['price'] - ($missionM['price_value'] ));

                    $row ++;
                }
//                   die();
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), 'รวมการบริการผสมเทียม');
            $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(16);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $row), 'รวมรายได้ค่าบริการผสมเทียม');
            $objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $row))->getFont()->setSize(16);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), 'ตัว.');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $row), 'บาท');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $summisamt);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $row), $summispri);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $sumMamt);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $row), $sumMpri);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $sumcbamt);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $row), $sumcbpri);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $sumcomamt);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $row), $sumcompri);

            $objPHPExcel->getActiveSheet()->getStyle('A3:F4')->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'BFBFBF')
                        )
                    )
            );
        }


        $highestRow = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
        $highestColum = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $highestColum . '1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:' . $highestColum . '2');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(18);


        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



        $objPHPExcel->getActiveSheet()->getStyle('B4:' . $highestColum . $highestRow)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('B4:B' . $highestRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'BFBFBF')
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColum . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColum . $highestRow)->applyFromArray(
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
        $row = $highestRow + 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'หมายเหตุ :');
        $row++;
        foreach ($comment as $item) {
            foreach ($item as $com) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($com['cooperative_name']));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), ($this->getMonthName($com['months'])));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), ($com['user_comment']));
                $objPHPExcel->getActiveSheet()->mergeCells('C' . $row . ':E' . ($row ));
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':E' . ($row ))->applyFromArray(
                        array(
                            'font' => array(
                                'name' => 'AngsanaUPC'
                            )
                        )
                );
                $row++;
            }
        }
        return $objPHPExcel;
    }

}
