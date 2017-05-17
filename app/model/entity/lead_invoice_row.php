<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
class Lead_Invoice_Row extends EntityModel{
    protected $validateProperties = [
        'id',
        'ctime',
        'date',
        'echeance',
        'montant',
        'restant',
        'facture',
        'libelle',
        'documents',
        'lead_id',
        'user_id',


    ];
    function beforePut(){}
    function beforeRecursive(){}
    function beforeCreate(){
        $this->ctime = $this->now();
        //ddj($this);
    }
    function beforeRead(){}
    function beforeUpdate(){}
    function beforeDelete(){}
    function afterPut(){}
    function afterCreate(){}
    function afterRead(){}
    function afterUpdate(){}
    function afterDelete(){}
    function afterRecursive(){}

}
