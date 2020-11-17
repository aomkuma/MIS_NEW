<?php  

namespace App\Model;
class XxCustPoRmV extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'xxcust_po_rm_v';
  	protected $primaryKey = 'ID';
  	public $timestamps = false;
  	protected $fillable = array('REGION'
  								, 'TRANSACTION_DATE'
  								, 'ITEM_DESCRIPTION'
  								, 'ITEM_CODE'
  								, 'UOM'
  								, 'QUANTITY'
  								, 'AMOUNT'
  							);
  	
}