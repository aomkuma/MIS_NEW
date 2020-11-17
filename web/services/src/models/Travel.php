<?php  

namespace App\Model;
class Travel extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'travel';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'days'
  								, 'months'
  								, 'years'
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

    public function travelDetail()
    {
        return $this->hasMany('App\Model\TravelDetail', 'travel_id');
    }
  	
}