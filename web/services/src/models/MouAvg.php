<?php  

namespace App\Model;
class MouAvg extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'mou_avg';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'mou_id'
  								, 'avg_date'
  								, 'amount'
  							);

    protected $casts = [
      'amount' => 'float'
    ];
  	
}