<?php
include 'connectdb.php';

function http_request($url, $method, $data = [], $optional_headers = null)
{
    $params = array('http' => array(
                'method' => $method,
                'content' => http_build_query($data)
              ));
    if ($optional_headers !== null) {
      $params['http']['header'] = $optional_headers;
    }
    $ctx = stream_context_create($params);
    $fp = @fopen($url, 'rb', false, $ctx);
     if (!$fp) {
      print_r($fp);
          return array("STATUS"=>'ERROR',"MSG"=>"ERROR :: Problem with $url");
      //throw new Exception("Problem with $url, $php_errormsg");
    }
    $response = @stream_get_contents($fp);
    if ($response === false) {
      print_r($response);
          return array("STATUS"=>'ERROR',"MSG"=>"ERROR :: Problem reading data from $url");
//            throw new Exception("Problem reading data from $url");
    }

    return $response; 
}

$output = '';
if(isset($_POST["import"]))
{
 $year=$_REQUEST['year'];
 $month=$_REQUEST['month'];
 $ou=$_REQUEST['ou'];
 
 $query = "Delete From tb_import_data_from_erp where ";
 $query.="ou='".$ou."' ";
 $query.="and month='".$month."' ";
 $query.="and year='".$year."' ;";
 //echo $query;
 mysqli_query($connect, $query);
 
 
 $name_parts = explode(".", $_FILES["excel"]["name"]);
 $extension = end($name_parts); // For getting Extension of selected file

 $allowed_extension = array("xls", "xlsx", "csv"); //allowed extension
 if(in_array($extension, $allowed_extension)) //check selected file extension is present in allowed extension array
 {
  $file = $_FILES["excel"]["tmp_name"]; // getting temporary source of excel file
  include("PHPExcel/IOFactory.php"); // Add PHPExcel Library in this code
  $objPHPExcel = PHPExcel_IOFactory::load($file); // create object of PHPExcel library by using load() method and in load method define path of selected file
$j=1;
  $output .= "<label class='text-success'>ข้อมูลที่นำเข้าล่าสุด</label><br />";
  
  $output .= "<table class='table table-striped'>";
   $output .= '<thead>';
  $output .= '<tr>';
  $output .= '<th>#</th>';
  $output .= '<th>InvoiceNumber</th>';
  $output .= '<th>Supplier</th>';
  $output .= '<th>PONumber</th>';
  $output .= '<th>InventoryItem</th>';
  $output .= '<th>Description</th>';
  $output .= '<th>QuantityInvoiced</th>';
  $output .= '<th>ReceiptNumber</th>';
  $output .= '<th>UOM</th>';
  $output .= '<th>UnitPrice</th>';
  $output .= '<th>Amount</th>';
  $output .= '<th>VATAmount</th>';
  $output .= '<th>GLDate</th>';
  $output .= '<th>Shipto</th>';
  $output .= '<th>สำนักงาน</th>';
  $output .= '<th>เดือน</th>';
  $output .= '<th>ปี</th>';
  $output .= '</tr>';
  $output .= '</thead>';
  $Total_QuantityInvoice=0;
  $Total_Amount=0;

  $APIDataList = [];

  foreach ($objPHPExcel->getWorksheetIterator() as $worksheet)
  {
   $highestRow = $worksheet->getHighestRow();
   $output .= " <tbody>";
   for($row=2; $row<=$highestRow; $row++)
   {
    $output .= "<tr>";
    $InvoiceNumber = mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(0, $row)->getValue());
    $Supplier = mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(1, $row)->getValue());
    $PONumber = mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(2, $row)->getValue());
    $InventoryItem = mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(3, $row)->getValue());
    $Description = mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(4, $row)->getValue());
    $ReceiptNumber = mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(5, $row)->getValue());
    $QuantityInvoiced = mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(6, $row)->getValue());
    $UOM = mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(7, $row)->getValue());
    $UnitPrice = mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(8, $row)->getValue());
    $Amount= mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(9, $row)->getValue());
    $VATAmount= mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(10, $row)->getValue());
    $GLDate= mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(11, $row)->getValue());
    $Shipto= mysqli_real_escape_string($connect, $worksheet->getCellByColumnAndRow(12, $row)->getValue());
    
    //summary
    $Total_QuantityInvoice=$Total_QuantityInvoice+$QuantityInvoiced;
    $Total_Amount=$Total_Amount+$Amount;
    
    $query = "INSERT INTO tb_import_data_from_erp(Invoice_Number, Supplier, PO_Number, Inventory_Item, Description, Receipt_Number, 
                    Quantity_Invoiced, UOM, Unit_Price, Amount, VAT_Amount,GL_Date,Ship_to,ou,month,year) 
                    VALUES ('".$InvoiceNumber."', '".$Supplier."', '".$PONumber."', '".$InventoryItem."', 
                    '".$Description."', '".$ReceiptNumber."', '".$QuantityInvoiced."', '".$UOM."', '".$UnitPrice."',
                    '".$Amount."', '".$VATAmount."', '".$GLDate."', '".$Shipto."',
                        '".$ou."', '".$month."', '".$year."' )";
    mysqli_query($connect, $query);
    $output .= '<td>'.$j.'</td>';
    $output .= '<td>'.$InvoiceNumber.'</td>';
    $output .= '<td>'.$Supplier.'</td>';
    $output .= '<td>'.$PONumber.'</td>';
    $output .= '<td>'.$InventoryItem.'</td>';
    $output .= '<td>'.$Description.'</td>';
    $output .= '<td>'.number_format($QuantityInvoiced,2).'</td>';
    $output .= '<td>'.$ReceiptNumber.'</td>';
    $output .= '<td>'.$UOM.'</td>';
    $output .= '<td>'.$UnitPrice.'</td>';
    $output .= '<td>'.number_format($Amount,2).'</td>';
    $output .= '<td>'.$VATAmount.'</td>';
    $output .= '<td>'.$GLDate.'</td>';
    $output .= '<td>'.$Shipto.'</td>';
    $output .= '<td>'.$ou.'</td>';
    $output .= '<td>'.$month.'</td>';
    $output .= '<td>'.$year.'</td>';
    $output .= '</tr>';
    $j++;

    $data = [];
    $data['Shipto'] = $Shipto;
    $data['Supplier'] = $Supplier;
    $data['GLDate'] = $GLDate;
    $data['Description'] = $Description;
    $data['InventoryItem'] = $InventoryItem;
    $data['UOM'] = $UOM;
    $data['QuantityInvoiced'] = $QuantityInvoiced;
    $data['Amount'] = $Amount;

    $APIDataList[] = $data;

   }
   $output .= " </tbody>";
    $output .= " </tfooter>";
    $output .= '<tr><th colspan="6">รวม</th>';
    $output .= '<th>'.number_format($Total_QuantityInvoice,2).'</th>';
    $output .= '<th></th>';
    $output .= '<th></th>';
    $output .= '<th></th>';
    $output .= '<th>'.number_format($Total_Amount,2).'</th>';
    $output .= '<th></th>';
    $output .= '<th></th>';
    $output .= '<th></th>';
    $output .= '<th></th>';
    $output .= '<th></th>';
    $output .= '<th></th>';
    $output .= '</tr>';
    $output .= " </tfooter>";
  } 
  $output .= '</table>';

  // Call MIS API to update Milk Buy Info
   $url = 'https://mis.dpo.go.th/services/public/mbi/update/';
   $method = 'POST';
   $params = ['mbi_list' => $APIDataList];
   http_request($url, $method, $params);
   
 }
