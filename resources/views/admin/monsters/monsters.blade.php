@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-6 align-self-left">
            <h4 class="mt-3">Monsters</h4>
        </div>
        <div class="col-md-6 align-self-right">
            <a href="{{route('home')}}" class="btn btn-success float-right ml-2">Home</a>
            <a href="{{route('monsters.create')}}" class="btn btn-primary float-right ml-2">Create</a>
        </div>
    </div>
    <hr />
    @livewire('admin.monsters.data-table')

    <div class="mb-2">
        <h5>Awaiting approval</h5>
        @livewire('admin.monsters.data-table', [
            'published' => false
        ])
    </div>
</div>
@endsection
