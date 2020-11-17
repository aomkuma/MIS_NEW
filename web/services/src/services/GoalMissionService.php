<?php

namespace App\Service;

use App\Model\GoalMission;
use App\Model\GoalMissionAvg;
use App\Model\GoalMissionHistory;
use App\Model\Veterinary;
use App\Model\Mineral;
// use App\Model\Insemination;
use App\Model\Sperm;
use App\Model\SpermSale;
use App\Model\Material;
use App\Model\CowGroup;
use App\Model\Travel;
use App\Model\CowBreed;
use App\Model\TrainingCowBreed;
use App\Model\CooperativeMilk;
use App\Model\CowGroupFather;
use App\Model\SaleChanel;

use Illuminate\Database\Capsule\Manager as DB;

class GoalMissionService {

    public static function getSaleChanel($chanel_name){
        return SaleChanel::where('chanel_name', $chanel_name)->first();
    }

    public static function getGoalMissionByGoalName($menu_type, $goal_name, $factory_id, $years, $chanel_id = '') {
        return GoalMission::select('goal_mission.id',DB::raw("SUM(amount) AS total_amount")
                                , DB::raw("SUM(price_value) AS price_value")
                                , DB::raw("SUM(addon_amount) AS addon_amount")
                                )
                        ->join('master_goal', 'goal_mission.goal_id', '=', 'master_goal.id')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->where('goal_mission.years', $years)
                        ->where('goal_mission.factory_id', $factory_id)
                        ->where('goal_mission.goal_id', trim($goal_name))
                        ->where(function($query) use ($chanel_id) {
                            if (!empty($chanel_id)) {
                                $query->where('sale_chanel', $chanel_id);
                            }
                        })
                        // ->toSql();
                        ->first();
    }

    public static function getGoalMissionYear($menu_type, $years) {
        return GoalMission::select(DB::raw("SUM(amount) AS total_amount")
                                , DB::raw("SUM(price_value) AS price_value")
                                )
                        ->where('years', $years)
                        ->where('menu_type', $menu_type)
                        ->first();
    }

    public static function getMenuTotal($menu_type, $years, $months = '') {
        $res = GoalMission::select(DB::raw("SUM(mis_goal_mission_avg.amount) AS total_amount")
                                , DB::raw("SUM(mis_goal_mission_avg.price_value) AS price_value")
                                )
                        ->join('goal_mission_avg', 'goal_mission_avg.goal_mission_id', '=', 'goal_mission.id')
                        // ->where('years', $years)
                        ->where(function($query) use ($years, $months) {
                            if (!empty($months)) {
                                $query->where('avg_date', $years . '-' . $months. '-01');
                            }
                        })
                        ->where('menu_type', $menu_type)
                        ->first();
        return $res['price_value'];
        // switch($menu_type){
        //     case 'บริการสัตวแพทย์': $res = GoalMissionService::getVeterinaryTotal($menu_type, $years, $months);break;
        //     // case 'ผสมเทียม': $res = GoalMissionService::getVeterinaryTotal($menu_type, $years, $months);break;
        //     case 'แร่ธาตุ พรีมิกซ์ และอาหาร': $res = GoalMissionService::getMineralTotal($menu_type, $years, $months);break;
        //     case 'ผลิตน้ำเชื้อแช่แข็ง': $res = GoalMissionService::getSpermTotal($menu_type, $years, $months);break;
        //     case 'จำหน่ายน้ำเชื้อแช่แข็ง': $res = GoalMissionService::getSpermSaleTotal($menu_type, $years, $months);break;
        //     case 'วัสดุผสมเทียมและอื่นๆ': $res = GoalMissionService::getMaterialTotal($menu_type, $years, $months);break;
        //     case 'ปัจจัยการเลี้ยงโค': $res = GoalMissionService::getCowBreedTotal($menu_type, $years, $months);break;
        //     case 'ฝึกอบรม': $res = GoalMissionService::getTrainingCowBreedTotal($menu_type, $years, $months);break;
        //     case 'ท่องเที่ยว': $res = GoalMissionService::getTraveTotal($menu_type, $years, $months);break;
        //     case 'สหกรณ์และปริมาณน้ำนม': $res = GoalMissionService::getCooperativeMilkTotal($menu_type, $years, $months);break;
        //     case 'ข้อมูลฝูงโค': $res = GoalMissionService::getCowGroupTotal($menu_type, $years, $months);break;
        //     case 'ข้อมูลฝูงโคพ่อพันธุ์': $res = GoalMissionService::getCowGroupFatherTotal($menu_type, $years, $months);break;
        // }

        // return $res;
    }


