@extends('errors.layout')

@section('code', '419')
@section('title', 'Session expirée')
@section('icon', 'ki-time')
@section('color', 'text-warning')
@section('message', 'Votre session a expiré. Veuillez actualiser la page et réessayer.')

@section('actions')
    <a href="{{ url()->previous() }}" class="kt-btn kt-btn-outline">
        <i class="ki-filled ki-arrows-circle"></i> Actualiser
    </a>
    <a href="{{ route('login') }}" class="kt-btn kt-btn-primary">
        <i class="ki-filled ki-entrance-left"></i> Se reconnecter
    </a>
@endsection
