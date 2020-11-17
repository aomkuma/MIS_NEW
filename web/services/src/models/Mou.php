<?php  

namespace App\Model;
class Mou extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'mou';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'region_id'
  								, 'cooperative_id'
  								, 'years'
  								, 'mou_amount'
                  , 'mou_value'
                  , 'price_per_amount'
                  , 'percent_positive'
                  , 'percent_negative'
  								, 'start_date'
  								, 'end_date'
  								, 'create_date'
  								, 'update_date'
  							);

    protected $casts = [
      'region_id' => 'float',
      'mou_amount' => 'float',
      'mou_value' => 'float',
      'price_per_amount' => 'float',
      'percent_positive' => 'float',
      'percent_negative' => 'float'
    ];

    public function cooperative()
    {
        return $this->hasOne('App\Model\Cooperative', 'id', 'cooperative_id');
    }

    public function mouHistories()
    {
        return $this->hasMany('App\Model\MouHistory', 'mou_id');
    }

    public function mouAvg()
    {
        return $this->hasMany('App\Model\MouAvg', 'mou_id');
    }
  	
}