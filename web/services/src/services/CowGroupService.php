<?php

namespace App\Service;

use App\Model\CowGroup;
use App\Model\CowGroupDetail;
use Illuminate\Database\Capsule\Manager as DB;

class CowGroupService {

    public static function loadDataApprove($UserID) {
        return CowGroup::select("cow_group.*", 'cooperative.cooperative_name')
                        ->join('cooperative', 'cooperative.id', '=', 'cow_group.cooperative_id')
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

    public static function getMainList($years, $months, $region_id, $goal_id, $field_name) {
        return CowGroup::select(DB::raw("SUM(" . $field_name . ") AS sum_baht")
                                // , DB::raw("SUM(".$field_price.") AS sum_baht")
                                , "cow_group.update_date", "office_approve_id"
                                , "office_approve_date"
                                , "office_approve_comment")
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region_id)
                        ->where("cow_group_item_id", $goal_id)
                        ->first()
                        ->toArray();
    }

    public static function getMainListquar($years, $st, $en, $region_id, $goal_id, $field_name) {
        return CowGroup::select(DB::raw("SUM(" . $field_name . ") AS sum_baht")
                                // , DB::raw("SUM(".$field_price.") AS sum_baht")
                                , "cow_group.update_date")
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                        ->where("years", $years)
                        ->whereBetween("months", [$st, $en])
                        ->where("region_id", $region_id)
                        ->where("cow_group_item_id", $goal_id)
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return CowGroup::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('cowGroupDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function getData($cow_group_name, $cooperative_id, $months, $years) {
        return CowGroup::where('cow_group_name', $cow_group_name)
                        ->where('cooperative_id', $cooperative_id)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('cowGroupDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function getcomment($years, $months, $region_id) {
        return CowGroup::select("cooperative.cooperative_name", "cow_group.months", "cow_group.user_comment")
                        ->join('cooperative', 'cooperative.id', '=', 'cow_group.cooperative_id')
                        ->where("cow_group.years", $years)
                        ->where("cow_group.months", $months)
                        ->where("cow_group.region_id", $region_id)
                        ->get()
                        ->toArray();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowGroup::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowGroup::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowGroupDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowGroupDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {

        return CowGroupDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function getDetailmonth($years, $months, $type_id, $region) {
        $ckid = null;
        return CowGroup::select(DB::raw("SUM(go_factory_weight + cow_weight + decline_weight + yellow_milk_weight) AS amount")
                                , DB::raw("SUM(go_factory_values + cow_values + decline_values + yellow_milk_values) AS price"))
                        // ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        // ->where('office_approve_id', !$ckid)
                        /*->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })*/
                        ->where(function($query) use ($type_id) {
                            if(!empty($type_id)){
                                $query->where('cow_group_item_id', $type_id);
                            }
                        })
                        ->first()
                        ->toArray();
    }

    public static function getDetailyear($years, $type_id, $region) {
        return CowGroup::select(DB::raw("SUM(total_sell) AS amount")
                                , DB::raw("SUM(`total_sell_values`) AS price"))
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                        ->where("years", $years)
                        ->where("region_id", $region)
                        ->where("cow_group_item_id", $type_id)
                        ->first()
                        ->toArray();
    }

    public static function getDetailquar($years, $type_id, $region, $quar) {
        $st = 1;
        $en = 3;
        $ckid = null;
        if ($quar == 1) {
            //      $years-=1;
            $st = 10;
            $en = 12;
        } else if ($quar == 2) {
            $st = 1;
            $en = 3;
        } else if ($quar == 3) {
            $st = 4;
            $en = 6;
        } else {
            $st = 7;
            $en = 9;
        }
        return CowGroup::select(DB::raw("SUM(total_sell) AS amount")
                                , DB::raw("SUM(`total_sell_values`) AS price"))
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                        ->where("years", $years)
                        ->whereBetween("months", [$st, $en])
                        ->where("region_id", $region)
                        ->where("cow_group_item_id", $type_id)
                        ->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })
                        ->first()
                        ->toArray();
    }

    public static function updateDataApprove($id, $obj) {

        return CowGroup::where('id', $id)->update($obj);
    }

    public static function getReportData($years, $months, $goal_id){
        return CowGroup::select(DB::raw("SUM(beginning_period) AS beginning_period")
                                , DB::raw("SUM(total_born) AS total_born")
                                , DB::raw("SUM(total_movein) AS total_movein")
                                , DB::raw("SUM(total_buy) AS total_buy")
                                , DB::raw("SUM(total_die) AS total_die")
                                , DB::raw("SUM(total_sell) AS total_sell")
                                , DB::raw("SUM(total_moveout) AS total_moveout")
                                , DB::raw("SUM(last_period) AS last_period")
                            )
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where(function($query) use ($goal_id) {
                            if(!empty($goal_id)){
                                $query->where('cow_group_item_id', $goal_id);
                            }
                        })
                        ->first()
                        ->toArray();
    }

    public static function getReportSummaryData($years, $months, $cow_group_name){
        return CowGroup::select(DB::raw("SUM(go_factory_weight) AS go_factory_weight")
                                , DB::raw("SUM(go_factory_price) AS go_factory_price")
                                , DB::raw("SUM(go_factory_values) AS go_factory_values")
                                , DB::raw("SUM(cow_weight) AS cow_weight")
                                , DB::raw("SUM(cow_price) AS cow_price")
                                , DB::raw("SUM(cow_values) AS cow_values")
                                , DB::raw("SUM(decline_weight) AS decline_weight")
                                , DB::raw("SUM(decline_price) AS decline_price")
                                , DB::raw("SUM(decline_values) AS decline_values")
                                , DB::raw("SUM(yellow_milk_weight) AS yellow_milk_weight")
                                , DB::raw("SUM(yellow_milk_price) AS yellow_milk_price")
                                , DB::raw("SUM(yellow_milk_values) AS yellow_milk_values")
                            )
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where(function($query) use ($cow_group_name) {
                            if(!empty($cow_group_name)){
                                $query->where('cow_group_name', $cow_group_name);
                            }
                        })
                        ->first()
                        ->toArray();
    }
}
