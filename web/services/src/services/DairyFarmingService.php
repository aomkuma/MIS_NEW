<?php
    
    namespace App\Service;
    
    use App\Model\DairyFarming;
    use App\Model\VeterinaryDetail;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class DairyFarmingService {

        public static function getList($actives = ''){
            return DairyFarming::select("dairy_farming.*", DB::raw("'-' AS child_name"))
                    ->where(function($query) use ($actives){
                        if(!empty($actives)){
                            $query->where('actives' , $actives);
                        }
                    })
                    ->whereNull('dairy_farming.parent_id')
                    ->orderBy("dairy_farming.update_date", 'DESC')
                    ->get()->toArray();      
        }

        public static function getChildList($parent_id, $actives = ''){
            return DairyFarming::select("dairy_farming.*"
                                        , DB::raw("dairy_farming_name AS child_name")
                                        , DB::raw("'' AS dairy_farming_name")
                                    )
                    ->where(function($query) use ($actives){
                        if(!empty($actives)){
                            $query->where('actives' , $actives);
                        }
                    })
                    ->where('dairy_farming.parent_id', $parent_id)
                    ->orderBy("dairy_farming.update_date", 'DESC')
                    ->get()->toArray();      
        }

        public static function getListForVeterinary($type, $parent_id = '', $data_arr = []){
            return DairyFarming::
                    where(function($query) use ($type, $parent_id, $data_arr){
                        
                        $query->where('actives' , 'Y');
                        if($type == 'MAIN'){
                            $query->whereNull('parent_id');
                            if(!empty($data_arr)){
                                $query->whereIn('dairy_farming_name', $data_arr);
                            }
                            
                        }else{
                            if(!empty($parent_id)){
                                $query->where('parent_id' , $parent_id);    
                            }else{
                                $query->whereNotNull('parent_id');
                            }   
                        }
                    })
                    ->orderBy("dairy_farming_name", 'ASC')
                    // ->toSql();
                    ->get()->toArray();      
        }

        public static function getData($id){
            return DairyFarming::where('id', $id)
                    ->first();
                    //->toArray();      
        }
        
        public static function updateData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = DairyFarming::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = DairyFarming::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateDairyFarmTypeData($id, $dairy_farming_type){
            $obj = ['dairy_farming_type' => $dairy_farming_type];
            DairyFarming::where('parent_id', $id)->update($obj);

            $obj = ['farm_type' => $dairy_farming_type];
            VeterinaryDetail::where('dairy_farming_id', $id)->update($obj);
        }

        public static function removeData($id){
            return DairyFarming::find($id)->delete();
        }

    }