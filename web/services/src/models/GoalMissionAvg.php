<?php  

namespace App\Model;
class GoalMissionAvg extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'goal_mission_avg';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'goal_mission_id'
  								, 'avg_date'
  								, 'amount'
                  , 'addon_amount'
  								, 'price_value'
  							);


  	
}