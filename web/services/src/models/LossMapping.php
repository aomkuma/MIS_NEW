<?php  

namespace App\Model;
class LossMapping extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'loss_mapping';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
								, 'factory_id'
  								, 'loss_type'
  								, 'loss_id'
  								, 'product_milk_id'
  								, 'subproduct_milk_id'
  								, 'product_milk_detail_id'
  								, 'create_by'
  								, 'update_by'
  								, 'create_date'
  								, 'update_date'
  							);
  	
}