<?php

namespace App\Controller;

use App\Service\SubProductMilkService;
use App\Service\ProductMilkDetailService;
use App\Service\MasterGoalService;

class SubProductMilkController extends Controller {

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

            $_List = SubProductMilkService::getList();
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
            $product_milk_id = $params['obj']['product_milk_id'];
            if(empty($product_milk_id)){
                $product_milk_id = $params['obj']['id'];
            }
//                $menu_type = $params['obj']['menu_type'];
//                $condition = $params['obj']['condition'];
            
            $_List = SubProductMilkService::getListByProductMilk($product_milk_id);
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

            $_Data = SubProductMilkService::getData($id);
//            print_r($_Data);
//            die();
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
//                 print_r($_Data);
//                 die();
            // // Update to none role
            
            $result = SubProductMilkService::checkDuplicate($_Data['id'], $_Data,$_Data['product_milk_id']);

            if (empty($result)) {

                // Get old data
                $OldData = SubProductMilkService::getData($_Data['id']);

                $id = SubProductMilkService::updateData($_Data);
                $this->data_result['DATA']['id'] = $id;

                $ProductMilkDetail = ProductMilkDetailService::getListByParent($_Data['id']);

                foreach ($ProductMilkDetail as $key1 => $value1) {
                    
                    // find master goal by name
                    $old_goal_name = $OldData['proname'] . ' - ' . $OldData['product_character'] . ' ' . $OldData['subname'] . ' - ' . $value1['name']  . ' ' . $value1['number_of_package'] . ' ' . $value1['unit'] . ' ' . $value1['amount'] . ' ' . $value1['amount_unit'] . ' ' . $value1['taste'];
                    $MasterGoal = MasterGoalService::getDataByName($old_goal_name);
                    // Add master goal
                    if(!empty($MasterGoal)){
                        $MasterGoal['goal_name'] = $OldData['proname'] . ' - ' . $_Data['product_character'] . ' ' . $_Data['name'] . ' - ' . $value1['name']  . ' ' . $value1['number_of_package'] . ' ' . $value1['unit'] . ' ' . (empty($value1['amount'])?'0.00':$value1['amount']) . ' ' . $value1['amount_unit'] . ' ' . $value1['taste'];
                        MasterGoalService::updateData($MasterGoal);
                    }

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

}
