<?php

namespace App\Http\Inject\Reports;

class ReportBase{
    public $datas;

    public function __construct($filters){
        $this->datas = [];
    }
}
