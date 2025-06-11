<?php
namespace Leazycms\Web\Models\Trait;
use Leazycms\Web\Models\Notification;

trait Notificationable
{
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notificationable');
    }

    public function notificationCleaner()
    {
        $this->notifications()
        ->where('user_id', auth()->id())
        ->update(['is_read' => true]);
    }
    public function addNotification($notificationData)
    {
    $data = $this->notifications()->create([
        'title' => $notificationData['title'],
        'message' => $notificationData['message'],
        'type' => $notificationData['type'] ?? 'info',
        'url' => $notificationData['url'] ?? null,
        'is_read' => 0,
        'user_id' => $notificationData['to_user'] ?? null,
    ]);
    return route('notifreader',$data->id);
    }
    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false)->latest();
    }

    public function readNotifications()
    {
        return $this->notifications()->where('is_read', true)->latest();
    }

    public function markNotificationAsRead($notificationId)
    {
        $notification = $this->notifications()->find($notificationId);
        if ($notification) {
            $notification->mark_as_read();
        }
    }
}