    public static function getVeterinaryTotal($menu_type, $years, $months = '') {
        $data = Veterinary::select(DB::raw("SUM(item_amount) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("veterinary_item", "veterinary_item.veterinary_id", '=', 'veterinary.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }   

    public static function getMineralTotal($menu_type, $years, $months = '') {
        $data = Mineral::select(DB::raw("SUM(`values`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("mineral_detail", "mineral_detail.mineral_id", '=', 'mineral.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }    

    public static function getSpermTotal($menu_type, $years, $months = '') {
        $data = Sperm::select(DB::raw("SUM(`price`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("sperm_detail", "sperm_detail.sperm_id", '=', 'sperm.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    public static function getSpermSaleTotal($menu_type, $years, $months = '') {
        $data = SpermSale::select(DB::raw("SUM(`values`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("sperm_sale_detail", "sperm_sale_detail.sperm_id", '=', 'sperm_sale.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    public static function getMaterialTotal($menu_type, $years, $months = '') {
        $data = Material::select(DB::raw("SUM(`price`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("material_detail", "material_detail.material_id", '=', 'material.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    public static function getCowBreedTotal($menu_type, $years, $months = '') {
        $data = CowBreed::select(DB::raw("SUM(`price`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("cow_breed_detail", "cow_breed_detail.cow_breed_id", '=', 'cow_breed.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    public static function getTrainingCowBreedTotal($menu_type, $years, $months = '') {
        $data = TrainingCowBreed::select(DB::raw("SUM(`values`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("training_cowbreed_detail", "training_cowbreed_detail.training_cowbreed_id", '=', 'training_cowbreed.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }    

    public static function getTraveTotal($menu_type, $years, $months = '') {
        $data = Travel::select(DB::raw("SUM(`total_price`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("travel_item", "travel_item.travel_id", '=', 'travel_item.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }    

    public static function getCooperativeMilkTotal($menu_type, $years, $months = '') {
        $data = CooperativeMilk::select(DB::raw("SUM(`total_values`) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("cooperative_milk_detail", "cooperative_milk_detail.cooperative_milk_id", '=', 'cooperative_milk.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    public static function getCowGroupTotal($menu_type, $years, $months = '') {
        $data = CowGroup::select(DB::raw("SUM(`go_factory_values` + cow_values + decline_values) AS total_amount")
                                // , "SUM(price_value) AS price_value"
                                )
                        ->join("cow_group_detail", "cow_group_detail.cow_group_id", '=', 'cow_group.id')
                        ->where('years', $years)
                        // ->where('menu_type', $menu_type)
                        ->where(function($query) use ($months) {
                            if (!empty($months)) {
                                $query->where('months', $months);
                            }
                        })
                        ->first();
        return $data['total_amount'];
    }

    

    public static function getList($condition, $UserID, $RegionList) {
        return GoalMission::select("goal_mission.*"
                                    , "region.RegionName"
                                    , "master_goal.goal_name"
                                )
                        ->where(function($query) use ($condition, $RegionList) {
                            if (!empty($condition['Year']['yearText'])) {
                                $query->where('years', $condition['Year']['yearText']);
                            }
                            if (!empty($condition['Region']['RegionID'])) {
                                $query->where('region_id', $condition['Region']['RegionID']);
                            }else{
                                $query->whereIn('region.RegionID', $RegionList);
                            }
                            if (!empty($condition['Goal']['id'])) {
                                $query->where('goal_id', $condition['Goal']['id']);
                            }
                            if (!empty($condition['goal_type'])) {
                                $query->where('goal_mission.goal_type', $condition['goal_type']);
                            }
                            if (!empty($condition['menu_type'])) {
                                $query->where('goal_mission.menu_type', $condition['menu_type']);
                            }
                        })
        
                        ->where(function($query) use ($UserID) {

                            // $query->where('create_by', $UserID);
                            // $query->orWhere('update_by', $UserID);
                            // $query->orWhere('dep_approve_id', $UserID);
                            // $query->orWhere('division_approve_id', $UserID);
                            // $query->orWhere('office_approve_id', $UserID);
                        })
                        // ->whereIn('goal_mission.region_id', $RegionList)
                        ->join('region', 'region.RegionID', '=', 'goal_mission.region_id')
                        ->leftJoin('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->orderBy("update_date", 'DESC')
                        ->get();
    }

    public static function getyearGoal($regid, $year) {
        return GoalMission::where('years', $year)
                        ->where('region_id', $regid)
                        ->get()
                        ->toArray();
    }

    public static function checkDuplicate($id, $years, $goal_id, $region_id, $sale_chanel = null) {
        return GoalMission::where('id', '<>', $id)
                        ->where('years', $years)
                        ->where('goal_id', $goal_id)
                        ->where('region_id', $region_id)
                        ->where(function($query) use ($sale_chanel) {
                            if(!empty($sale_chanel)){
                                $query->where('sale_chanel', $sale_chanel);
                            }
                        })
                        ->first();
    }

    public static function getData($id) {
        return GoalMission::where('id', $id)
                        ->with('goalMissionAvg')
                        ->with('goalMissionHistory')
                        ->first();
    }

    public static function getAvgList($goal_mission_id) {
        return GoalMissionAvg::where('goal_mission_id', $goal_mission_id)
                        ->orderBy('id', 'ASC')
                        ->get()
                        ->toArray();
    }

    public static function getAvgMonth($goal_mission_id, $avgDate) {
        return GoalMissionAvg::where('goal_mission_id', $goal_mission_id)
                        ->where('avg_date', $avgDate)
                        ->first();
    }

    public static function updateData($obj) {
        if (empty($obj['id'])) {
            $obj['create_date'] = date('Y-m-d H:i:s');
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = GoalMission::create($obj);
            return $model->id;
        } else {
            $obj['update_date'] = date('Y-m-d H:i:s');
            $model = GoalMission::find($obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function updateDataEditable($id, $editable) {

        $model = GoalMission::find($id);
        $model->editable = $editable;
        return $model->save();
    }

    public static function updateDataApprove($id, $obj) {

        return GoalMission::where('id', $id)->update($obj);
    }

    public static function updateAvg($obj) {
        if (empty($obj['id'])) {
            $model = GoalMissionAvg::create($obj);
            return $model->id;
        } else {
            $model = GoalMissionAvg::where('id', $obj['id'])->update($obj);
            return $obj['id'];
        }
    }

    public static function addHistory($obj) {

        $obj['change_date'] = date('Y-m-d H:i:s');
        $model = GoalMissionHistory::create($obj);
        return $model->id;
    }

    public static function removeData($id) {
        return AccountRole::find($id)->delete();
    }

    public static function getGoaltravel($goalid, $year) {
        $ckid = null;
        return GoalMission::where('years', $year)
                        ->where('goal_id', $goalid)
                        /*->where('office_approve_id', !$ckid)
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', $ckid);
                            $query->orWhere('office_approve_comment', '');
                        })*/
                        ->get()
                        ->toArray();
    }

    public static function getMission($goalid, $regid, $year) {
        
        $ckid = null;
        return GoalMission::where('years', $year)
                        // ->where('region_id', $regid)
                        ->where('goal_id', $goalid)
                        ->where('unit', '<>', '')
                        ->whereNotNull('unit')
                        
                        /*->where('office_approve_id', !($ckid))
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', ($ckid));
                            $query->orWhere('office_approve_comment', '');
                        })*/
                        ->get()
                        ->toArray();
    }

    public static function getMissionList($goalid, $year) {
        
        $ckid = null;
        $list = GoalMission::where('years', $year)
                        ->whereIn('goal_id', $goalid)
                        ->where('unit', '<>', '')
                        ->whereNotNull('unit')
                        ->get();
        if(!empty($list)){
            $list = $list->toArray();
        }

        return $list;
                        
    }

    public static function getMissionforinsem($goalid, $year) {
        $ckid = null;
        return GoalMission::where('years', $year)
                        ->where('goal_id', $goalid)
                        ->where('office_approve_id', !($ckid))
                        ->where(function($query) use ($ckid) {

                            $query->where('office_approve_comment', ($ckid));
                            $query->orWhere('office_approve_comment', '');
                        })
                        ->join("region", 'goal_mission.region_id', '=', 'region.RegionID')
                        ->get()
                        ->toArray();
    }

    public static function getMissionByMenuType($menu_type, $year) {
        
        $ckid = null;
        return GoalMission::select(DB::raw('SUM(amount) AS amount'))
                        ->where('years', $year)
                        ->where('goal_mission.menu_type', $menu_type)
                        ->where('unit', '<>', '')
                        ->whereNotNull('unit')
                        ->join("master_goal", 'goal_mission.goal_id', '=', 'master_goal.id')
                        ->where("master_goal.actives", 'Y')
                        ->get()
                        ->toArray();


    }

    public static function getMissionavg($goal_mission_id, $year, $month) {
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $date = $year . '-' . $month . '-01';
       
        return GoalMissionAvg::where('goal_mission_id', $goal_mission_id)
                        ->where('avg_date', $date)
                        ->get()
                        ->toArray();
    }

    public static function getMissionavgList($goal_mission_id, $year, $month) {
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $date = $year . '-' . $month . '-01';
       
        $list = GoalMissionAvg::select(DB::raw('SUM(amount) AS amount'))
                        ->whereIn('goal_mission_id', $goal_mission_id)
                        ->where('avg_date', $date)
                        ->get();
                        
        if($list){
            $list = $list->toArray();
        }

        return $list;
    }

    public static function getMissionavgByMenuType($menu_type, $year, $month) {
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $date = $year . '-' . $month . '-01';
       
        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        // ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        // ->where('master_goal.actives', 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        // ->where('master_goal.menu_type', $menu_type)
                        ->where('goal_mission_avg.avg_date', $date)
                        ->join("master_goal", 'goal_mission.goal_id', '=', 'master_goal.id')
                        ->where("master_goal.actives", 'Y')
                        ->first()
                        ->toArray();
    }

    public static function getMissionavgByMenuTypeAndGoalNameKeyword($menu_type, $keyword, $year, $month, $factory_id) {
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $date = $year . '-' . $month . '-01';
       
        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->where('goal_mission_avg.avg_date', $date)
                        ->where('goal_name', 'LIKE', DB::raw("'%" . $keyword . "%'"))
                        ->where("master_goal.actives", 'Y')
                        ->where(function($query) use ($factory_id) {
                            if(!empty($factory_id)){
                                $query->where('goal_mission.factory_id', $factory_id);
                            }
                        })
                        ->first()
                        ->toArray();
    }

    public static function getMissionavgByMenuTypeAndGoalName($menu_type, $goal_name, $year, $month) {
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $date = $year . '-' . $month . '-01';
       
        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->where('goal_mission_avg.avg_date', $date)
                        ->where("master_goal.actives", 'Y')
                        ->where(function($query) use ($goal_name) {

                            if(!empty($goal_name)){
                                $query->where('goal_name', 'LIKE', DB::raw("'%" . $goal_name[0] . "%'"));
                                unset($goal_name[0]);
                                array_values($goal_name);
                                foreach ($goal_name as $value) {
                                    $query->orWhere('goal_name', 'LIKE', DB::raw("'%" . $value . "%'"));
                                }
                            }
                            
                        })
                        ->first()
                        ->toArray();
    }

    public static function getMissionavgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type, $year, $month) {
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $date = $year . '-' . $month . '-01';
       
        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where("master_goal.actives", 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->where('goal_mission_avg.avg_date', $date)
                        ->where(function($query) use ($sub_goal_type) {

                            if(!empty($sub_goal_type)){
                                $query->where('sub_goal_type', 'LIKE', DB::raw("'%" . $sub_goal_type[0] . "%'"));
                                unset($sub_goal_type[0]);
                                array_values($sub_goal_type);
                                foreach ($sub_goal_type as $value) {
                                    $query->orWhere('sub_goal_type', 'LIKE', DB::raw("'%" . $value . "%'"));
                                }
                            }
                            
                        })
                        ->first()
                        ->toArray();
    }

    public static function getMissionavgByMenuTypeAndGoalID($menu_type, $goal_id, $year, $month) {
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $date = $year . '-' . $month . '-01';
       
        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where("master_goal.actives", 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->where('goal_mission_avg.avg_date', $date)
                        ->where(function($query) use ($goal_id) {
                            if(!empty($goal_id)){
                                $query->whereIn('master_goal.id', $goal_id);
                            }
                        })
                        ->first()
                        ->toArray();
    }

    public static function getMissionavgByMenuTypeAndGoalIDNotIn($menu_type, $goal_id, $year, $month) {
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $date = $year . '-' . $month . '-01';
       
        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where("master_goal.actives", 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->where('goal_mission_avg.avg_date', $date)
                        ->where(function($query) use ($goal_id) {
                            if(!empty($goal_id)){
                                $query->whereNotIn('master_goal.id', $goal_id);
                            }
                        })
                        ->first()
                        ->toArray();
    }

    public static function getMissionOctAvgByMenuType($menu_type, $year, $month) {
        $to_year = $year;
        if($month > 9){
            $to_year = $year - 1;
        }
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $start_date = ($year - 1) . '-10-01';
        $to_date = $to_year . '-' . $month . '-01';

        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join("master_goal", 'goal_mission.goal_id', '=', 'master_goal.id')
                        ->where("master_goal.actives", 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->whereBetween('goal_mission_avg.avg_date', [$start_date, $to_date])
                        ->first()
                        ->toArray();
    }

    public static function getMissionOctAvgByMenuTypeAndGoalNameKeyword($menu_type, $keyword, $year, $month, $factory_id) {
        $to_year = $year;
        if($month > 9){
            $to_year = $year - 1;
        }
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $start_date = ($year - 1) . '-10-01';
        $to_date = $to_year . '-' . $month . '-01';

        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where("master_goal.actives", 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->where('goal_name', 'LIKE', DB::raw("'%" . $keyword . "%'"))
                        ->whereBetween('goal_mission_avg.avg_date', [$start_date, $to_date])
                        ->where(function($query) use ($factory_id) {
                            if(!empty($factory_id)){
                                $query->where('goal_mission.factory_id', $factory_id);
                            }
                        })
                        ->first()
                        ->toArray();
    }

    public static function getMissionOctAvgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type, $year, $month) {
        $to_year = $year;
        if($month > 9){
            $to_year = $year - 1;
        }
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $start_date = ($year - 1) . '-10-01';
        $to_date = $to_year . '-' . $month . '-01';

        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where("master_goal.actives", 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->whereBetween('goal_mission_avg.avg_date', [$start_date, $to_date])
                        ->where(function($query) use ($sub_goal_type) {

                            if(!empty($sub_goal_type)){
                                $query->where('sub_goal_type', 'LIKE', DB::raw("'%" . $sub_goal_type[0] . "%'"));
                                unset($sub_goal_type[0]);
                                array_values($sub_goal_type);
                                foreach ($sub_goal_type as $value) {
                                    $query->orWhere('sub_goal_type', 'LIKE', DB::raw("'%" . $value . "%'"));
                                }
                            }
                            
                        })
                        ->first()
                        ->toArray();
    }

    public static function getMissionOctAvgByMenuTypeAndGoalID($menu_type, $goal_id, $year, $month) {
        $to_year = $year;
        if($month > 9){
            $to_year = $year - 1;
        }
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $start_date = ($year - 1) . '-10-01';
        $to_date = $to_year . '-' . $month . '-01';

        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where("master_goal.actives", 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->whereBetween('goal_mission_avg.avg_date', [$start_date, $to_date])
                        ->where(function($query) use ($goal_id) {
                            if(!empty($goal_id)){
                                $query->whereIn('master_goal.id', $goal_id);
                            }
                        })
                        ->first()
                        ->toArray();
    }

    public static function getMissionOctAvgByMenuTypeAndGoalIDNotIn($menu_type, $goal_id, $year, $month) {
        $to_year = $year;
        if($month > 9){
            $to_year = $year - 1;
        }
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $start_date = ($year - 1) . '-10-01';
        $to_date = $to_year . '-' . $month . '-01';

        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where("master_goal.actives", 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->whereBetween('goal_mission_avg.avg_date', [$start_date, $to_date])
                        ->where(function($query) use ($goal_id) {
                            if(!empty($goal_id)){
                                $query->whereNotIn('master_goal.id', $goal_id);
                            }
                        })
                        ->first()
                        ->toArray();
    }

    public static function getMissionOctAvgByMenuTypeAndGoalName($menu_type, $goal_name, $year, $month) {
        $to_year = $year;
        if($month > 9){
            $to_year = $year - 1;
        }
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $start_date = ($year - 1) . '-10-01';
        $to_date = $to_year . '-' . $month . '-01';

        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where("master_goal.actives", 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->whereBetween('goal_mission_avg.avg_date', [$start_date, $to_date])
                        ->where(function($query) use ($goal_name) {

                            if(!empty($goal_name)){
                                $query->where('goal_name', 'LIKE', DB::raw("'%" . $goal_name[0] . "%'"));
                                unset($goal_name[0]);
                                array_values($goal_name);
                                foreach ($goal_name as $value) {
                                    $query->orWhere('goal_name', 'LIKE', DB::raw("'%" . $value . "%'"));
                                }
                            }
                            
                        })
                        ->first()
                        ->toArray();
    }

    public static function getMissionYearByMenuType($menu_type, $year) {
        return GoalMission::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(price_value) AS price")
                        )
                ->where('years', $year)
                ->where('goal_mission.menu_type', $menu_type)
                ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                ->where("master_goal.actives", 'Y')
                ->first()
                ->toArray();
    }

    public static function getMissionYearByMenuTypeAndGoalNameKeyword($menu_type, $keyword, $year, $factory_id) {
        return GoalMission::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(price_value) AS price")
                        )
                ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                ->where("master_goal.actives", 'Y')
                ->where('years', $year)
                ->where('goal_mission.menu_type', $menu_type)
                ->where('goal_name', 'LIKE', DB::raw("'%" . $keyword . "%'"))
                ->where(function($query) use ($factory_id) {
                    if(!empty($factory_id)){
                        $query->where('goal_mission.factory_id', $factory_id);
                    }
                })
                ->first()
                ->toArray();
    }

    public static function getMissionYearByMenuTypeAndSubGoalType($menu_type, $sub_goal_type, $year) {
        return GoalMission::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(price_value) AS price")
                        )
                ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                ->where("master_goal.actives", 'Y')
                ->where('years', $year)
                ->where('goal_mission.menu_type', $menu_type)
                ->where(function($query) use ($sub_goal_type) {

                    if(!empty($sub_goal_type)){
                        $query->where('sub_goal_type', 'LIKE', DB::raw("'%" . $sub_goal_type[0] . "%'"));
                        unset($sub_goal_type[0]);
                        array_values($sub_goal_type);
                        foreach ($sub_goal_type as $value) {
                            $query->orWhere('sub_goal_type', 'LIKE', DB::raw("'%" . $value . "%'"));
                        }
                    }
                    
                })
                ->first()
                ->toArray();
    }

    public static function getMissionYearByMenuTypeAndGoalID($menu_type, $goal_id, $year) {
        return GoalMission::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(price_value) AS price")
                        )
                ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                ->where("master_goal.actives", 'Y')
                ->where('years', $year)
                ->where('goal_mission.menu_type', $menu_type)
                ->where(function($query) use ($goal_id) {
                            if(!empty($goal_id)){
                                $query->whereIn('master_goal.id', $goal_id);
                            }
                        })
                ->first()
                ->toArray();
    }

    public static function getMissionYearByMenuTypeAndGoalIDNotIn($menu_type, $goal_id, $year) {
        return GoalMission::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(price_value) AS price")
                        )
                ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                ->where("master_goal.actives", 'Y')
                ->where('years', $year)
                ->where('goal_mission.menu_type', $menu_type)
                ->where(function($query) use ($goal_id) {
                            if(!empty($goal_id)){
                                $query->whereNotIn('master_goal.id', $goal_id);
                            }
                        })
                ->first()
                ->toArray();
    }

    public static function getMissionYearByMenuTypeAndGoalName($menu_type, $goal_name, $year) {
        return GoalMission::select(DB::raw("SUM(amount) AS amount")
                                , DB::raw("SUM(price_value) AS price")
                        )
                // ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                ->where("master_goal.actives", 'Y')
                ->where('years', $year)
                ->where('goal_mission.menu_type', $menu_type)
                ->where(function($query) use ($goal_name) {

                            if(!empty($goal_name)){
                                $query->where('goal_name', 'LIKE', DB::raw("'%" . $goal_name[0] . "%'"));
                                unset($goal_name[0]);
                                array_values($goal_name);
                                foreach ($goal_name as $value) {
                                    $query->orWhere('goal_name', 'LIKE', DB::raw("'%" . $value . "%'"));
                                }
                            }
                            
                        })
                ->first()
                ->toArray();
    }

    public static function getMissionavgquar($goal_mission_id, $year, $quar) {

        $month = [];
        $years = $year;

        $result = ['amount' => 0, 'price_value' => 0];
        if ($quar == 1) {

            $month = [10, 11, 12];
        } else if ($quar == 2) {
            $month = [1, 2, 3];
        } else if ($quar == 3) {
            $month = [4, 5, 6];
        } else {
            $month = [7, 8, 9];
        }
        foreach ($month as $value) {
            $date = $years . '-' . $value . '-01';
            $missionM = GoalMissionService::getMissionavg($goal_mission_id, $years, $value);

            $result['amount'] += $missionM[0]['amount'];
            $result['price_value'] += $missionM[0]['price_value'];
        }

        return $result;
    }

    public static function getMissionOctAVGSumAmount($menu_type, $year, $month, $sub_goal_type) {
        $to_year = $year;
        if($month > 9){
            $to_year = $year - 1;
        }
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $start_date = ($year - 1) . '-10-01';
        $to_date = $to_year . '-' . $month . '-01';

        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where("master_goal.actives", 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->whereBetween('goal_mission_avg.avg_date', [$start_date, $to_date])
                        ->where(function($query) use ($sub_goal_type) {

                            if(!empty($sub_goal_type)){
                                $query->where('sub_goal_type', 'LIKE', DB::raw("'%" . $sub_goal_type . "%'"));
                                
                            }
                            
                        })
                        ->first()
                        ->toArray();
     
    }


    public static function getMissionBySubGoalType($menu_type, $year, $month, $sub_goal_type) {
        $to_year = $year;
        if($month > 9){
            $to_year = $year - 1;
        }
        if(strlen($month) == 1){
            $month = '0'.$month;
        }
        $date = $to_year . '-' . $month . '-01';

        return GoalMissionAvg::select(DB::raw("SUM(mis_goal_mission_avg.amount + mis_goal_mission_avg.addon_amount) AS amount"), 
                            DB::raw("SUM(mis_goal_mission_avg.price_value) AS price")
                        )
                        ->join('goal_mission', 'goal_mission.id', '=', 'goal_mission_avg.goal_mission_id')
                        ->join('master_goal', 'master_goal.id', '=', 'goal_mission.goal_id')
                        ->where("master_goal.actives", 'Y')
                        ->where('goal_mission.menu_type', $menu_type)
                        ->where('goal_mission_avg.avg_date', $date)
                        ->where(function($query) use ($sub_goal_type) {

                            if(!empty($sub_goal_type)){
                                $query->where('sub_goal_type', 'LIKE', DB::raw("'%" . $sub_goal_type . "%'"));
                                
                            }
                            
                        })
                        ->first()
                        ->toArray();
     
    }

}
