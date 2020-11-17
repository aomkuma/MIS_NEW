<?php

namespace App\Service;

use App\Model\SpermSale;
use App\Model\SpermSaleDetail;
use Illuminate\Database\Capsule\Manager as DB;

class SpermSaleService {

    public static function loadDataApprove($UserID){
            return SpermSale::select("sperm_sale.*", 'cooperative.cooperative_name')
                            ->join('cooperative', 'cooperative.id', '=', 'sperm_sale.cooperative_id')
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

    public static function getMainList($years, $months, $region_id) {
        return SpermSale::select(DB::raw("SUM(amount) AS sum_amount")
                                , DB::raw("SUM(`values`) AS sum_baht")
                                , "sperm_sale.update_date","office_approve_id"
                                    ,"office_approve_date"
                                    ,"office_approve_comment")
                        ->join("sperm_sale_detail", 'sperm_sale_detail.sperm_sale_id', '=', 'sperm_sale.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        ->where("region_id", $region_id)
                        ->orderBy('update_date', 'DESC')
                        ->first()
                        ->toArray();
    }

    public static function getMainListforreport($years, $months,  $sperm_type_id) {
        return SpermSale::select(DB::raw("SUM(amount) AS sum_amount")
                                , DB::raw("SUM(`values`) AS sum_baht")
                                , "sperm_sale.update_date")
                        ->join("sperm_sale_detail", 'sperm_sale_detail.sperm_sale_id', '=', 'sperm_sale.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        
                        ->where("sperm_sale_type_id", $sperm_type_id)
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return SpermSale::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('spermSaleDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function getData($cooperative_id, $months, $years) {
        return SpermSale::where('cooperative_id', $cooperative_id)
                        ->where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('spermSaleDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SpermSale::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SpermSale::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SpermSaleDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SpermSaleDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {

        return SpermSaleDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function updateDataApprove($id, $obj) {

            return SpermSale::where('id', $id)->update($obj);
        }
        public static function getDetailmonth($years, $months, $type_id = '', $region = '') {
        $ckid = null;
        return SpermSale::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(`values`) AS price"))
                         ->join("sperm_sale_detail", 'sperm_sale_detail.sperm_sale_id', '=', 'sperm_sale.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        // ->where('office_approve_id', !$ckid)
                        // ->where(function($query) use ($ckid) {

                        //     $query->where('office_approve_comment', $ckid);
                        //     $query->orWhere('office_approve_comment', '');
                        // })

                        ->where(function($query) use ($type_id) {
                            if(!empty($type_id)){
                                $query->where('sperm_sale_type_id', $type_id);
                            }
                        })
                        // ->where("sperm_sale_type_id", $type_id)
                        ->first()
                        ->toArray();
    }


    public static function getDetailData($years, $months, $region_id, $cooperative_id, $sperm_sale_type_id) {
        return SpermSaleDetail::select(DB::raw("SUM(amount) AS sum_amount")
                                , DB::raw("SUM(`values`) AS sum_baht"))
                ->join('sperm_sale', 'sperm_sale.id', '=', 'sperm_sale_detail.sperm_sale_id')
                ->where("sperm_sale.years", $years)
                ->where("sperm_sale.months", $months)
                ->where("sperm_sale.region_id", $region_id)
                ->where("sperm_sale.cooperative_id", $cooperative_id)
                ->where("sperm_sale_detail.sperm_sale_type_id", $sperm_sale_type_id)
                ->first()
                ->toArray();
    }

}
