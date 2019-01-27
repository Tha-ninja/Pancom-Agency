<?php

class Payments_model extends CI_Model{

	public function __construct(){
		parent::__construct();
	}


	public function insertPaymentDetails($service_name, $business_number, $transaction_reference, $internal_transaction_id, $transaction_timestamp, $transaction_type, $account_number, $sender_phone, $first_name, $last_name, $middle_name, $amount, $currency, $signature) {
        $data = array(
            'service_name' => $service_name,
            'business_number' => $business_number,
            'transaction_reference' => $transaction_reference,
            'internal_transaction_id' => $internal_transaction_id,
            'transaction_timestamp' => $transaction_timestamp,
            'transaction_type' => $transaction_type,
            'account_number' => $account_number,
            'sender_phone' => $sender_phone,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'middle_name' => $middle_name,
            'amount' => $amount,
            'currency' => $currency,
            'signature' => $signature,
        );
        $this->db->insert('payments', $data);
        //return $this->db->insert_id();
    }

	public function getPayments($from,$amount){
		$this->db->select('*');
		$this->db->from('payments');
		$this->db->where('sender_phone', $from);
		$this->db->where('status', '0');
		$this->db->like('amount', $amount, 'after');
		$this->db->limit(1);
		$query = $this->db->get();

         if ($query->num_rows() == 1) {
            return $query->result();
        } else {
            return false;
        }
	}

	public function updatePayment($tref){

		$data = array('status' => '1');

		$this->db->where('transaction_reference', $tref);
		$this->db->update('payments', $data);
	}
}