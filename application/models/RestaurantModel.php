<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RestaurantModel extends CI_Model{
    private $table = 'Restaurant';

    public $id;
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

    public function findImage($id){
        return $this->db->select('image')->where(array('id' => $id))->get($this->table)->row();
    }
}

?>