<?php  

namespace App\Model;
class MineralDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'mineral_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'mineral_id'
                  , 'food_id'
  								, 'amount'
                  , 'values'
                  , 'create_date'
                  , 'update_date'
  							);
  }