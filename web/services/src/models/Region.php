<?php  

namespace App\Model;
class Region extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'region';
  	protected $primaryKey = 'RegionID';
  	public $timestamps = false;
  	protected $fillable = array('RegionID'
  								, 'RegionName'
  								);
  	
}