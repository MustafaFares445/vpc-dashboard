<?php

namespace App\Services;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class CalendarEventService
{
    public function forRange(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $clientEvents = Client::query()
            ->visibleTo($user)
            ->whereBetween('next_follow_up_at', [$from, $to])
            ->get(['id', 'name', 'next_follow_up_at'])
            ->map(fn (Client $client): array => [
                'type' => 'follow_up',
                'date' => $client->next_follow_up_at->toDateString(),
                'title' => 'متابعة: '.$client->name,
                'url' => ClientResource::getUrl('edit', ['record' => $client]),
            ]);

        $taskEvents = Task::query()
            ->visibleTo($user)
            ->whereBetween('due_at', [$from, $to])
            ->get(['id', 'title', 'due_at', 'status'])
            ->map(fn (Task $task): array => [
                'type' => 'task',
                'date' => $task->due_at->toDateString(),
                'title' => 'مهمة: '.$task->title,
                'url' => TaskResource::getUrl('edit', ['record' => $task]),
            ]);

        return $clientEvents->concat($taskEvents)->sortBy([['date', 'asc'], ['title', 'asc']])->values();
    }
}
