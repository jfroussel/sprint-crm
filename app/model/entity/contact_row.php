<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\NormalizeTrait;
use App\Model\HistorizeTrait;
class Contact_Row extends EntityModel{
    use HistorizeTrait;
    use NormalizeTrait;
    
    protected $validateProperties = [
        'id',
        'primary',
        'debtor_id',
        'debtor_primary',
        'nom',
        'prenom',
        'role',
        'fonction',
        'principal',
        'address',
        'postal_code',
        'city',
        'country',
        'email',
        'tel',
        'fax',
        'portable',
        'type',
        'comment',
        'active',
        'ctime',
        'import_timestamp'
    ];
    
    function beforePut(){
		$this->normalizeDateFields();
	}
    function beforeRecursive(){}
    function beforeCreate(){
        $this->ctime = $this->now();
        if(!$this->debtor_primary && $this->debtor_id){
            $this->debtor_primary = $this->db['debtor'][$this->debtor_id]->primary;
        }
    }
    function beforeRead(){}
    function beforeUpdate(){

        if($this->principal){
            $instance_id = $this->db['contact']->unSelect()->select('instance_id')->where('id = ?',[$this->id])->getCell();
            $this->db->exec('UPDATE contact SET principal = ? WHERE debtor_primary = ? AND id != ? AND instance_id = ?',[false,$this->debtor_primary,$this->id,$instance_id]);
        }
        $this->mtime = $this->now();

    }
    function beforeDelete(){}
    function afterPut(){}
    function afterCreate(){
        //$this->historize();
    }
    function afterRead(){}
    function afterUpdate(){}
    function afterDelete(){}
    function afterRecursive(){}

}
