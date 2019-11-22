<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RoomModel extends CI_Model{
    private $table = 'Room';

    public $id;
    public $room_number;
    public $customer_id;
    public $check_in;
    public $check_out;
    public $price;
    public $paymen_status;
    public $invoice_number;
    public $image;
    
    public function rules(){
        return [
                [
                    'field' => 'check_in',
                    'label' => 'check_in',
                    'rules' => 'required'
                ],
                [
                    'field' => 'check_out',
                    'label' => 'check_out',
                    'rules' => 'required'
                ],
            ];
    }

    public function getAll(){
        return $this->db->get($this->table)->result();
    }
    
    public function store($request){
        $this->room_number = $request->room_number;
        $this->price = $request->price;
        $this->customer_id = $request->customer_id;
        $this->check_in = $request->check_in;
        $this->check_out = $request->check_out;
        $this->invoice_number = $request->invoice_number;

        if($this->db->insert($this->table, $this)){
            return ['msg' => 'Berhasil', 'error' => false];
        }

        return ['msg' => 'Gagal', 'error' => true];
    }

    public function update($request, $id){
        $updateData = ['paymen_status' => $request->paymen_status];

        if($this->db->where('id', $id)->update($this->table, $updateData)){
            return ['msg' => 'Berhasil', 'error' => false];
        }

        return ['msg' => 'Gagal', 'error' => false];
    }

    public function upload($image, $invoice){
        $updateData = ['image' => $image];
        if($this->db->where('invoice_number', $invoice)->update($this->table, $updateData)){
            return ['msg' => 'Berhasil', 'error' => false];
        }

        return ['msg' => 'Gagal', 'error' => false];
    }

    public function destroy($id){
        if(empty($this->db->select('*')->where(array('id' => $id))->get($this->table)->row())){
            return ['msg' => 'Id tidak ditemukan', 'error' => true];
        }

        if($this->db->delete($this->table, array('id' => $id))){
            return ['msg' => 'Berhasil', 'error' => false];
        }

        return ['msg' => 'Gagal', 'error' => true];
    }

    public function checkRoomNumberUnique($number){
        return $this->db->select('*')->where(array('room_number' => $number))->get($this->table)->row();
    }
}

?>