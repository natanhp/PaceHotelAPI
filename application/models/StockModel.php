<?php defined('BASEPATH') OR exit('No direct script access allowed');

class StockModel extends CI_Model{
    private $table = 'Stock';

    public $id;
    public $stock;
    public $name;

    public function getAll(){
        return $this->db->get($this->table)->result();
    }
    

    public function update($request, $id){
        $updateData = ['merk' => $request->merk,
                         'name' => $request->name,
                        'amount' => $request->amount,
                        'created_at' => $request->created_at];

        if($this->db->where('id', $id)->update($this->table, $updateData)){
            return ['msg' => 'Berhasil', 'error' => false];
        }

        return ['msg' => 'Gagal', 'error' => false];
    }

    public function checkStock($idStock){
        return $this->db->select('stock')->where(array('id' => $idStock))->get($this->table)->row();
    }
}

?>