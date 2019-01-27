<?php

class Requests_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	public function smsLog($from,$message){

		$data = array(
				'phone' => $from,
				'sms' => $message
				 );
		$this->db->insert('sms_log', $data);
	}

	public function saveRequest($from,$text,$linkId){

		$data = array(
				'phone' => $from,
				'text' => $text,
				'linkid' => $linkId );
		$this->db->insert('requests', $data);
	}

	public function getRequest($from){
		$this->db->select('*');
		$this->db->from('requests');
		$this->db->where('phone', $from);
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		$query = $this->db->get();

         if ($query->num_rows() == 1) {
            return $query->result();
        } else {
            return false;
        }
	}

	public function addSellRequest($make,$model,$type,$price,$from,$request_id){

		$data = array(
				'make' => $make,
				'body' => $type,
				'model' => $model,
				'price' => $price,
				'contact' => $from);
		$this->db->insert('sellers', $data);

		$this->db->delete('requests', array('id' => $request_id));
	}

	public function addBuyRequest($make,$model,$type,$price,$from,$request_id){

		$data = array(
				'make' => $make,
				'body' => $type,
				'model' => $model,
				'price' => $price,
				'contact' => $from);
		$this->db->insert('buyers', $data);

		$this->db->delete('requests', array('id' => $request_id));
	}

	public function getSellerVehicleDetails($from){

		$this->db->select('*');
		$this->db->from('sellers');
		$this->db->where('contact', $from);
		$this->db->where('pay', '0');
		$this->db->where('status','0');
		$this->db->order_by('seller_id', 'DESC');
		$this->db->limit(1);
		$query = $this->db->get();

         if ($query->num_rows() == 1) {
            return $query->result();
        } else {
            return false;
        }
	}

	public function validateSeller($seller_id){
		$data  = array('pay' => '1');

		$this->db->where('seller_id', $seller_id);
		$this->db->update('sellers', $data);
	}

	public function getBuyerVehicleDetails($from){

		$this->db->select('*');
		$this->db->from('buyers');
		$this->db->where('contact', $from);
		$this->db->where('pay', '0');
		$this->db->where('status','0');
		$this->db->order_by('buyer_id', 'DESC');
		$this->db->limit(1);
		$query = $this->db->get();

         if ($query->num_rows() == 1) {
            return $query->result();
        } else {
            return false;
        }
	}

	public function validateBuyer($buyer_id){
		$data  = array('pay' => '1');

		$this->db->where('buyer_id', $buyer_id);
		$this->db->update('buyers', $data);
	}

	}
