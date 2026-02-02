@extends('layouts.demo1.base')

@section('content')
<div class="kt-container-fixed">
    <!-- Header -->
    <div class="flex items-center justify-between mb-7.5">
        <h1 class="text-2xl font-bold text-mono">Gestion d'Entreprise</h1>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="kt-alert kt-alert-success mb-5">
            <i class="ki-filled ki-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-alert kt-alert-danger mb-5">
            <i class="ki-filled ki-information-2"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Tabs -->
    <div class="kt-card">
        

        <div class="kt-card-content p-5 lg:p-7.5">
            @if($onglet === 'salaires')
                @include('pages.gestion_entreprise.partials.salaires')
            @elseif($onglet === 'parametres')
                @include('pages.gestion_entreprise.partials.parametres')
            @elseif($onglet === 'tresorerie')
                @include('pages.gestion_entreprise.partials.tresorerie')
            @endif
        </div>
    </div>
</div>
@endsection
