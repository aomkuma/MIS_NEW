<?php

namespace App\Service;

use App\Model\SubProductMilk;
use Illuminate\Database\Capsule\Manager as DB;

class SubProductMilkService {

    public static function getIDByName($name, $product_milk_id) {
        $res = SubProductMilk::where('product_milk_id', $product_milk_id)
                        ->where(DB::raw("CONCAT(product_character, ' ', name)"), $name)
                        ->first();
        return empty($res->id)?0:$res->id;
    }

    public static function checkDuplicate($subid, $data, $id) {
        return SubProductMilk::where('subproduct_milk.id', '<>', $subid)
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->join('factory', 'factory.id', '=', 'product_milk.factory_id')
                        // ->where(DB::raw("TRIM(REPLACE(mis_subproduct_milk.name,' ',''))"), trim(str_replace(' ', '', $name)))
                        ->where('subproduct_milk.product_milk_id', $id)
                        ->where('subproduct_milk.product_character', $data['product_character'])
                        ->where('subproduct_milk.name', $data['name'])
                        ->first();
    }

    public static function getList($actives = '', $menu_type = '', $condition = []) {
        return SubProductMilk::select("subproduct_milk.*", 'product_milk.name as proname', 'factory.factory_name')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->join('factory', 'factory.id', '=', 'product_milk.factory_id')
                        ->orderBy("subproduct_milk.id", 'DESC')
                        ->get()->toArray();
    }

    public static function getListByProductMilk($product_milk_id, $actives = '') {
        return SubProductMilk::select("subproduct_milk.*", 'product_milk.name as proname')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->where('product_milk_id', $product_milk_id)
                        ->where(function($query) use ($actives){
                            if(!empty($actives)){
                                $query->where('subproduct_milk.actives' , $actives);
                            }
                            })
                        ->orderBy("subproduct_milk.id", 'ASC')
                        ->get()->toArray();
    }

    public static function getData($id) {
        return SubProductMilk::select("subproduct_milk.id as subid", 'product_milk.name as proname', 'subproduct_milk.name as subname', 'product_milk.id as proid', "subproduct_milk.product_character", "factory.factory_name", "factory.id as fac_id")
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->join('factory', 'factory.id', '=', 'product_milk.factory_id')
                        ->where('subproduct_milk.id', $id)
                        ->first();
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SubProductMilk::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = SubProductMilk::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeData($id) {
        return AccountRole::find($id)->delete();
    }

}
