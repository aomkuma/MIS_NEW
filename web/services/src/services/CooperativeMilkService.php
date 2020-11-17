<?php
    
    namespace App\Service;
    
    use App\Model\CooperativeMilk;
    use App\Model\CooperativeMilkDetail;
    

    use Illuminate\Database\Capsule\Manager as DB;
    
    class CooperativeMilkService {

        public static function loadDataApprove($UserID){
            return CooperativeMilk::select("cooperative_milk.*", 'region.RegionName')
                            ->join('region', 'region.RegionID', '=', 'cooperative_milk.region_id')
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

        public static function getMainList($years, $months, $region_id, $cooperative_id = '') {
        return CooperativeMilk::select(DB::raw("SUM(total_person) AS sum_total_person")
                                , DB::raw("SUM(total_person_sent) AS sum_total_person_sent")
                                , DB::raw("SUM(total_cow) AS sum_total_cow")
                                , DB::raw("SUM(total_cow_beeb) AS sum_total_cow_beeb")
                                , DB::raw("SUM(milk_amount) AS sum_milk_amount")
                                , DB::raw("SUM(total_values) AS sum_total_values")
                                , DB::raw("SUM(average_values) AS sum_average_values")

                                , "cooperative_milk.update_date","office_approve_id"
                                    ,"office_approve_date"
                                    ,"office_approve_comment")
                        ->join("cooperative_milk_detail", 'cooperative_milk_detail.cooperative_milk_id', '=', 'cooperative_milk.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region_id)
                        // ->where("cooperative_milk_detail.cooperative_id", $cooperative_id)
                        ->where(function($query) use ($cooperative_id){
                            if(!empty($cooperative_id)){
                                $query->where('cooperative_id' , $cooperative_id);
                            }
                        })
                        ->first()
                        ->toArray();
    }
     public static function getMainListquar($years, $st, $en, $region_id, $cooperative_id = '') {
        return CooperativeMilk::select(DB::raw("SUM(total_person) AS sum_total_person")
                                , DB::raw("SUM(total_person_sent) AS sum_total_person_sent")
                                , DB::raw("SUM(total_cow) AS sum_total_cow")
                                , DB::raw("SUM(total_cow_beeb) AS sum_total_cow_beeb")
                                , DB::raw("SUM(milk_amount) AS sum_milk_amount")
                                , DB::raw("SUM(total_values) AS sum_total_values")
                                , DB::raw("SUM(average_values) AS sum_average_values")

                                , "cooperative_milk.update_date")
                        ->join("cooperative_milk_detail", 'cooperative_milk_detail.cooperative_milk_id', '=', 'cooperative_milk.id')
                        ->where("years", $years)
                        ->whereBetween("months", [$st, $en])
                        ->where("region_id", $region_id)
                        // ->where("cooperative_milk_detail.cooperative_id", $cooperative_id)
                        ->where(function($query) use ($cooperative_id){
                            if(!empty($cooperative_id)){
                                $query->where('cooperative_id' , $cooperative_id);
                            }
                        })
                        ->first()
                        ->toArray();
    }

        public static function getDataByID($id){
            return CooperativeMilk::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('cooperativeMilkDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                        $query->orderBy('id', 'DESC');
                    }))
                    ->orderBy('id', 'DESC')
                    ->first();      
        }

        public static function getData($cooperative_id, $months, $years){
            return CooperativeMilk::where('region_id', $cooperative_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('cooperativeMilkDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->orderBy('id', 'DESC')
                    ->first();      
        }

        public static function updateData($obj){
            
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CooperativeMilk::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CooperativeMilk::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateDetailData($obj){

            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CooperativeMilkDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = CooperativeMilkDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeDetailData($id){
           
            return CooperativeMilkDetail::find($id)->delete();
        }

        public static function removeData($id){

        }

        public static function updateDataApprove($id, $obj) {

            return CooperativeMilk::where('id', $id)->update($obj);
        }
    }
