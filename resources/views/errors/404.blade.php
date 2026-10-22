@extends('layouts.app')

@section('title', 'Nav atrasts')

@section('content')
    <div class="card empty-state">
        <h1>Lapa vai filma netika atrasta</h1>
        <p class="muted">Iespējams, saite ir nepareiza vai filma vairs nav pieejama OMDb datubāzē.</p>
        <a href="{{ route('search') }}" class="button">Uz meklēšanu</a>
    </div>
@endsection
