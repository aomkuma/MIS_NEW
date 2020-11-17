<?php

namespace App\Service;

use App\Model\Insemination;
use App\Model\InseminationDetail;
use Illuminate\Database\Capsule\Manager as DB;

class InseminationService {

    public static function loadDataApprove($UserID) {
        return Insemination::select("insemination.*", 'cooperative.cooperative_name')
                        ->join('cooperative', 'cooperative.id', '=', 'insemination.cooperative_id')
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

    public static function getMainList($years, $months, $region_id/* , $farm_type, $item_type */) {
        return Insemination::select(DB::raw("SUM(cow_amount) AS sum_cow_amount")
                                , DB::raw("SUM(service_cost + sperm_cost + material_cost) AS sum_income_amount")
                                , "insemination.update_date", "office_approve_id"
                                , "office_approve_date"
                                , "office_approve_comment"
                                , "user_comment")
                        ->join("insemination_detail", 'insemination_detail.insemination_id', '=', 'insemination.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region_id)
                        ->first()
                        ->toArray();
    }

    public static function getDetailmonth($years, $months, $region) {
        $ckid = null;
        return Insemination::select(DB::raw("SUM(cow_amount) AS amount")
                                , DB::raw("SUM(`service_cost`) AS price")
                , "user_comment")
                        ->join("insemination_detail", 'insemination_detail.insemination_id', '=', 'insemination.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region)
                        ->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })
                        ->first()
                        ->toArray();
    }

    public static function getDetailyear($years, $region) {
        return Insemination::select(DB::raw("SUM(cow_amount) AS amount")
                                , DB::raw("SUM(`service_cost`) AS price"))
                        ->join("insemination_detail", 'insemination_detail.insemination_id', '=', 'insemination.id')
                        ->where("years", $years)
                        ->where("region_id", $region)
                        ->first()
                        ->toArray();
    }

    public static function getDetailquar($years, $region, $quar) {
        $st = 1;
        $en = 3;
        $ckid = null;
        if ($quar == 1) {
            //$years-=1;
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
        return Insemination::select(DB::raw("SUM(cow_amount) AS amount")
                                , DB::raw("SUM(`service_cost`) AS price"))
                        ->join("insemination_detail", 'insemination_detail.insemination_id', '=', 'insemination.id')
                        ->where("years", $years)
                        ->whereBetween("months", [$st, $en])
                        ->where("region_id", $region)
                        ->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return Insemination::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('inseminationDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function getData($cooperative_id, $months, $years) {
        return Insemination::where('cooperative_id', $cooperative_id)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('inseminationDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->first();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Insemination::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Insemination::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = InseminationDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = InseminationDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {

        return InseminationDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function updateDataApprove($id, $obj) {

        return Insemination::where('id', $id)->update($obj);
    }
    public static function getcomment($years, $months, $region_id) {
        return Insemination::select("cooperative.cooperative_name","insemination.months", "insemination.user_comment")
                        ->join('cooperative', 'cooperative.id', '=', 'insemination.cooperative_id')
                        ->where("insemination.years", $years)
                        ->where("insemination.months", $months)
                        ->where("insemination.region_id", $region_id)
                        ->get()
                        ->toArray();
    }

}
