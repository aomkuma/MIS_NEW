<?php

namespace App\Service;

use App\Model\MasterLoss;
use App\Model\LossMapping;
use App\Model\ProductMilk;
use App\Model\SubProductMilk;
use App\Model\ProductMilkDetail;


use Illuminate\Database\Capsule\Manager as DB;

class MasterLossService {

    
    public static function checkDuplicateMapping($Data) {
        return LossMapping::where('factory_id', $Data['factory_id'])
                ->where('loss_type', $Data['loss_type'])
                ->where('loss_id', $Data['loss_id'])
                ->where('product_milk_id', $Data['product_milk_id'])
                ->where('loss_id', $Data['loss_id'])
                ->where('product_milk_detail_id', $Data['product_milk_detail_id'])
                ->first();
    }

    public static function getProductMilkList($factory_id = '', $loss_id = '', $menu_type = '') {
        return LossMapping::select("loss_mapping.*", "product_milk.id AS product_milk_id", "name")
                ->join('product_milk', 'product_milk.id', '=', 'loss_mapping.product_milk_id')
                ->where(function($query) use ($factory_id, $loss_id, $menu_type) {
                            if (!empty($factory_id)) {
                                $query->where('loss_mapping.factory_id', $factory_id);
                            }

                            if (!empty($loss_id)) {
                                $query->where('loss_mapping.loss_id', $loss_id);
                            }
                            if (!empty($menu_type)) {
                                $query->where('loss_type', $menu_type);
                            }
                        })
                ->groupBy('loss_mapping.product_milk_id')
                ->groupBy('loss_mapping.loss_id')
                ->groupBy('loss_mapping.loss_type')
                ->get();
    }


    public static function getSubProductMilkList($product_milk_id, $factory_id = '', $loss_id = '', $menu_type = '') {
        return LossMapping::select("loss_mapping.*", "subproduct_milk.id AS subproduct_milk_id" , "product_character", "name")->join('subproduct_milk', 'subproduct_milk.id', '=', 'loss_mapping.subproduct_milk_id')
                ->where('loss_mapping.product_milk_id', $product_milk_id)
                ->where(function($query) use ($factory_id, $loss_id, $menu_type) {
                            if (!empty($factory_id)) {
                                $query->where('loss_mapping.factory_id', $factory_id);
                            }
                            if (!empty($loss_id)) {
                                $query->where('loss_mapping.loss_id', $loss_id);
                            }
                            if (!empty($menu_type)) {
                                $query->where('loss_type', $menu_type);
                            }
                        })
                ->groupBy('loss_mapping.subproduct_milk_id')
                ->groupBy('loss_mapping.loss_id')
                ->groupBy('loss_mapping.loss_type')
                
                ->get();
    }

    
    public static function getProductMilkDetailList($subproduct_milk_id, $factory_id = '', $loss_id = '', $product_milk_id = '', $menu_type = '') {
        return LossMapping::select("loss_mapping.*", 'product_milk_detail.id AS product_milk_detail_id', "name", "number_of_package", "unit", "amount", "amount_unit", "taste")->join('product_milk_detail', 'product_milk_detail.id', '=', 'loss_mapping.product_milk_detail_id')
                ->where('loss_mapping.subproduct_milk_id', $subproduct_milk_id)
                ->where(function($query) use ($factory_id, $loss_id, $product_milk_id, $menu_type) {
                            if (!empty($factory_id)) {
                                $query->where('loss_mapping.factory_id', $factory_id);
                            }
                            if (!empty($loss_id)) {
                                $query->where('loss_mapping.loss_id', $loss_id);
                            }

                            if (!empty($product_milk_id)) {
                                $query->where('loss_mapping.product_milk_id', $product_milk_id);
                            }
                            if (!empty($menu_type)) {
                                $query->where('loss_type', $menu_type);
                            }
                        })
                ->groupBy('loss_mapping.product_milk_detail_id')
                ->groupBy('loss_mapping.loss_id')
                ->groupBy('loss_mapping.loss_type')
                
                ->get();
    }

    
    public static function checkDuplicate($id, $menu_type, $Loss_name) {
        return MasterLoss::where('id', '<>', $id)
                        ->where('name', $Loss_name)
                        ->first();
    }

    public static function loadList($actives = '', $condition = []) {
        return MasterLoss::where(function($query) use ($actives, $condition) {
                            if (!empty($actives)) {
                                $query->where('actives', $actives);
                            }

                            if (!empty($condition['keyword'])) {
                                $query->where('name', 'LIKE', DB::raw("'%". $condition['keyword'] ."%'"));
                            }
                            if (!empty($menu_type)) {
                                $query->where('menu_type', $menu_type);
                            }
                        })
                        ->orderBy("update_date", 'DESC')
                        ->get();
    }

