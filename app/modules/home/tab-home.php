<?php
namespace App\Modules\Home;
use App\AbstractController;
use App\Model\Entity\Paperwork_Table;
use App\Model\LawCashing;
use DateTime;
use DateInterval;
use App\Model\Dso;


class TabHome extends AbstractController{
	protected $needAuth = true;
   
    function load(){

		$dso = $this->di->get(Dso::class);
        $lc = $this->di->get(LawCashing::class);
		$paperwork = $this->di->get(Paperwork_Table::class);
		$data = [
			'balance' => $this->getAllDebtorBalanceAge(),
			'pieces' => $this->getAllDebtorPieces(),
			'dso' => [
				'labels'=>$dso->createDsoLabels(),
			],
			'createdso' => $dso->createDso(),
            'lawCashing' => [
                'month' => $lc->createLawCashingLabels(),
                'details'=>  $lc->createLawCashing(),
            ],

		];
		return $data;
	}




	function lawCashing(){
        $lc = $this->di->get(LawCashing::class);

        $lawCashing = [
            'month'    =>  $lc->createLawCashingLabels(),
            'details'    =>  $lc->createLawCashing(),
        ];

        return $lawCashing;
    }
	
    protected function getAllDebtorBalanceAge(){

        $encoursNonEchu = $this->allDebtorNonEchu();
        $date30 = $this->allDebtorNonEchuBalanceAge(30, 0);
        $date60 =$this->allDebtorNonEchuBalanceAge(60, 31);
        $date90 = $this->allDebtorNonEchuBalanceAge(90, 61);
        $date180 = $this->allDebtorNonEchuBalanceAge(180, 91);
        $dateMoreOneYear = $this->allDebtorNonEchuAn();

        $balance=[];
        $balance['nonEchu'] =  $encoursNonEchu;
        $balance['j30'] = $date30;
        $balance['j60'] = $date60;
        $balance['j90'] = $date90;
        $balance['j180'] = $date180;
        $balance['plus1an'] = $dateMoreOneYear;
        return $balance;

    }
    
    protected function allDebtorNonEchuBalanceAge($start, $end){
        //$this->db->debug();
        if ($this->db['paperwork']->columnExists('debit') && $this->db['paperwork']->columnExists('date_echeance')) {
            $soldeNonEchu = $this->db['paperwork']
                ->unselect()
                ->select("SUM(debit)")
                ->where('lettrage IS NULL AND type_ecriture IN ?', [['FACT', 'AVOIR', 'OD', 'REGLT','REGUL']])
                ->where("date_echeance BETWEEN  CURDATE() - INTERVAL $start day AND  CURDATE() -INTERVAL $end day")
                ->getCell();
            return $soldeNonEchu;
        }
    }

    protected function allDebtorNonEchuAn(){
        if ($this->db['paperwork']->columnExists('debit') && $this->db['paperwork']->columnExists('date_echeance')) {
            $soldeNonEchu = $this->db['paperwork']
                ->unselect()
                ->select("SUM(debit)")
                ->where('lettrage IS NULL AND type_ecriture IN ?', [['FACT', 'AVOIR', 'OD', 'REGLT','REGUL']])
                ->where('date_echeance BETWEEN  CURDATE() - INTERVAL 1 year AND  CURDATE() -INTERVAL 181 day')
                ->getCell();
            return $soldeNonEchu;
		}
	}
    
    protected function getAllDebtorPieces(){
        $promesses = $this->db['promise']->getAmountSum();
        $litiges = $this->db['litige']->getAmountSum();
        $echeances = $this->db['schedule']->getAmountSum();
        $pieces = [];
        $pieces['promesses'] = $promesses;
        $pieces['litiges'] = $litiges;
        $pieces['echeances'] = $echeances;

        $pieces = str_replace('€', '', $pieces);
        //ddj($pieces);
        return $pieces;
    }
    

    
    protected function allDebtorNonEchu(){
        //$this->db->debug();
        if ($this->db['paperwork']->columnExists('debit') && $this->db['paperwork']->columnExists('date_echeance')) {
            $soldeNonEchu = $this->db['paperwork']
                ->unselect()
                ->select("SUM(debit)")
                ->where('lettrage IS NULL AND type_ecriture IN ?', [['FACT', 'AVOIR', 'OD', 'REGLT','REGUL']])
                ->where('date_echeance >= CURDATE()')
                ->getCell();

            return $soldeNonEchu;
        }
    }

}
