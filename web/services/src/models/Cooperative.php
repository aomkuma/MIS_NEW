<?php  

namespace App\Model;
class Cooperative extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'cooperative';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'region_id'
  								, 'cooperative_name'
  								, 'actives'
  							);


  	
}