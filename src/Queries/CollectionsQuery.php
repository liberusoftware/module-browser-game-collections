<?php

declare(strict_types=1);

namespace Liberu\BrowserGame\Collections\Queries;

use Illuminate\Database\Eloquent\Builder;
use Liberu\BrowserGame\Collections\Models\CollectionsRecord;

final class CollectionsQuery
{
    public function visible(?string $tenantId, ?string $teamId): Builder
    {
        return CollectionsRecord::query()
            ->when($tenantId, fn (Builder $q, string $v): Builder => $q->where('tenant_id', $v))
            ->when($teamId, fn (Builder $q, string $v): Builder => $q->where('team_id', $v));
    }

    public function availableAchievements(?string $tenantId, ?string $teamId): Builder
    {
        return $this->visible($tenantId, $teamId)
            ->where('kind', 'achievement')
            ->where('status', 'active');
    }

    public function forActor(string $actorId, ?string $tenantId, ?string $teamId): Builder
    {
        return $this->visible($tenantId, $teamId)->whereHas('progress', function (Builder $query) use ($actorId): void {
            $query->where('actor_id', $actorId);
        });
    }

    public function unlockedForActor(string $actorId, ?string $tenantId, ?string $teamId): Builder
    {
        return $this->forActor($actorId, $tenantId, $teamId)->whereHas('progress', function (Builder $query) use ($actorId): void {
            $query->where('actor_id', $actorId)->whereNotNull('completed_at');
        });
    }
}
