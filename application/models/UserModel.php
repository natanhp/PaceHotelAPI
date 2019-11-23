<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model{
    private $table = 'Customer';

    public $id;
    public $name;
    public $email;
    public $password;
    public $token;
    public $verified;
    
    public function rules(){
        return [
                [
                    'field' => 'name',
                    'label' => 'name',
                    'rules' => 'required'
                ]
            ];
    }

    public function getAll(){
        return $this->db->get('Customer')->result();
    }
    
    public function store($request){
        $this->name = $request->name;
        $this->email = $request->email;
        $this->token = $request->token;
        $this->password = password_hash($request->password, PASSWORD_BCRYPT);
        if($this->db->insert($this->table, $this)){
            return ['msg' => 'Berhasil', 'error' => false];
        }

        return ['msg' => 'Gagal', 'error' => true];
    }

    public function login($request){
        $this->email = $request->email;
        $this->password = $request->password;
        $query=$this->db->select(array('id', 'name', 'email', 'password', 'verified'))->where(array('email' => $this->email))->get($this->table)->row();
        if(!empty($query)){
            if(password_verify($this->password, $query->password) && $query->verified == 1){
                $token = AUTHORIZATION::generateToken(['email' => $this->email]);
                return ['msg' => "Berhasil", 'error' => false, 'token' => $token, 'id' => $query->id, 'name' => $query->name, 'email' => $query->email];
            }
        }

        return ['msg' => 'Gagal', 'error' => true, 'token' => null];
    }

    public function emailVerification($request, $token){
        $updateData = ['verified' => $request->verified];
        if($this->db->where('token', $token)->update($this->table, $updateData)){
            return ['msg' => 'Berhasil', 'error' => false];
        }

        return ['msg' => 'Gagal', 'error' => false];
    }

    public function update($request, $id){
        $updateData = ['email' => $request->email, 'name' => $request->name];
        if($this->db->where('id', $id)->update($this->table, $updateData)){
            return ['msg' => 'Berhasil', 'error' => false, 'name' => $request->name, 'email' => $request->email];
        }

        return ['msg' => 'Gagal', 'error' => false];
    }
}

?>