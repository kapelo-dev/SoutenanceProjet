@extends('layouts.demo1.base')

@section('content')
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">Nouvelle transaction</h1>
            <div class="text-sm text-secondary-foreground">Saisie manuelle d'une opération Mobile Money.</div>
        </div>
        <a href="{{ route('transactions.index') }}" class="kt-btn kt-btn-outline">
            <i class="ki-filled ki-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>

<div class="kt-container-fixed">
    <div class="kt-card">
        <form method="POST" action="{{ route('transactions.store') }}">
            @csrf
            <div class="kt-card-content p-5 lg:p-7.5">
                @include('pages.transactions.partials.form')
            </div>
            <div class="kt-card-footer flex justify-end gap-2.5">
                <a href="{{ route('transactions.index') }}" class="kt-btn kt-btn-outline">Annuler</a>
                <button type="submit" class="kt-btn kt-btn-primary">Enregistrer la transaction</button>
            </div>
        </form>
    </div>
</div>
@endsection
