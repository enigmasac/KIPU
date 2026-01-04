<?php

namespace App\View\Components\Documents\Show;

use App\Abstracts\View\Components\Documents\Show as Component;
use Illuminate\Support\Str;

class Restore extends Component
{
    public $description;

    public $user_name;

    public $type_lowercase;

    public $last_cancelled;

    public $last_cancelled_date;

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $this->description = 'documents.slider.cancel';

        $this->last_cancelled = $this->document->histories()->status('cancelled')->latest()->first();

        $this->user_name = ($this->last_cancelled) ? $this->last_cancelled->owner->name : trans('general.na');

        $this->type_lowercase = Str::lower(trans_choice($this->textPage, 1));

        if ($this->last_cancelled) {
            $last_cancelled_at = $this->last_cancelled->created_at;
        } else {
            $last_cancelled_at = $this->document->updated_at;
        }

        $this->last_cancelled_date = $last_cancelled_at
            ? '<span class="font-medium">' . company_date($last_cancelled_at) . '</span>'
            : '<span class="font-medium">' . trans('general.na') . '</span>';

        return view('components.documents.show.restore');
    }
}
