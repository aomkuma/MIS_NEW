<?php
namespace App\Controller;
    
    class Controller {
        
        protected $data_result = array('STATUS'=>'OK','DATA'=>null);
        
        public function returnResponse($status, $data, $response, $check_numeric = true){
            ob_clean();
            ob_flush();
            if($check_numeric){
                return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($status)
                        ->withJson(array('data'=>$data), null, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            }else{
                return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($status)
                        ->withJson(array('data'=>$data), null, JSON_UNESCAPED_UNICODE);
            }
        }
        
        public function returnSystemErrorResponse($logger, $data_result, $e, $response){
            $logger->error('Exception :: ', array('error_msg'=>$e));
            $data_result['STATUS'] = 'ERROR';
            $data_result['DATA'] = $e;
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(503)
                    ->withJson($data_result, 
                                null, 
                                JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        }
        
    }

?>