<?php
namespace App\Modules\Home;
use App\AbstractController;
use DateTime;
use DateInterval;
use App\Model\Dso;
class Leadone extends AbstractController{
    protected $needAuth = true;

    function load($id){
        return [
            'lead' => $this->mainDb['lead']->where('id = ? AND user_id = ?', [$id, $this->user->id])->getRow(),
            'leadInvoices' => $this->mainDb['lead_invoice']->where('lead_id = ? AND user_id = ?', [$id, $this->user->id])->getAll(),
        ];
    }

//    function load($id){
//        return $this->db['lead']->where('id = ? AND user_id = ?', [$id, $this->user->id])->getRow();
//    }

//    function getLeadInvoiceDetails($id){
//        return $this->db['lead_invoice']->where('lead_id = ?', [$id])->getRow();
//    }
}
