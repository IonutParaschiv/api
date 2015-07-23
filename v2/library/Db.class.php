<?php
class Db{

    const DATABASE_NAME = "bachelor";
    const SHARED_SECRET = "jaxierulestheblock";
/**
 * this function connects to the database
 * @return [type] [description]
 */
    private function conn(){
        $dbun = "root";
        $dbpw = "";
        $host = "localhost";
        $db = self::DATABASE_NAME;

        $conn = new PDO('mysql:host='.$host.';dbname='.$db,$dbun,$dbpw);
        // Check connection
        if (!$conn) {
          echo "Failed to connect to MySQL: " . mysqli_connect_error();
          exit();
        }
        return $conn;
    }

/**
 * gets everything from specified table
 * @param  [string] $subject table to be queried
 * @return [object]          
 */
    public function getAll($subject){
        $conn = self::conn();
        $query = "SELECT * FROM ".$subject;
        $stmt = $conn->prepare($query);
        $result = $stmt->execute();
        if($result){
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $row = array("error" =>"Could not process request");
        }
        return $row;
    }

/**
 * This function creates a new account
 * @param  [array] $params parameters to be used in the account creation
 * @return [object]         in case of success , returns account object
 */
    public function createAccount($params, $master = false, $shared_secret = '', $apiKey = ''){
        if( empty($params->email) || empty($params->password) ){
            return array("status"=>400, "message"=>"missing parameter");
        }else{

            #first, check if email already exists
            if(self::emailExsits($params->email)){
                $response = new stdClass();
                $response->status = 400;
                $response->message = 'Email exists';
                return $response;
            }

            $conn = self::conn();
            $table = 'account';
            if($master == true && $shared_secret = self::SHARED_SECRET){
                $table = 'master_account';
            }
            $query =    "INSERT INTO " .self::DATABASE_NAME. ".".$table." ( id ,  name ,  surname ,  email ,  password ,  join_date , salt, token, apiKey, parent, master) 
                        VALUES ('', :name, :surname, :email, :password, NOW(), :salt, :token, :apiKey, :parent, :master)";
            $stmt = $conn->prepare($query);

            $apiKey = Security::hashString($params->name.$params->email.time());
            $passComponents = Security::saltPassword($params->password);


            $parent = 1;
            $stmt->bindParam(':name', $params->name);
            $stmt->bindParam(':surname', $params->surname);
            $stmt->bindParam(':email', $params->email);
            $stmt->bindParam(':password',$passComponents['password']);
            $stmt->bindParam(':apiKey', $apiKey);
            $stmt->bindParam(':salt', $passComponents['salt']);
            $stmt->bindParam(':token', $apiKey);
            $stmt->bindParam(':parent', $parent);
            $stmt->bindParam(':master', $master);
            $result = $stmt->execute();

            if($result){
                return self::getLatest('account');
            }else{
                return "The account could not be created";
            }
        }
    }
/**
 * This functions gets a single account
 * @param  [type] $accId [description]
 * @return [type]        [description]
 */
    public function getAccount($accId){
        $conn = self::conn();
        $query = "SELECT id, name, surname, email, join_date, apiKey FROM ".self::DATABASE_NAME.".account WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id", $accId);

        $result = $stmt->execute();

        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }
/**
 * This function edits an existing account
 * @param  array $params parameters to be changed
 * @param  int $accId  the account id
 * @return [object]         full account object
 */
    public function editAccount($params, $accId){
//UPDATE `bachelor`.`account` SET `name` = 'testeress', `surname` = 'testingsensensen' WHERE `account`.`id` = 2;
        unset($params->id, $params->apiKey);
        unset($params->email, $params->password);
        $fields = array();
        foreach ($params as $key => $value) {
            $set = $key." = '".$value."'";
            array_push($fields, $set);
        }
        $fields = implode(', ', $fields);

        $conn = self::conn();
        $query = "UPDATE ".self::DATABASE_NAME.".account SET ".$fields." WHERE account.id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $accId);

        $result = $stmt->execute();
        if($result){
            $response = self::getAccount($accId); 
            return $response;  
        }else{
            return false;
        }
    }
/**
 * This function inserts a company into the database
 * @param  [object] $params [parameters to be added into the database]
 * @return [object]         [in case of success returns the created company]
 */
    public function createCompany($accountid = 0, $params){
        $userid = $accountid == 0 ? $params->account_id : $accountid;
        $conn = self::conn();
        $query = "INSERT INTO " . self::DATABASE_NAME. ".company (id, account_id, name, email, address, opening_h) 
                    VALUES ('', :account_id, :name, :email, :address, :opening_h)";
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':account_id', $userid);
        $stmt->bindParam(':name', $params->name);
        $stmt->bindParam(':email', $params->email);
        $stmt->bindParam(':address', $params->address);
        $stmt->bindParam(':opening_h', $params->opening_h);
        // $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $result = $stmt->execute();
        if($result){
            return self::getLatest('company');
        }else{
            return "The company could not be created";
        }
    }
    /**
     * gets entire row from company table
     * @param  [int] $company_id [company id]
     * @return [object]             [company object]
     */
    public function getCompany($company_id){
        $conn = self::conn();
        $query = "SELECT * FROM ". self::DATABASE_NAME. ".company WHERE id = :company_id";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':company_id', $company_id);

        $result = $stmt->execute();

        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return false;
        }

    }
