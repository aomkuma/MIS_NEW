<?php  

namespace App\Model;
class LostOutProcessDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'lost_out_process_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'lost_out_process_id'
  								, 'lost_out_process_type'
                  , 'package_amount'
  								, 'amount'
  								, 'price_value'
                  , 'create_date'
                  , 'update_date'
  							);
  }