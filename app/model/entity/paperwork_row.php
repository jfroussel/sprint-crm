<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
use App\Model\NormalizeTrait;
use DateTime;


class Paperwork_Row extends EntityModel{
	use NormalizeTrait;
	


    protected $validateProperties = [
        'debtor_primary',
        'nom_client',
        'type_compte',
        'date_facture',
        'date_echeance',
        'date_lettrage',
        'libelle',
        'debit',
        'credit',
        'type_ecriture',
        'numero_piece',
        'journal',
        'lettrage',
        'jours_retard',
        'sens',
        'montant',
        'pre_lettrage',
        'commentaire',
        'date_livraison',
        'date_re_echeancement',
        'date_importation',
        'date_modification',
        'mtcp',
        'mtfr',
        'mtifr',
        'mtir',
        'mtpr',
        'nb_litiges',
        'state',
        'import_timestamp',
    ];

    protected $tauxBce = [
        '2001'    =>  '11,75',
        '2002'    =>  '10,15',
        '2003'    =>  '9,75',
        '2004'    =>  '9,00',
        '2005'    =>  '9,00',
        '2006'    =>  '9,00',
        '2007'    =>  '10,50',
        '2008'    =>  '11,00',
        '2009'    =>  '12,00',
        '2010'    =>  '11,00',
        '2011'    =>  '11,25',
        '2012'    =>  '10,75',
        '2013'    =>  '13',
        '2014'    =>  '14',
        '2015'    =>  '15',
        '2016'    =>  '16',
        '2017'    =>  '17',

    ];


    function beforePut(){
		if(isset($this->debit)){
			$this->debit = $this->normalizeDecimal($this->debit);
		}
		if(isset($this->credit)){
			$this->credit = $this->normalizeDecimal($this->credit);
		}
		$this->normalizeDateFields();
    }
    function beforeRecursive(){

    }
    function beforeCreate(){

        if(!isset($this->solde_echu)){
            $this->solde_echu = null;
        }


    }

    function beforeRead(){

    }
    function beforeUpdate(){

    }
    function beforeDelete(){}
    function afterPut(){}
    function afterCreate(){}
    function afterRead(){

    }
    function afterUpdate(){}
    function afterDelete(){}
    function afterRecursive(){}








    protected $states_fr = [
        'standard' => 'etalon'.' <i class="fa fa-flag" aria-hidden="true"></i>',
        'litigation' => 'litige'.' <i class="fa fa-gavel" aria-hidden="true"></i>',
        'promise'   => 'promesse'.' <i class="fa fa-handshake-o" aria-hidden="true"></i>',
        'schedule'  => 'echeancier'.' <i class="fa fa-calendar-check-o" aria-hidden="true"></i>',
        'payment'   => 'reglement'.'<i class="fa fa-credit-card" aria-hidden="true"></i>',
    ];

    function getDynamic(){

        $this->dynamic->state_fr = $this->state&&isset($this->states_fr[$this->state])?$this->states_fr[$this->state]: '';
        return (array)$this->dynamic;
    }

    function calculateIfr(){
        $ifr = 40;
        return $ifr;
    }

    function calculateFr(){
        $fr = 350;
        return $fr;
    }


    function calculateCp(){

        $this->mtcp = (($this->debit * 10/100) * $this->jours_retard)/365;
        return number_format($this->mtcp, 2, ',', ' ');
    }


    function calculateIr($year){
        return $this->calculatePenalities($year);
    }


    function calculatePr($year){
        return $this->calculatePenalities($year);
    }



}
