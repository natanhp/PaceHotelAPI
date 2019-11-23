<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class User extends RestController{
    public function __construct(){
        // header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS, POST, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, Authorization');
        parent::__construct();
        $this->load->model('UserModel');
        $this->load->library(['form_validation', 'email']);
        $this->load->helper(['jwt', 'authorization', 'email']);
    }

    public function index_get(){
        return $this->returnData($this->db->get('Customer')->result(), false);
    }

    public function login_post(){
        $user = new UserData();
        $user->password = $this->post('password');
        $user->email = $this->post('email');

        $response = $this->UserModel->login($user);
       

        return $this->response(['message' => $response['msg'], 'error' => $response['error'], 'token' => $response['token'], 'id' => $response['id'], 'name' => $response['name'], 'email' => $response['email']]);
    }

    public function index_post($id = null){
        $validation = $this->form_validation;
        $rule = $this->UserModel->rules();

        if($id == null){
            array_push($rule,
            [
                'field' => 'password',
                'label' => 'password',
                'rules' => 'required'
            ],
            [
                'field' => 'email',
                'label' => 'email',
                'rules' => 'required|valid_email|is_unique[Customer.email]'
            ]);
        }else{
            array_push($rule,
            [
                'field' => 'email',
                'label' => 'email',
                'rules' => 'required|valid_email'
            ]);
        }

        $validation->set_rules($rule);
        if(!$validation->run()){
            return $this->returnData($this->form_validation->error_array(), true);
        }

        $user = new UserData();
        $user->name = $this->post('name');
        $user->password = $this->post('password');
        $user->email = $this->post('email');

        if($id == null){
            $token = uniqid(true);
            $user->token = $token;
            $this->sendMail($user->email, $token);
            $response = $this->UserModel->store($user);
        }else{
            $response = $this->UserModel->update($user, $id);

            return $this->response($response);
        }

        return $this->returnData($response['msg'], $response['error']);
    }

    public function emailverification_get($token){
        $user = new UserData();
        $user->verified = "1";
        
        $response = $this->UserModel->emailVerification($user, $token);

        return $this->returnData($response['msg'], $response['error']);
    }

    public function returnData($msg, $error){
        $response['error'] = $error;
        $response['message'] = $msg;

        return $this->response($response);
    }

    private function sendMail($recipient, $token){
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
        $ci->email->subject('Email Verification');
        $ci->email->message('Please follow this link: http://localhost/~ned/backend/index.php/user/emailverification/'.$token);
        $this->email->send();
    }
}

class UserData{
    public $name;
    public $password;
    public $email;
    public $token;
    public $verified;
}
?>