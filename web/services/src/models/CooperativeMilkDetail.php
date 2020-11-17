<?php  

namespace App\Model;
class CooperativeMilkDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'cooperative_milk_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'cooperative_milk_id'
  								, 'cooperative_id'
                  , 'member_id'
                  , 'total_person'
                  , 'total_person_sent'
                  , 'total_cow'
                  , 'total_cow_beeb'
                  , 'milk_amount'
                  , 'total_values'
                  , 'average_values'
                  , 'stop_send_milk'
                  , 'new_member'
                  , 'leave_member'
                  , 'cow_pregnant'
                  , 'cow_none_pregnant'
                  , 'cattle_stop_rolling'
                  , 'cow_child'
                  , 'cow_women'
                  , 'cow_man'
  								, 'create_date'
  								, 'update_date'
  							);
}