<?php

namespace App\Service;

use App\Model\UploadLog;

class UploadLogService {

    public static function loadList($menu_type, $data_id) {
        return UploadLog::where('menu_type', $menu_type)
        				->where('data_id', $data_id)
                        ->orderBy('create_date', 'DESC')
                        ->get();
    }

    public static function updateLog($obj){
    	$obj['create_date'] = date('Y-m-d H:i:s');
        $model = UploadLog::create($obj);
        return $model->id;
    }

}
