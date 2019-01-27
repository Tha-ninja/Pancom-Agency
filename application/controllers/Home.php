<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct(){
		parent::__construct();

		$this->auth->checkkey();

		
	}

	public function index(){
		
		//echo $this->input->get('txt');
	}

	public function smsRequests(){
		$from = $this->input->post('from');
		$to = $this->input->post('to');
		$text =  strtoupper($this->input->post('text'));
		$date = $this->input->post('date');
		$id = $this->input->post('id');
		$linkId = $this->input->post('linkId');

		

		
			if($text == "SELL"){
				$this->Requests_model->saveRequest($from,$text,$linkId);
				$this->sellRequest($from,$text,$linkId);
				}

			elseif($text == "BUY"){
				$this->Requests_model->saveRequest($from,$text,$linkId);
				$this->buyRequest($from,$text,$linkId);
				}

			elseif(strpos($text, 'KSH50.00') !== false){
				$amount = "50.0";
				$payment = $this->Payments_model->getPayments($from,$amount);
				if($payment){
					foreach ($payment as $value) {
						$tref = $value->transaction_reference;
						}
					$vehicle = $this->Requests_model->getSellerVehicleDetails($from);

							if($vehicle){
								foreach ($vehicle as $value) {
									$seller_id = $value->seller_id;
								}

								$this->Requests_model->validateSeller($seller_id);
								$this->Payments_model->updatePayment($tref);
								$this->validateSeller($from,$linkId);

							}
					}

				}

				elseif(strpos($text, 'KSH200.00') !== false){
				$amount = "200.0";
				$payment = $this->Payments_model->getPayments($from,$amount);
				if($payment){
					foreach ($payment as $value) {
						$tref = $value->transaction_reference;
						}
					$vehicle = $this->Requests_model->getBuyerVehicleDetails($from);

							if($vehicle){
								foreach ($vehicle as $value) {
									$buyer_id = $value->buyer_id;
								}

								$this->Requests_model->validateBuyer($buyer_id);
								$this->Payments_model->updatePayment($tref);
								$this->validateBuyer($from,$linkId);

							}
					}

				}

			elseif(strpos($text, "#") !== false) {

				$validate = $this->validateVehicleRequest($from,$text,$linkId);

				if($validate){
				$result = $this->Requests_model->getRequest($from);

				if($result){
					foreach ($result as $value) {
						$request_id = $value->id;
						$request = $value->text;
					}

					if($request == "SELL"){
						$this->processSellRequest($from,$text,$linkId,$request,$request_id);
						
					}elseif ($request == "BUY"){
						$this->processBuyRequest($from,$text,$linkId,$request,$request_id);
						
					}else{
						 
						 //if the request text wasn't in the buy sell criteria 
						$this->sendDefaultSms($from,$linkId);
					}

				}else{
					//Couldn't find buy / sell request record
					$this->sendDefaultSms($from,$linkId);
				}
				}
			}

			else{

				$this->sendDefaultSms($from,$linkId);
			}
		
	}

	public function sellRequest($from,$text,$linkId){

			$message = urlencode("Welcome to Pancom Digital Market. Please SMS the details of motor vehicle\motor cycle of choice in the following format: *Make*Model*Type of body*Price# e.g *Toyota*ee102*Station Wagon*300,000# OR *KingBird*KB150*MotorCycle*50,000# to 20234.");

			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.africastalking.com/restless/send?username=$username&Apikey=$apikey&from=$to&linkId=$linkId&bulkSMSMode=0&to=$from&message=$message",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Postman-Token: f875758b-be44-9b78-14fc-f5f8f7322871"
			),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			            curl_close($curl);

			$this->Requests_model->smsLog($from,$message);
	}

	public function buyRequest($from,$text,$linkId){

			$message = urlencode("Welcome to Pancom Digital Market. Please SMS the details of motor vehicle\motor cycle of choice in the following format: *Make*Model*Type of body*Price# e.g *Nissan*B15*Saloon*300,000# OR *KingBird*KB150*MotorCycle*50,000# to 20234.");


			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.africastalking.com/restless/send?username=$username&Apikey=$apikey&from=$to&linkId=$linkId&bulkSMSMode=0&to=$from&message=$message",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Postman-Token: f875758b-be44-9b78-14fc-f5f8f7322871"
			),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			            curl_close($curl);

			$this->Requests_model->smsLog($from,$message);

	}

	public function processSellRequest($from,$text,$linkId,$request,$request_id){

			$find = array("#");
			$replace = array(" ");

			$arr = (str_replace($find,$replace,$text));
			$cars = (list($id,$make,$type,$model,$price) = explode('*', $arr));

			$this->Requests_model->addSellRequest($make,$model,$type,$price,$from,$request_id);


			$message = urlencode("Validate the details by sending Kshs.50 to Pancom Agency through 'Lipa na MPESA' then select 'Buy Goods & Services' till No 593291 and forward the MPesa SMS to 20234");

			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.africastalking.com/restless/send?username=$username&Apikey=$apikey&from=$to&linkId=$linkId&bulkSMSMode=0&to=$from&message=$message",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Postman-Token: f875758b-be44-9b78-14fc-f5f8f7322871"
			),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			            curl_close($curl);

			$this->Requests_model->smsLog($from,$message);
	}

	public function processBuyRequest($from,$text,$linkId,$request,$request_id){

			$find = array("#");
			$replace = array(" ");

			$arr = (str_replace($find,$replace,$text));
			$cars = (list($id,$make,$type,$model,$price) = explode('*', $arr));

			$this->Requests_model->addBuyRequest($make,$model,$type,$price,$from,$request_id);


			$message = urlencode("Validate the details by sending Kshs.200 to Pancom Agency through 'Lipa na MPESA' then select 'Buy Goods & Services' till No 593291 and forward the MPesa SMS to 20234");

			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.africastalking.com/restless/send?username=$username&Apikey=$apikey&from=$to&linkId=$linkId&bulkSMSMode=0&to=$from&message=$message",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Postman-Token: f875758b-be44-9b78-14fc-f5f8f7322871"
			),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			            curl_close($curl);

			$this->Requests_model->smsLog($from,$message);
	}

	public function validateSeller($from,$linkId){

			$message = urlencode("Your Motor Vehicle/Cycle details have been validated. You will receive 3 buyer details that match your choice.");
			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.africastalking.com/restless/send?username=$username&Apikey=$apikey&from=$to&linkId=$linkId&bulkSMSMode=0&to=$from&message=$message",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Postman-Token: f875758b-be44-9b78-14fc-f5f8f7322871"
			),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			            curl_close($curl);

			$this->Requests_model->smsLog($from,$message);
	}

	public function validateBuyer($from,$linkId){

			$message = urlencode("Your Motor Vehicle/Cycle details have been validated. You will receive 3 seller details that match your choice.");
			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.africastalking.com/restless/send?username=$username&Apikey=$apikey&from=$to&linkId=$linkId&bulkSMSMode=0&to=$from&message=$message",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Postman-Token: f875758b-be44-9b78-14fc-f5f8f7322871"
			),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			            curl_close($curl);

			$this->Requests_model->smsLog($from,$message);
	}

	public function validateVehicleRequest($from,$text,$linkId){

			$find = array("#");
			$replace = array(" ");

			$arr = (str_replace($find,$replace,$text));
			$cars = (list($id,$make,$type,$model,$price) = explode('*', $arr));

			if (($id != "") || ($make == NULL) || ($type == NULL) || ($model == NULL) || ($price == NULL)) {

			$message = urlencode("You have sent your Motor Vehicle/Cycle details wrongly. Please send the details in the following format *Make*Model*Body type*Price# e.g *Nissan*Sunny B12*Saloon*250,000# For Model and Body Type clarification visit our website www.pancomagency.org or Call customer care on 0757960008/0512211444.");

			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.africastalking.com/restless/send?username=$username&Apikey=$apikey&from=$to&linkId=$linkId&bulkSMSMode=0&to=$from&message=$message",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Postman-Token: f875758b-be44-9b78-14fc-f5f8f7322871"
			),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			            curl_close($curl);

			$this->Requests_model->smsLog($from,$message);


			return FALSE;	
			}else{

				return TRUE;
			}
	}


	public function sendDefaultSms($from,$linkId){

			$message = urlencode("You have sent a default SMS. To sell Motor Vehicle/Cycle send the word SELL to 20234. To buy Motor Vehicle/Cycle send the word Buy to 20234. For enquiries contact Pancom Digital market on 0757960008/0512211444.");

			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.africastalking.com/restless/send?username=$username&Apikey=$apikey&from=$to&linkId=$linkId&bulkSMSMode=0&to=$from&message=$message",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Postman-Token: f875758b-be44-9b78-14fc-f5f8f7322871"
			),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			            curl_close($curl);

			$this->Requests_model->smsLog($from,$message);

	}


	public function getPaymentDetails() {
        $data = json_decode(file_get_contents('php://input'), true);
        file_put_contents("AllPaymentsKopoKopo.txt", print_r($data, true), FILE_APPEND | LOCK_EX);

        if (isset($data['transaction_reference'])) {
// set json string to php variables

            $service_name = $data['service_name'];
            $business_number = $data['business_number'];
            $transaction_reference = $data['transaction_reference'];
            $internal_transaction_id = $data['internal_transaction_id'];
            $transaction_timestamp = $data['transaction_timestamp'];
            $transaction_type = $data['transaction_type'];
            $account_number = $data['account_number'];
            $sender_phone = $data['sender_phone'];
            $first_name = $data['first_name'];
            $last_name = $data['last_name'];
            $middle_name = $data['middle_name'];
            $amount = $data['amount'];
            $currency = $data['currency'];
            $signature = $data['signature'];
        } else {
// set json string to php variables
            $service_name = $_POST['service_name'];
            $business_number = $_POST['business_number'];
            $transaction_reference = $_POST['transaction_reference'];
            $internal_transaction_id = $_POST['internal_transaction_id'];
            $transaction_timestamp = $_POST['transaction_timestamp'];
            $transaction_type = $_POST['transaction_type'];
            $account_number = $_POST['account_number'];
            $sender_phone = $_POST['sender_phone'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $middle_name = $_POST['middle_name'];
            $amount = $_POST['amount'];
            $currency = $_POST['currency'];
            $signature = $_POST['signature'];
        }

        $base_string = "account_number=" . $account_number .
                "&amount=" . $amount .
                "&business_number=" . $business_number .
                "&currency=" . $currency .
                "&first_name=" . $first_name .
                "&internal_transaction_id=" . $internal_transaction_id .
                "&last_name=" . $last_name .
                "&middle_name=" . $middle_name .
                "&sender_phone=" . $sender_phone .
                "&service_name=" . $service_name .
                "&transaction_reference=" . $transaction_reference .
                "&transaction_timestamp=" . $transaction_timestamp .
                "&transaction_type=" . $transaction_type;
        $previousPaymentDate = date("Y/m/d");
        file_put_contents("AllPaymentsKopoKopo.txt", print_r($base_string, true), FILE_APPEND | LOCK_EX);

        $this->Payments_model->insertPaymentDetails($service_name, $business_number, $transaction_reference, $internal_transaction_id, $transaction_timestamp, $transaction_type, $account_number, $sender_phone, $first_name, $last_name, $middle_name, $amount, $currency, $signature);

    }

}
