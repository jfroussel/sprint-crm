<?php
/**
 * Created by PhpStorm.
 * User: jeff
 * Date: 13/02/17
 * Time: 15:43
 */

namespace App\Artist;

use RedCat\Strategy\Di;
use DateTime;
use App\Route\Route;
use RedCat\Route\Request;
use FoxORM\MainDb;

use RedCat\Artist\ArtistPlugin;

class MyCreateBilling extends ArtistPlugin{
    protected $description = "create billing details first day of month";
    protected $args = [];
    protected $opts = [];

    protected $table;
    protected $db;
    protected $route;
    protected $user;


    function __construct($name = null, MainDb $db, Route $route, Di $di, Request $request){
        parent::__construct($name);
        $this->di = $di;
        $this->db = $db;
        $this->request = $request;
        $this->route = $route;
    }

    protected function exec(){

        foreach($this->db['user']->where('type = ? AND  (user_id IS NULL OR user_id = id) ', ['saas']) as $id => $user){
            $this->createMonthBilling($id);
        }
    }

    function createMonthBilling($id){
        $info = [];
        $current = new DateTime();
        $simulation = $current;

        $sms = 'sms';
        $email = 'email';
        $simpleletter = 'letter';
        $registeredletter = 'letter';
        $plan = 350;
        $price = ['sms' => 0.5, 'email' => 0.2, 'simpleletter' => 1.5, 'registeredletter' => 3.5];
        $tva = 20;

        $firstDayOfThisMonth = new DateTime("first day of this month");
        $firstDayOfThisMonth = $firstDayOfThisMonth->format('Y-m-d');
        $lastDayOfThisMonth = new DateTime("last day of this month");
        $lastDayOfThisMonth = $lastDayOfThisMonth->format('Y-m-d');
        $billingDate = $lastDayOfThisMonth;
        $info['ref'] = mt_rand();
        $info['plan'] = $plan;
        $info['lastDayOfMonth'] = $lastDayOfThisMonth;
        $info['firstdayOfMonth'] = $firstDayOfThisMonth;
        $info['sms'] = $this->getMonthServices($id,$sms, $firstDayOfThisMonth, $lastDayOfThisMonth);
        $info['smsPrice'] = $this->getMonthServices($id,$sms, $firstDayOfThisMonth, $lastDayOfThisMonth) * $price['sms'];
        $info['email'] = $this->getMonthServices($id,$email, $firstDayOfThisMonth, $lastDayOfThisMonth);
        $info['emailPrice'] = $this->getMonthServices($id, $email, $firstDayOfThisMonth, $lastDayOfThisMonth) * $price['email'];
        $info['simpleletter'] = $this->getMonthServices($id, $simpleletter, $firstDayOfThisMonth, $lastDayOfThisMonth);
        $info['simpleletterPrice'] = $this->getMonthServices($id, $simpleletter, $firstDayOfThisMonth, $lastDayOfThisMonth) * $price['simpleletter'];
        $info['registeredletter'] = $this->getMonthServices($id,$registeredletter, $firstDayOfThisMonth, $lastDayOfThisMonth);
        $info['registeredletterPrice'] = $this->getMonthServices($id,$registeredletter, $firstDayOfThisMonth, $lastDayOfThisMonth) * $price['registeredletter'];
        $info['total'] = $info['plan']['premium'] + $info['smsPrice'] + $info['emailPrice'] + $info['simpleletterPrice'] + $info['registeredletterPrice'] ;
        $info['tva'] = ($info['total'] * $tva)/100;
        $info['totalttc'] = $info['total'] + $info['tva'] + $info['plan'];
        $info['billingDate'] = $billingDate;

        $info['_one_instance'] = ['_type'=> 'user', 'id' => $id];

        if($current == $simulation){
            $entity = $this->db['billing']->entity($info);
            $this->db['billing'][] = $entity;
        }

        //dd($info);
        return $info;
    }
    protected function getMonthServices($id, $name,$start, $end){

        //$this->db->debug();
        $type = $name;
        $email = $this->db[$type]
            ->where('instance_id =?', [$id])
            ->where("ctime BETWEEN ? AND ? ",[$start, $end])
            ->count();
        return $email;
    }


}
