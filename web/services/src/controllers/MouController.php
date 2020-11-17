<?php

    namespace App\Controller;
    
    use App\Service\MouService;

    class MouController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getList($request, $response, $args){
            try{
                // error_reporting(E_ERROR);
                // error_reporting(E_ALL);
                // ini_set('display_errors','On');
                $params = $request->getParsedBody();
                $actives = $params['obj']['actives'];
                $condition = $params['obj']['condition'];

                // group by region first
                $Regions = MouService::getRegionList($condition);
                // Loop get by region
                $DataList = [];
                foreach ($Regions as $key => $value) {
                    $_List = MouService::getList($value['region_id'], $actives, $condition);
                    $value['Data'] = $_List;
                    array_push($DataList, $value);
                    // $Regions['Data'][] = $_List;
                }
                

                $this->data_result['DATA']['DataList'] = $DataList;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $id = $params['obj']['id'];
                
                $_Data = MouService::getData($id);

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
                $avgList = $params['obj']['avgList'];
                
                if(!empty($_Data['id'])){
                    // Add history
                    $_MOUData = MouService::getData($_Data['id']);
                    

                    unset($_MOUData['mou_histories']);
                    $_MOUData['id'] = '';
                    $_MOUData['mou_id'] = $_Data['id'];
                    // print_r($_MOUData);
                    // exit;
                    MouService::addHistoryData($_MOUData);
                }

                $id = MouService::updateData($_Data);

                foreach ($avgList as $key => $value) {
                    $value['mou_id'] = $id;
                    MouService::updateAvgData($value);
                }
                
                $this->data_result['DATA']['id'] = $id;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }