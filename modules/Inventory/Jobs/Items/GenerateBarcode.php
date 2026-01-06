<?php

namespace Modules\Inventory\Jobs\Items;

use App\Abstracts\Job;
use Modules\Inventory\Traits\Barcode;

class GenerateBarcode extends Job
{
    use Barcode;

    protected $request;
    protected $item;

    /**
     * Create a new job instance.
     *
     * @param  $request
    */
    public function __construct($item, $request)
    {
        $this->item = $item;
        $this->request = $this->getRequestInstance($request);
    }

    public function handle()
    {
        $this->setBarcode($this->item, $this->request->barcode);

        $this->item->inventory()->first()->update(['barcode' => $this->request->barcode]);

        return $this->item;
    }
}
