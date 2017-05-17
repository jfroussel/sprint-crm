<?php
namespace App\Model;
use InvalidArgumentException;
use DateTime;
use DateInterval;
use App\Model\Db;
class LawCashing{

    protected $db;
    function __construct(Db $db){
        $this->db = $db;

    }

    function createLawCashing(){
        $planning = [];
        for($i=0; $i<13; $i++){

            $interval = new DateInterval('P'.$i.'M');
            $start = new DateTime();
            $start = $start->modify('- 1 year');
            $start = $start->add($interval)->format('Y-m-d');
            $first = new DateTime($start);
            $first = $first->modify('first day of this month');
            $last = new DateTime($start);
            $last = $last->modify('last day of this month');
            $planning[$i]['today'] = $start;
            $planning[$i]['first_day'] = $first;
            $planning[$i]['last_day'] = $last;
            $planning[$i]['ca'] = $this->ca($first->format('Y-m-d'),$last->format('Y-m-d'));

        }
        //ddj($planning);
        return $planning;
    }

    protected function ca($first,$last){

        if($this->db['paperwork']->columnExists('debit')) {

            $sumMontant = $this->db['paperwork']
                ->unselect()
                ->select("SUM(debit)")
                ->where('type_ecriture IN ?',[['FACT','AVOIR']])
                ->where("date_echeance BETWEEN  '$first'  AND '$last' ")
                ->getCell();

            return $sumMontant;
        }
    }


    function createLawCashingLabels(){
        $labels = [];
        for($i=0; $i<13; $i++){
            $firstMonth = new DateTime();
            $firstMonth = $firstMonth->modify('- 1 year');
            $labels[$i] = $firstMonth->modify("+ $i month")->format('M-y');
        }
        return $labels;
    }


    protected function calculateLawCashingDays($start, $end){
        $interval = $start->diff($end);
        $result = $interval->format('%a');
        return $result;
    }

    protected function calculateEncoursPeriod($start){

        if ($this->db['paperwork']->columnExists('debit') || $this->db['paperwork']->columnExists('lettrage')) {
            //$this->db->debug();
            $sumMontant = $this->db['paperwork']
                ->unselect()
                ->select("SUM(debit)")
                ->where('lettrage IS NULL')
                ->where('type_ecriture IN ?', [['FACT', 'AVOIR']])
                ->where("date_facture <= '$start' ")
                ->getCell();

            return $sumMontant;
        }
    }


    protected function calculateLawCashing($encours, $days, $ca){
        if($ca){
            $lc = ($encours*$days)/$ca;
            return $lc;
        }
    }
}
