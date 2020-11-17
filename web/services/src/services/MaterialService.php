<?php
    
    namespace App\Service;
    
    use App\Model\Material;
    use App\Model\MaterialDetail;
    

    use Illuminate\Database\Capsule\Manager as DB;
    
    class MaterialService {

        public static function loadDataApprove($UserID){
            return Material::select("material.*", 'cooperative.cooperative_name')
                            ->join('cooperative', 'cooperative.id', '=', 'material.cooperative_id')
                            ->where(function($query) use ($UserID){
                                $query->where('dep_approve_id' , $UserID);    
                                $query->whereNull('dep_approve_date');    
                            })
                            ->orWhere(function($query) use ($UserID){
                                $query->where('division_approve_id' , $UserID);    
                                $query->whereNull('division_approve_date');    
                            })
                            ->orWhere(function($query) use ($UserID){
                                $query->where('office_approve_id' , $UserID);    
                                $query->whereNull('office_approve_date');    
                            })
                            ->get();
        }

        public static function getMainList($years, $months, $region_id, $material_type_id){
            return Material::select(DB::raw("SUM(amount) AS sum_amount")
                                        ,DB::raw("SUM(`price`) AS sum_baht")
                                        ,"material.update_date","office_approve_id"
                                    ,"office_approve_date"
                                    ,"office_approve_comment")
                            ->join("material_detail", 'material_detail.material_id', '=', 'material.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("region_id", $region_id)
                            ->where('material_type_id', $material_type_id)
                            ->first()
                            ->toArray();
        }

        public static function getDataByID($id){
            return Material::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('materialDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->orderBy('id', 'DESC')
                    ->first();      
        }

        public static function getData($cooperative_id, $months, $years){
            return Material::where('cooperative_id', $cooperative_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('materialDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->orderBy('id', 'DESC')
                    ->first();      
        }
        
		public static function updateData($obj){
			
			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Material::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = Material::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}
		public static function updateDetailData($obj){

			if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = MaterialDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = MaterialDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
		}

		public static function removeDetailData($id){
           
            return MaterialDetail::find($id)->delete();
        }

		public static function removeData($id){

		}

        public static function updateDataApprove($id, $obj) {

            return Material::where('id', $id)->update($obj);
        }

        public static function getDetailmonth($years, $months, $type_id, $region) {
            $ckid = null;
            return Material::select(DB::raw("SUM(amount) AS amount")
                                    , DB::raw("SUM(`price`) AS price"))
                            ->join("material_detail", 'material_detail.material_id', '=', 'material.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where(function($query) use ($type_id){
                                if(!empty($type_id)){
                                    $query->whereIn('material_type_id' , $type_id);  
                                }
                                
                            })
                            ->first()
                            ->toArray();
        }

        public static function getDetailmonthExcept($years, $months, $type_id, $region) {
            $ckid = null;
            return Material::select(DB::raw("SUM(amount) AS amount")
                                    , DB::raw("SUM(`price`) AS price"))
                            ->join("material_detail", 'material_detail.material_id', '=', 'material.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where(function($query) use ($type_id){
                                $query->whereNotIn('material_type_id' , $type_id);
                            })
                            ->first()
                            ->toArray();
        }

        public static function getUnitList($years, $months, $region_id, $material_type_id) {
            return MaterialDetail::select("amount_unit")
                    ->join('material', 'material.id', '=', 'material_detail.material_id')
                    ->where("material.years", $years)
                    ->where("material.months", $months)
                    ->where("material.region_id", $region_id)
                    ->where("material_detail.material_type_id", $material_type_id)
                    ->groupBy('amount_unit')
                    ->get()
                    ->toArray();
        }

        public static function getDetailData($years, $months, $region_id, $cooperative_id, $material_type_id, $amount_unit) {
            return MaterialDetail::select(DB::raw("SUM(amount) AS sum_amount")
                                    , DB::raw("SUM(`price`) AS sum_baht"))
                    ->join('material', 'material.id', '=', 'material_detail.material_id')
                    ->where("material.years", $years)
                    ->where("material.months", $months)
                    ->where("material.region_id", $region_id)
                    ->where("material.cooperative_id", $cooperative_id)
                    ->where("material_detail.material_type_id", $material_type_id)
                    ->where("material_detail.amount_unit", $amount_unit)
                    ->first()
                    ->toArray();
        }
    }
