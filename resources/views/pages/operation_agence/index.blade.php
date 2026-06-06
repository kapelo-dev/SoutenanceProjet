@extends('layouts.demo1.base')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5 lg:items-end">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Opérations en Agence
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Vue d'ensemble de toutes les opérations effectuées en agence
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a class="kt-btn kt-btn-primary" data-kt-modal-toggle="#modal_nouvelle_operation">
                <i class="ki-filled ki-plus"></i>
                Nouvelle Opération
            </a>
        </div>
    </div>
</div>
<!-- End of Container -->
<!-- Container -->
<div class="kt-container-fixed">
    <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-header py-5 flex-wrap gap-2">
            <div class="flex items-center gap-5 ml-auto">
                <label class="kt-input">
                    <i class="ki-filled ki-magnifier"></i>
                    <input data-kt-datatable-search="#transactions_table" placeholder="Rechercher une transaction" type="text" value=""/>
                </label>
                
            </div>
        </div>
        <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border" data-kt-datatable-table="true" id="transactions_table" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="w-[50px] text-center">
                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-check="true" type="checkbox"/>
                                </th>
                                <th class="min-w-[180px] text-center" style="width: 18%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            N° Transaction
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[200px] text-center" style="width: 25%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Client
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[140px] text-center" style="width: 15%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Montant
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[120px] text-center" style="width: 12%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Statut
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[160px] text-center" style="width: 18%;">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">
                                            Date
                                        </span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                            @php
                                $typeLabel = ucfirst($transaction->type ?? 'N/A');
                                $isDepot = $transaction->type === 'depot';
                                $isRetrait = $transaction->type === 'retrait';
                                $amountClass = $isDepot ? 'text-success' : ($isRetrait ? 'text-destructive' : 'text-muted-foreground');
                                $amountSign = $isDepot ? '+' : ($isRetrait ? '-' : '');
                                $statut = $transaction->statut ?? 'N/A';
                                $statutBadgeClass = match ($statut) {
                                    'valide' => 'kt-badge-success',
                                    'en_attente' => 'kt-badge-warning',
                                    'annule', 'annulée', 'echoue', 'échoué' => 'kt-badge-destructive',
                                    default => 'kt-badge-secondary',
                                };
                                $clientNom = $transaction->client_nom ?: ($transaction->agent->nomComplet ?? 'Client inconnu');
                                $clientTel = $transaction->client_telephone ?: ($transaction->agent->telephone ?? '');
                                $avatar = $transaction->agent && $transaction->agent->utilisateur && $transaction->agent->utilisateur->photo_profil
                                    ? asset('storage/' . $transaction->agent->utilisateur->photo_profil)
                                    : asset('assets/media/avatars/300-3.png');
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="{{ $transaction->id }}"/>
                                </td>
                                <td class="text-center">
                                    <div class="flex flex-col gap-0.5 items-center">
                                        <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="{{ route('transactions.show', $transaction->id) }}">
                                            {{ $transaction->reference }}
                                        </a>
                                        <span class="text-xs text-secondary-foreground font-normal">
                                            {{ $typeLabel }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2.5">
                                        <div>
                                            <img class="h-9 rounded-full" src="{{ $avatar }}" alt="{{ $clientNom }}"/>
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="leading-none font-medium text-sm">
                                                {{ $clientNom }}
                                            </span>
                                            @if($clientTel)
                                            <span class="text-xs text-secondary-foreground font-normal">
                                                {{ $clientTel }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center text-foreground font-semibold">
                                    <span class="{{ $amountClass }}">
                                        {{ $amountSign }}{{ number_format($transaction->montant, 0, ',', ' ') }} FCFA
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="kt-badge kt-badge-sm kt-badge-outline {{ $statutBadgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $statut)) }}
                                    </span>
                                </td>
                                <td class="text-center text-foreground font-normal">
                                    {{ optional($transaction->date)->locale('fr')->isoFormat('D MMM Y, HH:mm') }}
                                </td>
                                
                            </tr>
                @empty
                <tr class="empty-row">
                    <td colspan="7" class="text-center py-20 !border-0" style="width: 100% !important; padding: 5rem 0 !important; border: none !important; border-left: none !important; border-right: none !important;">
                        <div class="flex flex-col items-center justify-center gap-5 w-full">
                            <div class="flex items-center justify-center rounded-full bg-gray-100 size-20 dark:bg-gray-900">
                                <i class="ki-filled ki-file text-4xl text-gray-500 dark:text-gray-400"></i>
                            </div>
                            <div class="flex flex-col gap-2 items-center text-center max-w-md">
                                <h3 class="text-lg font-semibold text-foreground">Aucune opération</h3>
                                <p class="text-sm text-secondary-foreground">Il n'y a actuellement aucune opération en agence.</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
                    </table>
                </div>
                <div class="kt-card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-secondary-foreground text-sm font-medium">
                    <div class="flex items-center gap-2 order-2 md:order-1">
                        Afficher
                        <select class="kt-select w-16" data-kt-datatable-size="true" data-kt-select="" name="perpage">
                        </select>
                        par page
                    </div>
                    <div class="flex items-center gap-4 order-1 md:order-2">
                        <span data-kt-datatable-info="true">
                        </span>
                        <div class="kt-datatable-pagination" data-kt-datatable-pagination="true">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End of Container -->
<style>
    /* S'assurer que le message vide est centré sur toute la largeur (même logique que la page transactions) */
    #transactions_table tbody tr.empty-row {
        border: none !important;
        display: table-row !important;
    }
    
    #transactions_table tbody tr.empty-row td {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 100% !important;
        border: none !important;
        border-left: none !important;
        border-right: none !important;
        border-top: none !important;
        border-bottom: none !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        position: relative !important;
    }
    
    /* Masquer toutes les bordures des colonnes pour cette ligne */
    #transactions_table tbody tr.empty-row td,
    #transactions_table tbody tr.empty-row th {
        border: none !important;
    }
    
    /* Forcer le tableau à prendre toute la largeur pour cette ligne */
    #transactions_table {
        width: 100% !important;
    }
    
    #transactions_table tbody tr.empty-row td[colspan="7"] {
        display: table-cell !important;
        width: 100% !important;
    }
    
    /* Cacher le message par défaut du datatable si présent */
    #transactions_table tbody tr td:only-child:not(.empty-row td) {
        display: none;
    }
