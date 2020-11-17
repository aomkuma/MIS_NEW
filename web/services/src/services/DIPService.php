<?php
    
    namespace App\Service;
    
    use App\Model\DIP;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class DIPService {

    	public static function getList($years = ''){
            return DIP::where(function($query) use ($years){
                        if(!empty($years)){
                            $query->where('years' , $years);
                        }
                    })
                    ->orderBy("years", 'DESC')
                    ->get();      
        }

        public static function getData($id){
            return DIP::find($id);      
        }
        
        public static function updateData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $model = DIP::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = DIP::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeData($id){
            return DIP::find($id)->delete();
        }

    }