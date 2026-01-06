<?php
namespace Modules\Inventory\Widgets\Warehouses;

use App\Utilities\Date;
use App\Traits\DateTime;
use App\Abstracts\Widget;
use App\Traits\Currencies;
use Modules\Inventory\Models\History;
use Akaunting\Apexcharts\Chart;

class TotalStockLineChart extends Widget
{
    use Currencies, DateTime;

    public $default_name = 'inventory::widgets.stock_line_chart';

    public $default_settings = [
        'width' => 'w-full my-8 lg:px-12',
    ];

    public $description = 'widgets.description.cash_flow';

    public $start_date;

    public $end_date;

    public $period;

    public function show($warehouse = null)
    {
        if (! $warehouse) {
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

        try {
            $stock = $this->calculateTotals($warehouse);
        } catch (\Throwable $th) {

            //throw $th;
        }

        $chart = new Chart();

        $chart->setType('line')
            ->setOptions($options)
            ->setLabels(array_values($labels))
            ->setColors($colors)
            ->setDataset(trans('inventory::widgets.total_stock'), 'column', array_values($stock));

        if ($this->period == 'month') {
            $date = isset($stock[Date::now()->format('Y-m')]) ? Date::now()->format('Y-m') : Date::now()->subMonth()->format('Y-m');
        } else {
            $date = Date::now()->quarter;
        }

        $totals = [
            'item' => isset($stock[$date]) ? $stock[$date] : 0,
        ];

        return $this->view('inventory::widgets.show_warehouse_line_chart', [
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

    private function calculateTotals($warehouse)
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

        $histories = $this->applyFilters(History::where('warehouse_id', $warehouse->id), ['date_field' => 'created_at'])->get();

        $this->setTotals($totals, $histories, $date_format);

        if ($this->period == 'month') {
            $now_date = Date::now()->format('Y-m');
            
            $last_month = '';

            for ($i = $start_date; $start_date <= $now_date ; $i++) {
                try {
                    Date::parse($i);
                } catch (\Exception $e) {
                    break;
                }

                if (isset($totals[$last_month]) && isset($totals[$i])) {
                    $totals[$i] += $totals[$last_month];
                }

                if ($now_date == $i) {
                    break;
                }

                $last_month = $i;
            }
        }

        return $totals;
    }

    private function setTotals(&$totals, $histories, $date_format)
    {
        foreach ($histories as $history) {
            if ($this->period == 'month') {
                $i = Date::parse($history->created_at)->format($date_format);
            } else {
                $i = Date::parse($history->created_at)->quarter;
            }

            if (! isset($totals[$i])) {
                continue;
            }

            if ($history->type_type == 'App\Models\Common\Item') {
                $totals[$i] += $history->quantity;
            } elseif ($history->type_type == 'Modules\Inventory\Models\Item') {
                $totals[$i] += $history->quantity;
            } elseif ($history->type_type == 'Modules\Inventory\Models\Adjustment') {
                $totals[$i] -= $history->quantity;
            } elseif ($history->type_type == 'Modules\Inventory\Models\TransferOrder') {
                if ($history->type->destination_warehouse_id == $history->warehouse_id) {
                    $totals[$i] += $history->quantity;
                } else {
                    $totals[$i] -= $history->quantity;
                }
            } elseif ($history->type_type == 'App\Models\Document\DocumentItem') {
                $document_type = isset($history->type->type) ? $history->type->type : null;
                
                if ($document_type) {
                    if ($document_type == 'bill') {
                        $totals[$i] += $history->quantity;
                    } elseif ($document_type == 'invoice') {
                        $totals[$i] -= $history->quantity;
                    }
                }
            }
        }
    }
}
