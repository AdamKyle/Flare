<?php

namespace App\Game\Core\Listeners;

use App\Flare\Events\UpdateTopBarEvent;
use App\Game\Core\Events\BuyItemEvent;

class BuyItemListener
{

    public function handle(BuyItemEvent $event)
    {
        $event->character->gold = $event->character->gold - $event->item->cost;
        $event->character->save();

        $event->character->inventory->slots()->create([
            'inventory_id' => $event->character->inventory->id,
            'item_id'      => $event->item->id,
        ]);

        $event->character->refresh();

        event(new UpdateTopBarEvent($event->character));
    }
}
