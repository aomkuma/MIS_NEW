<?php  

namespace App\Model;
class GoalMissionHistory extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'goal_mission_history';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'goal_mission_id'
  								, 'avg_date'
  								, 'amount'
                  , 'addon_amount'
  								, 'price_value'
                  , 'edit_name'
                  , 'unlock_name'
                  , 'change_date'
                  , 'remark'
  							);


  	
}