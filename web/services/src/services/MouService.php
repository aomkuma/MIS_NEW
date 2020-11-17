<?php

namespace App\Service;

use App\Model\Mou;
use App\Model\MouAvg;
use App\Model\MouHistory;
use App\Model\Cooperative;
use Illuminate\Database\Capsule\Manager as DB;

class MouService {

    public static function getRegionList($condition = []) {
        return Cooperative::select('region_id')
                        ->where(function($query) use ($condition){
                            if (!empty($condition['region_id'])) {
                                $query->where('region_id', $condition['region_id']);
                            }
                        })
                        ->groupBy('region_id')
                        ->orderBy("region_id", 'ASC')
                        ->get()->toArray();
    }

    public static function getList($region_id, $actives = '', $condition = []) {
        return Mou::select('mou.*', 'cooperative.cooperative_name')
                        ->where(function($query) use ($actives) {
                            if (!empty($actives)) {
                                $query->where('actives', $actives);
                            }
                        })
                        ->where(function($query) use ($condition){
                            if (!empty($condition['years'])) {
                                $query->where('years', $condition['years']);
                            }
                            if (!empty($condition['cooperative_id'])) {
                                $query->where('cooperative_id', $condition['cooperative_id']);
                            }
                        })
                        ->where('mou.region_id', $region_id)
                        ->join('cooperative', 'cooperative.id', '=', 'mou.cooperative_id')
                        // ->with('cooperative')
                        ->orderBy("update_date", 'DESC')
                        ->get();
    }

    public static function getData($id) {
        return Mou::where('id', $id)
                        //->with('mouHistories')
                        ->with(array('mouAvg' => function($query) {
                                $query->orderBy('avg_date', 'ASC');
                            }))
                        ->with(array('mouHistories' => function($query) {
                                $query->orderBy('update_date', 'DESC');
                                $query->with('cooperative');
                            }))
                        ->first()->toArray();
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Mou::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = Mou::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateAvgData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = MouAvg::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = MouAvg::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function addHistoryData($obj) {
        $obj['create_date'] = date('Y-m-d H:i:s');
        $obj['update_date'] = date('Y-m-d H:i:s');
        $model = MouHistory::create($obj);
        return $model->id;
    }

    public static function removeData($id) {
        return Mou::find($id)->delete();
    }

    public static function getMission($years, $months) {
        $st = $years . '-' . $months . '-01';
        $en = $years . '-' . $months . '-31';

        return Mou::select(DB::raw("SUM(mou_amount) AS amount"))
                        ->whereBetween('start_date', [$st, $en])
                        ->whereBetween('end_date', [$st, $en])
                        ->first()
                        ->toArray();
    }

    public static function getMissionyear($years) {
        $st = $years - 1 . '-10-01';
        $en = $years . '-9-31';

        return Mou::select(DB::raw("SUM(mou_amount) AS amount"))
                        ->whereBetween('start_date', [$st, $en])
                        ->whereBetween('end_date', [$st, $en])
                        ->first()
                        ->toArray();
    }

}
