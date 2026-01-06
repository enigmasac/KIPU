<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Response;
use App\Abstracts\Http\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Inventory\Traits\Barcode;
use Modules\Inventory\Models\Warehouse;
use Illuminate\Http\Request as IlluminateRequest;
use Modules\Inventory\Http\Requests\Setting as Request;

class Settings extends Controller
{
    use Barcode;

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        $barcode_types = $this->getBarcodeTypes();

        foreach (json_decode(setting('inventory.units')) as $key => $value) {
            $items[$key] = ['unit_value' => $value];

            if ($value == setting('inventory.default_unit')) {
                $items[$key] += ['default_unit' => '1'];
            } else {
                $items[$key] += ['default_unit' => '0'];
            }
        }

        foreach (json_decode(setting('inventory.reasons')) as $key => $value) {
           $reasons[$key] = ['reason_value' => $value];
        }

        $warehouses = Warehouse::enabled()->pluck('name', 'id');

        return view('inventory::settings.edit', compact('items', 'reasons', 'warehouses', 'barcode_types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     *
     * @return Response
     */
    public function update(Request $request)
    {
        $units = $this->checkTables('unit', $request);

        if (is_string($units)) {
            flash($units)->error()->important();

            return response()->json([
                'success' => false,
                'error' => true,
                'redirect' => route('inventory.settings.edit'),
                'data' => [],
            ]);
        } else {
            foreach ($request['items'] as $key => $value) {
                if ($value['default_unit'] == "1") {
                    $default_unit = $value['unit_value'];
                }
            }
        }

        $reasons = $this->checkTables('reason', $request);

        if (is_string($reasons)) {
            flash($reasons)->error()->important();

            return response()->json([
                'success' => false,
                'error' => true,
                'redirect' => route('inventory.settings.edit'),
                'data' => [],
            ]);
        }

        setting()->set('inventory.adjustment_prefix', $request['adjustment_prefix']);
        setting()->set('inventory.adjustment_next', $request['adjustment_next']);
        setting()->set('inventory.adjustment_digit', $request['adjustment_digit']);
        setting()->set('inventory.barcode_type', $request['barcode_type']);
        setting()->set('inventory.barcode_print_template', $request['_barcode_print_template']);
        setting()->set('inventory.transfer_order_prefix', $request['transfer_order_prefix']);
        setting()->set('inventory.transfer_order_next', $request['transfer_order_next']);
        setting()->set('inventory.transfer_order_digit', $request['transfer_order_digit']);
        setting()->set('inventory.default_warehouse', $request['default_warehouse']);
        setting()->set('inventory.negative_stock', $request['negative_stock']);
        setting()->set('inventory.reorder_level_notification', $request['reorder_level_notification']);
        setting()->set('inventory.track_inventory', $request['track_inventory']);
        setting()->set('inventory.default_unit', isset($default_unit) ? $default_unit : null);
        setting()->set('inventory.units', json_encode((object) $units));
        setting()->set('inventory.reasons', json_encode((object) $reasons));
        setting()->save();

        if (config('setting.cache.enabled')) {
            Cache::forget(setting()->getCacheKey());
        }

        $response = [
            'success' => true,
            'error' => false,
            'redirect' => route('inventory.settings.edit'),
            'data' => [],
        ];

        if ($response['success']) {

            $message = trans('messages.success.updated', ['type' => trans_choice('general.settings', 2)]);

            flash($message)->success();
        } else {
            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function checkTables($type, $request)
    {
        $result = [];

        $request_key = $type == 'unit' ? 'items' : 'reasons';

        if (!isset($request[$request_key])) {
            return trans('inventory::settings.error.' . $type . '_null');
        } else {
            foreach ($request[$request_key] as $key => $value) {
                if ($value[$type . '_value'] == null) {
                    return trans('inventory::settings.error.' . $type . '_empty');
                }

                $result += [$key => $value[$type . '_value']];
            }
        }
        return $result;
    }

    public function exampleBarcode(IlluminateRequest $request)
    {
        $barcode = $this->getExampleBarcode($request['barcode_type']);

        $label = $this->getExampleBarcodeValues($request['barcode_type']);

        return response()->json([
            'data' => [
                'html'  => $barcode,
                'label' => $label,
            ],
        ]);
    }
}
