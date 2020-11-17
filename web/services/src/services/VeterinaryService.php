<?php

namespace App\Service;

use App\Model\Veterinary;
use App\Model\VeterinaryDetail;
use App\Model\VeterinaryItem;
use Illuminate\Database\Capsule\Manager as DB;

class VeterinaryService {

    public static function loadDataApprove($UserID) {
        return Veterinary::select("veterinary.*", 'cooperative.cooperative_name')
                        ->join('cooperative', 'cooperative.id', '=', 'veterinary.cooperative_id')
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

    public static function getMainList($years, $months, $region_id, $farm_type, $item_type, $dairy_farming_id = []) {

        return Veterinary::select(DB::raw("SUM(mis_veterinary_item.item_amount) AS sum_amount")
                                , "veterinary.update_date"
                                , "office_approve_id"
                                , "office_approve_date"
                                , "office_approve_comment"
                        )
                        // ->join("cooperative", 'cooperative.id', '=', 'veterinary.cooperative_id')
                        ->join("veterinary_detail", 'veterinary_detail.veterinary_id', '=', 'veterinary.id')
                        ->join("veterinary_item", 'veterinary_detail.id', '=', 'veterinary_item.veterinary_detail_id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("veterinary.region_id", $region_id)
                        ->where("farm_type", $farm_type)
                        // ->where('veterinary_item.item_type', $item_type)
                        ->whereIn('veterinary_item.item_type', $item_type)
                        ->where(function($query) use ($dairy_farming_id) {
                            if (!empty($dairy_farming_id)) {
                                $query->whereIn('dairy_farming_id', $dairy_farming_id);
                            }else{
                                $query->whereNotIn('dairy_farming_id', $dairy_farming_id);
                            }
                        })
                        // ->where("dairy_farming_id", '<>', 24)
                        ->orderBy('update_date', 'DESC')
                        ->first()
                        ->toArray();
    }

    public static function getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id = '', $sub_dairy_farming_id = '') {
        return Veterinary::select(DB::raw("SUM(mis_veterinary_item.item_amount) AS sum_amount")
                                , "veterinary.update_date")
                        ->join("veterinary_detail", 'veterinary_detail.veterinary_id', '=', 'veterinary.id')
                        ->join("veterinary_item", 'veterinary_detail.id', '=', 'veterinary_item.veterinary_detail_id')
                        // ->leftJoin("cooperative", 'cooperative.id', '=', 'veterinary.cooperative_id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("veterinary.region_id", $region_id)
                        ->where('veterinary_item.item_type', $item_type)
                        ->where('veterinary.cooperative_id', $cooperative_id)
                        ->where(function($query) use ($dairy_farming_id, $sub_dairy_farming_id) {
                            if (!empty($dairy_farming_id)) {
                                $query->where('dairy_farming_id', $dairy_farming_id);
                            }
                            if (!empty($sub_dairy_farming_id)) {
                                $query->where('sub_dairy_farming_id', $sub_dairy_farming_id);
                            }
                        })
                        // ->groupBy('cooperative.id')
                        // ->orderBy('cooperative_id', 'ASC')
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return Veterinary::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('veterinaryDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                                $query->with('dairyFarming');
                                $query->with('subDairyFarming');
                                $query->with('veterinaryItem');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function getData($cooperative_id, $months, $years) {
        return Veterinary::
                        where(function($query) use ($cooperative_id) {
                            if (!empty($cooperative_id)) {
                                $query->where('cooperative_id', $cooperative_id);
                            }
                        })
                        // where('cooperative_id', $cooperative_id)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('veterinaryDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                                $query->with('dairyFarming');
                                $query->with('subDairyFarming');
                                $query->with('veterinaryItem');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Veterinary::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Veterinary::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = VeterinaryDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = VeterinaryDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateItemData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = VeterinaryItem::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = VeterinaryItem::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeData($id) {
        VeterinaryItem::where('veterinary_id', $id)->delete();
        VeterinaryDetail::where('veterinary_id', $id)->delete();
        return Veterinary::find($id)->delete();
    }

    public static function removeDetailData($id) {
        VeterinaryItem::where('veterinary_detail_id', $id)->delete();
        return VeterinaryDetail::find($id)->delete();
    }

    public static function removeItemData($id) {
        return VeterinaryItem::find($id)->delete();
    }

    public static function updateDataApprove($id, $obj) {

        return Veterinary::where('id', $id)->update($obj);
    }

    public static function getDetailmonth($years, $months, $type_id, $region, $dairy_farming_id = [], $item_type_amount_list = []) {
        $ckid = null;
        return Veterinary::select(DB::raw("SUM(mis_veterinary_item.item_amount) AS amount")
                        )
                        ->join("veterinary_detail", 'veterinary_detail.veterinary_id', '=', 'veterinary.id')
                        ->join("veterinary_item", 'veterinary_item.veterinary_detail_id', '=', 'veterinary_detail.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        /*->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })*/
                        // ->where("veterinary_item.item_type", 'โคนม')
                        ->where(function($query) use ($dairy_farming_id, $item_type_amount_list) {
                            if(!empty($dairy_farming_id)){
                                $query->whereIn("dairy_farming_id", $dairy_farming_id);
                                $query->whereIn("veterinary_item.item_type", $item_type_amount_list);
                            }
                        })
                        ->first()
                        ->toArray();
    }

    public static function getDetailmonthPrice($years, $months, $type_id, $region, $dairy_farming_id = [], $item_type_price_list = []) {
        $ckid = null;
        return Veterinary::select(DB::raw("SUM(mis_veterinary_item.item_amount) AS amount")
                        )
                        ->join("veterinary_detail", 'veterinary_detail.veterinary_id', '=', 'veterinary.id')
                        ->join("veterinary_item", 'veterinary_item.veterinary_detail_id', '=', 'veterinary_detail.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        /*->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })*/
                        // ->whereIn("veterinary_item.item_type", ['ค่าวัสดุ','ค่าเวชภัณฑ์','ค่าบริการ'])
                        ->whereIn("veterinary_item.item_type", $item_type_price_list)
                        ->whereIn("dairy_farming_id", $dairy_farming_id)
                        ->first()
                        ->toArray();
    }

    public static function getDetailmonthInsemination($years, $months, $type_id, $region) {
        $ckid = null;
        return Veterinary::select(DB::raw("SUM(mis_veterinary_item.item_amount) AS amount")
                        )
                        ->join("veterinary_detail", 'veterinary_detail.veterinary_id', '=', 'veterinary.id')
                        ->join("veterinary_item", 'veterinary_item.veterinary_detail_id', '=', 'veterinary_detail.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->whereIn("veterinary_item.item_type", ['โคนม', 'ปริมาณงาน'])
                        ->whereIn("dairy_farming_id", [17,29])
                        ->first()
                        ->toArray();
    }

    public static function getcomment($years, $months, $region_id) {
        return Veterinary::select("cooperative.cooperative_name", "veterinary.months", "veterinary.user_comment")
                        ->join('cooperative', 'cooperative.id', '=', 'veterinary.cooperative_id')
                        ->where("veterinary.years", $years)
                        ->where("veterinary.months", $months)
                        ->where("veterinary.region_id", $region_id)
                        ->get()
                        ->toArray();
    }

}