    public static function loadListByName($factory_id, $loss_id = '') {
        return MasterLoss::join('loss_mapping', 'loss_mapping.loss_id', '=', 'master_loss.id')
                        ->where('loss_mapping.factory_id', $factory_id)
                        ->where(function($query) use ($loss_id) {
                            if (!empty($loss_id)) {
                                $query->where('loss_mapping.loss_id', $loss_id);
                                $query->where('master_loss.id', $loss_id);
                            }
                        })
                        ->groupBy('loss_mapping.loss_id')
                        ->orderBy("master_loss.update_date", 'DESC')
                        ->get();
    }

    public static function getMappingList($factory_id, $menu_type = '') {
        return LossMapping::select("loss_mapping.*", 'master_loss.name')
                        ->join('master_loss', 'loss_mapping.loss_id', '=', 'master_loss.id')
                        ->where(function($query) use ($factory_id, $menu_type) {
                            if (!empty($factory_id)) {
                                $query->where('factory_id', $factory_id);
                            }
                            if (!empty($menu_type)) {
                                $query->where('loss_type', $menu_type);
                            }
                        })
                        ->groupBy('loss_mapping.loss_id')
                        ->groupBy('loss_mapping.loss_type')
                        ->orderBy("loss_mapping.id", 'ASC')
                        ->get();
    }

    public static function getMappingListView($factory_id) {
        return LossMapping::select("loss_mapping.*", 'master_loss.name')
                        ->join('master_loss', 'loss_mapping.loss_id', '=', 'master_loss.id')
                        ->where(function($query) use ($factory_id) {
                            if (!empty($factory_id)) {
                                $query->where('factory_id', $factory_id);
                            }
                        })
                        ->groupBy('loss_mapping.loss_id')
                        ->orderBy("loss_mapping.id", 'ASC')
                        ->get();
    }

    public static function getProductMilkListView($factory_id = '', $loss_id = '', $loss_type = '', $product_milk_id = '') {
        return LossMapping::select("loss_mapping.*", "product_milk.id AS product_milk_id", "name")
                ->join('product_milk', 'product_milk.id', '=', 'loss_mapping.product_milk_id')
                ->where(function($query) use ($factory_id, $loss_id, $loss_type, $product_milk_id) {
                            if (!empty($factory_id)) {
                                $query->where('loss_mapping.factory_id', $factory_id);
                            }

                            if (!empty($loss_id)) {
                                $query->where('loss_mapping.loss_id', $loss_id);
                            }

                            if (!empty($loss_type)) {
                                $query->where('loss_mapping.loss_type', $loss_type);
                            }

                            if (!empty($product_milk_id)) {
                                $query->where('loss_mapping.product_milk_id', $product_milk_id);
                            }
                        })
                ->groupBy('loss_mapping.product_milk_id')
                ->get();
    }


    public static function getSubProductMilkListView($product_milk_id, $loss_id = '', $loss_type = '', $subproduct_milk_id = '') {
        return LossMapping::select("loss_mapping.*", "subproduct_milk.id AS subproduct_milk_id" , "product_character", "name")->join('subproduct_milk', 'subproduct_milk.id', '=', 'loss_mapping.subproduct_milk_id')
                ->where('loss_mapping.product_milk_id', $product_milk_id)
                ->where(function($query) use ($loss_type, $subproduct_milk_id, $loss_id) {
                            if (!empty($loss_type)) {
                                $query->where('loss_mapping.loss_type', $loss_type);
                            }
                            if (!empty($subproduct_milk_id)) {
                                $query->where('loss_mapping.subproduct_milk_id', $subproduct_milk_id);
                            }

                            if (!empty($loss_id)) {
                                $query->where('loss_mapping.loss_id', $loss_id);
                            }
                        })
                ->groupBy('loss_mapping.subproduct_milk_id')
                ->get();
    }

    
    public static function getProductMilkDetailListView($subproduct_milk_id, $loss_id = '', $loss_type = '') {
        return LossMapping::select("loss_mapping.*", 'product_milk_detail.id AS product_milk_detail_id', "name", "number_of_package", "unit", "amount", "amount_unit", "taste")->join('product_milk_detail', 'product_milk_detail.id', '=', 'loss_mapping.product_milk_detail_id')
                ->where('loss_mapping.subproduct_milk_id', $subproduct_milk_id)
                ->where(function($query) use ($loss_type, $loss_id) {
                            if (!empty($loss_type)) {
                                $query->where('loss_mapping.loss_type', $loss_type);
                            }
                            if (!empty($loss_id)) {
                                $query->where('loss_mapping.loss_id', $loss_id);
                            }
                        })
                ->groupBy('loss_mapping.product_milk_detail_id')
                ->get();
    }

    public static function getData($id) {
        return MasterLoss::find($id);
    }

    public static function getDataByName($name) {
        return MasterLoss::where('name', $name)->get()->toArray()[0];
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = MasterLoss::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            // print_r($obj);exit;
            $model = MasterLoss::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateMappingData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = LossMapping::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            // print_r($obj);exit;
            $model = LossMapping::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function getMappingData($id) {
        return LossMapping::find($id);
    }

    public static function removeMappingData($id) {
        return LossMapping::find($id)->delete();
    }

    public function getLossType(){
        return MasterLoss::where('actives', 'Y')->get();
    }

}
