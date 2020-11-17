<?php  

namespace App\Model;
class TravelItem extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'travel_item';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'travel_id'
  								, 'travel_detail_id'
  								, 'goal_id'
  								, 'total_person_pay'
  								, 'unit_price'
  								, 'discount'
  								, 'total_price'
  								, 'create_date'
  								, 'update_date'
  							);


  	
}