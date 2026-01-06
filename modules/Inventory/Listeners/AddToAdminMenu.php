<?php

namespace Modules\Inventory\Listeners;

use App\Events\Menu\AdminCreated as Event;
use App\Traits\Modules;
use App\Traits\Permissions;
use Modules\Inventory\Events\AdminMenu;

class AddToAdminMenu
{
    use Permissions, Modules;

    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        if (!$this->moduleIsEnabled('inventory')) {
            return;
        }

        $event->menu->removeByTitle(trim(trans_choice('general.items', 2)));
        
        $title = trim(trans('inventory::general.name'));
        if ($this->canAccessMenuItem($title, [
                'read-common-items',
                'read-inventory-item-groups',
                'read-inventory-variants',
                'read-inventory-manufacturers',
                'read-inventory-transfer-orders',
                'read-inventory-adjustments',
                'read-inventory-warehouses',
                'read-inventory-histories',
            ])) {
            $event->menu->dropdown($title, function ($sub) {
                $title = trim(trans_choice('general.items', 2));
                if ($this->canAccessMenuItem($title, 'read-common-items')) {
                    $sub->route('inventory.items.index', $title, [], 10, []);
                }

                $title = trim(trans_choice('inventory::general.item_groups', 2));
                if ($this->canAccessMenuItem($title, 'read-inventory-item-groups')) {
                    $sub->route('inventory.item-groups.index', $title, [], 20, []);
                }

                $title = trim(trans_choice('inventory::general.variants', 2));
                if ($this->canAccessMenuItem($title, 'read-inventory-variants')) {
                    $sub->route('inventory.variants.index', $title, [], 30, []);
                }

                // $title = trim(trans_choice('inventory::general.manufacturers', 2));
                // if ($this->canAccessMenuItem($title, 'read-inventory-manufacturers')) {
                //     $sub->route('inventory.manufacturers.index', $title, [], 40, []);
                // }

                $title = trim(trans_choice('inventory::general.transfer_orders', 2));
                if ($this->canAccessMenuItem($title, 'read-inventory-transfer-orders')) {
                    $sub->route('inventory.transfer-orders.index', $title, [], 50, []);
                }

                $title = trim(trans_choice('inventory::general.adjustments', 2));
                if ($this->canAccessMenuItem($title, 'read-inventory-adjustments')) {
                    $sub->route('inventory.adjustments.index', $title, [], 60, []);
                }

                $title = trim(trans_choice('inventory::general.warehouses', 2));
                if ($this->canAccessMenuItem($title, 'read-inventory-warehouses')) {
                    $sub->route('inventory.warehouses.index', $title, [], 70, []);
                }

                $title = trim(trans_choice('inventory::general.histories', 2));
                if ($this->canAccessMenuItem($title, 'read-inventory-histories')) {
                    $sub->route('inventory.histories.index', $title, [], 80, []);
                }
            }, 45, [
                'title' => $title,
                'icon' => 'inventory_2',
            ]);
        }

        event(new AdminMenu($event->menu));
    }
}
