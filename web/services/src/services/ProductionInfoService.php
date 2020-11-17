<?php

namespace App\Service;

use App\Model\ProductionInfo;
use App\Model\ProductionInfoDetail;
use Illuminate\Database\Capsule\Manager as DB;

class ProductionInfoService {

    public static function loadDataApprove($UserID) {
        return ProductionInfo::select("production_info.*", 'factory.factory_name')
                        ->join('factory', 'factory.id', '=', 'production_info.factory_id')
                        ->where(function($query) use ($UserID) {
                            $query->where('dep_approve_id', $UserID);
                            $query->whereNull('dep_approve_date');
                        })
                        ->orWhere(function($query) use ($UserID) {
                            $query->where('division_approve_id', $UserID);
                            $query->whereNull('division_approve_date');
                        })
                        ->orWhere(function($query) use ($UserID) {
                            $query->where('office_approve_id', $UserID);
                            $query->whereNull('office_approve_date');
                        })
                        ->get();
    }

    public static function getMainList($years, $months, $factory_id, $master_type_id) {
        return ProductionInfo::select(DB::raw("SUM(mis_production_info_detail.amount) AS sum_amount")
                                , DB::raw("SUM(mis_production_info_detail.price_value) AS sum_baht")
                                , "production_info.update_date"
                                , "office_approve_id"
                                , "office_approve_date"
                                , "office_approve_comment"
                        )
                        ->join("production_info_detail", 'production_info_detail.production_info_id', '=', 'production_info.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("factory_id", $factory_id)
                        ->where("production_info_type2", $master_type_id)
                        ->groupBy('production_info.id')
                        ->orderBy('update_date', 'DESC')
                        ->first();
                        // ->toArray();
    }

    public  static function getMonthList($years, $months, $factory_id = null) {
        return ProductionInfo::select(DB::raw("SUM(mis_production_info_detail.amount) AS sum_amount")
                                , DB::raw("SUM(mis_production_info_detail.price_value) AS sum_baht")
                        )
                        ->join("production_info_detail", 'production_info_detail.production_info_id', '=', 'production_info.id')
                        ->join("master_goal", 'production_info_detail.production_info_type2', '=', 'master_goal.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where(function($query) use ($factory_id) {
                            if(!empty($factory_id)){
                                $query->where("production_info.factory_id", $factory_id);
                            }
                        })
                        
                        ->first();
    }

    public static function getDetailList($years, $months, $factory_id, $master_type_id) {
        return ProductionInfo::select(DB::raw("SUM(mis_production_info_detail.amount) AS sum_amount")
                                , DB::raw("SUM(mis_production_info_detail.price_value) AS sum_baht")
                        )
                        ->join("production_info_detail", 'production_info_detail.production_info_id', '=', 'production_info.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("factory_id", $factory_id)
                        ->where("production_info_type3", $master_type_id)
                        ->groupBy('production_info.id')
                        ->orderBy('production_info.update_date', 'DESC')
                        ->first();
                        // ->toArray();
    }

    public static function getDetail($years, $months, $factory_id, $master_type_id) {
        return ProductionInfo::select(DB::raw("SUM(mis_production_info_detail.amount) AS amount")
                                , DB::raw("SUM(mis_production_info_detail.price_value) AS price")
                        )
                        ->join("production_info_detail", 'production_info_detail.production_info_id', '=', 'production_info.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("factory_id", $factory_id)
                        ->where("production_info_type1", $master_type_id)
                        ->first();
                        // ->toArray();
    }

    public static function getDetailList2($years, $months) {
        return ProductionInfo::select(DB::raw("SUM(mis_production_info_detail.amount) AS amount")
                                , DB::raw("SUM(mis_production_info_detail.price_value) AS sum_baht")
                        )
                        ->join("production_info_detail", 'production_info_detail.production_info_id', '=', 'production_info.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        
                        ->orderBy('production_info.update_date', 'DESC')
                        ->first()
                        ->toArray();
    }

    public static function getDetailListByFac($years, $months, $factory_id) {
        return ProductionInfo::select(DB::raw("SUM(mis_production_info_detail.amount) AS amount")
                                , DB::raw("SUM(mis_production_info_detail.price_value) AS sum_baht")
                        )
                        ->join("production_info_detail", 'production_info_detail.production_info_id', '=', 'production_info.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("factory_id", $factory_id)
                        ->orderBy('production_info.update_date', 'DESC')
                        ->first()
                        ->toArray();
    }

    public static function getDetailListsub($years, $months, $master_type_id) {

        return ProductionInfo::select(DB::raw("SUM(mis_production_info_detail.amount) AS sum_amount")
                                , DB::raw("SUM(mis_production_info_detail.price_value) AS sum_baht")
                        )
                        ->join("production_info_detail", 'production_info_detail.production_info_id', '=', 'production_info.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("production_info_type3", $master_type_id)
                        ->orderBy('production_info.update_date', 'DESC')
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return ProductionInfo::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('productionInfoDetail' => function($query) {
                                $query->orderBy('id', 'ASC');
                            }))
                        ->first();
    }

    public static function getData($factory_id, $months, $years) {
        return ProductionInfo::
                        where(function($query) use ($factory_id) {
                            if (!empty($factory_id)) {
                                $query->where('factory_id', $factory_id);
                            }
                        })
                        // where('factory_id', $factory_id)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('productionInfoDetail' => function($query) {
                                $query->orderBy('id', 'ASC');
                            }))
                        ->first();
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = ProductionInfo::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = ProductionInfo::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = ProductionInfoDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = ProductionInfoDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeData($id) {

        ProductionInfoDetail::where('production_info_id', $id)->delete();
        return ProductionInfo::find($id)->delete();
    }

    public static function removeDetailData($id) {

        return ProductionInfoDetail::find($id)->delete();
    }

    public static function updateDataApprove($id, $obj) {

        return ProductionInfo::where('id', $id)->update($obj);
    }

    public static function removeDetailDataByParent($id){
            
        return ProductionInfoDetail::where('production_info_id', $id)->delete();
    }

}
