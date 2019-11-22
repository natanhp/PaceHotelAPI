<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Restaurant extends RestController{
    public function __construct(){
        // header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS, POST, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, Authorization');
        parent::__construct();
        $this->load->model('RestaurantModel');
        $this->load->model('StockModel');
        $this->load->library(['form_validation','email']);
        $this->load->helper(['jwt', 'authorization']);
    }

    public function index_get(){
        if(AUTHORIZATION::validateToken(str_replace("Bearer ","",$this->input->get_request_header("Authorization")))){
            return $this->returnData($this->db->get('Restaurant')->result(), false);
        }else{
            return $this->returnData(
                $this->input->get_request_header('Authorization'), false);
        }
    }

    public function index_post(){
        if(AUTHORIZATION::validateToken(str_replace("Bearer ","",$this->input->get_request_header("Authorization")))){
        $validation = $this->form_validation;
        $rule = $this->RestaurantModel->rules();

        $validation->set_rules($rule);
        if(!$validation->run()){
            return $this->returnData($this->form_validation->error_array(), true);
        }

        $price = 60000000;

        $restaurant_stock = $this->StockModel->checkStock(3);

        if($restaurant_stock->stock == 0){
            return $this->returnData("Sorry there are no available restaurant right now.", true);
        }

        $invoice_number = 'REST'.uniqid();

        $restaurant = new RestaurantData();
        $restaurant->customer_id = $this->post('customer_id');
        $restaurant->reservation_date = DateTime::createFromFormat("d/m/Y", $this->post('reservation_date'))->format("Y-m-d");
        $restaurant->price = $price;
        $restaurant->invoice_number = $invoice_number;

        
        $response = $this->RestaurantModel->store($restaurant);
        $this->StockModel->update($restaurant_stock->stock-1, 3);

        $this->sendMail($this->post('customer_email'), $price, $invoice_number);

        return $this->returnData($response['msg'], $response['error']);

        }else{
            return $this->returnData(
                $this->input->get_request_header('Authorization'), false);
        }
    }

    public function upload_post($invoice_number){
        if(AUTHORIZATION::validateToken(str_replace("Bearer ","",$this->input->get_request_header("Authorization")))){
            $response = $this->RestaurantModel->upload($this->_uploadImage($invoice_number), $invoice_number);

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

        $response = $this->RestaurantModel->destroy($id);

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
        $ci->email->message("Please transfer $rupiah to 111111111 Bank Papua and upload the payment receupt to this link: http://localhost/~ned/backend/index.php/restaurant/upload/$invoice_number");
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

class RestaurantData{
    public $customer_id;
    public $price;
    public $reservation_date;
    public $invoice_number;
}
?>