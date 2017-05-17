<?php
namespace App\Modules\Parameters;

use App\AbstractController;

use DateTime;

class TabBillings extends AbstractController{

	protected $needAuth = true;

    function load(){
        $info = [];
        $info['users'] = $this->countUsersInstance();
        $info['sms'] = $this->smsServices();
        $info['email'] = $this->emailServices();
        $info['simpleletter'] = $this->SimpleLetterServices();
        $info['registeredletter'] = $this->RegisteredLetterServices();
        $info['month'] = $this->actualMonth();
        $info['userInfo'] = $this->db['user'][$this->user->instance_id];
        return $info;
    }

    protected function actualMonth(){
        $date = date('M'.' / '.' Y');
        return $date;
    }
    protected function smsServices(){
        $date = new DateTime();
        //$this->db->debug();
        $sms = $this->db['sms']
            ->where('YEAR(ctime) = ? AND MONTH(ctime) = ?',[$date->format('Y'),$date->format('m')])
            ->count();
        return $sms;
    }



    protected function SimpleLetterServices(){
        $date = new DateTime();
        $letterType = 'LETTRE_PRIORITAIRE';
        //$this->db->debug();
        $letter = $this->db['letter']
            ->where('YEAR(ctime) = ? AND MONTH(ctime) = ? AND type = ? ',[$date->format('Y'),$date->format('m'), $letterType])
            ->count();
        return $letter;
    }
    protected function RegisteredLetterServices(){
        $date = new DateTime();
        $letterType = 'courrier-recommande';
        //$this->db->debug();
        $letter = $this->db['letter']
            ->where('YEAR(ctime) = ? AND MONTH(ctime) = ? AND type = ?  ',[$date->format('Y'),$date->format('m'), $letterType])
            ->count();
        return $letter;
    }
    protected function emailServices(){
        $date = new DateTime();
        //$this->db->debug();
        $email = $this->db['email']
            ->where('YEAR(ctime) = ? AND MONTH(ctime) = ?',[$date->format('Y'),$date->format('m')])
            ->count();
        return $email;
    }
    protected function countUsersInstance(){
        //$this->db->debug();
        $user = $this->db['user']
            ->count();
        return $user;
    }
}
