<?php  

namespace App\Model;
class AccountPermission extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'account_permission';
  	protected $primaryKey = 'account_permission_id';
  	public $timestamps = false;
  	protected $fillable = array('account_permission_id'
  								, 'UserID'
  								, 'menu_id'
  								, 'actives'
  							);
  	
}