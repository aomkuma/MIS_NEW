<?php  

namespace App\Model;
class Mineral extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'mineral';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'cooperative_id'
  								, 'region_id'
  								, 'months'
  								, 'years'
  								, 'create_date'
  								, 'update_date'
                  , 'dep_approve_id'
                  , 'dep_approve_date'
                  , 'dep_approve_comment'
                  , 'division_approve_id'
                  , 'division_approve_date'
                  , 'division_approve_comment'
                  , 'office_approve_id'
                  , 'office_approve_date'
                  , 'office_approve_comment'
                  , 'dep_approve_name'
                  , 'division_approve_name'
                  , 'office_approve_name'
                  , 'user_comment'
  							);

    public function mineralDetail()
    {
        return $this->hasMany('App\Model\MineralDetail', 'mineral_id');
    }
  }