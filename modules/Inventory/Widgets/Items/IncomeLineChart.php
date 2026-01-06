<?php

namespace Modules\Inventory\Widgets\Items;

use Akaunting\Apexcharts\Chart;
use App\Abstracts\Widget;
use App\Utilities\Date;
use App\Traits\Currencies;
use App\Traits\DateTime;
use Modules\Inventory\Models\History;

class IncomeLineChart extends Widget
{
    use Currencies, DateTime;

    public $default_name = 'inventory::widgets.sales_quantity_by_warehouse';

    public $description = 'widgets.description.cash_flow';

    public $start_date;

    public $end_date;

    public $period;

    public function show($item = null)
    {
        if ( !$item->inventory()->first()) {
            return;
        }

        $this->setFilter();

        $labels = $this->getLabels();

        $colors = $this->getColors();

        $options = [
            'chart' => [
                'stacked'           => true,
            ],
            'plotOptions' => [
                'bar' => [
                    'columnWidth'   => '40%',
                ],
            ],
            'legend' => [
                'position'          => 'top',
            ],
        ];

        $chart = new Chart();

        $chart->setType('line')
            ->setOptions($options)
            ->setLabels(array_values($labels))
            ->setColors($colors);

        $stocks = [];

        foreach ($item->inventory()->get() as $inventory_item) {
            if (empty($inventory_item->warehouse)) {
                continue;
            }

            $warehouse_stocks = $this->calculateTotals($inventory_item);

            foreach ($warehouse_stocks as $key => $warehouse_stock) {
                if (isset($stocks[$key])) {
                    $stocks[$key] += $warehouse_stock;
                } else {
                    $stocks[$key] = $warehouse_stock;
                }
            }
        }

        $chart->setDataset($item->name, 'column', array_values($stocks));

        $totals = [
            'item' => array_sum($stocks),
        ];

        return $this->view('inventory::widgets.show_item_line_chart', [
            'chart' => $chart,
            'totals' => $totals,
        ]);
    }

    public function setFilter(): void
    {
        $financial_start = $this->getFinancialStart()->format('Y-m-d');

        // check and assign year start
        if (($year_start = Date::today()->startOfYear()->format('Y-m-d')) !== $financial_start) {
            $year_start = $financial_start;
        }

        $this->start_date = Date::parse(request('start_date', $year_start));
        $this->end_date = Date::parse(request('end_date', Date::parse($year_start)->addYear(1)->subDays(1)->format('Y-m-d')));
        $this->period = request('period', 'month');
    }

    public function getLabels(): array
    {
        $range = request('range', 'custom');

        $start_month = $this->start_date->month;
        $end_month = $this->end_date->month;

        // Monthly
        $labels = [];

        $s = clone $this->start_date;

        if ($range == 'last_12_months') {
            $end_month   = 12;
            $start_month = 0;
        } elseif ($range == 'custom') {
            $end_month   = $this->end_date->diffInMonths($this->start_date);
            $start_month = 0;
        }

        for ($j = $end_month; $j >= $start_month; $j--) {
            $labels[$end_month - $j] = $s->format('M Y');

            if ($this->period == 'month') {
                $s->addMonth();
            } else {
                $s->addMonths(3);
                $j -= 2;
            }
        }

        return $labels;
    }

    public function getColors(): array
    {
        return [
            '#8bb475',
            '#fb7185',
        ];
    }

    private function calculateTotals($inventory_item): array
    {
        $totals = [];

        $date_format = 'Y-m';

        if ($this->period == 'month') {
            $n = 1;
            $start_date = $this->start_date->format($date_format);
            $end_date = $this->end_date->format($date_format);
            $next_date = $start_date;
        } else {
            $n = 3;
            $start_date = $this->start_date->quarter;
            $end_date = $this->end_date->quarter;
            $next_date = $start_date;
        }

        $s = clone $this->start_date;

        // $totals[$start_date] = 0;
        while ($next_date <= $end_date) {
            $totals[$next_date] = 0;

            if ($this->period == 'month') {
                $next_date = $s->addMonths($n)->format($date_format);
            } else {
                if (isset($totals[4])) {
                    break;
                }

                $next_date = $s->addMonths($n)->quarter;
            }
        }

        $histories = $this->applyFilters(
                        History::where('warehouse_id', $inventory_item->warehouse_id)
                               ->where('item_id', $inventory_item->item_id)
                               ->where('type_type', 'App\Models\Document\DocumentItem'),
                                    [
                                        'date_field' => 'created_at'
                                    ])
                               ->get();

        $this->setTotals($totals, $histories, $date_format);

        return $totals;
    }

    private function setTotals(&$totals, $histories, $date_format)
    {
        $filter_mount = null;
        $last_mount_quantity = 0;

        foreach ($histories as $history) {
            if ($this->period == 'month') {
                $i = Date::parse($history->created_at)->format($date_format);
            } else {
                $i = Date::parse($history->created_at)->quarter;
            }

            if (!isset($totals[$i])) {
                continue;
            }

            if ($filter_mount != $i) {
                $filter_mount = $i;

                if (isset($totals[Date::parse($history->created_at)->addMonths(-1)->format($date_format)])) {
                    $last_mount_quantity = $totals[Date::parse($history->created_at)->addMonths(-1)->format($date_format)];
                }
            }

            $document_item = $history->document_item;

            if ($document_item && $document_item->type == 'invoice') {
                if ($totals[$i] == 0) {
                    $totals[$i] = $last_mount_quantity + $history->quantity;
                } else {
                    $totals[$i] += $history->quantity;
                }
            }

            $last_mount_quantity = 0;
        }
    }
}
