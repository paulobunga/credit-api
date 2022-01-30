<?php

namespace App\Trait;

use App\Models\Team;

trait HasTeams
{
    public function teams()
    {
        return $this->morphToMany(Team::class, 'model', 'model_has_teams');
    }

    /**
     * Assign the given team to the model.
     *
     * @param array|int|\App\Modles\Team ...$team
     *
     * @return $this
     */
    protected function getStoredTeam($team): Team
    {
        if (is_numeric($team)) {
            return Team::find($team);
        }

        if (is_array($team)) {
            return Team::where([
                'name' => $team['name'],
                'currency' => $team['currency'],
                'type' => $team['type']
            ])->first();
        }

        return $team;
    }

    /**
     * Assign the given team to the model.
     *
     * @param array|int|\App\Models\Team ...$teams
     *
     * @return $this
     */
    public function assignTeams(...$teams)
    {
        $teams = collect($teams)
            ->map(function ($team) {
                if (empty($team)) {
                    return false;
                }

                return $this->getStoredTeam($team);
            })
            ->filter(function ($team) {
                return $team instanceof Team;
            })
            ->map->id
            ->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->teams()->sync($teams, false);
            $model->load('teams');
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($teams, $model) {
                    if ($model->getKey() != $object->getKey()) {
                        return;
                    }
                    $model->teams()->sync($teams, false);
                    $model->load('teams');
                }
            );
        }

        return $this;
    }

    /**
     * Revoke the given team from the model.
     *
     * @param array|int|\App\Models\Team $team
     */
    public function removeTeam($team)
    {
        $this->teams()->detach($this->getStoredTeam($team));

        $this->load('teams');

        return $this;
    }

    /**
     * Remove all current teams and set the given ones.
     *
     * @param array|int|\App\Models\Team ...$teams
     *
     * @return $this
     */
    public function syncTeams(...$teams)
    {
        $this->teams()->detach();

        return $this->assignTeams(...$teams);
    }
}