</style>
<script>
    window.initOperationAgencePage = function() {
        if (!document.getElementById('transactions_table')) return;
        const isEmpty = @json($transactions->isEmpty());
        if (window.AjaxNavigation?.setupEmptyDatatable) {
            window.AjaxNavigation.setupEmptyDatatable('transactions_table', 7, isEmpty);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.initOperationAgencePage());
    } else {
        window.initOperationAgencePage();
    }
    document.addEventListener('ajax-content-loaded', () => {
        if (document.getElementById('transactions_table') && document.getElementById('modal_nouvelle_operation')) {
            window.initOperationAgencePage();
        }
    });
</script>
   <!-- Modal Nouvelle Opération (opérations des agents : versements, ajouts espèces, etc.) -->
   <div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_nouvelle_operation">
      <div class="kt-modal-content max-w-[600px]">
       <div class="kt-modal-header">
        <h3 class="kt-modal-title">
         Nouvelle opération en agence
        </h3>
        <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
         <i class="ki-filled ki-cross"></i>
        </button>
       </div>
       <div class="kt-modal-body">
        <form id="form_nouvelle_operation_agence" class="flex flex-col gap-5">
         <div class="flex flex-col gap-2 relative">
          <label class="kt-label">
           Agent <span class="text-destructive">*</span>
          </label>
          <input class="kt-input" type="text" name="agent_search" id="operation_agent_search" placeholder="Rechercher agent (nom, prénom…)" autocomplete="off" required />
          <input type="hidden" name="agent_id" id="operation_agent_id" value="" />
          <div id="operation_agent_list" class="hidden absolute left-0 right-0 top-full z-20 mt-1 rounded-lg border border-border bg-background shadow-lg max-h-48 overflow-auto"></div>
          <span class="text-xs text-muted-foreground">Agent qui effectue l'opération (versement, ajout espèces, etc.)</span>
         </div>
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           Type d'opération <span class="text-destructive">*</span>
          </label>
          <select class="kt-select" name="type_operation_id" id="operation_type_operation_id" data-kt-select="true" required>
           <option value="" data-requiert-operateur="0">Sélectionnez le type</option>
           @foreach($typesOperation ?? [] as $typeOp)
            <option value="{{ $typeOp->id }}" data-requiert-operateur="{{ $typeOp->requiert_operateur ? '1' : '0' }}">{{ $typeOp->libelle }}</option>
           @endforeach
          </select>
          <span class="text-xs text-muted-foreground">Type sans opérateur figé ; l'opérateur (T-Money, Flooz…) se choisit à part si le type est « virtuel ».</span>
         </div>
         <div class="flex flex-col gap-2 relative" id="operation_operateur_block" style="display: block;">
          <label class="kt-label mb-2" for="operation_operateur_trigger">
           Opérateur (T-Money, Flooz…) <span class="text-destructive" id="operation_operateur_required_star" style="display:none">*</span>
          </label>
          <input type="hidden" name="operateur_id" id="operation_operateur_id" value="" />
          <button type="button" id="operation_operateur_trigger" class="kt-input w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-left flex items-center gap-2 min-h-[40px]" aria-haspopup="listbox" aria-expanded="false">
           <span id="operation_operateur_display" class="flex items-center gap-2 text-muted-foreground">Sélectionnez un opérateur</span>
          </button>
          <div id="operation_operateur_dropdown" class="hidden absolute left-0 right-0 top-full z-30 mt-1 rounded-lg border border-border bg-background shadow-lg max-h-56 overflow-auto py-1">
           <div class="px-3 py-2 text-xs text-muted-foreground border-b border-border">Choisir un opérateur</div>
           @foreach($operateurs ?? [] as $operateur)
            @php $logoUrl = $operateur->logo ? asset('storage/' . $operateur->logo) : ''; @endphp
            <button type="button" class="operation_operateur_option w-full flex items-center gap-2 px-3 py-2.5 text-left hover:bg-muted/50 transition-colors" data-id="{{ $operateur->id }}" data-libelle="{{ e($operateur->libelle) }}" data-logo="{{ $logoUrl }}" data-couleur="{{ $operateur->couleur ?? '#6b7280' }}">
             @if($operateur->logo)
              <span class="h-6 w-6 shrink-0 rounded-full overflow-hidden flex items-center justify-center bg-muted"><img src="{{ $logoUrl }}" alt="{{ $operateur->libelle }}" class="h-full w-full object-cover" /></span>
             @else
              <span class="h-6 w-6 rounded-full flex items-center justify-center text-xs font-semibold text-white shrink-0" style="background-color: {{ $operateur->couleur ?? '#6b7280' }}">{{ strtoupper(substr($operateur->libelle ?? '', 0, 1)) }}</span>
             @endif
             <span class="text-sm">{{ $operateur->libelle }}</span>
            </button>
           @endforeach
           @if(empty($operateurs) || count($operateurs) === 0)
            <div class="px-3 py-2 text-xs text-muted-foreground">Aucun opérateur configuré.</div>
           @endif
          </div>
          @if(empty($operateurs) || count($operateurs) === 0)
          <span class="text-xs text-muted-foreground">Aucun opérateur configuré. Allez dans « Opérateurs Mobile Money » pour en ajouter.</span>
          @else
          <span class="text-xs text-muted-foreground">Requis pour « Apport virtuel » et « Retrait virtuel ». Optionnel pour les autres types.</span>
          @endif
         </div>
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           Montant (FCFA) <span class="text-destructive">*</span>
          </label>
          <input class="kt-input" type="number" name="montant" id="operation_montant" placeholder="0" min="0" step="1" value="0" required />
         </div>
         <div class="flex flex-col gap-2">
          <label class="kt-label">
           Note (optionnel)
          </label>
          <textarea class="kt-input" name="note" id="operation_note" rows="3" placeholder="Commentaire"></textarea>
         </div>
        </form>
       </div>
       <div class="kt-modal-footer">
        <button class="kt-btn kt-btn-ghost" data-kt-modal-dismiss="true">
         Annuler
        </button>
        <button class="kt-btn kt-btn-primary" type="button" id="btn_enregistrer_operation">
         <i class="ki-filled ki-check"></i>
         Enregistrer
        </button>
       </div>
      </div>
     </div>
   <!-- End Modal Nouvelle Opération -->

