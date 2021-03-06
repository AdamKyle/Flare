@extends('layouts.app')

@section('content')
    <div class="justify-content-center">
        <x-core.page-title
            title="Previous Adventures"
            route="{{route('game')}}"
            link="Game"
            color="primary"
        ></x-core.page-title>
        @livewire('character.adventures.data-table', [
            'adventureLogs' => $logs,
            'character'     => $character,
        ])
    </div>
@endsection
