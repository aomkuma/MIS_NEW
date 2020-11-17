<?php
phpinfo();

// error_reporting(E_ERROR);
//                 error_reporting(E_ALL);
//                 ini_set('display_errors','On');

// $db = getConnectionOracle();
                
// echo $sql = "select *
//                     from  xxcust_order_rm_v
//                     where transaction_date between to_date('01-JAN-2019','DD-MON-YYYY') and  trunc(sysdate)";

// // $sql = "select * from tbl_account";
                    

// $stmt = $db->prepare($sql);
// $stmt->execute();
// $result = $stmt->fetchAll(PDO::FETCH_OBJ);
// if (count($result) >= 1) {
//     foreach ($result as $result) {
//         $jsonArray[] = $result;
//     }
// }
// print_r($jsonArray);
// exit;

// function getConnectionOracle() {
//     $DBHost = "10.10.1.10"; //Database Host URL or IP Address
//     $DBOraclePort = "1530"; //DB Oracle Port
//     $DBName = "TEST9"; //if MySQL use Database Name, if Oracle use Oracle System ID (SID)
//     //Connection String
//     //$connectionDB = "mysql:host={$DBHost};dbname={$DBName}";
//     $connectionDB = "oci:dbname=(DESCRIPTION=(ADDRESS=(HOST={$DBHost})(PROTOCOL=tcp)(PORT={$DBOraclePort}))(CONNECT_DATA=(SID={$DBName})))";
//     $DBUser = "XXCUST";
//     $DBPswd = "XXCUST123";
//     $dbh = new PDO($connectionDB, $DBUser, $DBPswd);
//     $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
//     return $dbh;
// }

// function getConnectionMySql() {
//     $DBHost = "localhost"; //Database Host URL or IP Address
//     $DBOraclePort = "3306"; //DB Oracle Port
//     $DBName = "dpo"; //if MySQL use Database Name, if Oracle use Oracle System ID (SID)
//     //Connection String
//     $connectionDB = "mysql:host={$DBHost};dbname={$DBName}";
//     // $connectionDB = "oci:dbname=(DESCRIPTION=(ADDRESS=(HOST={$DBHost})(PROTOCOL=tcp)(PORT={$DBOraclePort}))(CONNECT_DATA=(SID={$DBName})))";
//     $DBUser = "root";
//     $DBPswd = "";
//     $dbh = new PDO($connectionDB, $DBUser, $DBPswd);
//     $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
//     return $dbh;
// }

?>