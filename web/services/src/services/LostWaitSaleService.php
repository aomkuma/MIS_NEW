<?php
    
    namespace App\Service;
    
    use App\Model\LostWaitSale;
    use App\Model\LostWaitSaleDetail;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class LostWaitSaleService {

        public static function loadDataApprove($UserID){
            return LostWaitSale::select("lost_wait_sale.*", 'factory.factory_name')
                            ->join('factory', 'factory.id', '=', 'lost_wait_sale.factory_id')
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
            return LostWaitSale::select(DB::raw("SUM(mis_lost_wait_sale_detail.amount) AS sum_amount")
                                , DB::raw("SUM(mis_lost_wait_sale_detail.price_value) AS sum_baht")
                        )
                        ->join("lost_wait_sale_detail", 'lost_wait_sale_detail.lost_wait_sale_id', '=', 'lost_wait_sale.id')
                        ->join("master_goal", 'lost_wait_sale_detail.lost_wait_sale_type', '=', 'master_goal.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where(function($query) use ($factory_id) {
                            if(!empty($factory_id)){
                                $query->where("lost_wait_sale.factory_id", $factory_id);
                            }
                        })
                        
                        ->first();
        }

        public static function getMainList($years, $months, $factory_id, $master_type_id){
            return LostWaitSale::select(DB::raw("SUM(mis_lost_wait_sale_detail.amount) AS sum_amount")
                                        ,DB::raw("SUM(mis_lost_wait_sale_detail.price_value) AS sum_baht")
                                    
                                    ,"lost_wait_sale.update_date"
                                    ,"office_approve_id"
                                    ,"office_approve_date"
                                    ,"office_approve_comment"
                                )
                            ->join("lost_wait_sale_detail", 'lost_wait_sale_detail.lost_wait_sale_id', '=', 'lost_wait_sale.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("factory_id", $factory_id)
                            ->where("lost_wait_sale_type", $master_type_id)
                            ->orderBy('update_date', 'DESC')
                            ->first()
                            ->toArray();
        }
        public static function getMainListreport($years, $months, $factory_id){
            return LostWaitSale::select(DB::raw("SUM(mis_lost_wait_sale_detail.amount) AS sum_amount")
                                        ,DB::raw("SUM(mis_lost_wait_sale_detail.price_value) AS sum_baht")
                                    
                                    ,"lost_wait_sale.update_date"
                                    ,"office_approve_id"
                                    ,"office_approve_date"
                                    ,"office_approve_comment"
                                )
                            ->join("lost_wait_sale_detail", 'lost_wait_sale_detail.lost_wait_sale_id', '=', 'lost_wait_sale.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("factory_id", $factory_id)
                           
                            ->orderBy('update_date', 'DESC')
                            ->first()
                            ->toArray();
        }

        public static function getDataByID($id){
            return LostWaitSale::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('lostWaitSaleDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }

        public static function getData($factory_id, $months, $years){
            return LostWaitSale::
                    where(function($query) use ($factory_id){
                        if(!empty($factory_id)){
                            $query->where('factory_id' , $factory_id);
                        }  
                    })
                    // where('factory_id', $factory_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('lostWaitSaleDetail' => function($query){
                        $query->orderBy('update_date', 'DESC');
                    }))
                    ->first();      
        }
        
        public static function updateData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostWaitSale::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostWaitSale::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateDetailData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostWaitSaleDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = LostWaitSaleDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeData($id){
            
            LostWaitSaleDetail::where('lost_wait_sale_id', $id)->delete();
            return LostWaitSale::find($id)->delete();
        }

        public static function removeDetailData($id){
            
            return LostWaitSaleDetail::find($id)->delete();
        }

        public static function updateDataApprove($id, $obj) {

            return LostWaitSale::where('id', $id)->update($obj);
        }
        
        public static function removeDetailDataByParent($id){
            
            return LostWaitSaleDetail::where('lost_wait_sale_id', $id)->delete();
        }
    }