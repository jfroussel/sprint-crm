<?php
namespace App\Modules\Action\Schedule;

use DateTime;
use DateInterval;
use App\AbstractController;

class Create extends AbstractController{
	protected $needAuth = true;

	function load($debtor_id){
		return [
			'usersList'		=>	$this->db['user'],
			'contactsList'	=>	$this->db['contact']->where('debtor_primary = ?',[$this->db['debtor']->getPrimary($debtor_id)]),
            'debtor'        =>  $this->db['debtor'][$debtor_id],
            'user_name'     =>  $this->db['user'][$this->user->instance_id],
		];
	}

	function store($data){
		$row = $this->db['schedule']->simpleEntity($data);

		if($data['build_method'] == 'auto') {
            $row->_xmany_deadline = $this->renderDeadlines($data);
        }else if($data['build_method'] == 'manual') {
			$sold = $data['base_schedule'];
			$_xmany_deadline = [];
			foreach($data['_xmany_deadline'] as $deadline)
			{
				$sold -= $deadline[0];
				if($sold < 0) $sold = 0;
				$_xmany_deadline[] = ['amount' => $deadline[0], 'date' => $deadline[1], 'solde' => $sold];
			}
			$row->nbschedule = count($_xmany_deadline);
			$row->date_first_schedule = $_xmany_deadline[0]['date'];
			$row->_xmany_deadline = $_xmany_deadline;
        }
		$row->ctime = date("Y-m-d");
		$_many2many_paperwork = [];
		foreach($data['_many2many_paperwork'] as $id){
			$_many2many_paperwork[] = (int)$id;
		}
		$row->_many2many_paperwork = $_many2many_paperwork;
		return $row->store();
	}

	function getOneEcheances($id){
		 return $this->db['schedule']->getAmountSumForDebtor($id,true);
	}

	function renderDeadlines($data) {

        $deadlines = [];
		$sold = $data['base_schedule'];
        $base = $data['base_schedule'];
        $firstDate = $data['date_first_schedule'];
        $nbschedule = $data['nbschedule'];

        $lastAmount = 0;
        $amount = 0;

        if($base%$nbschedule!==0) {
            $lastAmount = $base%$nbschedule;
            $amount = round($base/$nbschedule, 2);
        }else{
            $amount = $base/$nbschedule;
        }

        for($i=0;$i<$nbschedule;$i++) {
			$sold -= $amount;
            if($i==0) {
                $deadlines[] = ['amount' => $amount, 'date' => $firstDate, 'solde' => $sold];
            }else{
                $date = $this->addMonth($deadlines[$i-1]['date']);
                if( $i == ($nbschedule-1) && $lastAmount !== 0)
                    $deadlines[] = ['amount' => ($lastAmount + $amount), 'date' => $date, 'solde' => $sold];
                else
                    $deadlines[] = ['amount' => $amount, 'date' => $date, 'solde' => $sold];
            }
        }

        return $deadlines;

    }

    function addMonth($date) {
        $tmp = new DateTime(str_replace('/', '-', $date));
        $tmp->add(new DateInterval('P1M'));
        return $tmp->format('d/m/Y');
    }
}
