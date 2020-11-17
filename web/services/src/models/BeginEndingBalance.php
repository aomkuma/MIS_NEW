<?php  

namespace App\Model;
class BeginEndingBalance extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'begin_ending_balance';
  	protected $primaryKey = 'id';
  	public $timestamps = false;

  	protected $fillable = array('id'
  								, 'months'
  								, 'years'
                  , 'quarters'
                  , 'fiscal_years'
                  , 'create_date'
  								, 'update_date'
                  
  							);
  	
    public function beginEndingBalanceDetails()
    {
        return $this->hasMany('App\Model\BeginEndingBalanceDetail', 'id', 'begin_ending_balance_id');
    }

}