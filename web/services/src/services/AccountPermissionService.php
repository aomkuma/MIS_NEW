<?php
    
    namespace App\Service;
    
    use App\Model\Account;
    use App\Model\AccountPermission;
    use App\Model\AccountRole;

    use Illuminate\Database\Capsule\Manager as DB;
    
    class AccountPermissionService {

    	public static function getAccountPermission($UserID){
            return AccountPermission::where('UserID', $UserID)
                    //->orderBy('menu_id', 'DESC')
            		->get();      
        }

        public static function getAccountRole($UserID){
            return AccountRole::where('UserID', $UserID)
                    ->orderBy('role', 'DESC')
                    ->get();      
        }

        public static function resetAccountPermission($UserID){
            $obj = ['actives' => 'N'];
            AccountPermission::where('UserID', $UserID)->update($obj);
            // $model = AccountPermission::where('UserID', $UserID)->first();
            // if(!empty($model)){
            //     $model->actives = 'N';
            //     $model->save();
            // }
        }

        public static function updateAccountPermission($obj){
            $model = AccountPermission::where('menu_id', $obj['menu_id'])
                    ->where('UserID', $obj['UserID'])
                    ->first();
            if(empty($model)){
                // $model = AccountPermission::create($obj);
                $model = new AccountPermission;
            }else{
                // $model->update($obj);
            }

            $model->UserID = $obj['UserID'];
            $model->menu_id = $obj['menu_id'];
            $model->actives = $obj['actives'];
            $model->save();
            
        }

        public static function resetAccountRole($UserID){
            $obj = ['actives' => 'N'];
            AccountRole::where('UserID', $UserID)->update($obj);
        }
        
        public static function updateAccountRole($obj){
            $model = AccountRole::where('role', $obj['role'])
                    ->where('UserID', $obj['UserID'])
                    ->first();
            if(empty($model)){
                $model = AccountRole::create($obj);
            }else{
                $model->update($obj);
            }
            
            return $model->account_role_id;
        }

        public static function removeAccountPermission($account_permission_id){
            return AccountPermission::find($account_permission_id)->delete();
        }

        public static function removeAccountRole($account_role_id){
            return AccountRole::find($account_role_id)->delete();
        }

    }