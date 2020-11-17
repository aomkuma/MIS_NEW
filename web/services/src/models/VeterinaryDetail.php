<?php  

namespace App\Model;
class VeterinaryDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'veterinary_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'veterinary_id'
                  , 'farm_type'
  								, 'dairy_farming_id'
                  , 'sub_dairy_farming_id'
  								, 'create_date'
  								, 'update_date'
                  , 'create_by'
                  , 'update_by'
  							);

    public function dairyFarming()
    {
        return $this->hasOne('App\Model\DairyFarming', 'id', 'dairy_farming_id');
    }

    public function subDairyFarming()
    {
        return $this->hasOne('App\Model\DairyFarming', 'id', 'sub_dairy_farming_id');
    }

    public function veterinaryItem()
    {
        return $this->hasMany('App\Model\veterinaryItem', 'veterinary_detail_id');
    }
  	
}