<script>
// Fonction globale d'initialisation pour éviter les doublons
window.initOperationAgenceModal = window.initOperationAgenceModal || function() {
    console.log('Initialisation du modal Opération Agence');
    
    var agentsData = @json($agentsJson ?? []);
    if (!Array.isArray(agentsData)) agentsData = [];
    
    var agentSearchEl = document.getElementById('operation_agent_search');
    var agentIdEl = document.getElementById('operation_agent_id');
    var agentListEl = document.getElementById('operation_agent_list');
    var operateurTrigger = document.getElementById('operation_operateur_trigger');
    var operateurDropdown = document.getElementById('operation_operateur_dropdown');
    var operateurDisplay = document.getElementById('operation_operateur_display');
    var operateurInput = document.getElementById('operation_operateur_id');
    var typeSelectEl = document.getElementById('operation_type_operation_id');
    var btnSave = document.getElementById('btn_enregistrer_operation');
    
    if (!agentSearchEl || !operateurTrigger) {
        console.warn('Éléments du modal non trouvés');
        return;
    }
    
    // Éviter double-init sur le même élément DOM
    if (agentSearchEl._hasOperationListener) return;
    agentSearchEl._hasOperationListener = true;
    
    // 1. Recherche d'agent
    agentSearchEl.addEventListener('input', function() {
        var q = (this.value || '').toLowerCase().trim();
        if (agentIdEl) agentIdEl.value = '';
        if (!agentListEl) return;
        if (q.length < 2) {
            agentListEl.classList.add('hidden');
            agentListEl.innerHTML = '';
            return;
        }
        var filtered = agentsData.filter(function(a) {
            return (a.libelle && a.libelle.toLowerCase().indexOf(q) !== -1) ||
                (a.nom && a.nom.toLowerCase().indexOf(q) !== -1) ||
                (a.prenom && a.prenom.toLowerCase().indexOf(q) !== -1);
        });
        agentListEl.innerHTML = filtered.slice(0, 10).map(function(a) {
            return '<div class="px-3 py-2 cursor-pointer hover:bg-muted border-b border-border last:border-0" data-agent-id="' + a.id + '" data-agent-libelle="' + (a.libelle || '').replace(/"/g, '&quot;') + '">' + (a.libelle || 'Agent #' + a.id) + '</div>';
        }).join('');
        agentListEl.classList.remove('hidden');
        if (filtered.length === 0) agentListEl.classList.add('hidden');
    });
    
    // 2. Sélection d'un agent
    if (agentListEl) {
        agentListEl.addEventListener('click', function(e) {
            var row = e.target.closest('[data-agent-id]');
            if (row) {
                if (agentIdEl) agentIdEl.value = row.getAttribute('data-agent-id');
                if (agentSearchEl) agentSearchEl.value = row.getAttribute('data-agent-libelle') || '';
                agentListEl.classList.add('hidden');
                agentListEl.innerHTML = '';
            }
        });
    }
    
    // 3. Dropdown opérateur - bouton toggle
    if (operateurTrigger && operateurDropdown) {
        operateurTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            operateurDropdown.classList.toggle('hidden');
            operateurTrigger.setAttribute('aria-expanded', operateurDropdown.classList.contains('hidden') ? 'false' : 'true');
        });
    }
    
    // 4. Sélection d'un opérateur
    if (operateurDropdown) {
        var options = operateurDropdown.querySelectorAll('.operation_operateur_option');
        options.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.getAttribute('data-id');
                var libelle = this.getAttribute('data-libelle');
                var logo = this.getAttribute('data-logo');
                var couleur = this.getAttribute('data-couleur') || '#6b7280';
                if (operateurInput) operateurInput.value = id;
                if (operateurDisplay) {
                    if (logo) {
                        operateurDisplay.innerHTML = '<span class="h-5 w-5 shrink-0 rounded-full overflow-hidden flex items-center justify-center bg-muted"><img src="' + logo + '" alt="" class="h-full w-full object-cover" /></span><span class="text-foreground">' + libelle + '</span>';
                    } else {
                        operateurDisplay.innerHTML = '<span class="h-5 w-5 rounded-full flex items-center justify-center text-xs font-semibold text-white shrink-0" style="background-color:' + couleur + '">' + (libelle ? libelle.charAt(0).toUpperCase() : '') + '</span><span class="text-foreground">' + libelle + '</span>';
                    }
                    operateurDisplay.classList.remove('text-muted-foreground');
                }
                operateurDropdown.classList.add('hidden');
                if (operateurTrigger) operateurTrigger.setAttribute('aria-expanded', 'false');
            });
        });
    }
    
    // 5. Fermer en cliquant dehors (listener unique, pas de duplication AJAX)
    if (!window._operationAgenceOutsideClickHandler) {
        window._operationAgenceOutsideClickHandler = function(e) {
            var agentList = document.getElementById('operation_agent_list');
            var agentSearch = document.getElementById('operation_agent_search');
            var opTrigger = document.getElementById('operation_operateur_trigger');
            var opDropdown = document.getElementById('operation_operateur_dropdown');
            if (agentList && agentSearch && !agentSearch.contains(e.target) && !agentList.contains(e.target)) {
                agentList.classList.add('hidden');
            }
            if (opTrigger && opDropdown && !opTrigger.contains(e.target) && !opDropdown.contains(e.target)) {
                opDropdown.classList.add('hidden');
                opTrigger.setAttribute('aria-expanded', 'false');
            }
        };
        document.addEventListener('click', window._operationAgenceOutsideClickHandler);
    }
    
    // 6. Toggle opérateur requis
    function toggleOperateurBlock() {
        var operateurSelect = document.getElementById('operation_operateur_id');
        var star = document.getElementById('operation_operateur_required_star');
        if (!typeSelectEl || !operateurSelect) return;
        var opt = typeSelectEl.options[typeSelectEl.selectedIndex];
        var requiert = opt && opt.getAttribute('data-requiert-operateur') === '1';
        if (requiert) {
            operateurSelect.setAttribute('required', 'required');
            if (star) star.style.display = '';
        } else {
            operateurSelect.removeAttribute('required');
            operateurSelect.value = '';
            if (star) star.style.display = 'none';
        }
    }
    if (typeSelectEl) {
        typeSelectEl.addEventListener('change', toggleOperateurBlock);
        toggleOperateurBlock();
    }
    
    // 7. Bouton enregistrer
    if (btnSave) {
        btnSave.addEventListener('click', function() {
            var formOp = document.getElementById('form_nouvelle_operation_agence');
            var modalOp = document.getElementById('modal_nouvelle_operation');
            var agId = document.getElementById('operation_agent_id') ? document.getElementById('operation_agent_id').value : '';
            var typeId = document.getElementById('operation_type_operation_id') ? document.getElementById('operation_type_operation_id').value : '';
            var mont = document.getElementById('operation_montant') ? document.getElementById('operation_montant').value : '';
            var opId = document.getElementById('operation_operateur_id') ? document.getElementById('operation_operateur_id').value : '';
            var typeOp = document.getElementById('operation_type_operation_id');
            var typeOpt = typeOp ? typeOp.selectedOptions[0] : null;
            var req = typeOpt && typeOpt.getAttribute('data-requiert-operateur') === '1';
            if (!agId) { alert('Veuillez sélectionner un agent.'); return; }
            if (!typeId) { alert('Veuillez sélectionner un type d\'opération.'); return; }
            if (req && !opId) { alert('Veuillez sélectionner un opérateur.'); return; }
            if (!mont || parseFloat(mont) <= 0) { alert('Veuillez saisir un montant valide.'); return; }
            var note = document.getElementById('operation_note') ? document.getElementById('operation_note').value : '';
            var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
            var url = '{{ route("operations-agence.store") }}';
            btnSave.disabled = true;
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ agent_id: agId, type_operation_id: typeId, operateur_id: opId || null, montant: parseFloat(mont), note: note })
            })
            .then(function(r) { return r.text().then(function(t) { try { return { ok: r.ok, data: JSON.parse(t) }; } catch (e) { return { ok: false, data: { message: 'Erreur serveur.' } }; } }); })
            .then(function(res) {
                btnSave.disabled = false;
                if (res.ok && res.data.success) {
                    alert(res.data.message || 'Opération enregistrée avec succès.');
                    if (modalOp) { var d = modalOp.querySelector('[data-kt-modal-dismiss="true"]'); if (d) d.click(); }
                    if (formOp) formOp.reset();
                    if (document.getElementById('operation_agent_id')) document.getElementById('operation_agent_id').value = '';
                    if (document.getElementById('operation_operateur_id')) document.getElementById('operation_operateur_id').value = '';
                    if (operateurDisplay) { operateurDisplay.innerHTML = 'Sélectionnez un opérateur'; operateurDisplay.classList.add('text-muted-foreground'); }
                    window.location.reload();
                } else {
                    alert((res.data && res.data.message) || 'Erreur lors de l\'enregistrement.');
                }
            })
            .catch(function() { btnSave.disabled = false; alert('Erreur réseau.'); });
        });
    }
};

// Appeler immédiatement
window.initOperationAgenceModal();
</script>
@endsection