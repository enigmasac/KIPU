<?php

namespace Modules\Woocommerce\Http\Resources\Banking;

use App\Models\Banking\Account;
use App\Traits\Transactions;
use App\Utilities\Date;
use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
{
    use Transactions;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $account_id = Account::where('number', $this->payment_method)->pluck('id')->first();
        
        if (empty($this->transaction_id)) {
            $this->transaction_id = $this->getNextTransactionNumber();
        }

        return [
            'type'           => 'income',
            'number'         => $this->transaction_id,
            'account_id'     => $account_id ?? setting('default.account'),
            'currency_rate'  => 1,
            'payment_method' => setting('default.payment_method'),
            'currency_code'  => $this->currency,
            'amount'         => $this->total,
            'paid_at'        => Date::parse($this->date_created_gmt)->format('Y-m-d H:i:s'),
            'company_id'     => company_id(),
            'created_from'    => source_name('woocommerce'),
        ];
    }
}
