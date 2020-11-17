angular.module('e-homework').controller('MenuUpdateController', function($scope, $compile, $cookies, $filter, $state, $routeParams, HTTPService, IndexOverlayFactory) {
    IndexOverlayFactory.overlayShow();
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }

    console.log('Hello ! Menu Update page');

    var ckEditorConfig = {
                            extraPlugins: 'uploadimage,image2,filebrowser,colorbutton',
                            height: 300,
                            uploadUrl: '/mots/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&responseType=json',
                            filebrowserBrowseUrl: '/mots/ckfinder/ckfinder.html',
                            filebrowserImageBrowseUrl: '/mots/ckfinder/ckfinder.html?type=Images',
                            filebrowserUploadUrl: '/mots/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
                            filebrowserImageUploadUrl: '/mots/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
                            contentsCss: [ CKEDITOR.basePath + 'contents.css', 'https://sdk.ckeditor.com/samples/assets/css/widgetstyles.css' ],
                            image2_alignClasses: [ 'image-align-left', 'image-align-center', 'image-align-right' ],
                            image2_disableResizer: true,
                            height:'400px',
                            toolbar : [
                                { name: 'document', items: [ 'Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates' ] },
                                { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
                                { name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
                                { name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
                                '/',
                                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ] },
                                { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
                                { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
                                { name: 'insert', items: [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
                                '/',
                                { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                                { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                                { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
                                { name: 'Page', items: [ 'Page' ] }
                            ]

                        };

	$scope.DEFAULT_LANGUAGE = 'TH';
    $scope.$parent.menu_selected = 'menu';
    $scope.ID = $routeParams.id;

    $scope.loadParentMenuList = function(){
        HTTPService.clientRequest('menu/get/parent', null).then(function(result){
            $scope.ParentMenuList = result.data.DATA.ParentMenuList;
        });
    }

    $scope.load = function(action, id){
        var params = {'id' : id};
        HTTPService.clientRequest(action, params).then(function(result){
            //console.log(result);

            $scope.ParentMenuList = result.data.DATA.ParentMenuList;
            $scope.Menu = result.data.DATA.Menu;
            if($scope.Menu.menu_type == 'PAGE'){
                $scope.PageContent =  result.data.DATA.PageContent;
                if($scope.PageContent == null){
                    $scope.setPage();
                }
                $scope.AttachFiles =  result.data.DATA.AttachFiles;
            }else{
                $scope.EXLink =  result.data.DATA.EXLink;
            }

            console.log($scope.AttachFiles);
            if (CKEDITOR.instances.editor1) CKEDITOR.instances.editor1.destroy();
            if (CKEDITOR.instances.editor2) CKEDITOR.instances.editor2.destroy();

            CKEDITOR.config.extraPlugins = 'colorbutton';
            CKEDITOR.config.colorButton_enableAutomatic = false;

            CKEDITOR.replace( 'editor1',ckEditorConfig );
            // CKEDITOR.config.height = '400px';

            CKEDITOR.replace( 'editor2',ckEditorConfig );

            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.addFiles = function(){
        $scope.FileList.push({'attachFile':null
                                ,'display_name':''
                            });
    }

    $scope.save = function(action, MenuObj, PageObj, AttachFileObj, EXLinkObj){
        // console.log(action, MenuObj, PageObj, AttachFileObj, EXLinkObj);
        console.log(CKEDITOR.instances.editor1.getData());
        
        if(MenuObj.menu_type == 'PAGE'){
            if(CKEDITOR.instances.editor1.getData() != ''){
                PageObj.contents = CKEDITOR.instances.editor1.getData();
            }else{
                PageObj.contents = '';
            }
            if(CKEDITOR.instances.editor2.getData() != ''){
                PageObj.contents_en = CKEDITOR.instances.editor2.getData();
            }else{
                PageObj.contents_en = '';
            }
    }
        var params = {'MenuObj':MenuObj
                    , 'PageObj':PageObj
                    , 'AttachFileObj':AttachFileObj
                    , 'EXLinkObj':EXLinkObj
                    };
        IndexOverlayFactory.overlayShow();
        HTTPService.uploadRequest(action, params).then(function(result){
            console.log(result);
            if(result.data.STATUS == 'OK'){
                $scope.FileList = [];
                if($scope.ID == undefined && $scope.ID == null){
                    window.location.href = '#/menu-manage/update/' + result.data.DATA.menu_id;
                }else{
                    $scope.load('menu/get', result.data.DATA.menu_id);
                }
                IndexOverlayFactory.overlayHide();
            }else{
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.removeAttach = function(action, id, index){
        HTTPService.deleteRequest(action, id).then(function(result){
            if($scope.ID != undefined && $scope.ID != null){
                $scope.load('menu/get', $scope.ID);
            }else{
                $scope.AttachFiles.splice(index, 1);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/menu-manage';
    }

    $scope.setPage = function(){
        $scope.PageContent = {
            'id':''
            ,'menu_id':''
            ,'title_th':''
            ,'title_en':''
            ,'contents':''
            ,'contents_en':''
        };
    }

    $scope.FileList = [];
    $scope.setPage();
    if($scope.ID != undefined && $scope.ID != null){
        $scope.load('menu/get', $scope.ID);
    }else{
        $scope.Menu = {'actives':'Y'};
        $scope.loadParentMenuList();
        CKEDITOR.config.extraPlugins = 'colorbutton';
        CKEDITOR.config.colorButton_enableAutomatic = false;

        CKEDITOR.replace( 'editor1',ckEditorConfig );
        // CKEDITOR.config.height = '400px';

        CKEDITOR.replace( 'editor2',ckEditorConfig );

        IndexOverlayFactory.overlayHide();
    }


});