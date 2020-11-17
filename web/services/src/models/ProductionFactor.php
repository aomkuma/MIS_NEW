<?php  

namespace App\Model;
class ProductionFactor extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'production_factor';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'name'
  								, 'actives'
  								, 'create_date'
  								, 'update_date'
  							);
}