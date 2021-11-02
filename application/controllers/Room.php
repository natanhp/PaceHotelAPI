<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Room extends RestController{
    public function __construct(){
        // header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS, POST, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, Authorization');
        parent::__construct();
        $this->load->model('RoomModel');
        $this->load->model('StockModel');
        $this->load->library(['form_validation','email']);
        $this->load->helper(['jwt', 'authorization']);
    }

    public function index_get(){
        if(AUTHORIZATION::validateToken(str_replace("Bearer ","",$this->input->get_request_header("Authorization")))){
            return $this->returnData($this->db->get('Room')->result(), false);
        }else{
            return $this->returnData(
                $this->input->get_request_header('Authorization'), false);
        }
    }

    public function index_post(){
        if(AUTHORIZATION::validateToken(str_replace("Bearer ","",$this->input->get_request_header("Authorization")))){
        $validation = $this->form_validation;
        $rule = $this->RoomModel->rules();

        $validation->set_rules($rule);
        if(!$validation->run()){
            return $this->returnData($this->form_validation->error_array(), true);
        }

        $price = 500000;

        $room_number = 0;
        do{
            $room_number = rand(1, 5);
            $check_room_number = $this->RoomModel->checkRoomNumberUnique($room_number);
        }while(!empty($check_room_number));

        $room_stock = $this->StockModel->checkStock(1);

        if($room_stock->stock == 0){
            return $this->returnData("Sorry there are no available rooms right now.", true);
        }

        $invoice_number = 'R'.uniqid().$room_number;

        $days = date_diff(DateTime::createFromFormat("Y-m-d", $this->post('check_in')), DateTime::createFromFormat("Y-m-d", $this->post('check_out')))->format('%a')+1;
        $total_price = $price * $days;

        $room = new RoomData();
        $room->customer_id = $this->post('customer_id');
        $room->room_number = $room_number;
        $room->check_in = DateTime::createFromFormat("Y-m-d", $this->post('check_in'))->format("Y-m-d");
        $room->check_out = DateTime::createFromFormat("Y-m-d", $this->post('check_out'))->format("Y-m-d");
        $room->price = $total_price;
        $room->invoice_number = $invoice_number;

        
        $response = $this->RoomModel->store($room);
        $this->StockModel->update($room_stock->stock-1, 1);

        $this->sendMail($this->post('customer_email'), $total_price, $invoice_number);

        return $this->returnData($response['msg'], $response['error']);

        }else{
            return $this->returnData(
                $this->input->get_request_header('Authorization'), false);
        }
    }

    public function upload_post($invoice_number){
        if(AUTHORIZATION::validateToken(str_replace("Bearer ","",$this->input->get_request_header("Authorization")))){
            $response = $this->RoomModel->upload($this->_uploadImage($invoice_number), $invoice_number);

            return $this->returnData($response['msg'], $response['error']);
        }else{
            return $this->returnData(
                $this->input->get_request_header('Authorization'), false);
        }
    }

    public function confirmpayment_post(){
        if(AUTHORIZATION::validateToken(str_replace("Bearer ","",$this->input->get_request_header("Authorization")))){
            $response = $this->RoomModel->update($this->post('id'));

            return $this->returnData($response['msg'], $response['error']);
        }else{
            return $this->returnData(
                $this->input->get_request_header('Authorization'), false);
        }
    }

    public function index_delete($id = null){
        if(AUTHORIZATION::validateToken(str_replace("Bearer ","",$this->input->get_request_header("Authorization")))){
        if($id == null){
            return $this->returnData('Parameter Id tidak Ditemukan', true);
        }

        $image = $this->RoomModel->findImage($id);
        unlink("./upload/".$image->image);
        $response = $this->RoomModel->destroy($id);
        $restaurant_stock = $this->StockModel->checkStock(1);
        $this->StockModel->update($restaurant_stock->stock+1, 1);

        return $this->returnData($response['msg'], $response['error']);
    }else{
        return $this->returnData(
            $this->input->get_request_header('Authorization'), false);
    }
    }

    public function returnData($msg, $error){
        $response['error'] = $error;
        $response['message'] = $msg;

        return $this->response($response);
    }

    private function sendMail($recipient, $price, $invoice_number){
        $rupiah = "Rp " . number_format($price,2,',','.');
        $ci = get_instance();
        $config['protocol'] = "smtp";
        $config['smtp_host'] = "mail.natanhp.id";
        $config['smtp_port'] = "465";
        $config['smtp_crypto'] = 'ssl';
        $config['smtp_user'] = "pacehotel@natanhp.id";
        $config['smtp_pass'] = "2eO=UCXH)M?d";
        $config['charset'] = "utf-8";
        $config['mailtype'] = "html";
        $config['newline'] = "\r\n";
        $ci->email->initialize($config);
        $ci->email->from('pacehotel@natanhp.id', 'Pace-Hotel');
        $ci->email->to($recipient);
        $ci->email->subject('Pace Hotel Payment Confirmation');
        $ci->email->message("Your invoice number is: $invoice_number \nPlease transfer $rupiah to 111111111 Bank Papua and upload the payment receupt to this link: http://localhost:8080/paymentconfirm");
        $this->email->send();
    }

    private function _uploadImage($invoice){
        $config['upload_path']          = './upload';
        $config['allowed_types']        = 'jpg|png';
        $config['overwrite']			= true;

        $this->load->library('upload', $config);


        if ($this->upload->do_upload('image')) {
            return $this->upload->data("file_name");
        }
    }

}

class RoomData{
    public $customer_id;
    public $room_number;
    public $price;
    public $check_in;
    public $check_out;
    public $invoice_number;
}
?>