<?php  

namespace App\Model;
class GoalMission extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'goal_mission';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'region_id'
                  , 'factory_id'
  								, 'years'
                  , 'goal_type'
                  , 'menu_type'
                  , 'sale_chanel'
  								, 'goal_id'
  								, 'amount'
  								, 'unit'
                  , 'addon_amount'
                  , 'addon_unit'
  								, 'price_value'
  								, 'editable'
                  , 'dep_approve_id'
                  , 'dep_approve_date'
                  , 'dep_approve_comment'
                  , 'division_approve_id'
                  , 'division_approve_date'
                  , 'division_approve_comment'
                  , 'office_approve_id'
                  , 'office_approve_date'
                  , 'office_approve_comment'
                  , 'dep_approve_name'
                  , 'division_approve_name'
                  , 'office_approve_name'
  								, 'create_date'
  								, 'update_date'
  								, 'create_by'
  								, 'update_by'
  							);

  	public function goalMissionAvg()
    {
  		return $this->hasMany('App\Model\GoalMissionAvg', 'goal_mission_id');
    }

    public function goalMissionHistory()
    {
  		return $this->hasMany('App\Model\GoalMissionHistory', 'goal_mission_id');
    }

  	
}