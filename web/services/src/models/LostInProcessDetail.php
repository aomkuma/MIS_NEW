<?php  

namespace App\Model;
class LostInProcessDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'lost_in_process_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'lost_in_process_id'
  								, 'lost_in_process_type'
                  , 'package_amount'
  								, 'amount'
  								, 'price_value'
                  , 'create_date'
                  , 'update_date'
  							);
  }