<?php  

namespace App\Model;
class LostInProcessValue extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'lost_in_process_value';
  	protected $primaryKey = 'id';
  	public $timestamps = false;

    protected $casts = [
      'values' => 'float'
    ];

  	protected $fillable = array('id'
  								, 'factory_id'
  								, 'months'
  								, 'years'
                  , 'quarters'
                  , 'fiscal_years'
                  , 'values'
  								, 'create_date'
  								, 'update_date'
                  
  							);
  	
    public function factory()
    {
        return $this->hasOne('App\Model\Factory', 'id', 'factory_id');
    }

}