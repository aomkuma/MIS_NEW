<?php  

namespace App\Model;
class Factory extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'factory';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'region_id'
  								, 'factory_name'
  							);
  	
}