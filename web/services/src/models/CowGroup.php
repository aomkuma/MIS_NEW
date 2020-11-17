<?php  

namespace App\Model;
class CowGroup extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'cow_group';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'cow_group_name'
  								, 'cooperative_id'
  								, 'region_id'
  								, 'months'
  								, 'years'
                  , 'go_factory_weight'
                  , 'go_factory_price'
                  , 'go_factory_values'
                  , 'cow_weight'
                  , 'cow_price'
                  , 'cow_values'
                  , 'decline_weight'
                  , 'decline_price'
                  , 'decline_values'
                  , 'yellow_milk_weight'
                  , 'yellow_milk_price'
                  , 'yellow_milk_values'
                  , 'cow_group_avg'
  								, 'create_date'
  								, 'update_date'
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
                  , 'user_comment'
  							);

    public function cowGroupDetail()
    {
        return $this->hasMany('App\Model\CowGroupDetail', 'cow_group_id');
    }
  }