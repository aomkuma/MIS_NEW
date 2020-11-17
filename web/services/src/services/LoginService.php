<?php
    
    namespace App\Service;
    
    use App\Model\Account;
    use App\Model\Admin;
    use App\Model\AccountRole;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class LoginService {
        
        public static function checkPermission($UserID){
            return AccountRole::where('UserID', $UserID)->where('actives', 'Y')->first();      
        }

        public static function authenticate($username , $password){
            return Account::where('Username', $username)->where('Password',$password)->first();      
        }

        public static function findWithIDCard($username , $password){
            return Attendee::where('IDCard', $username)->first();      
        }

        public static function checkDuplicateName($obj){
            return Attendee::where('Firstname', trim($obj['Firstname']))->where('Lastname', trim($obj['Lastname']))->first();      
        }

        public static function authenticateAdmin($username , $password){
            return Admin::where('Username', $username)->where('Password',$password)->first();      
        }

        public static function checkDuplicateIDCard($IDCard){
            return Attendee::where('IDCard', trim($IDCard))->first();      
        }

        public static function checkDuplicateMobile($Mobile){
            return Attendee::where('Mobile', trim($Mobile))->first();      
        }

        public static function registerMember($obj){
            $obj['Password'] = rand(10000000, 99999999);
            //$obj['WifiPassword'] = rand(10000000, 99999999);
            $obj['CreateDate'] = date('Y-m-d H:i:s');
            $obj['Birthdate'] = $obj['YearOfBirth'] . '-' . $obj['MonthOfBirth']. '-' . $obj['DateOfBirth'];
            // echo $obj['Birthdate'];exit;
            $attendee = new Attendee;
            $attendee->Password = $obj['Password'];
            // $attendee->Wifi = $obj['Wifi'];
            $attendee->Firstname = $obj['Firstname'];
            $attendee->Lastname = $obj['Lastname'];
            $attendee->IDCard = (empty($obj['IDCard'])?' ':$obj['IDCard']);
            $attendee->Email = $obj['Email'];
            $attendee->Gender = $obj['Gender'];
            $attendee->Birthdate = $obj['Birthdate'];
            $attendee->Mobile = $obj['Mobile'];
            $attendee->AddressNo = $obj['AddressNo'];
            $attendee->Moo = $obj['Moo'];
            $attendee->Street = $obj['Street'];
            $attendee->Soi = $obj['Soi'];
            $attendee->Province = $obj['Province'];
            $attendee->SubProvince = $obj['SubProvince'];
            $attendee->District = $obj['District'];
            $attendee->Postcode = $obj['Postcode'];
            $attendee->CreateDate = $obj['CreateDate'];
            if($obj['RegisterType'] != ''){
                $attendee->RegisterType = $obj['RegisterType'];
            }
            $attendee->save($obj);
            return $attendee;      
        }

        public static function saveFrontRegister($obj){
            $obj['Password'] = rand(10000000, 99999999);
            $obj['CreateDate'] = date('Y-m-d H:i:s');
            // echo $obj['Birthdate'];exit;
            $attendee = new Attendee;
            $attendee->Password = $obj['Password'];
            $attendee->Firstname = $obj['Firstname'];
            $attendee->Lastname = $obj['Lastname'];
            $attendee->Mobile = $obj['Mobile'];
            $attendee->Email = $obj['Email'];
            $attendee->IDCard = (empty($obj['IDCard'])?' ':$obj['IDCard']);
            $attendee->RegisterType = 'MANUAL';
            $attendee->CreateDate = $obj['CreateDate'];
            $attendee->save($obj);

            $new_attendee = Attendee::find($attendee->UserID);
            return $new_attendee;      
        }        

        public static function getMenuList($GroupID){
            return Permission::where("GroupID" , "==", $GroupID)->get();
        }

        public static function checkDuplicateAdmin($Username){  
            $model = Admin::where('Username', $Username)->first();
            return !(empty($model));
        }

        public static function saveAdmin($obj){
            $model = new Admin;
            $model->AdminType = 'NORMAL';
            $model->Firstname = $obj['Firstname'];
            $model->Lastname = $obj['Lastname'];
            $model->Username = $obj['Username'];
            $model->Password = $obj['Password'];
            $model->CreateDate = date('Y-m-d H:i:s');
            return $model->save();
        }
        
    }

?>