<?php  

namespace App\Model;
class VeterinaryItem extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'veterinary_item';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'veterinary_id'
                  , 'veterinary_detail_id'
  								, 'item_type'
  								, 'item_amount'
  								, 'create_date'
  								, 'update_date'
                  , 'create_by'
                  , 'update_by'
  							);

}