<?php  

namespace App\Model;
class MouHistory extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'mou_history';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
                  , 'mou_id'
  								, 'cooperative_id'
  								, 'years'
  								, 'mou_amount'
  								, 'start_date'
  								, 'end_date'
  								, 'create_date'
  								, 'update_date'
  							);
    
    public function cooperative()
    {
        return $this->hasOne('App\Model\Cooperative', 'id', 'cooperative_id');
    }
  	
}