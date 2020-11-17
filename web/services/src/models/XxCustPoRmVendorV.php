<?php  

namespace App\Model;
class XxCustPoRmVendorV extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'xxcust_po_rm_vendor_v';
  	protected $primaryKey = 'ID';
  	public $timestamps = false;
  	protected $fillable = array('REGION'
  								, 'VENDOR_NAME'
  								, 'TRANSACTION_DATE'
  								, 'ITEM_DESCRIPTION'
  								, 'ITEM_CODE'
  								, 'UOM'
  								, 'QUANTITY'
  								, 'AMOUNT'
  							);
  	
}