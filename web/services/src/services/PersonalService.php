<?php

namespace App\Service;

use App\Model\AttachFile;
use App\Model\DataRows;
use App\Model\DataSheets;
use App\Model\DataRowsheet3;
use Illuminate\Database\Capsule\Manager as DB;

class PersonalService {

    public static function getList($years, $months, $positiontype) {
        return DataRows::where("years", $years)
                        ->where("months", $months)
                        ->where("positiontype", $positiontype)
                        ->orderBy("id", 'ASC')
                        ->get();
    }

    public static function getPositiontype() {
        return DataRows::select(DB::raw("positiontype"))
                        ->groupBy("positiontype")
                        ->orderBy("id", 'ASC')
                        ->get()
                        ->toArray();
    }

    public static function getMainList($years, $months, $positiontype) {
        return DataRows::
                        join("data_sheets", 'data_sheets.id', '=', 'data_rows.sheet_id')
                        ->join("attachfiles", 'attachfiles.id', '=', 'data_sheets.attach_id')
                        ->where("data_rows.year", $years)
                        ->where("data_rows.month", $months)
                        ->where("data_rows.seq", 1)
                        ->where("data_rows.positiontype", $positiontype)
                        ->where("attachfiles.parent_id", 0)
                        ->orderBy("data_rows.id", 'ASC')
                        ->get()
                        ->toArray();
    }

    public static function getMainListsheet3($years, $months) {
        return DataRowsheet3::
                        join("data_sheets", 'data_sheets.id', '=', 'data_rowsheet3.sheet_id')
                        ->join("attachfiles", 'attachfiles.id', '=', 'data_sheets.attach_id')
                        ->where("data_rowsheet3.year", $years)
                        ->where("data_rowsheet3.month", $months)
                        ->where("attachfiles.parent_id", 0)
                        ->orderBy("data_rowsheet3.id", 'ASC')
                        ->get()
                        ->toArray();
    }
     public static function get8($years, $months) {
        return DataRows::select(DB::raw("SUM(mis_data_rows.lv8) AS summary"))
                        ->join("data_sheets", 'data_sheets.id', '=', 'data_rows.sheet_id')
                        ->join("attachfiles", 'attachfiles.id', '=', 'data_sheets.attach_id')
                        ->where("data_rows.year", $years)
                        ->where("data_rows.month", $months)
                        ->where("data_rows.seq", 2)
                        
                        ->where("attachfiles.parent_id", 0)
                        ->orderBy("data_rows.id", 'ASC')
                        ->get()
                        ->toArray();
    }

}
