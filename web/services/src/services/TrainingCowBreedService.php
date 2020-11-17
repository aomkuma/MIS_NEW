<?php

namespace App\Service;

use App\Model\TrainingCowBreed;
use App\Model\TrainingCowBreedDetail;
use Illuminate\Database\Capsule\Manager as DB;

class TrainingCowBreedService {

    public static function loadDataApprove($UserID) {
        return TrainingCowBreed::where(function($query) use ($UserID) {
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

    public static function getMainList($years, $months, $region_id, $training_cowbreed_type_id) {
        return TrainingCowBreed::select(DB::raw("SUM(amount) AS sum_amount")
                                , DB::raw("SUM(`values`) AS sum_baht")
                                , "training_cowbreed.update_date", "office_approve_id"
                                , "office_approve_date"
                                , "office_approve_comment")
                        ->join("training_cowbreed_detail", 'training_cowbreed_detail.training_cowbreed_id', '=', 'training_cowbreed.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        // ->where("region_id", $region_id)
                        ->where('training_cowbreed_type_id', $training_cowbreed_type_id)
                        ->first()
                        ->toArray();
    }

    public static function getDataByID($id) {
        return TrainingCowBreed::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('trainingCowBreedDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function getData($cooperative_id, $months, $years) {
        return TrainingCowBreed::where('months', $months)
                        ->where('years', $years)
                        //->with('mouHistories')
                        ->with(array('trainingCowBreedDetail' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                            }))
                        ->orderBy('id', 'DESC')
                        ->first();
    }

    public static function updateData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = TrainingCowBreed::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = TrainingCowBreed::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDetailData($obj) {

        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = TrainingCowBreedDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = TrainingCowBreedDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeDetailData($id) {

        return TrainingCowBreedDetail::find($id)->delete();
    }

    public static function removeData($id) {
        
    }

    public static function getDetailmonth($years, $months, $type_id, $region) {
        $ckid = null;
        return TrainingCowBreed::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(`values`) AS price"))
                        ->join("training_cowbreed_detail", 'training_cowbreed_detail.training_cowbreed_id', '=', 'training_cowbreed.id')
                        ->where("years", $years)
                        ->where("months", $months)
                        /*->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })*/
                        ->where(function($query) use ($type_id) {
                            if(!empty($type_id)){
                                $query->where('training_cowbreed_type_id', $type_id);
                            }
                        })
                        // ->where("training_cowbreed_type_id", $type_id)
                        ->first()
                        ->toArray();
    }

    public static function getDetailyear($years, $type_id, $region) {
        return TrainingCowBreed::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(`values`) AS price"))
                        ->join("training_cowbreed_detail", 'training_cowbreed_detail.training_cowbreed_id', '=', 'training_cowbreed.id')
                        ->where("years", $years)
//                        ->where("region_id", $region)
                        ->where("training_cowbreed_type_id", $type_id)
                        ->first()
                        ->toArray();
    }

    public static function getDetailquar($years, $type_id, $region, $quar) {
        $st = 1;
        $en = 3;
        $ckid = null;
        if ($quar == 1) {
            //  $years-=1;
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
        return TrainingCowBreed::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(`values`) AS price"))
                        ->join("training_cowbreed_detail", 'training_cowbreed_detail.training_cowbreed_id', '=', 'training_cowbreed.id')
                        ->where("years", $years)
                        ->whereBetween("months", [$st, $en])
                        ->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })
                        ->where("training_cowbreed_type_id", $type_id)
                        ->first()
                        ->toArray();
    }

    public static function updateDataApprove($id, $obj) {

        return TrainingCowBreed::where('id', $id)->update($obj);
    }

    public static function getcomment($years, $months, $region_id) {
        return TrainingCowBreed::select("training_cowbreed.months", "training_cowbreed.user_comment")
                        // ->join('cooperative', 'cooperative.id', '=', 'training_cowbreed.cooperative_id')
                        ->where("training_cowbreed.years", $years)
                        ->where("training_cowbreed.months", $months)
                        // ->where("training_cowbreed.region_id", $region_id)
                        ->get()
                        ->toArray();
    }

}
