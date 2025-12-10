<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'name',
        'employee_name',
        'subject',
        'value',
        'type',
        'start_date',
        'end_date',
        'notes',
        'description',
        'contract_description',
        'employee_signature',
        'company_signature',
        'created_by',
        'status',
    ];

    public function contract_type()
    {
        return $this->hasOne('App\Models\ContractType', 'id', 'type');
    }
   

    public function files()
    {
        return $this->hasMany('App\Models\ContractAttechment', 'contract_id' , 'id');
    }

    public function employee()
    {
        return $this->hasOne('App\Models\User', 'id', 'employee_name');
    }

    public function comment()
    {
        return $this->hasMany('App\Models\ContractComment', 'contract_id', 'id');
    }
    public function note()
    {
        return $this->hasMany('App\Models\ContractNote', 'contract_id', 'id');
    }
   
    public function ContractAttechment()
    {
        return $this->belongsTo('App\Models\ContractAttechment', 'id', 'contract_id');
    }

    public function ContractComment()
    {
        return $this->belongsTo('App\Models\ContractComment', 'id', 'contract_id');
    }

    public function ContractNote()
    {
        return $this->belongsTo('App\Models\ContractNote', 'id', 'contract_id');
    }
    public static function getContractSummary($contracts)
    {
        try {
            $total = 0;

            if (empty($contracts) || !is_iterable($contracts)) {
                return \Auth::user()->priceFormat(0);
            }

            foreach($contracts as $contract)
            {
                if (isset($contract->value) && is_numeric($contract->value)) {
                    $total += $contract->value;
                }
            }

            return \Auth::user()->priceFormat($total);
        } catch (\Exception $e) {
            \Log::error('getContractSummary error: ' . $e->getMessage());
            return \Auth::user()->priceFormat(0);
        }
    }
    public static function status()
    {

        $status = [
            'accept' => 'Accept',
            'decline' => 'Decline',
           
        ];
        return $status;
    }
}
