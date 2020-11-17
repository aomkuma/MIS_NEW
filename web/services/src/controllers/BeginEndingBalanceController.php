<?php

    namespace App\Controller;
    
    use App\Service\BeginEndingBalanceService;

    class BeginEndingBalanceController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        private function findQuarterAndFiscalYear($months, $years){

            $quarter = null;
            $fiscal_year = null;

            switch ($months) {
                case '1': $quarter = 2; $fiscal_year = $years; break;
                case '2': $quarter = 2; $fiscal_year = $years; break;
                case '3': $quarter = 2; $fiscal_year = $years; break;
                case '4': $quarter = 3; $fiscal_year = $years; break;
                case '5': $quarter = 3; $fiscal_year = $years; break;
                case '6': $quarter = 3; $fiscal_year = $years; break;
                case '7': $quarter = 4; $fiscal_year = $years; break;
                case '8': $quarter = 4; $fiscal_year = $years; break;
                case '9': $quarter = 4; $fiscal_year = $years; break;
                case '10': $quarter = 1; $fiscal_year = $years + 1; break;
                case '11': $quarter = 1; $fiscal_year = $years + 1; break;
                case '12': $quarter = 1; $fiscal_year = $years + 1; break;
            }

            return ['quarters' => $quarter, 'fiscal_years' => $fiscal_year];
        }

        public function getList($request, $response, $args){
            try {

                $params = $request->getParsedBody();
                $condition = $params['obj']['condition'];

                $DataList = BeginEndingBalanceService::getMainList($condition);

                $this->data_result['DATA']['DataList'] = $DataList;

                return $this->returnResponse(200, $this->data_result, $response, false);

            } catch (\Exception $e) {
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getData($request, $response, $args){
            try {

                $params = $request->getParsedBody();
                $months = $params['obj']['condition']['months'];
                $years = $params['obj']['condition']['years'];
                $RegionList = $params['obj']['region'];

                $milk_type = ['นมพาณิชย์', 'นมโรงเรียน'];

                $Data = BeginEndingBalanceService::getData($months, $years);
                $DetailList = [];
                $Summary = [];
                foreach ($milk_type as $key => $value) {

                    $data= [];
                    $data['milk_type'] = $value;
                    $data['begin_ending_balance_details'] = [];
                    $data['total'] = [];
                    foreach ($RegionList as $key1 => $value1) {
                        
                        $detail = BeginEndingBalanceService::getDataDetail($Data['id'], $data['milk_type'], $value1['id']);
                        if($detail){

                            $data['total']['begin_amount'] += $detail->begin_amount;
                            $data['total']['begin_price'] += $detail->begin_price;
                            $data['total']['ending_amount'] += $detail->ending_amount;
                            $data['total']['ending_price'] += $detail->ending_price;

                            $Summary['begin_amount'] += $detail->begin_amount;
                            $Summary['begin_price'] += $detail->begin_price;
                            $Summary['ending_amount'] += $detail->ending_amount;
                            $Summary['ending_price'] += $detail->ending_price;

                            $data['begin_ending_balance_details'][] = $detail;
                        }else{
                            $detail = [];
                            $detail['factory_id'] = $value1['id'];
                            $detail['begin_amount'] = null;
                            $detail['begin_price'] = null;
                            $detail['ending_amount'] = null;
                            $detail['ending_price'] = null;

                            $data['begin_ending_balance_details'][] = $detail;
                        }
                    }

                    $DetailList[] = $data;
                }

                $Data['milk_type'] = $DetailList;
                $Data['Summary'] = $Summary;

                $this->data_result['DATA'] = $Data;

                return $this->returnResponse(200, $this->data_result, $response, false);

            } catch (\Exception $e) {
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updateData($request, $response, $args) {
            try {

                $params = $request->getParsedBody();

                $condition = $params['obj']['condition'];
                $Data = $params['obj']['Data'];

                $find_result = $this->findQuarterAndFiscalYear($condition['months'], $condition['years']);

                $last_month = $condition['months'] - 1;
                $last_year = $condition['years'];

                if($last_month == 0){
                    $last_month = 12;
                    $last_year = $last_year - 1;
                } 

                $next_month = $condition['months'] + 1;
                $next_year = $condition['years'];

                if($next_month > 12){
                    $next_month = 1;
                    $next_year = $next_year + 1;
                } 

                $Data['months'] = $condition['months'];
                $Data['years'] = $condition['years'];
                $Data['quarters'] = $find_result['quarters'];
                $Data['fiscal_years'] = $find_result['fiscal_years'];
                // print_r($Data);exit;
                $id = BeginEndingBalanceService::updateData($Data);

                // find next month data

                $NextData = BeginEndingBalanceService::getData($next_month, $next_year);
                $next_id = null;
                if(empty($NextData)){
                    $find_result = $this->findQuarterAndFiscalYear($next_month, $next_year);
                    $NextData = [];
                    $NextData['months'] = $next_month;
                    $NextData['years'] = $next_year;
                    $NextData['quarters'] = $find_result['quarters'];
                    $NextData['fiscal_years'] = $find_result['fiscal_years'];
                    $next_id = BeginEndingBalanceService::updateData($NextData);
                }else{
                    $next_id = $NextData->id;
                }

                foreach ($Data['milk_type'] as $key => $value) {
                    // print_r($value);
                    foreach ($value['begin_ending_balance_details'] as $key1 => $value1) {
                        $value1['milk_type'] = $value['milk_type'];
                        $value1['begin_ending_balance_id'] = $id;

                        // get ending_balance from last month begin_balance 
                        $last_ending_balance = BeginEndingBalanceService::getEndingBalance($last_month, $last_year, $value1['factory_id'], $value1['milk_type']);

                        if($last_ending_balance){
                            $value1['begin_amount'] = $last_ending_balance->ending_amount;
                            $value1['begin_price'] = $last_ending_balance->ending_price;
                        }

                        $result = BeginEndingBalanceService::updateDataDetail($value1);

                        // update begin balance
                        $next_begin_balance = BeginEndingBalanceService::getEndingBalance($next_month, $next_year, $value1['factory_id'], $value1['milk_type']);

                        if($next_begin_balance){

                            $next_month_data = [];
                            $next_month_data['id'] = $next_begin_balance->id;
                            $next_month_data['begin_amount'] = $value1['ending_amount'];
                            $next_month_data['begin_price'] = $value1['ending_price'];
                            // print_r($next_month_data);exit;
                            BeginEndingBalanceService::updateDataDetail($next_month_data);

                        }else{

                            $next_month_data = [];
                            $next_month_data['id'] = '';
                            $next_month_data['milk_type'] = $value1['milk_type'];
                            $next_month_data['factory_id'] = $value1['factory_id'];
                            $next_month_data['begin_ending_balance_id'] = $next_id;
                            $next_month_data['begin_amount'] = $value1['ending_amount'];
                            $next_month_data['begin_price'] = $value1['ending_price'];
                            $next_month_data['ending_amount'] = 0;
                            $next_month_data['ending_price'] =0;
                            // print_r($next_month_data);exit;
                            BeginEndingBalanceService::updateDataDetail($next_month_data);

                        }
                    }

                }
                
                $this->data_result['DATA']['result'] = $result;

                return $this->returnResponse(200, $this->data_result, $response, false);

            } catch (\Exception $e) {
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }