<?php
    
    namespace App\Service;
    
    use App\Model\Page;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class PageService {

    	public static function getPage($page_type){
            return Page::where('actives', 'Y')
                    ->where('page_type', $page_type)
            		->orderBy('id', 'DESC')
            		->first();      
        }

        public static function updatePage($obj){

        	$model = Page::find($obj['id']);
        	if(empty($model)){
        		$model = new Page;
        		$model->create_date = date('Y-m-d H:i:s');
                $model->page_type = $obj['page_type'];
        	}
            $model->title_th = $obj['title_th'];
            $model->title_en = $obj['title_en'];
            $model->update_date = date('Y-m-d H:i:s');
            $model->contents = $obj['contents'];
            $model->contents_en = $obj['contents_en'];
            $model->save();
            return $model->id;
        }

    }