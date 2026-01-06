<?php

namespace Modules\Woocommerce\Notifications;

use App\Abstracts\Notification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notifiable;

class DisableModule extends Notification
{
    use Notifiable;

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->error()
            ->from(trans('modules.disabled', ['module' => trans('woocommerce::general.name')]), ':warning:')
            ->to(config('update.notifications')['slack']['channel'])
            ->content(trans('woocommerce::general.notifications.module_disabled', ['url' => route('apps.app.show', ['company_id' => company_id(), 'alias' => 'woocommerce'])]));
    }
    
    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => trans('modules.disabled', ['module' => trans('woocommerce::general.name')]),
            'description' => trans('woocommerce::general.notifications.module_disabled', ['url' => route('apps.app.show', ['company_id' => company_id(), 'alias' => 'woocommerce'])]),
        ];
    }
}
