<?php
    
    namespace App\Service;
    
    use App\Model\Factory;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class FactoryService {

    	public static function getList($RegionList=[], $factory_id = ''){
            return Factory::where(function($query) use ($RegionList){
                        if(!empty($RegionList)){
                            $query->whereIn('region_id' , $RegionList);
                        }
                    })
                    ->where(function($query) use ($factory_id){
                        if(!empty($factory_id)){
                            $query->where('id' , $factory_id);
                        }
                    })
                    ->get();      
        }

        public static function getData($id){
            return Factory::where('id', $id)->get();      
        }

    }