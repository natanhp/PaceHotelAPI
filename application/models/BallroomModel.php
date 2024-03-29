<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BallroomModel extends CI_Model{
    private $table = 'Ballroom';

    public $id;
    public $ball_room_number;
    public $customer_id;
    public $reservation_date;
    public $price;
    public $paymen_status;
    public $invoice_number;
    public $image;
    
    public function rules(){
        return [
                [
                    'field' => 'reservation_date',
                    'label' => 'reservation_date',
                    'rules' => 'required'
                ],
            ];
    }

    public function getAll(){
        return $this->db->get($this->table)->result();
    }
    
    public function store($request){
        $this->ball_room_number = $request->ball_room_number;
        $this->price = $request->price;
        $this->customer_id = $request->customer_id;
        $this->reservation_date = $request->reservation_date;
        $this->invoice_number = $request->invoice_number;

        if($this->db->insert($this->table, $this)){
            return ['msg' => 'Berhasil', 'error' => false];
        }

        return ['msg' => 'Gagal', 'error' => true];
    }

    public function update($id){
        $updateData = ['paymen_status' => '1'];

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
        return $this->db->select('*')->where(array('ball_room_number' => $number))->get($this->table)->row();
    }

    public function findImage($id){
        return $this->db->select('image')->where(array('id' => $id))->get($this->table)->row();
    }
}

?>