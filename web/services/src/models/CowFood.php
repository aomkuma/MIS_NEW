<?php  

namespace App\Model;
class CowFood extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'cow_food';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'name'
                  , 'actives'
  								, 'create_date'
  								, 'update_date'
  							);
}