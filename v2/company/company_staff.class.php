<?php 
/**
 * This class handles the staff calls
 */

class company_staff{

    public function create($params, $accountid, $companyid){

        $response = Db::createStaff($companyid, $params);

        return json_encode($response);

    }

    public function edit($companyid, $params, $staffid){
        
        $response = Db::editStaff($staffid, $params, $companyid);

        return json_encode($response);

    }

    public function get($companyid, $staffid){
        $response = Db::getStaff($companyid, $staffid);


        return json_encode($response);
    }

    public function delete($companyid, $staffid){

        $response = Db::deleteStaff($companyid, $staffid);


        return json_encode($response);
    }

    public function getAll($userId, $companyid){
        $response = Db::getCompanyStaff($companyid);

        return json_encode($response);
    }
}
 ?>