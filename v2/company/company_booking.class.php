<?php 

class company_booking{
	public function create($params, $accountid, $companyid){
		$response = Db::createBooking($params);

		return json_encode($response);
	}

	public function getAll($userId, $companyid){
		$response  = Db::getAllServices($userId, $companyid);

		return json_encode($response);
	}
}

 ?>