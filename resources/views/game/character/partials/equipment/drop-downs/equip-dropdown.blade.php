<div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" type="button" id="actionsButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu" aria-labelledby="actionsButton">
        @if (!$slot->equipped)

            <form id="item-comparison-{{$slot->id}}" action="{{route('game.inventory.compare', ['character' => $character])}}" method="GET" style="display: none">
                @csrf

                <input type="hidden" name="slot_id" value={{$slot->id}} />

                @if ($slot->item->crafting_type === 'armour')
                    <input type="hidden" name="item_to_equip_type" value={{$slot->item->type}} />
                @endif
            </form>

            <a class="dropdown-item" href="{{route('game.inventory.compare', ['character' => $character])}}"
               onclick="event.preventDefault();
                   document.getElementById('item-comparison-{{$slot->id}}').submit();">
                {{ __('Equip') }}
            </a>

            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#slot-{{$slot->id}}">Destroy</a>
        @else
            <form id="item-unequip-{{$slot->id}}" action="{{route('game.inventory.unequip', ['character' => $character])}}" method="POST" style="display: none">
                @csrf

                <input type="hidden" name="item_to_remove" value={{$slot->id}} />
            </form>
            <a class="dropdown-item" href="{{route('game.inventory.unequip', ['character' => $character])}}"
               onclick="event.preventDefault();
                   document.getElementById('item-unequip-{{$slot->id}}').submit();">
                {{ __('Unequip') }}
            </a>
        @endif

    </div>
</div>
