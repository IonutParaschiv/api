<?php 
	class company_service{
	public function create($params, $accountid, $companyid){

		$response = Db::createService($companyid, $params);


    	return json_encode($response);
    }

    public function edit($companyid, $params, $serviceid){
        $response = Db::editService($serviceid, $companyid, $params);

        return json_encode($response);
    }

    public function get($companyid, $serviceId){

        $response = Db::getService($companyid, $serviceId);


        return json_encode($response);
    }

    public function delete($companyid, $serviceid){
        $response = Db::deleteService($companyid,$serviceid);

        return json_encode($response);
    }

    public function getAll($userId, $companyid){

        $response = Db::getCompanyServices($companyid);

        return json_encode($response);
    }
	}
 ?>