<?php

namespace App\Flare\Builders;

use Illuminate\Database\Eloquent\Collection;
use App\Flare\Models\Item;
use App\Flare\Models\ItemAffix;

class RandomItemDropBuilder {

    /**
     * @var Collection $itemAffixes
     */
    private $itemAffixes; 

    /**
     * Set the item affixes
     * 
     * @param Colletion $itemAffixes
     * @return RandomItemDropBuilder
     */
    public function setItemAffixes(Collection $itemAffixes): RandomItemDropBuilder {
        $this->itemAffixes = $itemAffixes;

        return $this;
    }

    /**
     * Generate an item.
     * 
     * This will generate a random item.
     * 
     * We start by fetching a random item with prefixes and suffixes., we then duplicate the item and fetch a random affix.
     * From that we check if the affix is the same on the item - if it is, atach it, if not, check if its the same, if it is, delete the
     * duplicate and return the item in question - or attach the new affix and pass that back.
     * 
     * @return Item
     */
    public function generateItem(): Item {
        $item          = Item::inRandomOrder()->with(['itemSuffix', 'itemPrefix'])->where('type', '!=', 'artifact')->where('type', '!=', 'quest')->get()->first();
        $duplicateItem = $this->duplicateItem($item);

        $affix = $this->fetchRandomItemAffix();
        
        if (!is_null($duplicateItem->itemSuffix) || !is_null($duplicateItem->itemPrefix)) {
            $hasSameAffix = $this->hasSameAffix($duplicateItem, $affix);
            
            if ($hasSameAffix) {
                $duplicateItem->delete();

                return $item;
            } else {
                $this->attachAffix($duplicateItem, $affix);
            }
        } else {
            $this->attachAffix($duplicateItem, $affix);
        }

        $duplicateItem->update([
            'market_sellable' => true,
        ]);

        return $duplicateItem->refresh();
    }

    protected function duplicateItem(Item $item): Item {
        $duplicateItem = $item->duplicate();

        return $duplicateItem->refresh()->load(['itemSuffix', 'itemPrefix']);
    }

    protected function hasSameAffix(Item $duplicateItem, ItemAffix $affix): bool {
        $foundAffix = $duplicateItem->{'item'.ucFirst($affix->type)};

        if (is_null($foundAffix)) {
            return false;
        }

        return $foundAffix->name === $affix->name;
    }

    protected function attachAffix(Item $item, ItemAffix $itemAffix): Item {
        $item->update(['item_'.$itemAffix->type.'_id' => $itemAffix->id]);

        return $item->refresh();
    }

    protected function fetchRandomItemAffix() {
        $index = count($this->itemAffixes) - 1;

        return $this->itemAffixes[rand(0, $index)];
    }
}
