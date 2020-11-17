<?php

namespace App\Service;

use App\Model\Travel;
use App\Model\TravelDetail;
use App\Model\TravelItem;
use Illuminate\Database\Capsule\Manager as DB;

class TravelService {

    public static function getDetailByDay($years, $months, $days, $goal_id) {
        return Travel::select(DB::raw("SUM(total_person_pay) AS total_person_pay")
                                , DB::raw("SUM(unit_price) AS unit_price")
                                , DB::raw("SUM(discount) AS discount")
                                , DB::raw("SUM(total_price) AS total_price")
                                )
                        ->join("travel_item", 'travel_item.travel_id', '=', 'travel.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("days", $days)
                        ->where("goal_id", $goal_id)
                        ->first()
                        ->toArray();
                        // exit;
    }

    public static function loadDataApprove($UserID){
            return Travel::where(function($query) use ($UserID){
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

    public static function getMainList($years, $months, $field_amount, $field_price, $goal_id) {
        return Travel::select(DB::raw("SUM(" . $field_amount . ") AS sum_amount")
                                , DB::raw("SUM(" . $field_price . ") AS sum_baht")
                                , "travel.update_date","office_approve_id"
                                ,"office_approve_date"
                                ,"office_approve_comment"
                                )
                        // ->join("travel_detail", 'travel_detail.travel_id', '=', 'travel.id')
                        ->join("travel_item", 'travel_item.travel_id', '=', 'travel.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("goal_id", $goal_id)
                        // ->groupBy('travel_detail.id')
                        // ->where("region_id", $region_id)
                        ->first()
                        ->toArray();
                        // exit;
    }

    public static function getDataByID($id) {
        return Travel::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('travelDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function getData($days, $months, $years) {
        return Travel::where('days', $days)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('travelDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function getItem($travel_detail_id) {
        return TravelItem::select("travel_item.*"
                        , "master_goal.goal_name"
                    )
                    ->leftJoin("master_goal", "master_goal.id", '=', 'travel_item.goal_id')
                    ->where('travel_detail_id', $travel_detail_id)
                    ->orderBy('goal_id', 'DESC')
                    ->get();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Travel::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Travel::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = TravelDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = TravelDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateItemData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = TravelItem::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = TravelItem::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {
        TravelItem::where('travel_detail_id' , $id)->delete();
        return TravelDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function getDetailmonth($years, $month, $goal_id) {
        $ckid = null;
        return Travel::select(
                                DB::raw("SUM(`total_person_pay`) AS amount")
                                ,DB::raw("SUM(`total_price`) AS price")
                            )
                        ->join("travel_detail", 'travel_detail.travel_id', '=', 'travel.id')
                        ->join("travel_item", 'travel_item.travel_detail_id', '=', 'travel_detail.id')
                        ->join("master_goal", 'travel_item.goal_id', '=', 'master_goal.id')
                        ->where("years", $years)
                        /*->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })*/
                        ->where("months", $month)
                        ->where("actives", 'Y')
                        ->where(function($query) use ($goal_id) {
                            if(!empty($goal_id)){
                                $query->whereIn('travel_item.goal_id', $goal_id);   
                            }
                        })
                        ->first()
                        ->toArray();
    }

    public static function getDetailmonthExcept($years, $month, $goal_id) {
        $ckid = null;
        return Travel::select(
                                DB::raw("SUM(`total_person_pay`) AS amount")
                                ,DB::raw("SUM(`total_price`) AS price")
                            )
                        ->join("travel_detail", 'travel_detail.travel_id', '=', 'travel.id')
                        ->join("travel_item", 'travel_item.travel_detail_id', '=', 'travel_detail.id')
                        ->where("years", $years)
                        /*->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })*/
                        ->where("months", $month)
                        ->where(function($query) use ($goal_id) {
                            if(!empty($goal_id)){
                                $query->whereNotIn('travel_item.goal_id', $goal_id);   
                            }
                        })
                        ->first()
                        ->toArray();
    }

    public static function getDetailyear($years, $region) {
        return Travel::select(/*DB::raw("SUM(adult_pay) AS apay")
                                , DB::raw("SUM(`child_pay`) AS cpay")
                                , DB::raw("SUM(`student_pay`) AS spay")
                                , DB::raw("SUM(`adult_price`) AS p_adult")
                                , DB::raw("SUM(`child_price`) AS p_child")
                                , DB::raw("SUM(`student_price`) AS p_student")
                                , DB::raw("SUM(`adult_except`) AS a_except")
                                , DB::raw("SUM(`child_except`) AS c_except")
                                , DB::raw("SUM(`student_except`) AS s_except")*/
                            DB::raw("SUM(`total_person_pay`) AS amount")
                        )
                        ->join("travel_detail", 'travel_detail.travel_id', '=', 'travel.id')
                        ->join("travel_item", 'travel_item.travel_detail_id', '=', 'travel_detail.id')
                        ->where("years", $years)
                        // ->where("region_id", $region)
                        ->first()
                        ->toArray();
    }

    public static function getDetailquar($years, $region, $quar) {
        $st = 1;
        $en = 3;
        $ckid = null;
        if ($quar == 1) {
//            $years-=1;
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

        return Travel::select(DB::raw("SUM(adult_pay) AS apay")
                                , DB::raw("SUM(`child_pay`) AS cpay")
                                , DB::raw("SUM(`student_pay`) AS spay")
                                , DB::raw("SUM(`adult_price`) AS p_adult")
                                , DB::raw("SUM(`child_price`) AS p_child")
                                , DB::raw("SUM(`student_price`) AS p_student")
                                , DB::raw("SUM(`adult_except`) AS a_except")
                                , DB::raw("SUM(`child_except`) AS c_except")
                                , DB::raw("SUM(`student_except`) AS s_except"))
                        ->join("travel_detail", 'travel_detail.travel_id', '=', 'travel.id')
                        ->where("years", $years)
                       ->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })
                        ->whereBetween("months", [$st, $en])
                        ->first()
                        ->toArray();
    }

    public static function updateDataApprove($id, $obj) {

        return Travel::where('id', $id)->update($obj);
    }
    public static function getcomment($years, $months, $region_id) {
        return Travel::select("months", "user_comment")
                        
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region_id)
                        ->get()
                        ->toArray();
    }

}
