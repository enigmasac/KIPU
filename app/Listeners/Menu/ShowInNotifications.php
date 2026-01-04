<?php

namespace App\Listeners\Menu;

use App\Events\Menu\NotificationsCreated as Event;
use App\Models\Common\Notification;
use App\Utilities\Date;
use App\Utilities\Versions;

class ShowInNotifications
{

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(Event $event)
    {
        if (user()->cannot('read-notifications')) {
            return;
        }

        static $notifications;

        if (! empty($notifications)) {
            $event->notifications->notifications = $notifications;

            return;
        }

        // Notification tables
        $notifications = collect();

        // Update notifications
        if (user()->can('read-install-updates')) {
            $updates = Versions::getUpdates();

            foreach ($updates as $key => $update) {
                $prefix = ($key == 'core') ? 'core' : 'module';

                if ($prefix == 'module' && ! module($key)) {
                    continue;
                }

                $name = ($prefix == 'core') ? 'Akaunting' : module($key)?->getName();

                $new = new Notification();
                $new->id = $key;
                $new->type = 'updates';
                $new->notifiable_type = "users";
                $new->notifiable_id = user()->id;
                $new->data = [
                    'title' => $name . ' (v' . $update?->latest . ')',
                    'description' => trans('install.update.' . $prefix, ['module' => $name, 'url' => route('updates.index')]),
                ];
                $new->created_at = Date::now();

                $notifications->push($new);
            }
        }

        foreach (user()->unreadNotifications as $unreadNotification) {
            $notifications->push($unreadNotification);
        }

        $event->notifications->notifications = $notifications;
    }
}
