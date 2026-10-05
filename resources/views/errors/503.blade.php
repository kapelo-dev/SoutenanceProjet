@extends('errors.layout')

@section('code', '503')
@section('title', 'Service indisponible')
@section('icon', 'ki-setting-2')
@section('color', 'text-primary')
@section('message', "L'application est en maintenance. Veuillez réessayer dans quelques instants.")

@section('actions')
    <a href="{{ url()->current() }}" class="kt-btn kt-btn-primary">
        <i class="ki-filled ki-arrows-circle"></i> Réessayer
    </a>
@endsection
