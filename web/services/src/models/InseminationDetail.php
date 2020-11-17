<?php  

namespace App\Model;
class InseminationDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'insemination_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'insemination_id'
  								, 'region_id'
                  , 'cow_amount'
                  , 'service_cost'
                  , 'sperm_cost'
  								, 'material_cost'
  								, 'create_date'
  								, 'update_date'
  							);
  }