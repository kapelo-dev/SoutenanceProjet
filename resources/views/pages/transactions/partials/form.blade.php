{{-- Champs communs à la création et à la modification d'une transaction --}}
@php
    $transaction = $transaction ?? null;
    $valeur = fn (string $champ, $defaut = null) => old($champ, $transaction?->{$champ} ?? $defaut);
@endphp

@if($errors->any())
    <div class="kt-alert kt-alert-destructive mb-5">
        <div class="kt-alert-title">Le formulaire contient des erreurs :</div>
        <ul class="list-disc ps-5 text-sm">
            @foreach($errors->all() as $erreur)
                <li>{{ $erreur }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="flex flex-col gap-1">
        <label class="kt-label" for="agent_id">Agent <span class="text-destructive">*</span></label>
        <select class="kt-select" name="agent_id" id="agent_id" required>
            <option value="">Sélectionner un agent</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}" @selected((string) $valeur('agent_id') === (string) $agent->id)>
                    {{ $agent->nomComplet }}{{ $agent->code_agent ? ' (' . $agent->code_agent . ')' : '' }}{{ $agent->kiosque ? ' — ' . $agent->kiosque->nom : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex flex-col gap-1">
        <label class="kt-label" for="operateur_id">Opérateur <span class="text-destructive">*</span></label>
        <select class="kt-select" name="operateur_id" id="operateur_id" required>
            <option value="">Sélectionner un opérateur</option>
            @foreach($operateurs as $operateur)
                <option value="{{ $operateur->id }}" @selected((string) $valeur('operateur_id') === (string) $operateur->id)>
                    {{ $operateur->libelle }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex flex-col gap-1">
        <label class="kt-label" for="type">Type <span class="text-destructive">*</span></label>
        <select class="kt-select" name="type" id="type" required>
            @foreach(['depot' => 'Dépôt', 'retrait' => 'Retrait', 'transfert' => 'Transfert', 'paiement' => 'Paiement'] as $code => $libelle)
                <option value="{{ $code }}" @selected($valeur('type', 'depot') === $code)>{{ $libelle }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex flex-col gap-1">
        <label class="kt-label" for="statut">Statut <span class="text-destructive">*</span></label>
        <select class="kt-select" name="statut" id="statut" required>
            @foreach(['en_attente' => 'En attente', 'valide' => 'Validée', 'annule' => 'Annulée', 'echoue' => 'Échouée'] as $code => $libelle)
                <option value="{{ $code }}" @selected($valeur('statut', 'en_attente') === $code)>{{ $libelle }}</option>
            @endforeach
        </select>
        <span class="text-xs text-secondary-foreground">Une transaction validée met à jour les soldes de l'agent et ne peut plus être modifiée.</span>
    </div>

    <div class="flex flex-col gap-1">
        <label class="kt-label" for="montant">Montant (FCFA) <span class="text-destructive">*</span></label>
        <input class="kt-input" type="number" name="montant" id="montant" min="0.01" step="0.01" required
               value="{{ $valeur('montant') }}" />
    </div>

    <div class="flex flex-col gap-1">
        <label class="kt-label" for="commission">Commission (FCFA)</label>
        <input class="kt-input" type="number" name="commission" id="commission" min="0" step="0.01"
               value="{{ $valeur('commission') }}" />
    </div>

    <div class="flex flex-col gap-1">
        <label class="kt-label" for="client_nom">Nom du client</label>
        <input class="kt-input" type="text" name="client_nom" id="client_nom" maxlength="100"
               value="{{ $valeur('client_nom') }}" />
    </div>

    <div class="flex flex-col gap-1">
        <label class="kt-label" for="client_telephone">Téléphone du client</label>
        <input class="kt-input" type="text" name="client_telephone" id="client_telephone" maxlength="20"
               value="{{ $valeur('client_telephone') }}" />
    </div>

    <div class="flex flex-col gap-1 md:col-span-2">
        <label class="kt-label" for="description">Description</label>
        <textarea class="kt-textarea" name="description" id="description" rows="3" maxlength="500">{{ $valeur('description') }}</textarea>
    </div>
</div>
