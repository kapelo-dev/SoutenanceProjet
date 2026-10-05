@extends('errors.layout')

@section('code', '401')
@section('title', 'Non authentifié')
@section('icon', 'ki-lock')
@section('color', 'text-warning')
@section('message', 'Vous devez être connecté pour accéder à cette page.')

@section('actions')
    <a href="{{ route('login') }}" class="kt-btn kt-btn-primary">
        <i class="ki-filled ki-entrance-left"></i> Se connecter
    </a>
@endsection
