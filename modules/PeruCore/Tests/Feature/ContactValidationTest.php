<?php

namespace Modules\PeruCore\Tests\Feature;

use Tests\Feature\FeatureTestCase;
use App\Models\Common\Contact;
use Illuminate\Validation\ValidationException;

class ContactValidationTest extends FeatureTestCase
{
    public function test_it_validates_ruc_length()
    {
        $this->loginAs();

        try {
            Contact::create([
                'company_id' => company_id(),
                'type' => 'customer',
                'name' => 'Test RUC Fail',
                'document_type' => '6', // RUC
                'tax_number' => '12345', // Invalid length
                'currency_code' => 'PEN',
                'enabled' => 1,
            ]);
            $this->fail('Should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('tax_number', $e->errors());
        }
    }

    public function test_it_allows_valid_ruc()
    {
        $this->loginAs();

        $contact = Contact::create([
            'company_id' => company_id(),
            'type' => 'customer',
            'name' => 'Test RUC Success',
            'document_type' => '6', // RUC
            'tax_number' => '20123456789', // Valid length (11)
            'currency_code' => 'PEN',
            'enabled' => 1,
        ]);

        $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
    }
}
