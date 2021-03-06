<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row pb-2">
                    <x-data-tables.per-page wire:model="perPage" />
                    <x-data-tables.search wire:model="search" />
                </div>
                <x-data-tables.table :collection="$maps">
                    <x-data-tables.header>
                        <x-data-tables.header-row 
                            wire:click.prevent="sortBy('name')" 
                            header-text="Name" 
                            sort-by="{{$sortBy}}"
                            sort-field="{{$sortField}}"
                            field="name"
                        />
                        <x-data-tables.header-row 
                            wire:click.prevent="sortBy('default')" 
                            header-text="Default Starting Map" 
                            sort-by="{{$sortBy}}"
                            sort-field="{{$sortField}}"
                            field="default"
                        />
                        <x-data-tables.header-row 
                            wire:click.prevent="sortBy('characters_using')" 
                            header-text="Characters Using" 
                            sort-by="{{$sortBy}}"
                            sort-field="{{$sortField}}"
                            field="characters_using"
                        />
                    </x-data-tables.header>
                    <x-data-tables.body>
                        @forelse($maps as $map)
                            <tr>
                                <td>
                                   {{$map->name}}
                                </td>
                                <td>{{$map->default ? 'Yes' : 'No'}}</td>
                                <td>{{$map->characters_using}}</td>
                            </tr>
                        @empty
                            <x-data-tables.no-results colspan="3" />
                        @endforelse
                    </x-data-tables.body>
                </x-data-tables.table>
            </div>
        </div>
    </div>
</div>
