<?php

/**
 *    Copyright 2015-2018 ppy Pty. Ltd.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Transformers\Multiplayer;

use App\Models\Multiplayer\UserScoreAggregate;
use App\Transformers\UserCompactTransformer;
use League\Fractal;

class UserScoreAggregateTransformer extends Fractal\TransformerAbstract
{
    protected $availableIncludes = [
        'user',
    ];

    public function transform(UserScoreAggregate $score)
    {
        return [
            'accuracy' => $score->averageAccuracy(),
            'attempts' => $score->attempts,
            'completed' => $score->completed,
            'pp' => $score->averagePp(),
            'room_id' => $score->room_id,
            'total_score' => $score->total_score,
            'user_id' => $score->user_id,
        ];
    }

    public function includeUser(UserScoreAggregate $score)
    {
        return $this->item($score->user, new UserCompactTransformer);
    }
}
