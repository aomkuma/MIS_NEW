<?php  

namespace App\Model;
class Food extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'food';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'name'
  								, 'sell_type'
                  , 'actives'
  								, 'create_date'
  								, 'update_date'
  							);
}