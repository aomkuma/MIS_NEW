<?php
    
    namespace App\Service;
    
    use App\Model\Cooperative;
    use App\Model\Region;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class CooperativeService {

    	public static function getList($actives = 'Y', $RegionID = [], $condition = []){
            return Cooperative::select("cooperative.*", "region.RegionName")
                    ->where(function($query) use ($RegionID){
                        
                        if(count($RegionID) > 0){
                            $query->whereIn('region_id', $RegionID);
                        }
                    })
                    ->where(function($query) use ($condition){
                       
                        if(!empty($condition['region_id'])){
                            $query->where('region_id', $condition['region_id']);
                        }
                        if(!empty($condition['cooperative_name'])){
                            $query->where('cooperative_name', 'LIKE', DB::raw("'%". $condition['cooperative_name'] . "%'"));
                        }
                    })
                    ->where('actives', 'Y')
                    ->join("region", "region_id", '=', 'RegionID')
                    ->orderBy("region_id", 'ASC')
                    ->get();      
        }

        public static function getListByRegion($region_id){
            return Cooperative::where('region_id', $region_id)
                    ->where('actives', 'Y')
                    ->orderBy("id", 'ASC')
                    ->get();      
        }

        public static function getRegionList(){
            return Region::all();      
        }

        public static function getRegion($RegionID){
            return Region::find($RegionID);      
        }

        public static function getData($id){
            return Cooperative::find($id);      
        }
        
        public static function updateData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Cooperative::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Cooperative::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeData($id){
            $obj['actives'] = 'N';
            return Cooperative::find($id)->update($obj);
        }

    }