else
 {
  $output = '<label class="text-danger">Invalid File</label>'; //if non excel file then
 }
}
?>

<!-- <html> -->
<!--  <head> -->
<!--   <title>Import Excel To Database Send Data MiS</title> -->
<!--   <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->
<!--   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script> -->
<!--   <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" /> -->
  
<!--  </head>
 <body> -->
 <?php include 'header.php';?>
 <script src="https://code.jquery.com/jquery-3.1.1.js" integrity="sha256-16cdPddA6VdVInumRGo6IbivbERE8p7CQR3HzTBuELA=" crossorigin="anonymous"></script>
<!--     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"> -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  <script type="text/javascript">      	  
    	$(function() {
        $('.progress').hide(); 
         $('form').submit(function(){
        	
       		    });
    	});
    	function addContent(frm) {
    	     //anything you wanna do before you post
    	     var url="index.php";
    	     $('.progress').show(); 
        	 var progress = setInterval(function() {
        		    var $bar = $('.progress-bar');
        		    if ($bar.width()>=450) {
        		        clearInterval(progress);
        		        $('.progress-bar').removeClass('active');
        		        //$('.progress').hide();
        		    } else {
        		        $bar.width($bar.width()+50);
        		    }
        		    $bar.text($bar.width()/5 + "% รอจนกว่าจะโหลดเสร็จ");
        		}, 1);
    	     $.post(
    	            url,
    	            $('#' + frm).serialize(),
    	            function (data) {
    	                result = data;
    	            }
    	          )
    	          .success(function() {
    	            //add your success proccesses
    	        	  $('.progress').hide(); 
    	          })
    	          .complete(function() { 

    	          })
    	          .error(function() {
    	               alert('An error has occurred.');
    	          });      

    	     return false;// this stops the form from actually posting
    	 }
    </script>
 <!-- Jumbotron -->


  <!-- Title -->
  <h2 class="card-title h2">นำเข้าข้อมูลจาก ERP เข้าฐานข้อมูล เพื่อส่งให้ MIS</h2>

  <!-- Subtitle -->
  <p class="indigo-text my-4 font-weight-bold">
  ระบบนี้จัดทำขึ้นเพื่อบันทึกข้อมูลรับซื้อนมดิบที่ได้จากระบบ ERP ในรูปแบบไฟล์ .xls,csv 
  มาบันทึกในรูปแบบฐานข้อมูลและส่งออกข้อมูลในรูปแบบ API JSON เพื่อนำไปใช้ในระบบ MIS
  </p>

  <!-- Grid row -->
  <div class="row d-flex justify-content-center">

    <!-- Grid column -->
    <div class="col-xl-7 pb-2">

      <p class="card-text" >
         การเตรียมข้อมูลก่อนนำมาใช้ในระบบ
             <ol style="text-align: left;">
                 <li>ดาวน์โหลด Template <a href="template_import_excel_data_raw_milk_erp.xlsx" target="_blank">คลิก</a></li>
                 <li>Copy เฉพาะส่วนที่เป็นข้อมูลจากไฟล์ excel ที่ได้จากระบบ ERP หรือ จะตัดเอาส่วนที่ไม่ต้องการออกแทนการ Copy ใส่ใน Template ก็ได้</li>
                 <li>ข้อมูลทุก Column จะต้องเป็นข้อมูลรูปแบบ Text เท่านั้น</li>
             </ol>   
		</p>
		 <p class="card-text" >
         การนำข้อมูลรูปแบบ Json ไปใช้งาน
             <ol style="text-align: left;">
                 <li>ลิงค์ API JSON ดึงทั้งหมด <button class="btn btn-success btn-indigo btn-rounded"><a href="api_json.php" target="_blank">[API JSON]</a> <i class="fas fa-download ml-1"></i></button></li>
                 <li>ลิงค์ API JSON เลือกดึงเฉพาะ OU,MONT,YEAR ตามรูปแบบนี้ https://mis.dpo.go.th/DATA_API_Connect/api_json.php?ou={รหัสหน่วยงาน เช่น 02} &month={เดือน  เช่น 9}&year={ปี พ.ศ. เช่น 2563}</li>
                  <li>ให้ดูรูปแบบ Parameter ที่ใช้ ตามข้อมูล Json ที่ส่งให้ ใน Parameter OU,MONTH,YEAR </li>
             </ol>   
		</p>
    </div>
    <!-- Grid column -->

  </div>
  <!-- Grid row -->

  <hr class="my-4 pb-2">
  <button class="btn btn-info blue-gradient btn-rounded"> <a href="search.php">[ค้นหาข้อมูล]</a> <i class="far fa-gem ml-1"></i></button>
  
  <br><br>
  
  <div class="container">
 <form name="form-upload" id="form-upload" method="post" enctype="multipart/form-data" onSubmit="return addContent('form-upload');">
    <div class="row">
     <div  class="col col-md-3">
    <label>สำนักงาน</label>
    </div>
    <div  class="col col-md-3">
    <select name="ou" class="form-control" required>
    <option value="">-เลือก-</option>
      <option value="02">สำนักงาน อ.ส.ค. ภาคกลาง</option>
       <option value="03">สำนักงาน อ.ส.ค. ภาคใต้</option>
        <option value="04">สำนักงาน อ.ส.ค. ภาคตะวันออกเฉียงเหนือ</option>
         <option value="05">สำนักงาน อ.ส.ค. ภาคเหนือตอนล่าง</option>
          <option value="06">สำนักงาน อ.ส.ค. ภาคเหนือตอนบน</option>
    </select>
    </div>
   <div  class="col col-md-3">
