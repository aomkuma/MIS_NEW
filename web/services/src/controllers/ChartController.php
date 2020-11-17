<?php

    namespace App\Controller;
    
    use App\Service\ChartService;

    class ChartController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getDataDBI($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $years = $params['obj']['condition']['Year'];

                // Goal Mission
                $Data = [];
                $Label = ['บริการสัตวแพทย์', 'ผสมเทียม', 'แร่ธาตุ พรีมิกซ์ และอาหาร', 'ผลิตน้ำเชื้อแช่แข็ง', 'จำหน่ายน้ำเชื้อแช่แข็ง', 'วัสดุผสมเทียมและอื่นๆ', 'ปัจจัยการเลี้ยงโค', 'ฝึกอบรม', 'ท่องเที่ยว', 'สหกรณ์และปริมาณน้ำนม', 'ข้อมูลฝูงโค'];

                foreach ($Label as $key => $value) {
                    $menu_type = $value;
                    $_GoalMission = ChartService::getGoalMissionData($years, $menu_type);
                    $Data['Amount'][] = $this->checkNullToZero($_GoalMission['amount']);
                    $Data['Price'][] = $this->checkNullToZero($_GoalMission['price_value']);
                }
                
                $this->data_result['GOAL'] = $Data;

                // Data
                $Data = [];
                // Veterinary
                $Veterinary = ChartService::getVeterinaryData($years);
                $Data['Amount'][] = $this->checkNullToZero($Veterinary['sum_amount']);
                $Data['Price'][] = '0';

                // Insemination
                $Insemination = ChartService::getInseminationData($years);
                $Data['Amount'][] = $this->checkNullToZero($Insemination['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($Insemination['sum_value']);

                // Mineral
                $Mineral = ChartService::getMineralData($years);
                $Data['Amount'][] = $this->checkNullToZero($Mineral['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($Mineral['sum_value']);

                // Sperm
                $Sperm = ChartService::getSpermData($years);
                $Data['Amount'][] = $this->checkNullToZero($Sperm['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($Sperm['sum_value']);

                // SpermSale
                $SpermSale = ChartService::getSpermSaleData($years);
                $Data['Amount'][] = $this->checkNullToZero($SpermSale['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($SpermSale['sum_value']);

                // Material
                $Material = ChartService::getMaterialData($years);
                $Data['Amount'][] = $this->checkNullToZero($Material['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($Material['sum_value']);

                // CowBreed
                $CowBreed = ChartService::getCowBreedData($years);
                $Data['Amount'][] = $this->checkNullToZero($CowBreed['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($CowBreed['sum_value']);

                // TrainingCowBreed
                $TrainingCowBreed = ChartService::getTrainingCowBreedData($years);
                $Data['Amount'][] = $this->checkNullToZero($TrainingCowBreed['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($TrainingCowBreed['sum_value']);

                // Travel
                $Travel = ChartService::getTravelData($years);
                $Data['Amount'][] = $this->checkNullToZero($Travel['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($Travel['sum_value']);

                // CooperativeMilk
                $CooperativeMilk = ChartService::getCooperativeMilkData($years);
                $Data['Amount'][] = $this->checkNullToZero($CooperativeMilk['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($CooperativeMilk['sum_value']);

                // CowGroup
                $CowGroup = ChartService::getCowGroupData($years);
                $Data['Amount'][] = $this->checkNullToZero($CowGroup['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($CowGroup['sum_value']);
                
                $this->data_result['DATA'] = $Data;

                $this->data_result['LABEL'] = $Label;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getDataII($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $years = $params['obj']['condition']['Year1'];

                // Goal Mission
                $Data = [];
                $Label = ['ข้อมูลการผลิต', 'ข้อมูลการขาย', 'ข้อมูลรับซื้อน้ำนม', 'ข้อมูลจำหน่ายน้ำนม', 'การสูญเสียในกระบวนการ', 'การสูญเสียนอกกระบวนการ', 'การสูญเสียรอจำหน่าย', 'การสูญเสียในกระบวนการขนส่ง'];

                foreach ($Label as $key => $value) {
                    $menu_type = $value;
                    $_GoalMission = ChartService::getGoalMissionData($years, $menu_type);
                    $Data['Amount'][] = $this->checkNullToZero($_GoalMission['amount']);
                    $Data['Price'][] = $this->checkNullToZero($_GoalMission['price_value']);
                }
                
                $this->data_result['GOAL'] = $Data;

                // Data
                $Data = [];
                // Veterinary
                $ProductionInfo = ChartService::getProductionInfoData($years);
                $Data['Amount'][] = $this->checkNullToZero($ProductionInfo['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($ProductionInfo['sum_value']);

                // Insemination
                $ProductionSaleInfo = ChartService::getProductionSaleInfoData($years);
                $Data['Amount'][] = $this->checkNullToZero($ProductionSaleInfo['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($ProductionSaleInfo['sum_value']);

                // Mineral
                $XxcustOrderRmV = ChartService::getXxcustOrderRmVData($years);
                $Data['Amount'][] = $this->checkNullToZero($XxcustOrderRmV['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($XxcustOrderRmV['sum_value']);

                // Sperm
                $XxCustPoRmV = ChartService::getXxCustPoRmVData($years);
                $Data['Amount'][] = $this->checkNullToZero($XxCustPoRmV['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($XxCustPoRmV['sum_value']);

                // SpermSale
                $LostInProcess = ChartService::getLostInProcessData($years);
                $Data['Amount'][] = $this->checkNullToZero($LostInProcess['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($LostInProcess['sum_value']);

                // Material
                $LostOutProcess = ChartService::getLostOutProcessData($years);
                $Data['Amount'][] = $this->checkNullToZero($LostOutProcess['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($LostOutProcess['sum_value']);

                // CowBreed
                $LostWaitSale = ChartService::getLostWaitSaleData($years);
                $Data['Amount'][] = $this->checkNullToZero($LostWaitSale['sum_amount']);
                $Data['Price'][] = $this->checkNullToZero($LostWaitSale['sum_value']);

                $this->data_result['DATA'] = $Data;

                $this->data_result['LABEL'] = $Label;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        private function checkNullToZero($val){
            return $val == null?0:$val;
        }
    }

    