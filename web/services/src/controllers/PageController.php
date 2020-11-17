<?php

    namespace App\Controller;
    
    use App\Service\PageService;
    use App\Service\AttachFileService;

    class PageController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getPage($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $page_type = $params['obj']['page_type'];
                $_Page = PageService::getPage($page_type);
                $_AttachFiles = AttachFileService::getAttachFiles($_Page['id'], $_Page['page_type']);
                $_Page['AttachFiles'] = $_AttachFiles;

                $this->data_result['DATA']['Page'] = $_Page;
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updatePage($request, $response, $args){
            
            $_WEB_FILE_PATH = 'cio-files/files';

            try{
                // error_reporting(E_ERROR);
                // error_reporting(E_ALL);
                // ini_set('display_errors','On');
                $params = $request->getParsedBody();

                // print_r($params);exit;
                $_Page = $params['obj']['PageObj']; 
                $_AttachFiles = $params['obj']['AttachFileObj'];
                unset($_Page['AttachFiles']);

                // Save Page 
                $id = PageService::updatePage($_Page);
                // Update Attach files
                $files = $request->getUploadedFiles();
                if($files != null){
                    foreach($files as $key => $val){
                        foreach($val['AttachFileObj'] as $k => $v){
                            // print_r($v['attachFile']);
                            $f = $v['attachFile'];
                            // print_r($f);
                            if($f != null){
                                if($f->getClientFilename() != ''){
                                    $ext = pathinfo($f->getClientFilename(), PATHINFO_EXTENSION);
                                    $FileName = $id . '_' . date('YmdHis').'_'.rand(100000,999999). '.'.$ext;
                                    $FilePath = $_WEB_FILE_PATH . '/page/'.$FileName;

                                    $AttachFile = ['parent_id'=>$id
                                                    ,'page_type'=>$_Page['page_type']
                                                    ,'file_name'=>$f->getClientFilename()
                                                    ,'file_path'=>$FilePath
                                                    ,'content_type'=>$f->getClientMediaType()
                                                    ,'file_size'=>number_format($f->getSize()/1024, 2)

                                                ];
                                    // print_r($AttachFile);exit;
                                    AttachFileService::addAttachFiles($AttachFile);
                                    $f->moveTo('../../' . $FilePath);
                                    
                                }
                            }
                        }
                    }
                }

                // exit;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }