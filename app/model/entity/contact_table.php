<?php
namespace App\Model\Entity;
use App\Model\TableModel;

class Contact_Table extends TableModel{
    protected $uniqColumns = [['instance_id','primary']];
}
