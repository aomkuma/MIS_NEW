<?php

    namespace App\Controller;
    
    use App\Service\UploadLogService;

    class UploadLogController extends Controller {
        
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
                $menu_type = $params['obj']['menu_type'];
                $data_id = $params['obj']['id'];

                $List = UploadLogService::loadList($menu_type, $data_id);
                
                $this->data_result['DATA']['List'] = $List;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updateLog($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $Data = $params['obj']['Data'];

                $id = UploadLogService::loadList($Data);

                $this->data_result['DATA']['id'] = $id;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

    }

    