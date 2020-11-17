<?php  

namespace App\Model;
class AccountRole extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'account_role';
  	protected $primaryKey = 'account_role_id';
  	public $timestamps = false;
  	protected $fillable = array('account_role_id'
								, 'role'
  								, 'UserID'
  								, 'actives'
  							);
  	
}