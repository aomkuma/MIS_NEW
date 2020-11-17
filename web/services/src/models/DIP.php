<?php  

namespace App\Model;
class DIP extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'dip';
  	protected $primaryKey = 'id';
  	public $timestamps = false;
  	protected $fillable = array('id'
  								, 'years'
  								, 'dip_name'
                  				, 'join_date'
                  				, 'dip_status'
                  				, 'pass_date'
  								, 'create_date'
  								, 'update_date'
                  				, 'create_by'
                  				, 'update_by'
  							);
}