/**
 * This function edits the company details
 * @param  [type] $company_id [description]
 * @param  [type] $accountid  [description]
 * @param  [type] $params     [description]
 * @return [type]             [description]
 */
    public function editCompany($company_id, $accountid, $params){
        $conn = self::conn();

        $fields = array();
        foreach ($params as $key => $value) {
            $set = $key." = '".$value."'";
            array_push($fields, $set);
        }
        $fields = implode(', ', $fields);


        $query = "UPDATE ". self::DATABASE_NAME. ".company SET ".$fields." WHERE company.id = :company_id AND account_id = :accountid";
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':accountid', intval($accountid));
        $stmt->bindParam(':company_id', intval($company_id));
        $result = $stmt->execute();

        if($result){
            return self::getCompany($company_id);
        }else{
            return false;
        }
    }
    public function deleteCompany($accountid, $companyid){

        $conn = self::conn();

        $query = "DELETE FROM " .self::DATABASE_NAME. ".company WHERE account_id = :accountid AND id = :company_id";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':accountid', intval($accountid));
        $stmt->bindParam(':company_id', intval($companyid));

        $result = $stmt->execute();

        return $result;


    }
/**
 * This function returns a the companies that are specific to one account
 * @param  [int] $accountid [description]
 * @return [Object]            [Object containing all companies for one account]
 */
    public function getAccountCompanies($accountid){
        $conn = self::conn();

        $query = "SELECT * FROM " .self::DATABASE_NAME. ".company WHERE account_id = :accountid";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':accountid', $accountid);

        $result = $stmt->execute();

        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }

    public function getSingleAccountCompany($accountid, $companyid){
        //SELECT * FROM `company` WHERE id = 3 AND account_id = 1
        $conn = self::conn();

        $query = "SELECT * FROM ". self::DATABASE_NAME .".company WHERE id = :id AND account_id = :accid LIMIT 1";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':id', $companyid);
        $stmt->bindParam(':accid', $accountid);

        $result = $stmt->execute();

        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }
/**
 * This function inserts a service into the database
 * @param  [object] $params [parameters to be added into the database]
 * @return [object]         [in case of success it returns an object of the created service]
 */
    public function createService($companyid, $params){

        $conn = self::conn();
        
        $query = "INSERT INTO ". self::DATABASE_NAME. ".service (id, company_id, name, price, description, duration)
                    VALUES ('', :company_id, :name, :price, :description, :duration)";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':company_id', $companyid);
        $stmt->bindParam(':name', $params->name);
        $stmt->bindParam(':price', $params->price);
        $stmt->bindParam(':description', $params->description);
        $stmt->bindParam(':duration', $params->duration);

        $result = $stmt->execute();

        if($result){
            return self::getLatest('service');
        }else{
            return "The service could not be created";
        }
    }

    public function getCompanyServices($companyId){
        $conn = self::conn();

        $query = "SELECT * FROM " .self::DATABASE_NAME. ".service WHERE company_id = :companyid";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':companyid', $companyId);
        
        $result = $stmt->execute();
        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }
    public function getServicesById($ids = array()){
        $conn = self::conn();
        $services = array();
        foreach ($ids as $id) {
            $query = "SELECT * FROM " .self::DATABASE_NAME. ".service WHERE id = :id";
            $stmt = $conn->prepare($query);

            $stmt->bindParam(':id', $id);

            $result = $stmt->execute();

            if($result){
                array_push($services, $stmt->fetchAll(PDO::FETCH_ASSOC));
            }else{
                return $services;
            }

        }
            return $services;
    }

    public function getSingleService($id){
        $conn = self::conn();

        $query = "SELECT * FROM ".self::DATABASE_NAME.".service WHERE id = :id";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(":id", $id);

        $result = $stmt->execute();

        if($result){
            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $staffArr = array();
            $servStaffQ = "SELECT * FROM ".self::DATABASE_NAME. ".staffService WHERE service_id = :id";
            $servStaff = $conn->prepare($servStaffQ);
            $servStaff->bindParam(':id', $id);
            $rows = $servStaff->execute();
            if($rows){
                $rowsArr = $servStaff->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rowsArr as $index => $value) {
                    $staffId = $value['staff_id'];
                    $staffQ = "SELECT * FROM " .self::DATABASE_NAME. ".staff WHERE id = :id";
                    $staff = $conn->prepare($staffQ);
                    $staff->bindParam(':id', $staffId);

                    $staffRow = $staff->execute();
                    if($staffRow){
                        $staffEnt = $staff->fetchAll(PDO::FETCH_ASSOC);
                        unset($staffEnt['0']['services']);
                        array_push($staffArr, $staffEnt['0']);
                    }
                   
                }
                $response['0']['staff'] = $staffArr;
                
                return $response[0];
            }
        }
        return false;
    }
    public function getService($companyId, $serviceId){
        $conn = self::conn();
        $query = "SELECT * FROM ".self::DATABASE_NAME. ".service WHERE company_id = :companyid AND id = :serviceid";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':companyid', intval($companyId));
        $stmt->bindParam(':serviceid', intval($serviceId));

        $result = $stmt->execute();

        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return false;
        }

    }
