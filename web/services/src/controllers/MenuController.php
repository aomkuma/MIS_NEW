<?php

    namespace App\Controller;
    
    use App\Service\MenuService;
    use App\Service\AttachFileService;

    class MenuController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getMenuList($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $UserID = $user_session['UserID'];
                $Manage = $params['obj']['Manage'];
                if(empty($UserID)){
                    $UserID = $params['obj']['UserID'];
                }
                
                if(!empty($Manage)){
                    $UserID = '';
                    //getMenuListUpdatePermission
                    // get main menu
                    $_Menu = MenuService::getMenuListUpdatePermission(0);
                    foreach ($_Menu as $key => $value) {
                        // get child menu level 1
                        $_Menu1 = MenuService::getMenuListUpdatePermission($value['id']);
                        // print_r($_Menu1);exit;
                        $MenuList1 = [];
                        foreach ($_Menu1 as $key1 => $value1) {
                            $_Menu2 = MenuService::getMenuListUpdatePermission($value1['id']);
                            $MenuList2 = [];
                            foreach ($_Menu2 as $key2 => $value2) {
                                $_Menu3 = MenuService::getMenuListUpdatePermission($value2['id']);
                                $value2['sub_menu'] = $_Menu3;
                                $MenuList2[] = $value2;
                            }
                            $value1['sub_menu'] = $MenuList2;
                            $MenuList1[] = $value1;
                        }
                        $value['sub_menu'] = $MenuList1;
                        $MenuList[] = $value;
                    }
                }else{
                    $MenuList = [];
                    // get main menu
                    $_Menu = MenuService::getMenuList(0, $UserID);
                    foreach ($_Menu as $key => $value) {
                        // get child menu level 1
                        $_Menu1 = MenuService::getMenuList($value['id'], $UserID);
                        // print_r($_Menu1);exit;
                        $MenuList1 = [];
                        foreach ($_Menu1 as $key1 => $value1) {
                            $_Menu2 = MenuService::getMenuList($value1['id'], $UserID);
                            $MenuList2 = [];
                            foreach ($_Menu2 as $key2 => $value2) {
                                $_Menu3 = MenuService::getMenuList($value2['id'], $UserID);
                                $value2['sub_menu'] = $_Menu3;
                                $MenuList2[] = $value2;
                            }
                            $value1['sub_menu'] = $MenuList2;
                            $MenuList1[] = $value1;
                        }
                        $value['sub_menu'] = $MenuList1;
                        $MenuList[] = $value;
                    }
                }

                
                $this->data_result['DATA']['Menu'] = $MenuList;
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getMenuListManage($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                
                // get main menu
                $_Menu = MenuService::getMenuListManage();
                
                $this->data_result['DATA']['Menu'] = $_Menu;
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getMenuPage($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $menu_id = $params['obj']['menu_id'];
                // get main menu
                $_Page = MenuService::getPage($menu_id);
                $_AttachFiles = MenuService::getAttachFiles($menu_id);
                
                $this->data_result['DATA']['Page'] = $_Page;
                $this->data_result['DATA']['FileList'] = $_AttachFiles;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getMenuParent($request, $response, $args){
            try{
                $_ParentMenuList = MenuService::getMenuListParent();
                array_unshift($_ParentMenuList, ['id'=>99999999, 'menu_name_th'=>'ไม่มี']);
                $this->data_result['DATA']['ParentMenuList'] = $_ParentMenuList;
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getMenuByType($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $menu_type = $params['obj']['menu_type'];
                $user_session = $params['user_session'];

                $_Menu = MenuService::getMenuByType($menu_type);
                $menu_id = $_Menu['id'];
                // find parent menu
                $MenuList = [];
                $menu_check = $_Menu;
                $cnt = 0;
                do{
                    // echo $menu_check->parent_id;
                    // print_r($menu_check);
                    $menu = MenuService::getMenu($menu_check['parent_menu']);
                    // print_r($menu);
                    $menu_check = $menu;
                    array_unshift($MenuList, $menu);
                    //exit;
                    $cnt++;
                }while($menu['parent_menu'] != '0' && $cnt < 5);
                array_push($MenuList, $_Menu);
                // exit;
                if(!empty($user_session['email'])&&$menu_id!=''){
                // Update User visit page
                    MenuService::updateVisit($user_session['email'], $menu_id);
                }
                $this->data_result['DATA']['Menu'] = $MenuList;

                return $this->returnResponse(200, $this->data_result, $response, false);

            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }       

        public function getMenu($request, $response, $args){
            try{
                $params = $request->getParsedBody();
                $menu_id = $params['obj']['id'];
                $_Menu = MenuService::getMenu($menu_id);
                
                if($_Menu->menu_type == 'EXLINK'){
                    // Get ex link
                    $EXLink = MenuService::getEXLink($_Menu->id);
                    
                    $this->data_result['DATA']['EXLink'] = $EXLink;
                }else{
                    // Get content
                    $PageContent = MenuService::getPageContent($_Menu->id);
                    // Get attach file
                    $AttachFiles = MenuService::getAttachFiles($_Menu->id);

                    $this->data_result['DATA']['PageContent'] = $PageContent;
                    $this->data_result['DATA']['AttachFiles'] = $AttachFiles;
                }
                
                $_ParentMenuList = MenuService::getMenuListParent($_Menu->id);
                $this->data_result['DATA']['Menu'] = $_Menu;

                array_unshift($_ParentMenuList, ['id'=>99999999, 'menu_name_th'=>'ไม่มี']);
                $this->data_result['DATA']['ParentMenuList'] = $_ParentMenuList;
                
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function updateMenu($request, $response, $args){
            
            $_WEB_FILE_PATH = 'cio-files/files';
            $_WEB_IMG_PATH = 'cio-files/img';

            try{
                // error_reporting(E_ERROR);
                // error_reporting(E_ALL);
                // ini_set('display_errors','On');
                $params = $request->getParsedBody();

                // print_r($params);exit;
                $_Menu = $params['obj']['MenuObj']; 
                $_Page = $params['obj']['PageObj'];
                $_EXLink = $params['obj']['EXLinkObj'];
                $AttachFileObj = $params['obj']['AttachFileObj'];
                // print_r($params['obj']);exit;
                // Update Menu
                $menu_id = MenuService::updateMenu($_Menu);
                // Logo image
                $files = $request->getUploadedFiles();
                if($files['obj']['LogoObj'] != null){
                    
                    $files = $files['obj']['LogoObj'];
                    // print_r($files);
                    if($files->getClientFilename() != ''){
                        $ext = pathinfo($files->getClientFilename(), PATHINFO_EXTENSION);
                        $FileName = $menu_id . '_' . date('YmdHis').'_'.rand(100000,999999). '.'.$ext;
                        $FilePath = $_WEB_IMG_PATH . '/' . $FileName;
                        $_Menu['menu_logo'] = $FilePath;
                        MenuService::updateMenu($_Menu);
                        $files->moveTo('../../' . $FilePath);
                    }   
                }

                // Update Case Menu type = 'PAGE'
                if($_Menu['menu_type'] == 'PAGE'){

                    // Update Page
                    $_Page['menu_id'] = $menu_id;
                    MenuService::updatePage($_Page);
                    
                    // Update Attach files type 'file'
                    $files = $request->getUploadedFiles();
                    if($files != null){
                        foreach($files as $key => $val){
                            $cnt = 0;
                            foreach($val['AttachFileObj'] as $k => $v){
                                
                                $display_name = $AttachFileObj[$cnt]['display_name'];
                                $page_type = $AttachFileObj[$cnt]['page_type'];
                                $order_no =  $AttachFileObj[$cnt]['order_no'];
                                $f = $v['attachFile'];
                                // print_r($f);
                                if($f != null){
                                    if($f->getClientFilename() != ''){
                                        $ext = pathinfo($f->getClientFilename(), PATHINFO_EXTENSION);
                                        $FileName = $menu_id . '_' . date('YmdHis').'_'.rand(100000,999999). '.'.$ext;
                                        $FilePath = $_WEB_FILE_PATH . '/menu/'.$FileName;

                                        $AttachFile = ['menu_id'=>$menu_id
                                                        ,'parent_id'=>$menu_id
                                                        ,'display_name'=>$display_name
                                                        ,'page_type'=>$page_type
                                                        ,'file_name'=>$f->getClientFilename()
                                                        ,'file_path'=>$FilePath
                                                        ,'content_type'=>$f->getClientMediaType()
                                                        ,'file_size'=>number_format($f->getSize()/1024, 2)
                                                        ,'order_no'=>$order_no

                                                    ];
                                        
                                        AttachFileService::addAttachFiles($AttachFile);
                                        $f->moveTo('../../' . $FilePath);
                                        
                                    }
                                }
                                $cnt++;
                            }
                        }
                    }

                    // Update Attach files type 'link'
                    $cnt = 0;
                    foreach ($AttachFileObj as $key => $value) {
                        # code...
                        if($value['page_type'] == 'link'){
                            $display_name = $AttachFileObj[$cnt]['display_name'];
                            $file_path = $AttachFileObj[$cnt]['file_path'];
                            $order_no =  $AttachFileObj[$cnt]['order_no'];
                            $AttachFile = ['menu_id'=>$menu_id
                                            ,'parent_id'=>$menu_id
                                            ,'display_name'=>$display_name
                                            ,'page_type'=>$value['page_type']
                                            ,'file_path'=>$file_path
                                            ,'order_no'=>$order_no
                                        ];
                            AttachFileService::addAttachFiles($AttachFile);
                            $cnt++;
                        }
                    }
                    
                }else{
                    // $_EXLink['menu_id'] = $menu_id;
                    // MenuService::updateEXLink($_EXLink);
                }
                // exit;
                $this->data_result['DATA']['menu_id'] = $menu_id;
                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
    }