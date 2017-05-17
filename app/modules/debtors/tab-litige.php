<?php

namespace App\Modules\Debtors;

use App\AbstractController;

class TabLitige extends AbstractController{
    protected $needAuth = true;

    function load($id){
        return [
           'litige' => $this->db['litige']->where('debtor_id = ?', [$id]),
        ];
    }

    function store($data){
        return $this->db['litige']->simpleEntity($data)->store();
    }

}
