<?php  

namespace App\Model;
class CowBreedDetail extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'cow_breed_detail';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'cow_breed_id'
                  , 'cow_breed_type_id'
  								, 'amount'
                  , 'amount_unit'
                  , 'price'
                  , 'create_date'
                  , 'update_date'
  							);
  }