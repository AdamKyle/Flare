@extends('layouts.information', [
    'pageTitle' => 'Skill'
])

@section('content')
    <div class="mt-5">
        @include('admin.skills.skill', [
            'skill' => $skill,
            'customClass' => 'mt-5'
        ])
    </div>
@endsection
