<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\NormalizeTrait;
class Lead_Row extends EntityModel{
	
	use NormalizeTrait;
	
    protected $validateProperties = [
        'id',
        'siren',
        'tva',
        'email',
        'amount',
        'type',
        'seniority',
        'category',
        'frais',
        'fraisissu',
        'total',
        'token',
        'expire',
        'profil_type',
        'corporate_name',
        'last_name',
        'first_name',
        'address',
        'zip_code',
        'city',
        'country',
        'corporate_email',
        'phone',
        'mobile_phone',
        'fax',
        'phone_prefix',
        'mobile_phone_prefix',
        'fax_prefix',
        'price_category',
        'mandat_number',
        'deb_type',
        'debit_name',
        'debit_address',
        'debit_zip_code',
        'debit_city',
        'debit_country',
        'debit_siren',
        'debit_tva',
        'debit_last_name',
        'debit_first_name',
        'debit_capacity',
        'debit_email',
        'debit_phone',
        'debit_mobile',
        'invoice_comments',
        'type_frais',
        'cgu_accepted',
        'validated',
        'user_id',
        'ctime',
        'status',
        'gestionnaire',


    ];
	protected $validateRules = [
		'email'=>'email',
		//validStatus = ['waiting','checked','affected','completed']
	];
	protected $validateFilters = [
		
	];
	function beforePut(){
		$this->restant = $this->normalizeDecimal($this->restant);
		$this->montant = $this->normalizeDecimal($this->montant);
	}

	function beforeRecursive(){}
	function beforeCreate(){
        $this->ctime = $this->now();
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
	
	function getInvoiceTotalUnpaid(){
		$total = 0;
		foreach($this->_many_lead_invoice as $leadInvoice){
			$total += (float)$leadInvoice->restant;
		}
		return $total;
	}
}
