<?php

namespace Modules\Woocommerce\Jobs;

use App\Abstracts\Job;
use App\Http\Requests\Common\Contact as ContactRequest;
use App\Jobs\Common\UpdateContact;
use App\Models\Common\Contact;
use App\Traits\Contacts;
use App\Traits\Modules;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;
use Modules\Woocommerce\Http\Resources\Common\Contact as CommonContact;
use Modules\Woocommerce\Http\Resources\Module\CustomFields;
use Modules\Woocommerce\Models\WooCommerceIntegration;
use Throwable;

class SyncContact extends Job
{
    use Contacts;
    use Modules;

    protected $customer;

    public function __construct($customer)
    {
        $this->customer = $customer;

        parent::__construct($customer);
    }

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        try {
            DB::beginTransaction();

            $integration_params = [
                'company_id'     => company_id(),
                'woocommerce_id' => empty($this->customer->id) ? 0 : $this->customer->id,
                'item_type'      => Contact::class,
            ];

            if (! empty($this->customer->id)) {
                $integration = WooCommerceIntegration::firstOrNew($integration_params);
            } else {
                $integration = WooCommerceIntegration::make($integration_params);
            }

            $data = (array) (new CommonContact($this->customer))->jsonSerialize();

            /* We can not return here!!! Because start DB::beginTransaction();
            if ($this->customer->id === 0) {
                $data['currency_code'] = setting('default.currency', 'USD');

                $contact = Contact::where('email', $this->customer->email)
                                  ->whereIn('type', $this->getCustomerTypes())
                                  ->first();

                if (null === $contact) {
                    $contact = Contact::create($data);
                }

                return $contact->id;
            }
            */

            if ($this->moduleIsEnabled('custom-fields') && isset($this->customer->meta_data)) {
                $customFields = (array) (new CustomFields($this->customer->meta_data))->jsonSerialize();

                $data = array_merge($data, $customFields);

                request()->merge($data);
            }

            if ($integration->exists && null !== $integration->item) {
                $relationships = $this->countRelationships(
                    $integration->item,
                    ['transactions' => 'transactions', 'invoices' => 'invoices']
                );

                if ($relationships) {
                    $data['enabled'] = 1;
                }

                $integration->item->fill($data);

                if ($integration->item->isDirty()) {
                    $this->dispatch((new UpdateContact($integration->item, (new ContactRequest())->merge($data))));

                    $integration->save();
                }
            } else {
                $data['currency_code'] = setting('default.currency', 'USD');

                $contact = Contact::where('email', $this->customer->email)
                                  ->whereIn('type', $this->getCustomerTypes())
                                  ->first();

                if (null === $contact) {
                    $contact = Contact::create($data);
                }

                $integration->item_id              = $contact->id;
                $integration->save();
            }

            DB::commit();

            return $integration->item_id;
        } catch (JsonException | Throwable $e) {
            Log::error(
                'WC Integration::: Exception:' . basename($e->getFile()) . ':' . $e->getLine() . ' - '
                . $e->getCode() . ': ' . $e->getMessage()
            );

            report($e);

            DB::rollBack();

            throw new Exception($e);
        }
    }
}
