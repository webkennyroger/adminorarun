<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{id1}_{id2}', function ($user, $id1, $id2) {
    return (int) $user->id === (int) $id1 || (int) $user->id === (int) $id2;
});

Broadcast::channel('chat.group.{groupId}', function ($user, $groupId) {
    return true; // Implement actual group membership check here later
});

// Notificações em tempo real do painel admin (ex.: nova denúncia).
Broadcast::channel('admin.notifications', function ($user) {
    return $user->isAdmin() || $user->isManager();
});
