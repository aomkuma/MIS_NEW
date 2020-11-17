<?php

namespace App\Service;

use App\Model\Mineral;
use App\Model\MineralDetail;
use App\Model\Food;
use Illuminate\Database\Capsule\Manager as DB;

class MineralService {

    public static function loadDataApprove($UserID) {
        return Mineral::select("mineral.*", 'cooperative.cooperative_name')
                        ->join('cooperative', 'cooperative.id', '=', 'mineral.cooperative_id')
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

    public static function getMainList($years, $months, $region_id) {
        return Mineral::select(DB::raw("SUM(amount) AS sum_weight")
                                , DB::raw("SUM(`values`) AS sum_baht")
                                , "mineral.update_date", "office_approve_id"
                                , "office_approve_date"
                                , "office_approve_comment")
                        ->join("mineral_detail", 'mineral_detail.mineral_id', '=', 'mineral.id')
                        ->join("master_goal", 'mineral_detail.food_id', '=', 'master_goal.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region_id)
                        ->where("master_goal.actives", 'Y')
                        ->whereIn('master_goal.sub_goal_type', ['พรีมิกซ์','แร่ธาตุ', 'อาหาร'])
                        ->first()
                        ->toArray();
    }

    public static function getMainListByMaster($years, $months, $master_id, $RegionList) {
        return Mineral::select(DB::raw("SUM(amount) AS sum_weight")
                                , DB::raw("SUM(`values`) AS sum_baht")
                                , "mineral.update_date")
                        ->join("mineral_detail", 'mineral_detail.mineral_id', '=', 'mineral.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("food_id", $master_id)
                        ->whereIn("region_id", $RegionList)
                        ->first()
                        ->toArray();
    }

    public static function getDetailList($years, $months, $cooperative_id, $food_id) {
        return Mineral::select(DB::raw("SUM(amount) AS sum_weight")
                                , DB::raw("SUM(`values`) AS sum_baht"))
                        ->join("mineral_detail", 'mineral_detail.mineral_id', '=', 'mineral.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("cooperative_id", $cooperative_id)
                        ->where("food_id", $food_id)
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return Mineral::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('mineralDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function getData($cooperative_id, $months, $years) {
        return Mineral::where('cooperative_id', $cooperative_id)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('mineralDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Mineral::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Mineral::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = MineralDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = MineralDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {

        return MineralDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function getFoodList() {
        return Food::where('actives', 'Y')
                        ->orderBy("id", 'DESC')
                        ->get();
    }

    public static function updateDataApprove($id, $obj) {

        return Mineral::where('id', $id)->update($obj);
    }

    public static function getDetailmonth($years, $months, $type_id, $region) {
        $ckid = null;
        return Mineral::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(`values`) AS price"))
                        ->join("mineral_detail", 'mineral_detail.mineral_id', '=', 'mineral.id')
                        ->join("master_goal", 'mineral_detail.food_id', '=', 'master_goal.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("actives", 'Y')
                        /*->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })*/
                        ->where(function($query) use ($type_id) {
                            if(!empty($type_id)){
                                $query->where('food_id', $type_id);
                            }
                        })
                        // ->where("food_id", $type_id)
                        ->whereIn('master_goal.sub_goal_type', ['พรีมิกซ์','แร่ธาตุ'])
                        ->first()
                        ->toArray();
    }

    public static function getDetailmonthFood($years, $months, $type_id, $region) {
        $ckid = null;
        return Mineral::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(`values`) AS price"))
                        ->join("mineral_detail", 'mineral_detail.mineral_id', '=', 'mineral.id')
                        ->join("master_goal", 'mineral_detail.food_id', '=', 'master_goal.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("actives", 'Y')
                        /*->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })*/
                        ->where(function($query) use ($type_id) {
                            if(!empty($type_id)){
                                $query->where('food_id', $type_id);
                            }
                        })
                        // ->where("food_id", $type_id)
                        ->where('master_goal.sub_goal_type', 'อาหาร')
                        ->first()
                        ->toArray();
    }

    public static function getcomment($years, $months, $region_id) {
        return Mineral::select("cooperative.cooperative_name", "mineral.months", "mineral.user_comment")
                        ->join('cooperative', 'cooperative.id', '=', 'mineral.cooperative_id')
                        ->where("mineral.years", $years)
                        ->where("mineral.months", $months)
                        ->where("mineral.region_id", $region_id)
                        ->get()
                        ->toArray();
    }

}
