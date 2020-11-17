<?php

namespace App\Service;

use App\Model\XxcustOrderRmV;
use App\Model\XxCustOuV;
use App\Model\XxCustPoRmV;
use App\Model\XxCustPoRmVendorV;
use App\Model\XxCustVendorV;
use App\Model\Mou;
use Illuminate\Database\Capsule\Manager as DB;

class MBIService {

    public static function getListMBI($fromTime, $toTime, $region) {
        echo "$fromTime, $toTime, $region";
        return XxCustPoRmVendorV::select(
                                'REGION'
                                , DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        ->where('REGION', $region)
                        // ->groupBy('REGION')
                        ->first()->toArray();
    }

    public static function getListMBIreoprt($fromTime, $toTime) {
        // echo "$fromTime, $toTime, $region";
        return XxCustPoRmVendorV::select(
                                DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        //->where('REGION', $region)
                        // ->groupBy('REGION')
                        ->first()
                        ->toArray();
    }

    public static function getListMBIreoprt2($year, $month) {
        $fromTime = $year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT) . '-01';
        $last_day = date('t', strtotime($fromTime));
        $toTime = $year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . $last_day;

        return XxCustPoRmVendorV::select(
                               
                                 DB::raw("SUM(QUANTITY) AS amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        //->where('REGION', $region)
                        // ->groupBy('REGION')
                        ->first()
                        ->toArray();
    }

    public static function getListMBIByVendor($fromTime, $toTime, $cooperative_name, $region) {
        
        return XxCustPoRmVendorV::select(
                                'REGION'
                                , DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        ->where('VENDOR_NAME', $cooperative_name)
                        ->where('REGION', $region)
                        ->first();
    }

    public static function getListMBIByExceptVendor($fromTime, $toTime, $except_vendor, $region) {
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

    public static function getListMBIByYear($fromTime, $toTime, $region) {
        // echo "$fromTime, $toTime, $region";
        return XxCustPoRmVendorV::select(
                                DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        ->where('REGION', $region)
                        // ->where(DB::raw('YEAR(TRANSACTION_DATE)'), $years)
                        ->first();
    }

    public static function getListMBIDetail($fromTime, $toTime, $region) {
        // echo "$fromTime, $toTime, $region";
        return XxCustPoRmVendorV::select(
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

    public static function getactualMBIDetail($years, $months) {
        $st = $years . '-' . $months . '-01';
        $en = $years . '-' . $months . '-31';

        return XxCustPoRmVendorV::select(
                                DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$st, $en])

                        // ->groupBy('REGION')
                        ->first()->toArray();
    }

    public static function getactualMBIDetailByVendor($years, $months, $vendor) {
        $st = $years . '-' . $months . '-01';
        $en = $years . '-' . $months . '-31';

        return XxCustPoRmVendorV::select(
                                DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$st, $en])
                        
                        ->where(function($query) use ($vendor) {
                            if($vendor == 'สหกรณ์'){
                                $query->where('VENDOR_NAME', 'NOT LIKE', 'ศูนย์%');
                            }else{
                                $query->where('VENDOR_NAME', 'LIKE', $vendor . '%');
                            }
                        })
                        // ->groupBy('REGION')
                        ->first()->toArray();
    }

    public static function getListMBIByVendorreport($fromTime, $toTime, $region) {
        // echo "$fromTime, $toTime, $region";
        return XxCustPoRmVendorV::select(
                                'VENDOR_NAME'
                                , DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                        )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        ->where('REGION', $region)
                        ->groupBy('VENDOR_NAME')
                        ->get()
                        ->toArray();
    }

    public static function updateData($obj, $logger) {
        // XxCustPoRmVendorV::where('REGION', $obj['REGION'])
        //             ->where('VENDOR_NAME', $obj['VENDOR_NAME'])
        //             ->where('VENDOR_NAME', $obj['VENDOR_NAME'])
        //             ->where('TRANSACTION_DATE', $obj['TRANSACTION_DATE'])
        //             ->where('UOM', $obj['UOM'])
        //             ->where('ITEM_CODE', $obj['ITEM_CODE'])
        //             ->delete();

        // $logger->info($obj['TRANSACTION_DATE']);

        return XxCustPoRmVendorV::create($obj);
    }

    public static function deleteOldData($date, $region){
        XxCustPoRmVendorV::where('REGION', $region)
        ->where('TRANSACTION_DATE', 'LIKE', $date . '%')
        ->delete();
    }

}
