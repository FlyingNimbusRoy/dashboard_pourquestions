<?php

namespace App\Services\Crud;

use App\Models\Game;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class GameService extends CrudService
{
    protected string $model = Game::class;

    protected array $with = ['host'];

    protected array $searchable = ['name'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['host_id'] ?? null)) {
            $query->where('host_id', $filters['host_id']);
        }
        if (isset($filters['is_finished']) && $filters['is_finished'] !== '') {
            $query->where('is_finished', filter_var($filters['is_finished'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['is_cancelled']) && $filters['is_cancelled'] !== '') {
            $query->where('is_cancelled', filter_var($filters['is_cancelled'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['is_nsfw']) && $filters['is_nsfw'] !== '') {
            $query->where('is_nsfw', filter_var($filters['is_nsfw'], FILTER_VALIDATE_BOOLEAN));
        }
        if (filled($filters['date_from'] ?? null)) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (filled($filters['date_to'] ?? null)) {
            $query->where('created_at', '<=', $filters['date_to'].' 23:59:59');
        }

        return $query;
    }

    public function create(array $data): Model
    {
        throw ValidationException::withMessages(['Game' => 'Created by the game itself; cannot be created from the dashboard.']);
    }

    public function delete(Model $model): void
    {
        throw ValidationException::withMessages(['Game' => 'Cannot be deleted from the dashboard.']);
    }

    public function staleCutoff(): \Illuminate\Support\Carbon
    {
        return now()->subHours((int) config('dashboard.games.stale_after_hours'));
    }

    /** @return Builder<Game> */
    public function staleQuery(): Builder
    {
        return Game::query()->where('is_finished', false)->where('is_cancelled', false)->where('created_at', '<', $this->staleCutoff());
    }

    public function staleCount(): int
    {
        return $this->staleQuery()->count();
    }

    /** Set is_finished (flag only). Returns changed rows. Returns changed rows. */
    public function markFinished(iterable $games, ?int $userId = null): int
    {
        return $this->setFlag($games, 'is_finished', $userId);
    }

    public function markCancelled(iterable $games, ?int $userId = null): int
    {
        return $this->setFlag($games, 'is_cancelled', $userId);
    }

    public function finishAllStale(?int $userId = null): int
    {
        return $this->markFinished($this->staleQuery()->get(), $userId);
    }

    private function setFlag(iterable $games, string $column, ?int $userId): int
    {
        return DB::transaction(function () use ($games, $column, $userId): int {
            $n = 0;
            foreach ($games as $game) {
                $game = Game::query()->lockForUpdate()->find($game instanceof Game ? $game->getKey() : $game);
                if ($game === null || $game->getRawOriginal($column)) {
                    continue;
                }
                $game->forceFill([$column => true])->save(); // also touches updated_at
                $n++;
            }

            return $n;
        });
    }
}