<?php
  echo "<label>เดือน</label>";
  echo "</div>";
  echo "<div  class=\"col col-md-3\">";
echo'<select name="month" class="form-control">';
$var_month = array('มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม');
$m=0;
if(!isset($_REQUEST['month'])){
    $month_select=$_REQUEST['month'];
}else{
     $month_select=date("n");
}
for($i=1;$i<=12;$i++){    
    if(($month_select-$m)==1){ $selected="selected";}else{$selected="";}
    echo"<option value='{$i}' $selected>{$var_month[$m]}</otpion>";
    $m++;
}
echo'</select><br>';
echo "</div>";
echo "<div  class=\"col col-md-3\">";
echo "<label>ปีงบประมาณ</label>";
echo "</div>";
echo "<div  class=\"col col-md-3\">";
echo'<select name="year" class="form-control">>';
$var_y=date("Y")+544;
$ys = $var_y - 5;

for($i=$ys;$i<=$var_y;$i++){
    if($i==date("Y")+543){ $selected="selected";}else{$selected="";}    
    echo"<option value='{$i}' $selected>{$i}</option>";
}
echo'</select>';
?>
</div>
    <div  class="col col-md-3">
    <label>Excel File Upload</label>
    </div>
    <div  class="col col-md-3">
    <input type="file" name="excel" />
    </div>
    <div  class="col col-md-3 offset-md-6">
    <input type="submit" name="import" class="btn btn-danger" value="Import" />
    </div>
    <div  class="col col-md-6 offset-md-3">
     <div class="progress">
    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="13" aria-valuemin="0" aria-valuemax="13" style="width:53.85%">
<!--       <span>80%</span> -->
    </div>
  </div>
        </div>
   </form>
<!--        <a href="template_import_excel_data_raw_milk_erp.xlsx" target="_blank">[TemplateFileUpload]</a> -->
</div>
</div>
<!-- Jumbotron -->
<div class="row">
     <?php
   echo $output;
   ?>
</div>
 <?php include 'footer.php';?>
<!--  </body> -->
<!-- </html> -->