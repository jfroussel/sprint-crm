<?php
namespace App\Model;
use InvalidArgumentException;
use DateTime;
use DateInterval;
use App\Model\Db;
class Dso{
	
	protected $db;
	function __construct(Db $db){
		$this->db = $db;

	}
	
	/**
     *  Formule :
        Encours total fin de mois TTC x Nbre de jours -------divise par :
        Chiffre d'affaires total TTC de la période

        Exemple 1 :
        Sté A - décembre 2002 :
        Encours total fin de mois TTC (décembre 2002) = 5.700
        CA total TTC de la période (octobre à décembre 2002) = 7.600
        Nombre de jours de la période = 91
        5.700 x 91 / 7.600 = 68 jours (Période de recouvrement des comptes clients)
        Dans cet exemple le nombre de jours nécessaires au recouvrement des comptes clients est de 68 jours (en moyenne)
     */
    function createDso(){
        $dso=[];
        for($i=0,$j=1; $i<13,$j<14; $i++,$j++){

            $dateJour = new DateTime();
            $interval = new DateInterval('P'.$j.'M');
            $start = new DateTime();
            $start = $start->modify('- 1 year');
            $start = $start->add($interval);
            $end = new DateTime();
            $end = $end->modify('- 1 year');
            $end = $end->modify("+ $i month");

            $dso[$i]['days'] = $this->calculateDsoDays($start, $end);
            $dso[$i]['period'] = $i;
            $dso[$i]['dateJour'] = $dateJour;
            $dso[$i]['start'] = $start;
            $dso[$i]['end'] = $end;
            $dso[$i]['ca'] = $this->calculateCaPeriod($start->format('Y-m-d'), $end->format('Y-m-d'));
            $dso[$i]['encours'] = $this->calculateEncoursPeriod($start->format('Y-m-d'));
            $dso[$i]['encours60'] = $this->calculateEncoursPeriod60($start->format('Y-m-d'));


            if($dso[$i]['encours']){
                $dso[$i]['dso'] = round($this->calculateDso($dso[$i]['encours'],$dso[$i]['days'], $dso[$i]['ca']));
            }
            if($dso[$i]['encours60']){
                $dso[$i]['dso60'] = round($this->calculateDso($dso[$i]['encours60'],$dso[$i]['days'], $dso[$i]['ca']));
            }
            if($dso[$i]['encours'] && $dso[$i]['encours60']){
                $encoursRetard = $dso[$i]['encours60'] - $dso[$i]['encours'];
                $dso[$i]['dsoRetard'] = round($this->calculateDso($encoursRetard,$dso[$i]['days'], $dso[$i]['ca']));
            }


        }
        return $dso;
    }
    
    function createDsoLabels(){
        $labels = [];
        for($i=0; $i<14; $i++){
              $firstMonth = new DateTime();
              $firstMonth = $firstMonth->modify('- 1 year');
              $labels[$i] = $firstMonth->modify("+ $i month")->format('M-y');
        }
        return $labels;
    }
    
    protected function calculateDsoDays($start, $end){
        $interval = $start->diff($end);
        $result = $interval->format('%a');
        return $result;
    }
    protected function calculateCaPeriod($start, $end){

        if($this->db['paperwork']->columnExists('debit') || $this->db['paperwork']->columnExists('lettrage')) {
           //$this->db->debug();
            $sumMontant = $this->db['paperwork']
                ->unselect()
                ->select("SUM(debit)")
//                ->where('lettrage IS NULL')
                ->where('type_ecriture IN ?',[['FACT','AVOIR']])
                ->where("date_facture BETWEEN  '$end'  AND '$start' ")
                ->getCell();

            return $sumMontant;
        }
    }
	protected function calculateEncoursPeriod($start){

        if ($this->db['paperwork']->columnExists('debit') || $this->db['paperwork']->columnExists('lettrage')) {
            //$this->db->debug();
            $sumMontant = $this->db['paperwork']
                ->unselect()
                ->select("SUM(debit)")
                ->where('lettrage IS NULL')
                ->where('type_ecriture IN ?', [['FACT', 'AVOIR']])
//                ->where("date_facture BETWEEN  '$end'  AND '$start' ")
                ->where("date_facture <= '$start' ")
                ->getCell();

            return $sumMontant;
        }
    }
	protected function calculateEncoursPeriod60($start){

        if ($this->db['paperwork']->columnExists('debit') || $this->db['paperwork']->columnExists('lettrage')) {
            //$this->db->debug();
            $sumMontant = $this->db['paperwork']
                ->unselect()
                ->select("SUM(debit)")
                ->where('lettrage IS NULL')
                ->where('type_ecriture IN ?', [['FACT', 'AVOIR']])
//                ->where("date_facture BETWEEN  '$end'  AND '$start' ")
                ->where("date_facture <= '$start' + INTERVAL 60 day ")
                ->getCell();

            return $sumMontant;
        }
    }
    protected function calculateDso($encours, $days, $ca){
        if($ca){
            $dso = ($encours*$days)/$ca;
            return $dso;
        }
    }
}
