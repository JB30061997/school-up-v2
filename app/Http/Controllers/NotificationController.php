<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Liste des notifications de l'utilisateur connecté.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $notifications = $user
            ->notifications()
            ->latest()
            ->paginate(20)
            ->through(function (DatabaseNotification $notification) {
                return [
                    'id' => $notification->id,

                    'type' => class_basename(
                        $notification->type
                    ),

                    'data' => $notification->data,

                    'read' => $notification->read_at !== null,

                    'read_at' => $notification->read_at?->toISOString(),

                    'created_at' => $notification->created_at?->toISOString(),
                ];
            });

        return Inertia::render(
            'notifications/Index',
            [
                'notifications' => $notifications,

                'unreadCount' => $user
                    ->unreadNotifications()
                    ->count(),
            ]
        );
    }

    /**
     * Marquer une notification comme lue.
     */
    public function markAsRead(
        Request $request,
        string $notification
    ): RedirectResponse {
        $databaseNotification = $request
            ->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if ($databaseNotification->read_at === null) {
            $databaseNotification->markAsRead();
        }

        return back()->with(
            'success',
            'Notification marquée comme lue.'
        );
    }

    /**
     * Marquer une notification comme non lue.
     */
    public function markAsUnread(
        Request $request,
        string $notification
    ): RedirectResponse {
        $databaseNotification = $request
            ->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $databaseNotification->update([
            'read_at' => null,
        ]);

        return back()->with(
            'success',
            'Notification marquée comme non lue.'
        );
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function markAllAsRead(
        Request $request
    ): RedirectResponse {
        $request
            ->user()
            ->unreadNotifications
            ->markAsRead();

        return back()->with(
            'success',
            'Toutes les notifications ont été marquées comme lues.'
        );
    }

    /**
     * Supprimer une notification.
     */
    public function destroy(
        Request $request,
        string $notification
    ): RedirectResponse {
        $databaseNotification = $request
            ->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $databaseNotification->delete();

        return back()->with(
            'success',
            'Notification supprimée.'
        );
    }

    /**
     * Supprimer toutes les notifications lues.
     */
    public function destroyRead(
        Request $request
    ): RedirectResponse {
        $request
            ->user()
            ->notifications()
            ->whereNotNull('read_at')
            ->delete();

        return back()->with(
            'success',
            'Les notifications lues ont été supprimées.'
        );
    }
}
