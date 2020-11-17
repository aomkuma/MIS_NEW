<?php

namespace App\Service;

use App\Model\XxcustOrderRmV;
use App\Model\XxCustOuV;
use App\Model\XxCustPoRmV;
use App\Model\XxCustPoRmVendorV;
use App\Model\XxCustVendorV;
use App\Model\Mou;
use Illuminate\Database\Capsule\Manager as DB;

class MSIService {

    public static function getListMSI($fromTime, $toTime, $region) {
        // echo "$fromTime, $toTime, $region";
        return XxcustOrderRmV::select(
                                'REGION'
                                , DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        ->where('REGION', $region)
                        // ->groupBy('REGION')
                        ->first();
    }
    public static function getListMSIreoprt($fromTime, $toTime) {
        // echo "$fromTime, $toTime, $region";
        return XxcustOrderRmV::select(
                                DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        //->where('REGION', $region)
                        // ->groupBy('REGION')
                        ->first()
                        ->toArray();
    }
 public static function getListMSIreoprt2($year, $month) {
        $fromTime = $year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT) . '-01';
        $last_day = date('t', strtotime($fromTime));
        $toTime = $year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . $last_day;

        return XxcustOrderRmV::select(
                               
                                 DB::raw("SUM(QUANTITY) AS amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        //->where('REGION', $region)
                        // ->groupBy('REGION')
                        ->first()
                        ->toArray();
    }
    public static function getListMSIByVendor($fromTime, $toTime, $region) {
        // echo "$fromTime, $toTime, $region";
        return XxCustPoRmVendorV::select(
                                'REGION'
                                , DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        ->where('VENDOR_NAME', $region)
                        ->first();
    }

    public static function getListMSIByExceptVendor($fromTime, $toTime, $except_vendor, $region) {
        // echo "$fromTime, $toTime, $region";
        return XxCustPoRmVendorV::select(
                                'REGION'
                                , DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        ->whereNotIn('VENDOR_NAME', $except_vendor)
                        ->where('REGION', $region)
                        ->first();
    }

    public static function getListMSIByYear($fromTime, $toTime, $region) {
        // echo "$fromTime, $toTime, $region";
        return XxcustOrderRmV::select(
                                DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        ->where('REGION', $region)
                        // ->where(DB::raw('YEAR(TRANSACTION_DATE)'), $years)
                        ->first();
    }

    public static function getListMSIDetail($fromTime, $toTime, $region) {
        // echo "$fromTime, $toTime, $region";
        return XxcustOrderRmV::select(
                                DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        ->whereIn('REGION', $region)
                        // ->groupBy('REGION')
                        ->first();
    }

    public static function getListMOU($fromTime, $toTime, $region_id) {
        return Mou::select(
                                DB::raw("SUM(mou_value) AS sum_baht")
                                , DB::raw("SUM(mou_amount) AS sum_amount")
                        )
                        ->join('cooperative', 'cooperative.id', '=', 'mou.cooperative_id')
                        ->whereBetween('start_date', [$fromTime, $toTime])
                        ->whereBetween('end_date', [$fromTime, $toTime])
                        ->where('cooperative.region_id', $region_id)
                        ->first();
    }

    public static function getListMOUByYear($years, $region_id) {
        return Mou::select(
                                DB::raw("SUM(mou_value) AS sum_baht")
                                , DB::raw("SUM(mou_amount) AS sum_amount")
                        )
                        ->join('cooperative', 'cooperative.id', '=', 'mou.cooperative_id')
                        ->where('cooperative.region_id', $region_id)
                        ->where('years', $years)
                        ->first();
    }

    public static function getListMOUByCooperative($fromTime, $toTime, $cooperative_id) {
        return Mou::select(
                                DB::raw("SUM(mou_value) AS sum_baht")
                                , DB::raw("SUM(mou_amount) AS sum_amount")
                        )
                        ->whereBetween('start_date', [$fromTime, $toTime])
                        ->whereBetween('end_date', [$fromTime, $toTime])
                        ->where('years', $years)
                        ->first();
    }

    public static function getactualMSIDetail($years, $months) {
        $st = $years . '-' . $months . '-01';
        $en = $years . '-' . $months . '-31';

        return XxcustOrderRmV::select(
                                DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$st, $en])

                        // ->groupBy('REGION')
                        ->first()->toArray();
    }

}
