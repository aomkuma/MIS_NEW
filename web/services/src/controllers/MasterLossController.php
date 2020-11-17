<?php

    namespace App\Controller;
    
    use App\Service\MasterLossService;
    use App\Service\ProductMilkService;
    use App\Service\SubProductMilkService;
    use App\Service\ProductMilkDetailService;
    use App\Service\MasterGoalService;
    use App\Service\FactoryService;

    class MasterLossController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getList($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $actives = $params['obj']['actives'];
                $condition = $params['obj']['condition'];
                
                $List = MasterLossService::loadList($actives, $condition);
                
                $this->data_result['DATA']['List'] = $List;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getMappingList($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $factory_id = $params['obj']['factory_id'];
                $loss_type = $params['obj']['loss_type'];
                $loss_id = $params['obj']['loss_id'];
                $_product_milk_id = $params['obj']['product_milk_id'];
                $_subproduct_milk_id = $params['obj']['subproduct_milk_id'];

                // Get all factory
                $FactoryList = FactoryService::getList([], $factory_id);

                $DataList = [];
                foreach ($FactoryList as $f_key => $f_value) {
                 
                    // get Master loss list 
                    $MasterLoss = MasterLossService::loadListByName($f_value['id'], $loss_id);

                    $MasterLossList = [];
                    foreach ($MasterLoss as $m_key => $m_value) {
                        
                        $ProductMilkList = [];
                        $_ProductMilkList = MasterLossService::getProductMilkListView($f_value['id'], $loss_id, $loss_type, $_product_milk_id);
                        foreach ($_ProductMilkList as $key => $value) {
                            $product_milk_id = $value['product_milk_id'];
                            
                            $_SubProductMilkList = MasterLossService::getSubProductMilkListView($product_milk_id, $loss_id, $loss_type, $_subproduct_milk_id);

                            $SubProductMilk = [];
                            foreach ($_SubProductMilkList as $key1 => $value1) {
                                $subproduct_milk_id = $value1['subproduct_milk_id'];
                                $_ProductMilkDetailList = MasterLossService::getProductMilkDetailListView($subproduct_milk_id, $loss_id, $loss_type);

                                $value1['ProductMilkDetailList'] = $_ProductMilkDetailList;
                                array_push($SubProductMilk, $value1);
                            }

                            $value['SubProductMilkList'] = $SubProductMilk;
                            array_push($ProductMilkList, $value);
                        }

                        $m_value['ProductMilkList'] = $ProductMilkList;
                        array_push($MasterLossList, $m_value);
                    }

                    $f_value['MasterLossList'] = $MasterLossList;
                    array_push($DataList, $f_value);
                }

                $this->data_result['DATA']['Data'] = $DataList;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $id = $params['obj']['id'];
                
                $_Data = MasterLossService::getData($id);

                $this->data_result['DATA']['Data'] = $_Data;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updateData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $Data = $params['obj']['Data'];

                $id = MasterLossService::updateData($Data);

                $this->data_result['DATA']['id'] = $id;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updateMappingData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $Data = $params['obj']['Data'];

                // find Duplicate
                $duplicate = MasterLossService::checkDuplicateMapping($Data);    
                if(!empty($duplicate)){
                    $this->data_result['STATUS'] = 'ERROR';
                    $this->data_result['DATA'] = 'บันทึกข้อมูลซ้ำ';

                    return $this->returnResponse(200, $this->data_result, $response, false);
                    exit();
                }            

                $id = MasterLossService::updateMappingData($Data);

                // Update to master goal (lost out process, lost wait sale)

                // get Lost name
                $LostData = MasterLossService::getData($Data['loss_id']);

                // get product milk
                $ProductMilk = ProductMilkService::getData($Data['product_milk_id']);

                // get sub product milk
                $SubProductMilk = SubProductMilkService::getData($Data['subproduct_milk_id']);

                // get sub product milk
                $ProductMilkDetail = ProductMilkDetailService::getData($Data['product_milk_detail_id']);

                // $Master = ['การสูญเสียหลังกระบวนการ','การสูญเสียรอจำหน่าย'];
                // foreach ($Master as $key => $value) {
                    $MasterGoal = [];
                    $MasterGoal['id'] = '';
                    $MasterGoal['goal_type'] = 'II';
                    $MasterGoal['menu_type'] = $Data['loss_type'];//$value;
                    $MasterGoal['actives'] = 'Y';    
                    $MasterGoal['factory_id'] = $Data['factory_id'];
                    $MasterGoal['goal_name'] = /*$Data['loss_type']. ' - ' . */$LostData['name'] . ' - ' . $ProductMilk['name'] . ' - ' . $SubProductMilk['product_character'] . ' ' . $SubProductMilk['subname'] . ' - ' . $ProductMilkDetail['name']  . ' ' . $ProductMilkDetail['number_of_package'] . ' ' . $ProductMilkDetail['unit'] . ' ' . (empty($ProductMilkDetail['amount'])?'0':$ProductMilkDetail['amount']). ' ' . $ProductMilkDetail['amount_unit'] . ' ' . $ProductMilkDetail['taste'];
                    MasterGoalService::updateData($MasterGoal);

                // }
                
                $this->data_result['DATA']['id'] = $id;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function createToMasterGoal($request, $response, $args){
            try{
                $params = $request->getParsedBody();

                $id = $params['obj']['id'];
                $product_milk_id = $params['obj']['product_milk_id'];
                $subproduct_milk_id = $params['obj']['subproduct_milk_id'];
                $product_milk_detail_id = $params['obj']['product_milk_detail_id'];

                $Data = MasterLossService::getMappingData($id);

                // Update to master goal (lost out process, lost wait sale)

                // get Lost name
                $LostData = MasterLossService::getData($Data['loss_id']);

                // get product milk
                $ProductMilk = ProductMilkService::getData($Data['product_milk_id']);

                // get sub product milk
                $SubProductMilk = SubProductMilkService::getData($Data['subproduct_milk_id']);

                // get sub product milk
                $ProductMilkDetail = ProductMilkDetailService::getData($Data['product_milk_detail_id']);

                // $Master = ['การสูญเสียหลังกระบวนการ','การสูญเสียรอจำหน่าย'];
                // foreach ($Master as $key => $value) {
                    $MasterGoal = [];
                    $MasterGoal['id'] = '';
                    $MasterGoal['goal_type'] = 'II';
                    $MasterGoal['menu_type'] = $Data['loss_type'];//$value;
                    $MasterGoal['actives'] = 'Y';    
                    $MasterGoal['factory_id'] = $Data['factory_id'];
                    $MasterGoal['goal_name'] = /*$Data['loss_type']. ' - ' . */$LostData['name'] . ' - ' . $ProductMilk['name'] . ' - ' . $SubProductMilk['product_character'] . ' ' . $SubProductMilk['subname'] . ' - ' . $ProductMilkDetail['name']  . ' ' . $ProductMilkDetail['number_of_package'] . ' ' . $ProductMilkDetail['unit'] . ' ' . (empty($ProductMilkDetail['amount'])?'0':$ProductMilkDetail['amount']). ' ' . $ProductMilkDetail['amount_unit'] . ' ' . $ProductMilkDetail['taste'];

                    $dup = MasterGoalService::getGoalIDByName($MasterGoal['goal_name'], $MasterGoal['menu_type'], $MasterGoal['factory_id']);

                    if(empty($dup)){
                        MasterGoalService::updateData($MasterGoal);    
                    }
                    
                // }
                
                $this->data_result['DATA']['id'] = $id;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function deleteMappingData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $id = $params['obj']['id'];

                $Data = MasterLossService::getMappingData($id);
                
                // get Lost name
                $LostData = MasterLossService::getData($Data['loss_id']);

                // get product milk
                $ProductMilk = ProductMilkService::getData($Data['product_milk_id']);

                // get sub product milk
                $SubProductMilk = SubProductMilkService::getData($Data['subproduct_milk_id']);

                // get sub product milk
                $ProductMilkDetail = ProductMilkDetailService::getData($Data['product_milk_detail_id']);

                // Delete from master goal
                $factory_id = $Data['factory_id'];
                $menu_type = $Data['loss_type'];
                $goal_name = $LostData['name'] . ' - ' . $ProductMilk['name'] . ' - ' . $SubProductMilk['subname'] . ' - ' . $ProductMilkDetail['name']  . ' ' . $ProductMilkDetail['number_of_package'] . ' ' . $ProductMilkDetail['unit'] . ' ' . (empty($ProductMilkDetail['amount'])?'0.00':$ProductMilkDetail['amount']). ' ' . $ProductMilkDetail['amount_unit'] . ' ' . $ProductMilkDetail['taste'];
                // echo "$menu_type, $goal_name, $factory_id";
                // exit;
                MasterGoalService::removeDataByCondition($menu_type, $goal_name, $factory_id);
                $result = MasterLossService::removeMappingData($id);

                $this->data_result['DATA']['result'] = $result;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

    }

    