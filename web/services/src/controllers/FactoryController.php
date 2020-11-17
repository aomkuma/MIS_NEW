<?php

	namespace App\Controller;
    
    use App\Service\FactoryService;

    class FactoryController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getList($request, $response, $args){

        	try {
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $region = $params['obj']['region'];
                
                $RegionList = [];
                foreach ($region as $key => $value) {
                    $RegionList[] = $value['RegionID'];
                }
                // print_r($RegionList);exit;
                $List = FactoryService::getList($RegionList);
                
                $this->data_result['DATA']['DataList'] = $List;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
            } catch (\Exception $e) {
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

    }