/**
 * This function deletes a service by id
 * @param  [int] $companyId [the company id associated to the service]
 * @param  [int] $serviceId [the service id]
 * @return [bool]            
 */
    public function deleteService($companyId, $serviceId){
        $conn = self::conn();

        $query = "DELETE FROM " .self::DATABASE_NAME. ".service WHERE company_id = :companyid AND id = :serviceid";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':companyid', intval($companyId));
        $stmt->bindParam(':serviceid', intval($serviceId));

        $result = $stmt->execute();

        return $result;
    }
/**
 * This function edits an existing service
 * @param  [int] $serviceId [the service identifier]
 * @param  [int] $companyId [The company id associated with specific service]
 * @param  [json] $params    [values to be edited]
 * @return [object]            [the edited service]
 */
public function editService($serviceId, $companyId, $params){
     $conn = self::conn();

        $fields = array();
        foreach ($params as $key => $value) {
            $set = $key." = '".$value."'";
            array_push($fields, $set);
        }
        $fields = implode(', ', $fields);


        $query = "UPDATE ". self::DATABASE_NAME. ".service SET ".$fields." WHERE service.id = :serviceid AND company_id = :companyid";
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':companyid', intval($companyId));
        $stmt->bindParam(':serviceid', intval($serviceId));

        $result = $stmt->execute();

        if($result){
            return self::getService($companyId, $serviceId);
        }else{
            return false;
        }
}   
/**
 * This function inserts a staff member into the database
 * @param  [array] $params [parameters to be added in the staf table]
 * @return [object]         [in case of success returns the created staff object]
 */
    public function createStaff($companyid, $params){
        if(empty($companyid)){
            return array("status"=>400, "message"=>"missing parameter");
        }else{
            $conn = self::conn();
            $query = "INSERT INTO ". self::DATABASE_NAME .".staff ( id, company_id, name, surname, email, services ) 
            VALUES ( '', :company_id, :name, :surname, :email, :services )";

            $stmt = $conn->prepare($query);

            #if no services are supplied, the field is null
            if(empty($params->services)){
                $services = NULL;
            }else{
                $services = self::getServicesById($params->services);
            }
            

            $stmt->bindParam(':company_id', $companyid);
            $stmt->bindParam(':name', $params->name);
            $stmt->bindParam(':surname', $params->surname);
            $stmt->bindParam(':email', $params->email);
            $stmt->bindParam(':services', json_encode($services));


            $result = $stmt->execute();

            if($result){
                $newStaff = self::getLatest('staff');
                $newStaffId = $newStaff['0']['id'];

                if(!empty($params->services)){
                    foreach ($params->services as $index => $serviceId) {
                        $insertQuery = "INSERT INTO ". self::DATABASE_NAME . ".staffService (id, staff_id, service_id)
                        VALUES ('', :staffId, :serviceId)";

                        $insert = $conn->prepare($insertQuery);

                        $insert->bindParam(':staffId', $newStaffId);
                        $insert->bindParam(':serviceId', $serviceId);

                        $row = $insert->execute();
                    }
                }
                
                return $newStaff;
            }else{
                return "Staff member could not be created";
            }
        }
    }
    public function getSingleStaff($staffId){
        $conn = self::conn();
        $query = "SELECT * FROM " .self::DATABASE_NAME. ".staff WHERE id = :id";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':id', $staffId);

        $result = $stmt->execute();

        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }
