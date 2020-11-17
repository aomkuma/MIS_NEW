<?php

    namespace App\Controller;
    
    use App\Service\AccountPermissionService;

    class AccountPermissionController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getData($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $UserID = $params['obj']['UserID'];
                
                $_Role = AccountPermissionService::getAccountRole($UserID);
                $_Permission = AccountPermissionService::getAccountPermission($UserID);

                $this->data_result['DATA']['Role'] = $_Role;
                $this->data_result['DATA']['Permission'] = $_Permission;
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
                $UserID = $params['obj']['UserID'];
                $_Permission = $params['obj']['Permission'];
                $_Role = $params['obj']['Role'];
                
                // // Update to none role
                AccountPermissionService::resetAccountRole($UserID);
                // Set value
                $cnt = 1;
                foreach ($_Role as $key => $value) {
                    if($value){
                        $obj_update = [];
                        $obj_update['role'] = $cnt;
                        $obj_update['UserID'] = $UserID;
                        $obj_update['actives'] = 'Y';
                        AccountPermissionService::updateAccountRole($obj_update);
                    }
                    $cnt++;
                }

                // Update to none permission

                AccountPermissionService::resetAccountPermission($UserID);
                foreach ($_Permission as $key => $value) {
                    if($value['checked_menu'] || $value['checked_menu'] != ''){
                        $obj_update = [];
                        $obj_update['menu_id'] = $value['id'];
                        $obj_update['UserID'] = $UserID;
                        $obj_update['actives'] = 'Y';
                        // print_r($obj_update);exit;
                        AccountPermissionService::updateAccountPermission($obj_update);
                    }
                    foreach ($value['sub_menu'] as $_key => $_value) {
                        if($_value['checked_menu'] || $_value['checked_menu'] != ''){
                            $obj_update = [];
                            $obj_update['menu_id'] = $_value['id'];
                            $obj_update['UserID'] = $UserID;
                            $obj_update['actives'] = 'Y';
                            // print_r($obj_update);
                            AccountPermissionService::updateAccountPermission($obj_update);
                        }
                    }
                }//exit;
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }