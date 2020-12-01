<?php

namespace App\Controller;

use App\Service\ProductMilkService;
use App\Service\SubProductMilkService;
use App\Service\ProductMilkDetailService;
use App\Service\MasterGoalService;

class ProductMilkDetailController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function getList($request, $response, $args) {
        try {
            $params = $request->getParsedBody();
//                $actives = $params['obj']['actives'];
//                $menu_type = $params['obj']['menu_type'];
//                $condition = $params['obj']['condition'];

            $_List = ProductMilkDetailService::getList();
//                print_r($_List);
//                die();
            $this->data_result['DATA']['List'] = $_List;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getListByParent($request, $response, $args) {
        try {
            $params = $request->getParsedBody();
            $sub_product_milk_id = $params['obj']['sub_product_milk_id'];
            if(empty($sub_product_milk_id)){
                $sub_product_milk_id = $params['obj']['id'];
            }
//                $menu_type = $params['obj']['menu_type'];
//                $condition = $params['obj']['condition'];

            $_List = ProductMilkDetailService::getListByParent($sub_product_milk_id);
//                print_r($_List);
//                die();
            $this->data_result['DATA']['List'] = $_List;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getListByParent2($request, $response, $args) {
        try {
            $params = $request->getParsedBody();
            $sub_product_milk_id = $params['obj']['id'];
//                $menu_type = $params['obj']['menu_type'];
//                $condition = $params['obj']['condition'];

            $_List = ProductMilkDetailService::getListByParent2($sub_product_milk_id);
//                print_r($_List);
//                die();
            $this->data_result['DATA']['List'] = $_List;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getData($request, $response, $args) {
        try {
            $params = $request->getParsedBody();
            $id = $params['obj']['id'];

            $_Data = ProductMilkDetailService::getData($id);


            $this->data_result['DATA']['Data'] = $_Data;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function updateData($request, $response, $args) {

        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();
            $_Data = $params['obj']['Subdata'];
            //  print_r($_Data);die();
            // // Update to none role
            $result = ProductMilkDetailService::checkDuplicate($_Data['id'], $_Data, $_Data['sub_product_milk_id'], $_Data['actives']);
            // print_r($result);exit;
            if (empty($result)) {

                // Get old data
                $OldData = ProductMilkDetailService::getData($_Data['id']);

                $id = ProductMilkDetailService::updateData($_Data);
                $this->data_result['DATA']['id'] = $id;

                // get product milk name & sub product milk name
                $SubProductMilk = SubProductMilkService::getData($_Data['sub_product_milk_id']);
                $fac_id = $SubProductMilk['fac_id'];
                $ProductMilkName = $SubProductMilk['proname'];
                $SubProductMilkName = $SubProductMilk['product_character'] . ' ' . $SubProductMilk['subname'];
                
                // find master goal by name  , ' ' , number_of_package , ' ' , unit , ' ' , amount , ' ' , amount_unit , ' ' , taste
                $old_goal_name = $ProductMilkName . ' - ' . $SubProductMilkName . ' - ' . $OldData['name']  . ' ' . $OldData['number_of_package'] . ' ' . $OldData['unit'] . ' ' . (empty($OldData['amount']) || $OldData['amount'] == 0?'0':$OldData['amount']) . ' ' . $OldData['amount_unit'] . ' ' . $OldData['taste'];

                $menuTypeList = ['ข้อมูลการผลิต', 'ข้อมูลการขาย', 'การสูญเสียในกระบวนการ'/*, 'การสูญเสียหลังกระบวนการ', 'การสูญเสียรอจำหน่าย'*/];

                foreach ($menuTypeList as $key1 => $value1) {
                    $MasterGoal = MasterGoalService::getDataByName($old_goal_name, $value1, $fac_id);

                    // Add master goal
                    if(empty($MasterGoal)){
                        
                        $MasterGoal['id'] = '';
                        $MasterGoal['goal_type'] = 'II';
                        $MasterGoal['menu_type'] = $value1/*'ข้อมูลการผลิต'*/;
                        $MasterGoal['actives'] = 'Y';    
                        $MasterGoal['factory_id'] = $fac_id;
                        $MasterGoal['goal_name'] = $ProductMilkName . ' - ' . $SubProductMilkName . ' - ' . $_Data['name']  . ' ' . $_Data['number_of_package'] . ' ' . $_Data['unit'] . ' ' . (empty($_Data['amount']) || $_Data['amount'] == 0?'0':$_Data['amount']). ' ' . $_Data['amount_unit'] . ' ' . $_Data['taste'];
                    }else{
                        $MasterGoal['goal_name'] = $ProductMilkName . ' - ' . $SubProductMilkName . ' - ' . $_Data['name']  . ' ' . $_Data['number_of_package'] . ' ' . $_Data['unit'] . ' ' . (empty($_Data['amount']) || $_Data['amount'] == 0?'0':$_Data['amount']) . ' ' . $_Data['amount_unit'] . ' ' . $_Data['taste'];
                        $MasterGoal['actives'] = $_Data['actives'];
                    }

                    MasterGoalService::updateData($MasterGoal);
                }

            } else {
                // print_r($result);exit;
                $this->data_result['STATUS'] = 'ERROR';
                $this->data_result['DATA'] = 'บันทึกข้อมูลซ้ำ';
            }



            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function deleteMasterGoalData($request, $response, $args) {

        try {

            // Delete all master_goal not in goal_mission
            $res = MasterGoalService::deleteAllNotInGoalMission();
           
            $this->data_result['DATA'] = $res;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function updateMasterGoalData($request, $response, $args) {

        try {

            $factory_id_list = [1,2,3,4,5];
            $menuTypeList = ['ข้อมูลการผลิต', 'ข้อมูลการขาย', 'การสูญเสียในกระบวนการ'];
            // Delete all master_goal not in goal_mission
            $cnt = 0;
            foreach ($factory_id_list as $factory_value) {
                
                $product_milk_detail_list = ProductMilkDetailService::getAllByFactory($factory_value);

                foreach ($product_milk_detail_list as $key => $value) {

                    foreach ($menuTypeList as $menu_type) {
                     
                        $goal_name = $value['proname'] . ' - ' . $value['product_character'] . ' ' . $value['subname'] . ' - ' . $value['name']  . ' ' . $value['number_of_package'] . ' ' . $value['unit'] . ' ' . (empty($value['amount']) || $value['amount'] == 0?'0':$value['amount']). ' ' . $value['amount_unit'] . ' ' . $value['taste'];

                        $MasterGoal = MasterGoalService::getDataByName($goal_name, $menu_type, $factory_value);

                        // Add master goal
                        if(empty($MasterGoal)){

                            $MasterGoal['id'] = '';
                            $MasterGoal['goal_type'] = 'II';
                            $MasterGoal['menu_type'] = $menu_type/*'ข้อมูลการผลิต'*/;
                            $MasterGoal['actives'] = $value['actives'];    
                            $MasterGoal['factory_id'] = $factory_value;
                            $MasterGoal['goal_name'] = $goal_name;

                            $cnt++;
                            // print_r($MasterGoal);
                            MasterGoalService::updateData($MasterGoal);

                        }
                    }
                }

                // echo "Total of Factory $factory_value : " . $cnt;

            }
            
            // exit;
            $this->data_result['DATA'] = $cnt;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }
}
