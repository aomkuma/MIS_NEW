<?php

    namespace App\Controller;
    
    use App\Service\MasterGoalService;

    class MasterGoalController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function checkRegion($regionID) {
        switch ($regionID) {
            case '1':
                return 1;
                break;
            case '2':
                return 1;
                break;
            case '3':
                return 1;
                break;
            case '4':
                return 2;
                break;
            case '5':
                return 3;
                break;
            case '6':
                return 4;
                break;
            case '7':
                return 5;
                break;
            default: return '';break;
        }
    }

        public function getList($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $actives = $params['obj']['actives'];
                $menu_type = $params['obj']['menu_type'];
                $factory_id = MasterGoalController::checkRegion($params['obj']['factory_id']);

                $condition = $params['obj']['condition'];
                $htmlcode = $params['obj']['htmlcode'];
                // print_r($condition);exit;
                if(!empty($condition['Region']['RegionID'])){
                    $factory_id = MasterGoalController::checkRegion($condition['Region']['RegionID']);
                }else{
                    // $condition = [];
                }
                
                if(!empty($condition['factory_id'])){
                   $factory_id =  $condition['factory_id'];
                }
                
                $_List = MasterGoalService::getList($actives, $menu_type, $condition, '', $factory_id);
                $this->data_result['DATA']['OldList'] = $_List;
                $Data = [];

                if($htmlcode == 'Y'){
                    foreach ($_List as $key => $value) {
                        $value['goal_name'] = str_replace(' ', '&nbsp;', $value['goal_name']);
                        array_push($Data, $value);
                    }
                }else{
                    $Data = $_List;
                }

                $this->data_result['DATA']['List'] = $Data;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $id = $params['obj']['id'];
                
                $_Data = MasterGoalService::getData($id);

                $this->data_result['DATA']['Data'] = $_Data;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updateData($request, $response, $args){
            
            try{
                // error_reporting(E_ERROR);
                // error_reporting(E_ALL);
                // ini_set('display_errors','On');
                $params = $request->getParsedBody();
                $_Data = $params['obj']['Data'];
                // print_r($_Data);exit;
                // // Update to none role

                $result = null;

                if($_Data['actives'] != 'N'){
                    $result = MasterGoalService::checkDuplicate($_Data['id'], $_Data['menu_type'], trim($_Data['goal_name']), $_Data['factory_id']);
                }
                if(empty($result)){
                    $id = MasterGoalService::updateData($_Data);
                    $this->data_result['DATA']['id'] = $id;
                }else{
                    // print_r($result);exit;
                    $this->data_result['STATUS'] = 'ERROR';
                    $this->data_result['DATA'] = 'บันทึกข้อมูลซ้ำ';
                }

                
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }