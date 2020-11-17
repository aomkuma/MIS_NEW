<?php
    
    namespace App\Service;
    
    use App\Model\ProductionSaleInfo;
    use App\Model\ProductionSaleInfoDetail;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class ProductionSaleInfoService {

        public static function findIDWithProductTypeID($production_sale_info_type1, $production_sale_info_type2, $production_sale_info_type3, $sale_chanel_id){
            return ProductionSaleInfoDetail::where('production_sale_info_type1', $production_sale_info_type1)
                    ->where('production_sale_info_type2', $production_sale_info_type2)
                    ->where('production_sale_info_type3', $production_sale_info_type3)
                    ->where('sale_chanel_id', $sale_chanel_id)
                    ->first();  
            // if(empty($data)){
            //     return '';
            // }else{
            //     return $data->id;
            // }
        }

        public  static function getMonthList($years, $months, $factory_id = null) {
            return ProductionSaleInfo::select(DB::raw("SUM(mis_production_sale_info_detail.amount +  mis_production_sale_info_detail.addon) AS sum_amount")
                                , DB::raw("SUM(mis_production_sale_info_detail.price_value) AS sum_baht")
                        )
                        ->join("production_sale_info_detail", 'production_sale_info_detail.production_sale_info_id', '=', 'production_sale_info.id')
                        ->join("master_goal", 'production_sale_info_detail.production_sale_info_type1', '=', 'master_goal.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where(function($query) use ($factory_id) {
                            if(!empty($factory_id)){
                                $query->where("production_sale_info.factory_id", $factory_id);
                            }
                        })
                        
                        ->first();
        }

        public static function loadDataApprove($UserID){
            return ProductionSaleInfo::select("production_sale_info.*", 'factory.factory_name')
                            ->join('factory', 'factory.id', '=', 'production_sale_info.factory_id')
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

        public static function getMainList($years, $months, $factory_id, $master_type_id){
            return ProductionSaleInfo::select(DB::raw("SUM(mis_production_sale_info_detail.amount +  mis_production_sale_info_detail.addon) AS sum_amount")
                                        ,DB::raw("SUM(mis_production_sale_info_detail.price_value) AS sum_baht")
                                    
                                    ,"production_sale_info.update_date"
                                    ,"office_approve_id"
                                    ,"office_approve_date"
                                    ,"office_approve_comment"
                                )
                            ->join("production_sale_info_detail", 'production_sale_info_detail.production_sale_info_id', '=', 'production_sale_info.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("factory_id", $factory_id)
                            ->where("production_sale_info_type2", $master_type_id)
                            ->orderBy('update_date', 'DESC')
                            ->first()
                            ->toArray();
        }

        public static function getDetailList($years, $months, $factory_id, $master_type_id){
            return ProductionSaleInfo::select(DB::raw("SUM(mis_production_sale_info_detail.amount) AS sum_amount")
                                        ,DB::raw("SUM(mis_production_sale_info_detail.addon) AS sum_addon")
                                        ,DB::raw("SUM(mis_production_sale_info_detail.price_value) AS sum_baht")
                                    
                                )
                            ->join("production_sale_info_detail", 'production_sale_info_detail.production_sale_info_id', '=', 'production_sale_info.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("factory_id", $factory_id)
                            ->where("production_sale_info_type3", $master_type_id)
                            ->orderBy('production_sale_info.update_date', 'DESC')
                            ->first()
                            ->toArray();
        }
        public static function getDetailList2($years, $months){
            return ProductionSaleInfo::select(DB::raw("SUM(mis_production_sale_info_detail.amount + addon) AS amount")
                                        ,DB::raw("SUM(mis_production_sale_info_detail.price_value) AS sum_baht")
                                    
                                )
                            ->join("production_sale_info_detail", 'production_sale_info_detail.production_sale_info_id', '=', 'production_sale_info.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            
                            ->orderBy('production_sale_info.update_date', 'DESC')
                            ->first()
                            ->toArray();
        }
        public static function getDetailListsub($years, $months, $master_type_id) {
     
        return ProductionSaleInfo::select(DB::raw("SUM(mis_production_sale_info_detail.amount) AS sum_amount")
                                        ,DB::raw("SUM(mis_production_sale_info_detail.price_value) AS sum_baht")
                        )
                        ->join("production_sale_info_detail", 'production_sale_info_detail.production_sale_info_id', '=', 'production_sale_info.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        
                        ->where("production_sale_info_type3", $master_type_id)
                        ->orderBy('production_sale_info.update_date', 'DESC')
                        ->first()
                        ->toArray();
    }

        public static function getDataByID($id){
            return ProductionSaleInfo::where('id', $id)
                    //->with('mouHistories')
                    ->with(array('productionSaleInfoDetail' => function($query){
                        $query->orderBy('id', 'ASC');
                    }))
                    ->first();      
        }

        public static function getData($factory_id, $months, $years){
            return ProductionSaleInfo::
                    where(function($query) use ($factory_id){
                        if(!empty($factory_id)){
                            $query->where('factory_id' , $factory_id);
                        }  
                    })
                    // where('factory_id', $factory_id)
                    ->where('months', $months)
                    ->where('years', $years)
                    //->with('mouHistories')
                    ->with(array('productionSaleInfoDetail' => function($query){
                        $query->orderBy('id', 'ASC');
                    }))
                    ->first();      
        }

        public static function getDetail($years, $months, $factory_id, $master_type_id) {
            return ProductionSaleInfo::select(DB::raw("SUM(mis_production_sale_info_detail.amount + addon) AS amount")
                                    , DB::raw("SUM(mis_production_sale_info_detail.price_value) AS price")
                            )
                            ->join("production_sale_info_detail", 'production_sale_info_detail.production_sale_info_id', '=', 'production_sale_info.id')
                            ->where("years", $years)
                            ->where("months", $months)
                            ->where("factory_id", $factory_id)
                            ->where("production_sale_info_type1", $master_type_id)
                            ->first();
                            // ->toArray();
        }
        
        public static function updateData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = ProductionSaleInfo::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = ProductionSaleInfo::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateDetailData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = ProductionSaleInfoDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = ProductionSaleInfoDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function removeData($id){
            
            ProductionSaleInfoDetail::where('production_sale_info_id', $id)->delete();
            return ProductionSaleInfo::find($id)->delete();
        }

        public static function removeDetailData($id){
            
            return ProductionSaleInfoDetail::find($id)->delete();
        }

        public static function removeDetailDataByParent($id){
            
            return ProductionSaleInfoDetail::where('production_sale_info_id', $id)->delete();
        }

        public static function updateDataApprove($id, $obj) {

            return ProductionSaleInfo::where('id', $id)->update($obj);
        }
        
    }