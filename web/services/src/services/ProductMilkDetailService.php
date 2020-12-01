<?php

namespace App\Service;

use App\Model\ProductMilkDetail;
use Illuminate\Database\Capsule\Manager as DB;

class ProductMilkDetailService {

    public static function getAllByFactory($factory_id){

        return ProductMilkDetail::select("product_milk_detail.*", 'product_milk.name as proname', 'subproduct_milk.name as subname', 'factory.factory_name', 'product_character')
                        ->join('subproduct_milk', 'subproduct_milk.id', '=', 'product_milk_detail.sub_product_milk_id')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->join('factory', 'factory.id', '=', 'product_milk.factory_id')
                        ->where('factory_id', $factory_id)
                        ->orderBy("product_milk_detail.id", 'ASC')
                        ->get()
                        ->toArray();

        // return ProductMilkDetail::where('factory_id', $factory_id)->get()->toArray();
    }

    public static function getIDByName($name, $sub_product_milk_id) {
        $res = ProductMilkDetail::where('sub_product_milk_id', $sub_product_milk_id)
                        ->where(DB::raw("CONCAT(name , ' ' , number_of_package , ' ' , unit , ' ' , amount , ' ' , amount_unit , ' ' , taste)"), $name)
                        ->first();
        return empty($res->id)?0:$res->id;
    }

    public static function checkDuplicate($id, $data, $subid, $actives) {
        return ProductMilkDetail::where('product_milk_detail.id', '<>', $id)
                        ->join('subproduct_milk', 'subproduct_milk.id', '=', 'product_milk_detail.sub_product_milk_id')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->where('subproduct_milk.id', $subid)
                        ->where('product_milk_detail.name', $data['name'])
                        ->where('product_milk_detail.taste', $data['taste'])
                        ->where('product_milk_detail.unit', $data['unit'])
                        ->where('product_milk_detail.number_of_package', $data['number_of_package'])
                        ->where('product_milk_detail.amount_unit', $data['amount_unit'])
                        ->where('product_milk_detail.amount', $data['amount'])
                        ->where('product_milk_detail.actives', $actives)
                        // ->where(DB::raw("TRIM(REPLACE(mis_product_milk_detail.name,' ',''))"), trim(str_replace(' ', '', $name)))
                        ->first();
    }

//    public static function getList($actives = '', $menu_type = '', $condition = []) {
//        return ProductMilkDetail::
//                        orderBy("id", 'DESC')
//                        ->get()->toArray();
//    }

    public static function getList($actives = '', $menu_type = '', $condition = []) {
        return ProductMilkDetail::select("product_milk_detail.*", 'product_milk.name as proname', 'subproduct_milk.name as subname', 'factory.factory_name', 'product_character')
                        ->join('subproduct_milk', 'subproduct_milk.id', '=', 'product_milk_detail.sub_product_milk_id')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->join('factory', 'factory.id', '=', 'product_milk.factory_id')
                        ->orderBy("product_milk_detail.id", 'ASC')
                        ->get()
                        ->toArray();
    }

    public static function getListByParent($sub_product_milk_id, $actives = '') {
        return ProductMilkDetail::where('sub_product_milk_id', $sub_product_milk_id)
                        ->where(function($query) use ($actives){
                            if(!empty($actives)){
                                $query->where('product_milk_detail.actives' , $actives);
                            }
                            })
                        ->orderBy("product_milk_detail.id", 'ASC')
                        ->get()
                        ->toArray();
    }

    public static function getListByParent2($sub_product_milk_id,$facid) {
        return ProductMilkDetail::select("product_milk_detail.*", 'product_milk.name as proname', 'subproduct_milk.name as subname')
                        ->join('subproduct_milk', 'subproduct_milk.id', '=', 'product_milk_detail.sub_product_milk_id')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->join('factory', 'factory.id', '=', 'product_milk.factory_id')
                        ->where('sub_product_milk_id', $sub_product_milk_id)
                        // ->where('factory.id', $facid)
                        ->where(function($query) use ($facid){
                            if(!empty($facid)){
                                $query->where('factory.id' , $facid);     
                            }
                            })
                        ->orderBy("product_milk_detail.id", 'ASC')
                        ->get()
                        ->toArray();
    }

    public static function getData($id) {
        return ProductMilkDetail::select("product_milk_detail.*", 'product_milk.name as proname', 'subproduct_milk.name as subname', 'subproduct_milk.id as subid', 'subproduct_milk.product_milk_id', 'factory_name')
                        ->join('subproduct_milk', 'subproduct_milk.id', '=', 'product_milk_detail.sub_product_milk_id')
                        ->join('product_milk', 'product_milk.id', '=', 'subproduct_milk.product_milk_id')
                        ->join('factory', 'factory.id', '=', 'product_milk.factory_id')
                        ->where('product_milk_detail.id', $id)
                        ->first();
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = ProductMilkDetail::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = ProductMilkDetail::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function removeData($id) {
        return AccountRole::find($id)->delete();
    }

}
