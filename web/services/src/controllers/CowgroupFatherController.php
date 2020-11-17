<?php

namespace App\Controller;

use App\Service\CowgroupFatherService;
use App\Service\CooperativeService;
use App\Service\MasterGoalService;

class CowgroupFatherController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
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
            
            $Data = CowgroupFatherService::loadDataApprove($user_session['UserID']);
            
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
            // $regions = $params['obj']['region'];
            $regions = $condition['Region'];
            // print_r($condition);
            // exit;
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
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28'; // .CowgroupFatherController::getLastDayOfMonth($ym);
      
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

        // $TypeList = [
        //     ['id' => 1, 'name' => 'ฝูงโคต้นงวด', 'field_name' => 'beginning_period_total_values']
        //     // ,['id'=>2, 'name'=>'โคเพิ่ม']
        //     // ,['id'=>3, 'name'=>'โคลด']
        //     , ['id' => 4, 'name' => 'ฝูงโคปลายงวด', 'field_name' => 'last_period_total_values']
        //         // ,['id'=>5, 'name'=>'การจำหน่ายโค']
        // ];

        $TypeList = MasterGoalService::getSubTypeList('ข้อมูลฝูงโคพ่อพันธุ์');
        
        for ($i = 0; $i < $diffMonth; $i++) {

            // Prepare condition
            $curYear = $condition['YearTo'];
            $beforeYear = $condition['YearTo'] - 1;

            // Loop User Regions
            foreach ($TypeList as $key => $value) {

                $region_id = $regions;
                // $monthName = CowgroupFatherController::getMonthName($curMonth);

                $data = [];
                $data['MainItem'] = $value['sub_goal_type'];
                $field_name = 'total_buy';//$value['field_name'];
                // Load master goal
                $MasterGoalList = MasterGoalService::getList('Y', 'ข้อมูลฝูงโคพ่อพันธุ์', [], $value['sub_goal_type']);

                foreach ($MasterGoalList as $k => $v) {
                    $sub_data = [];
                    $sub_data['SubItem'] = $v['goal_name'];
                    $goal_id = $v['id'];
                    // get cooperative type

                    $Current = CowgroupFatherService::getMainList($curYear, $curMonth, $region_id, $goal_id, $field_name);
                    $sub_data['CurrentUnit'] = 'ตัว';
                    $sub_data['CurrentPercentage'] = floatval($Current['sum_baht']);

                    $Before = CowgroupFatherService::getMainList($beforeYear, $curMonth, $region_id, $goal_id, $field_name);
                    $sub_data['BeforeUnit'] = 'ตัว';
                    $sub_data['BeforePercentage'] = floatval($Before['sum_baht']);

                    $DiffAmount = $data['CurrentPercentage'] - $data['BeforePercentage'];
                    $sub_data['DiffUnit'] = 'ตัว'; //$DiffAmount;
                    if ($sub_data['BeforePercentage'] != 0) {
                        $sub_data['DiffPercentage'] = (($sub_data['CurrentPercentage'] - $sub_data['BeforePercentage']) /$sub_data['BeforePercentage']) * 100;
                    } else if(empty($sub_data['BeforePercentage']) && !empty($sub_data['CurrentPercentage'])){
                        $sub_data['DiffPercentage'] = 100;
                    }


                    $sub_data['Description'] = ['months' => $curMonth
                        , 'years' => $curYear
                        , 'region_id' => $region_id
                    ];

                    // array_push($data, $sub_data);
                    $data['SubItem'][] = $sub_data;
                    $DataSummary['SummaryCurrentCowgroupFatherAmount'] = $DataSummary['SummaryCurrentCowgroupFatherAmount'] + $data['CurrentPercentage'];
                    $DataSummary['SummaryBeforCowgroupFatherAmount'] = $DataSummary['SummaryBeforCowgroupFatherAmount'] + $data['BeforePercentage'];
                    $DataSummary['SummaryCowgroupFatherAmountPercentage'] = 0;
                    $DataSummary['SummaryCurrentCowgroupFatherIncome'] = $DataSummary['SummaryCurrentCowgroupFatherIncome'] + $data['CurrentPercentage'];
                    $DataSummary['SummaryBeforeCowgroupFatherIncome'] = $DataSummary['SummaryBeforeCowgroupFatherIncome'] + $data['BeforePercentage'];
                    $DataSummary['SummaryCowgroupFatherIncomePercentage'] = 0;
                }
                array_push($DataList, $data);
            }

            $curMonth++;
        }

        $DataSummary['SummaryCowgroupFatherAmountPercentage'] = (($DataSummary['SummaryCurrentCowgroupFatherAmount'] - $DataSummary['SummaryBeforCowgroupFatherAmount']) /$DataSummary['SummaryBeforCowgroupFatherAmount']) * 100;
            $DataSummary['SummaryCowgroupFatherIncomePercentage'] = (($DataSummary['SummaryCurrentCowgroupFatherIncome'] - $DataSummary['SummaryBeforeCowgroupFatherIncome']) /$DataSummary['SummaryBeforeCowgroupFatherIncome']) * 100;

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getData($request, $response, $args) {
        try {
            $params = $request->getParsedBody();

            $id = $params['obj']['id'];
            $cow_group_name = $params['obj']['cow_group_name'];
            $cooperative_id = $params['obj']['cooperative_id'];
            $months = $params['obj']['months'];
            $years = $params['obj']['years'];

            if (!empty($id)) {
                $_Data = CowgroupFatherService::getDataByID($id);
            } else {
                $_Data = CowgroupFatherService::getData($cow_group_name, $cooperative_id, $months, $years);
            }

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
            if($HeaderData['data']['DATA']['Header']['OrgType'] == 'DEPARTMENT'){
                $_Data['dep_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['dep_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];

            }else if($HeaderData['data']['DATA']['Header']['OrgType'] == 'DIVISION'){
                $_Data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['division_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];

            }else if($HeaderData['data']['DATA']['Header']['OrgType'] == 'OFFICE'){
                $_Data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['office_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
            }

            $_Detail = $params['obj']['Detail'];

            // get region from cooperative id
            $Cooperative = CooperativeService::getData($_Data['cooperative_id']);
            $_Data['region_id'] = $Cooperative['region_id'];
            // print_r($_Data);
            // exit();

            $id = CowgroupFatherService::updateData($_Data);

            foreach ($_Detail as $key => $value) {
                $value['cow_group_id'] = $id;
                CowgroupFatherService::updateDetailData($value);
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
            $result = CowgroupFatherService::removeDetailData($id);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getDataListquar($condition, $regions) {

        $curQuarter = intval($condition['QuarterFrom']);
        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        if (intval($curQuarter) == 1) {
            $curYear = intval($condition['YearFrom']) - 1;
        }
        if ($curQuarter == 1) {
            $monthst = 10;
            $monthen = 12;
        } else if ($curQuarter == 2) {
            $monthst = 1;
            $monthen = 3;
        } else if ($curQuarter == 3) {
            $monthst = 4;
            $monthen = 6;
        } else if ($curQuarter == 4) {
            $monthst = 7;
            $monthen = 9;
        }
        $DataList = [];
        $DataSummary = [];

        $TypeList = [
            ['id' => 1, 'name' => 'ฝูงโคต้นงวด', 'field_name' => 'beginning_period_total_values']
            // ,['id'=>2, 'name'=>'โคเพิ่ม']
            // ,['id'=>3, 'name'=>'โคลด']
            , ['id' => 4, 'name' => 'ฝูงโคปลายงวด', 'field_name' => 'last_period_total_values']
                // ,['id'=>5, 'name'=>'การจำหน่ายโค']
        ];

        $MasterGoalList = MasterGoalService::getList('Y', 'ข้อมูลฝูงโค');



        // Prepare condition
        // Loop User Regions
        foreach ($TypeList as $key => $value) {

            $region_id = $regions;
            // $monthName = CowgroupFatherController::getMonthName($curMonth);

            $data = [];
            $data['MainItem'] = $value['name'];
            $field_name = $value['field_name'];
            // Load master goal

            foreach ($MasterGoalList as $k => $v) {
                $sub_data = [];
                $sub_data['SubItem'] = $v['goal_name'];
                $goal_id = $v['id'];
                // get cooperative type

                $Current = CowgroupFatherService::getMainListquar($curYear, $monthst, $monthen, $region_id, $goal_id, $field_name);
                $sub_data['CurrentUnit'] = 'ตัว';
                $sub_data['CurrentPercentage'] = floatval($Current['sum_baht']);

                $Before = CowgroupFatherService::getMainListquar($beforeYear, $monthst, $monthen, $region_id, $goal_id, $field_name);
                $sub_data['BeforeUnit'] = 'ตัว';
                $sub_data['BeforePercentage'] = floatval($Before['sum_baht']);

                $DiffAmount = $data['CurrentPercentage'] - $data['BeforePercentage'];
                $sub_data['DiffUnit'] = 'ตัว'; //$DiffAmount;
                if ($sub_data['BeforePercentage'] != 0) {
                    $sub_data['DiffPercentage'] = ($sub_data['CurrentPercentage'] / $sub_data['BeforePercentage']) * 100;
                } else {
                    $sub_data['DiffPercentage'] = 100;
                }


                $sub_data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                // array_push($data, $sub_data);
                $data['SubItem'][] = $sub_data;
                $DataSummary['SummaryCurrentCowgroupFatherAmount'] = $DataSummary['SummaryCurrentCowgroupFatherAmount'] + $data['CurrentPercentage'];
                $DataSummary['SummaryBeforCowgroupFatherAmount'] = $DataSummary['SummaryBeforCowgroupFatherAmount'] + $data['BeforePercentage'];
                $DataSummary['SummaryCowgroupFatherAmountPercentage'] = 0;
                $DataSummary['SummaryCurrentCowgroupFatherIncome'] = $DataSummary['SummaryCurrentCowgroupFatherIncome'] + $data['CurrentPercentage'];
                $DataSummary['SummaryBeforeCowgroupFatherIncome'] = $DataSummary['SummaryBeforeCowgroupFatherIncome'] + $data['BeforePercentage'];
                $DataSummary['SummaryCowgroupFatherIncomePercentage'] = 0;
            }
            array_push($DataList, $data);
        }



        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getDataListannual($condition, $regions) {

        $curYear = $condition['YearFrom'];

        $beforeYear = $curYear - 1;
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearList = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $DataList = [];
        $DataSummary = [];

        $TypeList = [
            ['id' => 1, 'name' => 'ฝูงโคต้นงวด', 'field_name' => 'beginning_period_total_values']
            // ,['id'=>2, 'name'=>'โคเพิ่ม']
            // ,['id'=>3, 'name'=>'โคลด']
            , ['id' => 4, 'name' => 'ฝูงโคปลายงวด', 'field_name' => 'last_period_total_values']
                // ,['id'=>5, 'name'=>'การจำหน่ายโค']
        ];

        $MasterGoalList = MasterGoalService::getList('Y', 'ข้อมูลฝูงโค');



        // Prepare condition
        // Loop User Regions
        foreach ($TypeList as $key => $value) {

            $region_id = $regions;
            // $monthName = CowgroupFatherController::getMonthName($curMonth);

            $data = [];
            $data['MainItem'] = $value['name'];
            $field_name = $value['field_name'];
            // Load master goal

            foreach ($MasterGoalList as $k => $v) {
                $sub_data = [];
                $sub_data['SubItem'] = $v['goal_name'];
                $goal_id = $v['id'];
                // get cooperative type
                for ($j = 0; $j < 12; $j++) {
                    $curMonth = $monthList[$j];
                    $Current = CowgroupFatherService::getMainList($curYear-$yearList[$j], $curMonth, $region_id, $goal_id, $field_name);
                    $sub_data['CurrentUnit'] = 'ตัว';
                    $sub_data['CurrentPercentage'] += floatval($Current['sum_baht']);

                    $Before = CowgroupFatherService::getMainList($beforeYear-$yearList[$j], $curMonth, $region_id, $goal_id, $field_name);
                    $sub_data['BeforeUnit'] = 'ตัว';
                    $sub_data['BeforePercentage'] += floatval($Before['sum_baht']);

                    $DiffAmount = $data['CurrentPercentage'] - $data['BeforePercentage'];
                    $sub_data['DiffUnit'] = 'ตัว'; //$DiffAmount;
                    if ($sub_data['BeforePercentage'] != 0) {
                        $sub_data['DiffPercentage'] = ($sub_data['CurrentPercentage'] / $sub_data['BeforePercentage']) * 100;
                    } else {
                        $sub_data['DiffPercentage'] = 100;
                    }


                    $sub_data['Description'] = ['months' => $curMonth
                        , 'years' => $curYear
                        , 'region_id' => $region_id
                    ];

                    // array_push($data, $sub_data);
                    
                    $DataSummary['SummaryCurrentCowgroupFatherAmount'] = $DataSummary['SummaryCurrentCowgroupFatherAmount'] + $data['CurrentPercentage'];
                    $DataSummary['SummaryBeforCowgroupFatherAmount'] = $DataSummary['SummaryBeforCowgroupFatherAmount'] + $data['BeforePercentage'];
                    $DataSummary['SummaryCowgroupFatherAmountPercentage'] = 0;
                    $DataSummary['SummaryCurrentCowgroupFatherIncome'] = $DataSummary['SummaryCurrentCowgroupFatherIncome'] + $data['CurrentPercentage'];
                    $DataSummary['SummaryBeforeCowgroupFatherIncome'] = $DataSummary['SummaryBeforeCowgroupFatherIncome'] + $data['BeforePercentage'];
                    $DataSummary['SummaryCowgroupFatherIncomePercentage'] = 0;
                }
                $data['SubItem'][] = $sub_data;
            }
            array_push($DataList, $data);
        }



        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function updateDataApprove($request, $response, $args){
            // $URL = '172.23.10.224';
            $URL = '127.0.0.1';
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $id = $params['obj']['id'];
                $ApproveStatus = $params['obj']['ApproveStatus'];
                $ApproveComment = $params['obj']['ApproveComment'];
                $OrgType = $params['obj']['OrgType'];
                $approval_id = $user_session['UserID'];
                $OrgID = $user_session['OrgID'];

                if($ApproveStatus == 'approve'){
                    // http post to dpo database to retrieve division's header
                    $HeaderData = $this->do_post_request('http://' . $URL . '/dportal/dpo/public/mis/get/org/header/', "POST", ['UserID' => $approval_id, 'OrgID' => $OrgID]);
                    $HeaderData = json_decode(trim($HeaderData), TRUE);
                    
                    $data = [];
                    $ApproveComment = '';

                    if($OrgType == 'dep'){
                        $data['dep_approve_date'] = date('Y-m-d H:i:s');
                        $data['dep_approve_comment'] = $ApproveComment;
                        $data['dep_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];

                        $data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                    }else if($OrgType == 'division'){
                        $data['division_approve_date'] = date('Y-m-d H:i:s');
                        $data['division_approve_comment'] = $ApproveComment;
                        $data['division_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];

                        $data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                    }else if($OrgType == 'office'){
                        $data['office_approve_date'] = date('Y-m-d H:i:s');
                        $data['office_approve_comment'] = $ApproveComment;
                        $data['office_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                        
                    }
                }else if($ApproveStatus == 'reject'){

                    if($OrgType == 'dep'){
                        $data['dep_approve_date'] = date('Y-m-d H:i:s');                  
                        $data['dep_approve_comment'] = $ApproveComment;
                        $data['dep_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                    }else if($OrgType == 'division'){
                        $data['dep_approve_date'] = NULL;                  
                        $data['dep_approve_comment'] = NULL;
                        
                        $data['division_approve_id'] = NULL;
                        $data['division_approve_date'] = date('Y-m-d H:i:s');
                        $data['division_approve_comment'] = $ApproveComment;
                        $data['division_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                    }else if($OrgType == 'office'){

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
                $result = CowgroupFatherService::updateDataApprove($id, $data);

                $this->data_result['DATA']['result'] = $result;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }   
        }

        private function do_post_request($url, $method, $data = [], $optional_headers = null)
        {
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
                    return array("STATUS"=>'ERROR',"MSG"=>"ERROR :: Problem with $url");
                //throw new Exception("Problem with $url, $php_errormsg");
              }
              $response = @stream_get_contents($fp);
              if ($response === false) {
                print_r($response);
                    return array("STATUS"=>'ERROR',"MSG"=>"ERROR :: Problem reading data from $url");
    //            throw new Exception("Problem reading data from $url");
              }

              return $response;
              
        }

}