/**
 * This function edits specific staff member
 * @param  [int] $staffId [staff identifier in database]
 * @param  [object] $params  [paramenters to be edited]
 * @return [object]          [edited staff member]
 */
    public function editStaff($staffId, $params, $companyId){
        $conn = self::conn();
        
         #if no services are supplied, the field is null
        if(isset($params->services)){
            $services = self::getServicesById($params->services);
            $params->services = json_encode($services);
        }
        // var_dump($params);die();
        $fields = array();
        foreach ($params as $key => $value) {
            $set = $key." = '".$value."'";
            array_push($fields, $set);
        }
        $fields = implode(', ', $fields);
        $query = "UPDATE ". self::DATABASE_NAME .".staff SET ".$fields." WHERE staff.id = :staffId";
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':staffId', $staffId);

        $result = $stmt->execute();

        if($result){
            return self::getStaff( $companyId, $staffId);
        }else{
            return "Staff member could not be edited";
        }
    }
    /**
     * This function returns a single staff member
     * @param  [type] $companyid [description]
     * @param  [type] $staffId   [description]
     * @return [type]            [description]
     */
    public function getStaff($companyid, $staffId){
        $conn = self::conn();
        $query = "SELECT * FROM ". self::DATABASE_NAME . ".staff WHERE id = :staffId AND company_id = :companyId";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':staffId', $staffId);
        $stmt->bindParam(':companyId', $companyid);
        $result = $stmt->execute();

        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return false;
        }

    }
    /**
     * This function returns all staff members associated to a company id
     * @param  [type] $companyid [description]
     * @return [type]            [description]
     */
    public function getCompanyStaff($companyid){
        $conn = self::conn();
        $query = "SELECT * FROM ". self::DATABASE_NAME . ".staff WHERE company_id = :companyId";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':companyId', $companyid);

        $result = $stmt->execute();

        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }
    /**
     * this function deletes a staff member
     * @param  [type] $companyId [description]
     * @param  [type] $staffId   [description]
     * @return [type]            [description]
     */
     public function deleteStaff($companyId, $staffId){
        $conn = self::conn();

        $query = "DELETE FROM " .self::DATABASE_NAME. ".staff WHERE company_id = :companyid AND id = :staffid";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':companyid', intval($companyId));
        $stmt->bindParam(':staffid', intval($staffId));

        $result = $stmt->execute();

        return $result;
    }

    public function createBooking($params){
        $conn = self::conn();
        $query = "INSERT INTO ". self::DATABASE_NAME .".booking ( id, company_id, staff_id, service_id, start, end)
                    VALUES ('', :company_id, :staff_id, :service_id, :start, :end)";
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':company_id', $params['company_id']);
        $stmt->bindParam(':staff_id', $params['staff_id']);
        $stmt->bindParam(':service_id', $params['service_id']);
        $stmt->bindParam(':start', $params['start']);
        $stmt->bindParam(':end', $params['end']);

        $result = $stmt->execute();

        if($result){
            return self::getLatest('booking');
        }else{
            return "Booking could not be created";
        }
    }

/**
 * gets the latest entry from the specified table
 * @param  [string] $table subject of the query
 * @return [object]      latest row of table
 */
    private function getLatest($table){
        $conn = self::conn();
        $getLastEntry = $conn->prepare("SELECT * FROM ". self::DATABASE_NAME ."." . $table ." ORDER BY id DESC LIMIT 1");
        $row = $getLastEntry->execute();
        if($row){
            $lastEntry = $getLastEntry->fetchAll(PDO::FETCH_ASSOC);
            return $lastEntry;
        }
    }
    
    public function verifyKey($apiKey){
        $conn = self::conn();
        $query = "SELECT id, name, master FROM ". self::DATABASE_NAME .".master_account WHERE apiKey = :key";
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':key', $apiKey);

        $result = $stmt->execute();
        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $conn = self::conn();
            $query = "SELECT id, name, master FROM ". self::DATABASE_NAME .".account WHERE apiKey = :key";
            $stmt = $conn->prepare($query);

            $stmt->bindParam(':key', $apiKey);

            $result = $stmt->execute();
            if($result){
                return $result;
            }
        }
        return "key " .$apiKey;
    }

    public function getLogin($user){
        $conn = self::conn();
        $query = "SELECT id, email, password, salt, apiKey, token FROM " .self::DATABASE_NAME . ".account WHERE email = :email";
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':email', $user);

        $result = $stmt->execute();
        if($result){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $conn = self::conn();
            $query = "SELECT id, email, password, salt, apiKey FROM " .self::DATABASE_NAME . ".account WHERE email = :email";
            $stmt = $conn->prepare($query);

            $stmt->bindParam(':email', $user);
            $result = $stmt->execute();
            if($result){
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }else{
                return false;
            }
        }
    }

/**
 * This function checks if an email already exists in the database
 * @return [boolean] 
 */
    public function emailExsits($email){
        $conn = self::conn();
        $query = "SELECT email FROM " . self::DATABASE_NAME .".account WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);

        $stmt->execute();
        if($stmt->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }

}

