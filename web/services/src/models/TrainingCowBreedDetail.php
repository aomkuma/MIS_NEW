<?php  

namespace App\Model;
class TrainingCowBreedDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'training_cowbreed_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'training_cowbreed_id'
                  , 'training_cowbreed_type_id'
  								, 'amount'
                  , 'values'
                  , 'create_date'
                  , 'update_date'
  							);
  }