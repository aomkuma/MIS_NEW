<?php
    
    namespace App\Service;
    
    use App\Model\BeginEndingBalance;
    use App\Model\BeginEndingBalanceDetail;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class BeginEndingBalanceService {

        public static function getMainList($condition){
            return BeginEndingBalance::with('beginEndingBalanceDetails')
                            ->where('months', $condition['MonthFrom'])
                            ->where('years', $condition['YearTo'])
                            ->first();
        }

        public static function getData($months, $years){
            return BeginEndingBalance::where('months', $months)
                            ->where('years', $years)
                            ->first();
        }

        public static function getBeforeSumByDate($years, $months){
            return BeginEndingBalanceDetail::select(DB::raw("SUM(mis_begin_ending_balance_detail.begin_amount) AS sum_amount"))
                            ->join("begin_ending_balance", 'begin_ending_balance_detail.begin_ending_balance_id', '=', 'begin_ending_balance.id')
                            ->where('months', $months)
                            ->where('years', $years)
                            ->first();
        }

        public static function getEndingSumByDate($years, $months){
            return BeginEndingBalanceDetail::select(DB::raw("SUM(mis_begin_ending_balance_detail.ending_amount) AS sum_amount"))
                            ->join("begin_ending_balance", 'begin_ending_balance_detail.begin_ending_balance_id', '=', 'begin_ending_balance.id')
                            ->where('months', $months)
                            ->where('years', $years)
                            ->first();
        }

        public static function getEndingBalance($months, $years, $factory_id, $milk_type = ''){
            return BeginEndingBalanceDetail::select('begin_ending_balance_detail.*')
                            ->join("begin_ending_balance", 'begin_ending_balance_detail.begin_ending_balance_id', '=', 'begin_ending_balance.id')
                            ->where('months', $months)
                            ->where('years', $years)
                            ->where('factory_id', $factory_id)
                            ->where(function($query) use ($milk_type){
                                if(!empty($milk_type)){
                                    $query->where('milk_type' , $milk_type);
                                }
                            })
                            ->first();
        }

        public static function getDataDetail($begin_ending_balance_id, $milk_type, $factory_id){
            return BeginEndingBalanceDetail::where('begin_ending_balance_id', $begin_ending_balance_id)
                            ->where('milk_type', $milk_type)
                            ->where('factory_id', $factory_id)
                            ->first();
        }

        public static function updateData($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = BeginEndingBalance::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = BeginEndingBalance::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

        public static function updateDataDetail($obj){
            if(empty($obj['id'])){
                $obj['create_date'] = date('Y-m-d H:i:s');
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = BeginEndingBalanceDetail::create($obj);
                return $model->id;
            }else{
                $obj['update_date'] = date('Y-m-d H:i:s');
                $model = BeginEndingBalanceDetail::find($obj['id'])->update($obj);
                return $obj['id'];
            }
        }

    }
