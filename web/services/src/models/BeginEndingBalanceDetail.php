<?php  

namespace App\Model;
class BeginEndingBalanceDetail extends \Illuminate\Database\Eloquent\Model {  

  	protected $table = 'begin_ending_balance_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;

    protected $casts = [
      'begin_amount' => 'float',
      'begin_price' => 'float',
      'ending_amount' => 'float',
      'ending_price' => 'float'
    ];


  	protected $fillable = array('id'
                  , 'begin_ending_balance_id'
  								, 'factory_id'
                  , 'milk_type'
  								, 'begin_amount'
                  , 'begin_price'
                  , 'ending_amount'
                  , 'ending_price'
                  , 'create_date'
  								, 'update_date'
                  
  							);
  	
    public function factory()
    {
        return $this->hasOne('App\Model\Factory', 'id', 'factory_id');
    }

}