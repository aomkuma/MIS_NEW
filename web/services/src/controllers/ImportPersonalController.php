<?php

namespace App\Controller;

use App\Service\AttachFileService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ImportPersonalController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function import($request, $response) {
//        error_reporting(E_ERROR);
//        error_reporting(E_ALL);
//        ini_set('display_errors', 'On');
        $_WEB_FILE_PATH = 'files/files';
        try {
            $params = $request->getParsedBody();
            $Data = $params['obj']['Data'];
//            print_r($Data);
//            die();
            $files = $request->getUploadedFiles();

            if ($files != null) {
                $name = $files['obj']['AttachFile']->getClientFilename();
                $ext = pathinfo($name, 4);
                $FileName = date('YmdHis') . '_' . rand(100000, 999999) . '.' . $ext;
                $FilePath = $_WEB_FILE_PATH . '/import/' . $FileName;
                $AttachFile = ['file_name' => $name
                    , 'file_path' => $FilePath
                ];
                $attid = AttachFileService::updateAttachFiles($AttachFile, $Data);

                $files['obj']['AttachFile']->moveTo('../../' . $FilePath);
                $this->readExcelFile('../../' . $FilePath, $attid, $Data);
                return 'OK';
            }
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function readExcelFile($file, $id, $Data) {

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
    

        $spreadsheet->setActiveSheetIndex(0);
        $sheet1 = $spreadsheet->getActiveSheet()->toArray();
//        $highestRow1 = $spreadsheet->getActiveSheet()->getHighestRow();
        $sheetid1 = AttachFileService::savesheet($id, 'รายละเอียด', 1);
        $size = sizeof($sheet1) - 2;
//
        for ($i = 5; $i < $size; $i++) {
            AttachFileService::saverow($sheetid1, $sheet1[$i], $Data, 1);
        }

        //sheet2
        $sheet2 = $spreadsheet->getSheet(1)->toArray();
        $sheetid2 = AttachFileService::savesheet($id, 'นักวิชาการ', 2);
        $size2 = sizeof($sheet2) - 1;

        for ($i = 2; $i < $size2; $i++) {
            AttachFileService::saverow2($sheetid2, $sheet2[$i], $Data, 2);
        }
        //sheet3
        $sheet3 = $spreadsheet->getSheet(2)->toArray();
        $sheetid3 = AttachFileService::savesheet($id, 'เคลื่อนไหว', 3);
        $size3 = sizeof($sheet3) - 1;

        for ($i = 4; $i < $size3; $i++) {
            AttachFileService::saverow3($sheetid3, $sheet3[$i], $Data);
        }
    }

    public function getMainList($request, $response, $args) {
        try {

            $list = AttachFileService::getList();
//            print_r($list);
//            print_r('in');
            return $this->returnResponse(200, $list, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $list, $e, $response);
        }
    }

}
