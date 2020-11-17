<?php

namespace App\Service;

use App\Model\CowgroupFather;
use App\Model\CowgroupFatherDetail;
use Illuminate\Database\Capsule\Manager as DB;

class CowgroupFatherService {

    public static function loadDataApprove($UserID) {
        return CowgroupFather::select("cow_group_father.*", 'cooperative.cooperative_name')
                        ->join('cooperative', 'cooperative.id', '=', 'cow_group_father.cooperative_id')
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
        return CowgroupFather::select(DB::raw("SUM(" . $field_name . ") AS sum_baht")
                                // , DB::raw("SUM(".$field_price.") AS sum_baht")
                                , "cow_group_father.update_date", "office_approve_id"
                                , "office_approve_date"
                                , "office_approve_comment")
                        ->join("cow_group_father_detail", 'cow_group_father_detail.cow_group_id', '=', 'cow_group_father.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region_id)
                        ->where("cow_group_item_id", $goal_id)
                        ->first()
                        ->toArray();
    }

    public static function getMainListquar($years, $st, $en, $region_id, $goal_id, $field_name) {
        return CowgroupFather::select(DB::raw("SUM(" . $field_name . ") AS sum_baht")
                                // , DB::raw("SUM(".$field_price.") AS sum_baht")
                                , "cow_group_father.update_date")
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group_father.id')
                        ->where("years", $years)
                        ->whereBetween("months", [$st, $en])
                        ->where("region_id", $region_id)
                        ->where("cow_group_item_id", $goal_id)
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return CowgroupFather::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('CowgroupFatherDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function getData($cow_group_name, $cooperative_id, $months, $years) {
        return CowgroupFather::where('cow_group_name', $cow_group_name)
                        ->where('cooperative_id', $cooperative_id)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('CowgroupFatherDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function getcomment($years, $months, $region_id) {
        return CowgroupFather::select("cooperative.cooperative_name", "cow_group_father.months", "cow_group_father.user_comment")
                        ->join('cooperative', 'cooperative.id', '=', 'cow_group_father.cooperative_id')
                        ->where("cow_group_father.years", $years)
                        ->where("cow_group_father.months", $months)
                        ->where("cow_group_father.region_id", $region_id)
                        ->get()
                        ->toArray();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowgroupFather::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowgroupFather::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowgroupFatherDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = CowgroupFatherDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {

        return CowgroupFatherDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function getDetailmonth($years, $months, $type_id, $region) {
        $ckid = null;
        return CowgroupFather::select(DB::raw("SUM(total_sell) AS amount")
                                , DB::raw("SUM(`total_sell_values`) AS price"))
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group_father.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })
                        ->where("cow_group_item_id", $type_id)
                        ->first()
                        ->toArray();
    }

    public static function getDetailyear($years, $type_id, $region) {
        return CowgroupFather::select(DB::raw("SUM(total_sell) AS amount")
                                , DB::raw("SUM(`total_sell_values`) AS price"))
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group_father.id')
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
        return CowgroupFather::select(DB::raw("SUM(total_sell) AS amount")
                                , DB::raw("SUM(`total_sell_values`) AS price"))
                        ->join("cow_group_detail", 'cow_group_detail.cow_group_id', '=', 'cow_group_father.id')
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

        return CowgroupFather::where('id', $id)->update($obj);
    }

    public static function getReportData($years, $months, $goal_id){
        return CowgroupFather::select(DB::raw("SUM(beginning_period) AS beginning_period")
                                , DB::raw("SUM(total_born) AS total_born")
                                , DB::raw("SUM(total_movein) AS total_movein")
                                , DB::raw("SUM(total_buy) AS total_buy")
                            )
                        ->join("cow_group_father_detail", 'cow_group_father_detail.cow_group_id', '=', 'cow_group_father.id')
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

}
