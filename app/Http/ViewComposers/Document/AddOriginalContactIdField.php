<?php

namespace App\Http\ViewComposers\Document;

use Illuminate\View\View;
use App\Models\Document\CreditNote;
use App\Models\Document\DebitNote;

class AddOriginalContactIdField
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        return;
    }
}
