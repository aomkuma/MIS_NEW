<?php

    namespace App\Controller;
    
    use App\Service\ERPService;

    class ERPController extends Controller {
        
        protected $logger;
        protected $db;
        
        public function __construct($logger, $db){
            $this->logger = $logger;
            $this->db = $db;
        }

        public function getLastDayOfMonth($time) {
            return $date = date("t", strtotime($time . '-' . '01'));
        }

        public function getMonthName($month) {
            switch ($month) {
                case 1 : $monthTxt = 'มกราคม';
                    break;
                case 2 : $monthTxt = 'กุมภาพันธ์';
                    break;
                case 3 : $monthTxt = 'มีนาคม';
                    break;
                case 4 : $monthTxt = 'เมษายน';
                    break;
                case 5 : $monthTxt = 'พฤษภาคม';
                    break;
                case 6 : $monthTxt = 'มิถุนายน';
                    break;
                case 7 : $monthTxt = 'กรกฎาคม';
                    break;
                case 8 : $monthTxt = 'สิงหาคม';
                    break;
                case 9 : $monthTxt = 'กันยายน';
                    break;
                case 10 : $monthTxt = 'ตุลาคม';
                    break;
                case 11 : $monthTxt = 'พฤศจิกายน';
                    break;
                case 12 : $monthTxt = 'ธันวาคม';
                    break;
            }
            return $monthTxt;
        }


        public function checkRegion($regionID) {
            switch ($regionID) {
                case '1':
                    return 'จังหวัดสระบุรี';
                    break;
                case '2':
                    return 'กรุงเทพมหานคร';
                    break;
                case '3':
                    return 'ภาคกลาง';
                    break;
                case '4':
                    return 'จังหวัดประจวบคีรีขันธ์';
                    break;
                case '5':
                    return 'จังหวัดขอนแก่น';
                    break;
                case '6':
                    return 'จังหวัดสุโขทัย';
                    break;
                case '7':
                    return 'จังหวัดเชียงใหม่';
                    break;
                default: break;
            }
        }        

        public function getListMBI($request, $response, $args){
            // error_reporting(E_ERROR);
            //     error_reporting(E_ALL);
            //     ini_set('display_errors','On');
            
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $regions = $params['obj']['region'];
                $monthFrom = $params['obj']['condition']['MonthFrom'];
                $monthTo = $params['obj']['condition']['MonthTo'];
                $years = $params['obj']['condition']['YearTo'];

                $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
                $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
                $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28'; // .TravelController::getLastDayOfMonth($ym);
                //exit;
                $fromTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

                $date1 = new \DateTime($toTime);
                $date2 = new \DateTime($fromTime);
                $diff = $date1->diff($date2);
                $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
                if ($diffMonth == 0) {
                    $diffMonth = 1;
                } else {
                    $diffMonth += 1;
                }

                $DataList = [];
                $DataSummary = [];

                $beforYear = intval($years) - 1;
                $curMonth = $monthFrom;

                for ($i = 0; $i < $diffMonth; $i++) {

                    foreach ($regions as $key => $value) {

                        $monthName = ERPController::getMonthName($curMonth);
                        $region = ERPController::checkRegion($value['RegionID']);
                        $region_id = $value['RegionID'];

                        $data = [];
                        $data['RegionName'] = $region;
                        $data['Month'] = $monthName;

                        // Cur year
                        
                        $fromTime = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
                        $ymTo = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
                        $toTime = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-'  . $this->getLastDayOfMonth($ymTo);
                        
                        $Current = ERPService::getListMBI($fromTime, $toTime, $region);
                        
                        $data['CurrentAmount'] = floatval($Current['sum_amount']);
                        $data['CurrentBaht'] = floatval($Current['sum_baht']);

                        // Before year
                        
                        $fromTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
                        $ymTo = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
                        $toTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-' . $this->getLastDayOfMonth($ymTo);

                        $Before = ERPService::getListMBI($fromTime, $toTime, $region);
                        $data['BeforeAmount'] = floatval($Before['sum_amount']);
                        $data['BeforeBaht'] = floatval($Before['sum_baht']);

                        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                        $data['DiffAmount'] = $DiffAmount;

                        if ($data['BeforeAmount'] != 0) {
                            $data['DiffAmountPercentage'] = ($data['CurrentAmount'] / $data['BeforeAmount']) * 100;
                        } else {
                            $data['DiffAmountPercentage'] = 0;
                        }


                        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                        $data['DiffBaht'] = $DiffBaht;

                        if ($data['BeforeBaht'] != 0) {
                            $data['DiffBahtPercentage'] = ($data['CurrentBaht'] / $data['BeforeBaht']) * 100;
                        } else {
                            $data['DiffBahtPercentage'] = 0;
                        }

                        $data['Description'] = ['months' => $curMonth
                            , 'years' => $years
                            , 'region_id' => $region_id
                        ];

                        array_push($DataList, $data);

                        $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                        $DataSummary['SummaryValue'] = $DataSummary['SummaryValue'] + $data['CurrentBaht'];


                    }

                    $curMonth++;

                }

                // exit;
                
                $this->data_result['DATA']['List'] = $DataList;
                $this->data_result['DATA']['Summary'] = $DataSummary;


                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getListMBIDetail($request, $response, $args){
            // error_reporting(E_ERROR);
            //     error_reporting(E_ALL);
            //     ini_set('display_errors','On');
            
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $regions = $params['obj']['region'];
                $years = $params['obj']['condition']['YearTo'];

                $region_list = [];
                foreach ($regions as $key => $value) {
                    $region = ERPController::checkRegion($value['RegionID']);
                    $region_list[] = $region;
                }
                
                $DataList = [];

                $years = $years - 1;
                $beforYear = intval($years) - 1;
                $curMonth = 10;

                $quarter = 1;
                $Q_CurrentAmount = 0;
                $Q_CurrentBaht = 0;
                $Q_BeforeAmount = 0;
                $Q_BeforeBaht = 0;

                $Y_CurrentAmount = 0;
                $Y_CurrentBaht = 0;
                $Y_BeforeAmount = 0;
                $Y_BeforeBaht = 0;

                for ($i = 0; $i < 12; $i++) {

                    if($curMonth > 12){
                        $curMonth = 1;
                        $years += 1;
                        $beforYear += 1;
                    }

                    $monthName = ERPController::getMonthName($curMonth);
                        
                    $data = [];
                    $data['Month'] = $monthName;

                    // Cur year
                    
                    $fromTime = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
                    $ymTo = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
                    $toTime = $years . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-'  . $this->getLastDayOfMonth($ymTo);
                    
                    $Current = ERPService::getListMBIDetail($fromTime, $toTime, $region_list);
                    
                    $data['CurrentAmount'] = floatval($Current['sum_amount']);
                    $data['CurrentBaht'] = floatval($Current['sum_baht']);

                    // Before year
                    
                    $fromTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-01';
                    $ymTo = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT);
                    $toTime = $beforYear . '-' . str_pad($curMonth, 2, "0", STR_PAD_LEFT) . '-' . $this->getLastDayOfMonth($ymTo);

                    $Before = ERPService::getListMBIDetail($fromTime, $toTime, $region_list);
                    $data['BeforeAmount'] = floatval($Before['sum_amount']);
                    $data['BeforeBaht'] = floatval($Before['sum_baht']);

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;

                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = ($data['CurrentAmount'] / $data['BeforeAmount']) * 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = ($data['CurrentBaht'] / $data['BeforeBaht']) * 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }

                    $Q_CurrentAmount += $data['CurrentAmount'];
                    $Q_CurrentBaht += $data['CurrentBaht'];
                    $Q_BeforeAmount += $data['BeforeAmount'];
                    $Q_BeforeBaht += $data['BeforeBaht'];

                    $Y_CurrentAmount += $data['CurrentAmount'];
                    $Y_CurrentBaht += $data['CurrentBaht'];
                    $Y_BeforeAmount += $data['BeforeAmount'];
                    $Y_BeforeBaht += $data['BeforeBaht'];

                    $curMonth++;

                    array_push($DataList, $data);

                    if($i > 0 && ($i+1)%3 == 0){

                        $data = [];
                        $data['Month'] = 'ไตรมาส ' . $quarter;
                        $data['CurrentAmount'] = $Q_CurrentAmount;
                        $data['CurrentBaht'] = $Q_CurrentBaht;
                        $data['BeforeAmount'] = $Q_BeforeAmount;
                        $data['BeforeBaht'] = $Q_BeforeBaht;

                        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                        $data['DiffAmount'] = $DiffAmount;

                        if ($data['BeforeAmount'] != 0) {
                            $data['DiffAmountPercentage'] = ($data['CurrentAmount'] / $data['BeforeAmount']) * 100;
                        } else {
                            $data['DiffAmountPercentage'] = 0;
                        }


                        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                        $data['DiffBaht'] = $DiffBaht;

                        if ($data['BeforeBaht'] != 0) {
                            $data['DiffBahtPercentage'] = ($data['CurrentBaht'] / $data['BeforeBaht']) * 100;
                        } else {
                            $data['DiffBahtPercentage'] = 0;
                        }

                        $Q_CurrentAmount = 0;
                        $Q_CurrentBaht = 0;
                        $Q_BeforeAmount = 0;
                        $Q_BeforeBaht = 0;

                        $data['bg_color'] = '#ccc';

                        $quarter++;

                        array_push($DataList, $data);
                    }

                }

                $data = [];
                $data['Month'] = 'รวม';
                $data['CurrentAmount'] = $Y_CurrentAmount;
                $data['CurrentBaht'] = $Y_CurrentBaht;
                $data['BeforeAmount'] = $Y_BeforeAmount;
                $data['BeforeBaht'] = $Y_BeforeBaht;

                $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                $data['DiffAmount'] = $DiffAmount;

                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] = ($data['CurrentAmount'] / $data['BeforeAmount']) * 100;
                } else {
                    $data['DiffAmountPercentage'] = 0;
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = ($data['CurrentBaht'] / $data['BeforeBaht']) * 100;
                } else {
                    $data['DiffBahtPercentage'] = 0;
                }
                $data['bg_color'] = '#999';
                array_push($DataList, $data);

                // exit;
                
                $this->data_result['DATA']['List'] = $DataList;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }

        public function getList($request, $response, $args){
            error_reporting(E_ERROR);
                error_reporting(E_ALL);
                ini_set('display_errors','On');
            
            try{
                $params = $request->getParsedBody();
                $user_session = $params['user_session'];
                $years = $params['obj']['condition']['Year'];
                $jsonArray = [];

                $db = $this->getConnection();
                
                echo $sql = "select *
                                    from  xxcust_order_rm_v
                                    where transaction_date between to_date('01-JAN-2019','DD-MON-YYYY') and  trunc(sysdate)";
                                    

                $stmt = $db->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_OBJ);
                if (count($result) >= 1) {
                    foreach ($result as $result) {
                        $jsonArray[] = $result;
                    }
                }
                print_r($jsonArray);
                exit;
                $this->data_result['DATA'] = $jsonArray;

                return $this->returnResponse(200, $this->data_result, $response, false);
                
            }catch (\PDOException $e) {
                echo "<pre>";
                print_r($e);
            }catch(\Exception $e){
                return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
            }
        }
        private function getConnection() {
            $DBHost = "10.10.1.10"; //Database Host URL or IP Address
            $DBOraclePort = "1530"; //DB Oracle Port
            $DBName = "TEST9"; //if MySQL use Database Name, if Oracle use Oracle System ID (SID)
            //Connection String
            //$connectionDB = "mysql:host={$DBHost};dbname={$DBName}";
            $connectionDB = "oci:dbname=(DESCRIPTION=(ADDRESS=(HOST={$DBHost})(PROTOCOL=tcp)(PORT={$DBOraclePort}))(CONNECT_DATA=(SID={$DBName})))";
            $DBUser = "XXCUST";
            $DBPswd = "XXCUST123";
            $dbh = new \PDO($connectionDB, $DBUser, $DBPswd);
            $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            return $dbh;
        }
    }

    