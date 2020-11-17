<?php  

namespace App\Model;
class DairyFarming extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'dairy_farming';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'dairy_farming_type'
  								, 'dairy_farming_name'
  								, 'parent_id'
  								, 'actives'
  								, 'create_date'
  								, 'update_date'
  							);

    public function dairyFarming()
    {
        return $this->hasOne('App\Model\DairyFarming', 'id', 'parent_id');
    }

    public function dairyFarmings()
    {
        return $this->hasMany('App\Model\DairyFarming', 'parent_id');
    }
  	
}