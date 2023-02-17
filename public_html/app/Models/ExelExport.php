<?php

namespace App\Models;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;

class ExelExport  implements FromArray
{
    protected $_data = [];

    public function __construct(array $data)
    {
        $this->_data = $data;
    }

    /**
     * @return array
     */
    public function array() : array
    {
        return $this->_data;
    }

}
