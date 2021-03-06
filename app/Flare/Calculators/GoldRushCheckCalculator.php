<?php

namespace App\Flare\Calculators;

use App\Flare\Models\Adventure;
use App\Flare\Models\Monster;

class GoldRushCheckCalculator {

    /**
     * Fetches the gold rush check chance.
     *
     * Fecthes the adventure bonus if applies and applies it to the looting bonus agains the monsters drop check.
     *
     * If true, the check passed and the character should be rewarded.
     *
     * @param Monster $monster
     * @param float $lootingChance | 0.0
     * @param Adaventure $adventure | null
     * @return bool
     */
    public function fetchGoldRushChance(Monster $monster, float $lootingChance = 0.0, Adventure $adventure = null) {
        $adventureBonus = $this->getAdventureGoldrushChance($adventure);

        if ($adventureBonus >= 1) {
            return true;
        }

        $bonus = $lootingChance + $adventureBonus;

        if ($bonus < 1) {
            $bonus += 1;
        }

        return (rand(1, 1000) * $bonus) > (1000 - (1000 * $monster->drop_check));
    }

    protected function getAdventureGoldrushChance(Adventure $adventure = null): float {
        if (!is_null($adventure)) {
            return $adventure->gold_rush_chance;
        }

        return 0.0;
    }
}
