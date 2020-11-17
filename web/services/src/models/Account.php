<?php  

namespace App\Model;
class Account extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'account';
  	protected $primaryKey = 'UserID';
  	public $timestamps = false;
  	protected $fillable = array('UserID'
  								, 'UserType'
  								, 'UserActive'
  								, 'UpdateDateTime'
  							);
}