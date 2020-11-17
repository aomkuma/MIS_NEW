<?php  

namespace App\Model;
class Training extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'training';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'name'
                  , 'actives'
  								, 'create_date'
  								, 'update_date'
  							);
}