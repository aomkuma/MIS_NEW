<?php 
echo header('Content-Type: application/json');
include 'connectdb.php';

$query = "Select * From tb_import_data_from_erp 
where Inventory_Item = 'RM01010010'";
if(isset($_REQUEST['ou']) && isset($_REQUEST['month']) && isset($_REQUEST['year'])){
    $query .=  " and ou='".$_REQUEST['ou']."' and month='".$_REQUEST['month']."' and year='".$_REQUEST['year']."'";
}
$query .=" order by ID asc";
//$result=mysqli_query($connect, $query);

//Invoice_Number, Supplier, PO_Number, Inventory_Item, Description, Receipt_Number,
//Quantity_Invoiced, UOM, Unit_Price, Amount, VAT_Amount,GL_Date,Ship_to,ou,month,year
//$rs = mysqli_fetch_array( $result );
//print_r($result);
//foreach ($result as $val){
$a=0;
$return_arr = array();
//count(mysqli_num_rows($result));
if ($result=mysqli_query($connect, $query))
{
    // Return the number of rows in result set
    $rowcount=mysqli_num_rows($result);
   // echo $rowcount;
    $province=array("02"=>"สระบุรี","03"=>"ประจวบคีรีขันธ์","04"=>"ขอนแก่น","05"=>"สุโขทัย","06"=>"เชียงใหม่");
while($rs = mysqli_fetch_array( $result ))
{
    
    $data=array(
        'REGION'=>$province[$rs['ou']],
    'TRANSACTION_DATE'=>$rs['GL_Date'],
    'ITEM_DESCRIPTION'=>$rs['Description'],
     'ITEM_CODE'=>$rs['Inventory_Item'],
    'UOM'=>$rs['UOM'],
    'VENDOR_NAME'=>$rs['Supplier'],
    'VENDER_ID'=>'null',
    'QUANTITY'=>$rs['Quantity_Invoiced'],
    'AMOUNT'=>$rs['Amount'],
    'ORG_ID'=>'null',
    'OU'=>$rs['ou'],
    'MONT'=>$rs['month'],
    'YEAR'=>$rs['year']
    );
    array_push($return_arr,$data);
    /*
    $data['REGION']=$rs['ou'];
    $data['TRANSACTION_DATE']=$rs['GL_Date'];
    $data['ITEM_DESCRIPTION']=$rs['Description'];
     $data['ITEM_CODE']=$rs['Inventory_Item'];
    $data['UOM']=$rs['UOM'];
    
    
   // $data['']=$val->Invoice_Number;
    $data['VENDOR_NAME']=$rs['Supplier'];
    $data['VENDER_ID']='null';
    $data['QUANTITY']=$rs['Quantity_Invoiced'];
    $data['AMOUNT']=$rs['Amount'];
    $data['ORG_ID']='null';
    $data['OU']=$rs['ou'];
    $data['MONT']=$rs['month'];
    $data['YEAR']=$rs['year'];
    /*
     $data['']=$val->PO_Number;
    $data['']=$val->Receipt_Number;
    $data['']=$val->Unit_Price;
    $data['']=$val->VAT_Amount;
    $data['']=$val->Ship_to;
    $data['']=$val->ou;
    $data['']=$val->month;
    $data['']=$val->year;*/ 
    $a++;
}
mysqli_close($connect);
}
$json=json_encode($return_arr,true);

echo $json;
?>