<?php
    
    namespace App\Service;
    
    use App\Model\LostOutProcess;
    use App\Model\LostOutProcessDetail;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class LostOutProcessService {

        public static function loadDataApprove($UserID){
            return LostOutProcess::select("lost_out_process.*", 'factory.factory_name')
                            ->join('factory', 'factory.id', '=', 'lost_out_process.factory_id')
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

        public  static function getMonthList($years, $months, $factory_id = null) {
            return LostOutProcess::select(DB::raw("SUM(mis_lost_out_process_detail.amount) AS sum_amount")
                                , DB::raw("SUM(mis_lost_out_process_detail.price_value) AS sum_baht")
                        )
                        ->join("lost_out_process_detail", 'lost_out_process_detail.lost_out_process_id', '=', 'lost_out_process.id')
                        ->join("master_goal", 'lost_out_process_detail.lost_out_process_type', '=', 'master_goal.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where(function($query) use ($factory_id) {
                            if(!empty($factory_id)){
                                $query->where("lost_out_process.factory_id", $factory_id);
                            }
                        })
                        
                        ->first();
        }

        public static function getMainList($years, $months, $factory_id, $master_type_id){
            return LostOutProcess::select(DB::raw("SUM(mis_lost_out_process_detail.amount) AS sum_amount")
                                        ,DB::raw("SUM(mis_lost_out_process_detail.price_value) AS sum_baht")
                                    
                                    ,"lost_out_process.update_date"
                                    ,"office_approve_id"
                                    ,"office_approve_date"
                                    ,"office_approve_comment"
                                )
                            ->join("lost_out_process_detail", 'lost_out_process_detail.lost_out_process_id', '=', 'lost_out_process.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("factory_id", $factory_id)
                            ->where("lost_out_process_type", $master_type_id)
                            ->orderBy('update_date', 'DESC')
                            ->first()
                            ->toArray();
        }
        public static function getMainListreport($years, $months, $factory_id){
            return LostOutProcess::select(DB::raw("SUM(mis_lost_out_process_detail.amount) AS sum_amount")
                                        ,DB::raw("SUM(mis_lost_out_process_detail.price_value) AS sum_baht")
                                    
                                    ,"lost_out_process.update_date"
                                    ,"office_approve_id"
                                    ,"office_approve_date"
                                    ,"office_approve_comment"
                                )
                            ->join("lost_out_process_detail", 'lost_out_process_detail.lost_out_process_id', '=', 'lost_out_process.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("factory_id", $factory_id)
                           
                            ->orderBy('update_date', 'DESC')
                            ->first()
                            ->toArray();
        }

        public static function getDataByID($id){
            return LostOutProcess::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('lostOutProcessDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function getData($factory_id, $months, $years){
            return LostOutProcess::
                    where(function($query) use ($factory_id){
                        if(!empty($factory_id)){
                            $query->where('factory_id' , $factory_id);
                        }  
                    })
                    // where('factory_id', $factory_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('lostOutProcessDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }
        
        public static function updateData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostOutProcess::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostOutProcess::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateDetailData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostOutProcessDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostOutProcessDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeData($id){
            
            LostOutProcessDetail::where('lost_out_process_id', $id)->delete();
            return LostOutProcess::find($id)->delete();
        }

        public static function removeDetailData($id){
            
            return LostOutProcessDetail::find($id)->delete();
        }

        public static function updateDataApprove($id, $obj) {

            return LostOutProcess::where('id', $id)->update($obj);
        }

        public static function removeDetailDataByParent($id){
            
            return LostOutProcessDetail::where('lost_out_process_id', $id)->delete();
        }
        
    }