<?php

namespace App\Service;

use App\Model\AttachFile;
use App\Model\DataRows;
use App\Model\DataSheets;
use App\Model\DataRowsheet3;
use Illuminate\Database\Capsule\Manager as DB;

class AttachFileService {

    public static function searchAttachFile($keyword) {
        return AttachFile::where('file_name', 'LIKE', DB::raw("'%" . $keyword . "%'"))
                        ->orWhere('display_name', 'LIKE', DB::raw("'%" . $keyword . "%'"))
                        ->get();
    }

    public static function getAttachFiles($parent_id, $page_type, $condition = []) {
        return AttachFile::where('parent_id', $parent_id)
                        ->where('page_type', $page_type)
                        ->where(function($query) use ($condition) {
                            if (!empty($condition['keyword'])) {
                                $query->where('file_name', 'LIKE', DB::raw("'%" . $condition['keyword'] . "%'"));
                                $query->orWhere('file_code', 'LIKE', DB::raw("'%" . $condition['keyword'] . "%'"));
                                $query->orWhere('display_name', 'LIKE', DB::raw("'%" . $condition['keyword'] . "%'"));
                            }
                        })
                        ->orderBy('order_no', 'ASC')
                        ->get()->toArray();
    }

    public static function getAttachFilesWithLanguage($parent_id, $page_type, $language, $condition = []) {
        return AttachFile::where('parent_id', $parent_id)
                        ->where('page_type', $page_type)
                        ->where(function($query) use ($condition, $language) {
                            if (!empty($condition['keyword'])) {
                                $query->where('file_name', 'LIKE', DB::raw("'%" . $condition['keyword'] . "%'"));
                                $query->orWhere('file_code', 'LIKE', DB::raw("'%" . $condition['keyword'] . "%'"));
                            }
                            if (!empty($language)) {
                                $query->where('file_language', $language);
                            }
                        })
                        ->orderBy('order_no', 'ASC')
                        ->get()->toArray();
    }

    public static function addAttachFiles($AttachFile) {

        $model = new AttachFile;

        // $model->fill($AttachFile);
        $model->menu_id = $AttachFile['menu_id'];
        $model->display_name = $AttachFile['display_name'];
        $model->parent_id = $AttachFile['parent_id'];
        $model->page_type = $AttachFile['page_type'];
        $model->file_language = $AttachFile['file_language'];
        $model->file_name = $AttachFile['file_name'];
        $model->file_code = $AttachFile['file_code'];
        $model->file_path = $AttachFile['file_path'];
        $model->content_type = $AttachFile['content_type'];
        $model->file_size = $AttachFile['file_size'];
        $model->order_no = $AttachFile['order_no'];
        $model->save();
    }

    public static function updateAttachFiles($AttachFile, $data) {
        $model = AttachFile::where('year', $data['Year'])
                ->where('month', $data['Month'])
                ->where('parent_id', 0)
                ->first();
        if (!empty($model)) {
            $model->parent_id = 1;
            $model->save();
        }
        $date1 = new \DateTime();
        $newmodel = new AttachFile;

        $newmodel->parent_id = 0;
        $newmodel->modify = $date1;
        $newmodel->file_name = $AttachFile['file_name'];

        $newmodel->file_path = $AttachFile['file_path'];

        $newmodel->year = $data['Year'];
        $newmodel->month = $data['Month'];
        $newmodel->date = $data['date'];
        $newmodel->save();
        return $newmodel->id;
    }

    public static function removeAttachFile($id) {
        return AttachFile::find($id)->delete();
    }

    public static function getList() {
        return AttachFile::orderBy("modify", 'DESC')
                        ->get();
    }

    public static function saverow($sheetid, $data, $date, $seq) {
        $row = new DataRows();

        $row->sheet_id = $sheetid;
        $row->seq = $seq;
        $row->positiontype = $data[0];
        $row->department = $data[1];
        $row->director = $data[16];
        $row->lv1 = $data[13];
        $row->lv2 = $data[12];
        $row->lv3 = $data[11];
        $row->lv4 = $data[10];
        $row->lv5 = $data[9];
        $row->lv6 = $data[8];
        $row->lv7 = $data[7];
        $row->lv8 = $data[6];
        $row->lv9 = $data[5];
        $row->lv10 = $data[4];
        $row->outsource = $data[14];
        $row->summary = $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10] + $data[11] + $data[12] + $data[13];

//        $row->summary = $data[14];
        $row->year = $date['Year'];
        $row->month = $date['Month'];
        $row->save();
    }

    public static function saverow2($sheetid, $data, $date, $seq) {
        $row = new DataRows();

        $row->sheet_id = $sheetid;
        $row->positiontype = $data[0];
        $row->department = $data[1];
        $row->seq = $seq;
        $row->lv1 = $data[2];
        $row->lv2 = $data[3];
        $row->lv3 = $data[4];
        $row->lv4 = $data[5];
        $row->lv5 = $data[6];
        $row->lv6 = $data[7];
        $row->lv7 = $data[8];
        $row->lv8 = $data[9];
        $row->lv9 = $data[10];
        $row->lv10 = $data[11];
        $row->year = $date['Year'];
        $row->month = $date['Month'];
        $row->save();
    }

    public static function saverow3($sheetid, $data, $date) {
        $row = new DataRowsheet3();

        $row->sheet_id = $sheetid;
        $row->detail = $data[0];
        $row->nolv = $data[1];

        $row->lv1 = $data[2];
        $row->lv2 = $data[3];
        $row->lv3 = $data[4];
        $row->lv4 = $data[5];
        $row->lv5 = $data[6];
        $row->lv6 = $data[7];
        $row->lv7 = $data[8];
        $row->lv8 = $data[9];
        $row->lv9 = $data[10];
        $row->lv10 = $data[11];
        $row->year = $date['Year'];
        $row->month = $date['Month'];
        $row->summary = $data[2] + $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10] + $data[11];
        $row->save();
    }

    public static function savesheet($attid, $data, $seq) {
        $sheet = new DataSheets();
        $sheet->name = $data;
        $sheet->seq = $seq;
        $sheet->attach_id = $attid;
        $sheet->save();
        return $sheet->id;
    }

}
