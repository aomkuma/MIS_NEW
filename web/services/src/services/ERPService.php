<?php
    
    namespace App\Service;
    
    use App\Model\XxcustOrderRmV;
    use App\Model\XxCustOuV;
    use App\Model\XxCustPoRmV;
    use App\Model\XxCustPoRmVendorV;
    use App\Model\XxCustVendorV;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class ERPService {

        public static function getListMBI($fromTime, $toTime, $region){
            // echo "$fromTime, $toTime, $region";
            return XxCustPoRmV::select(
                                'REGION'
                                , DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                                )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        ->where('REGION', $region)
                        // ->groupBy('REGION')
                        ->first();     
        }

        public static function getListMBIDetail($fromTime, $toTime, $region){
            // echo "$fromTime, $toTime, $region";
            return XxCustPoRmV::select(
                                DB::raw("SUM(AMOUNT) AS sum_baht")
                                , DB::raw("SUM(QUANTITY) AS sum_amount")
                                )
                        ->whereBetween('TRANSACTION_DATE', [$fromTime, $toTime])
                        ->whereIn('REGION', $region)
                        // ->groupBy('REGION')
                        ->first();     
        }


    }