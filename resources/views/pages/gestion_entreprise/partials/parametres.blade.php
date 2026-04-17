<!-- Actions -->
<div class="flex flex-wrap items-center gap-3 mb-5">
    <button type="button" class="kt-btn kt-btn-primary" data-kt-modal-toggle="#modal_nouveau_parametre">
        <i class="ki-filled ki-plus me-2"></i>
        Nouveau paramètre
    </button>
</div>

<!-- Liste des paramètres -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    @forelse($parametres as $parametre)
        <div class="kt-card {{ !$parametre->actif ? 'opacity-60' : '' }}">
            <div class="kt-card-content p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-mono mb-1">{{ $parametre->nom }}</h3>
                        <div class="flex items-center gap-2">
                            @if($parametre->type === 'fixe')
                                <span class="kt-badge kt-badge-sm kt-badge-info">Fixe</span>
                            @elseif($parametre->type === 'commission')
                                <span class="kt-badge kt-badge-sm kt-badge-success">Commission</span>
                            @else
                                <span class="kt-badge kt-badge-sm kt-badge-warning">Mixte</span>
                            @endif
                            @if($parametre->actif)
                                <span class="kt-badge kt-badge-sm kt-badge-success">Actif</span>
                            @else
                                <span class="kt-badge kt-badge-sm kt-badge-secondary">Inactif</span>
                            @endif
                        </div>
                    </div>
                    @if($parametre->profils->isNotEmpty())
                        <div class="text-xs text-muted-foreground mb-2">
                            Destiné à : {{ $parametre->profils->pluck('libelle')->join(', ') }}
                        </div>
                    @else
                        <div class="text-xs text-muted-foreground mb-2">Tous les profils</div>
                    @endif
                    <div class="flex items-center gap-1">
                        <button type="button" 
                                class="kt-btn kt-btn-xs kt-btn-icon kt-btn-outline"
                                onclick="editParametre({{ json_encode($parametre) }})">
                            <i class="ki-filled ki-notepad-edit"></i>
                        </button>
                        <form action="{{ route('gestion-entreprise.parametres.delete', $parametre) }}" 
                              method="POST" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce paramètre ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="kt-btn kt-btn-xs kt-btn-icon kt-btn-outline kt-btn-danger">
                                <i class="ki-filled ki-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="space-y-2">
                    @if($parametre->montant_fixe > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Montant fixe:</span>
                            <span class="font-semibold">{{ number_format($parametre->montant_fixe, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif

                    @if($parametre->taux_commission > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Taux commission:</span>
                            <span class="font-semibold">{{ $parametre->taux_commission }}%</span>
                        </div>
                    @endif

                    @if($parametre->base_calcul)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Base de calcul:</span>
                            <span class="text-sm">{{ ucfirst($parametre->base_calcul) }}</span>
                        </div>
                    @endif

                    @if($parametre->formule)
                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-xs text-muted-foreground mb-1">Formule personnalisée:</div>
                            <code class="text-xs bg-muted/20 px-2 py-1 rounded">{{ $parametre->formule }}</code>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-2">
            <div class="kt-card">
                <div class="kt-card-content p-10 text-center">
                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-3"></i>
                    <p class="text-muted-foreground">Aucun paramètre de salaire défini. Créez-en un pour commencer.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Modal: Nouveau/Éditer Paramètre -->
<div class="kt-modal" data-kt-modal="true" id="modal_nouveau_parametre">
    <div class="kt-modal-content max-w-3xl">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="modal_parametre_title">Nouveau paramètre de salaire</h3>
            <button class="kt-modal-close" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>

        <form id="form_parametre" action="{{ route('gestion-entreprise.parametres.store') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" id="parametre_method" name="_method" value="POST">
            
            <div class="kt-modal-body">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-2 lg:col-span-2">
                        <label class="text-sm font-medium">
                            Nom du paramètre
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nom" id="parametre_nom" class="kt-input" 
                               placeholder="Ex: Salaire Agent Standard" required>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">
                            Type
                            <span class="text-danger">*</span>
                        </label>
                        <select name="type" id="parametre_type" class="kt-select" data-kt-select="true">
                            <option value="fixe">Fixe uniquement</option>
                            <option value="commission">Commission uniquement</option>
                            <option value="mixte">Mixte (Fixe + Commission)</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-foreground">Statut</span>
                        <div class="flex items-center gap-3 p-3 rounded-lg border-2 border-border bg-muted/30">
                            <label class="flex items-center gap-2 cursor-pointer shrink-0">
                                <input type="checkbox" name="actif" id="parametre_actif" value="1" checked class="kt-switch kt-switch-lg">
                                <span class="kt-switch-label text-sm font-semibold text-foreground">Actif</span>
                            </label>
                        </div>
                        <span class="text-xs text-muted-foreground">Inactif = exclu de la génération des salaires.</span>
                    </div>

                    <div class="flex flex-col gap-2 lg:col-span-2">
                        <span class="text-sm font-medium text-foreground">Profils destinataires</span>
                        <p class="text-xs text-muted-foreground">Choisir les profils (rôles) pour lesquels ce paramètre s'applique. Aucune sélection = tous les profils.</p>
                        <div class="flex flex-wrap gap-3 p-4 rounded-lg border border-border bg-muted/20">
                            @forelse($profils ?? [] as $profil)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="profil_ids[]" value="{{ $profil->id }}" class="kt-checkbox profil-destinataire" id="profil_param_{{ $profil->id }}">
                                    <span class="text-sm font-medium">{{ $profil->libelle }}</span>
                                </label>
                            @empty
                                <span class="text-sm text-muted-foreground">Aucun profil défini.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex flex-col gap-2" id="field_montant_fixe">
                        <label class="text-sm font-medium">
                            Montant fixe (FCFA)
                        </label>
                        <input type="number" name="montant_fixe" id="parametre_montant_fixe" 
                               class="kt-input" min="0" step="1000" value="0">
                    </div>

                    <div class="flex flex-col gap-2" id="field_taux_commission">
                        <label class="text-sm font-medium">
                            Taux de commission (%)
                        </label>
                        <input type="number" name="taux_commission" id="parametre_taux_commission" 
                               class="kt-input" min="0" max="100" step="0.1" value="0">
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">
                            Base de calcul
                        </label>
                        <select name="base_calcul" id="parametre_base_calcul" class="kt-select" data-kt-select="true">
                            <option value="">Sélectionner...</option>
                            <option value="transactions">Total des transactions (% commission)</option>
                            <option value="commissions">Somme des commissions des transactions</option>
                            <option value="soldes">Évolution des soldes</option>
                            <option value="objectifs">Atteinte d'objectifs</option>
                        </select>
                        <div id="base_calcul_help" class="kt-alert kt-alert-info mt-2" style="display: none;">
                            <i class="ki-filled ki-information-2"></i>
                            <span id="base_calcul_help_text"></span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">
                            Conditions (JSON optionnel)
                        </label>
                        <input type="text" name="conditions" id="parametre_conditions" 
                               class="kt-input" placeholder='{"min_transactions": 100}'>
                        <span class="text-xs text-muted-foreground">Format JSON pour conditions avancées</span>
                    </div>

                    <div class="flex flex-col gap-2 lg:col-span-2">
                        <label class="text-sm font-medium">
                            Formule personnalisée (optionnel)
                        </label>
                        <div class="flex flex-wrap items-end gap-2 p-3 rounded-lg bg-muted/20 border border-border">
                            <span class="text-xs font-medium text-muted-foreground w-full mb-1">Construire la formule :</span>
                            <select id="formule_builder_variable" class="kt-select flex-1 min-w-[180px]" data-kt-select="true">
                                <option value="">Variable...</option>
                                <option value="montant_transactions">montant_transactions (total des montants)</option>
                                <option value="nb_transactions">nb_transactions (nombre de transactions)</option>
                                <option value="commissions">commissions (somme des commissions des transactions)</option>
                                <option value="montant_fixe">montant_fixe (salaire fixe)</option>
                                <option value="taux_commission">taux_commission (% commission)</option>
                                <option value="solde_final">solde_final (solde agent)</option>
                                <option value="objectif_atteint">objectif_atteint (0 ou 1)</option>
                            </select>
                            <select id="formule_builder_operator" class="kt-select w-20 shrink-0" data-kt-select="true">
                                <option value="+">+</option>
                                <option value="-">−</option>
                                <option value="*">×</option>
                                <option value="/">/</option>
                                <option value="(">(</option>
                                <option value=")">)</option>
                            </select>
                            <input type="text" id="formule_builder_value" class="kt-input w-24 shrink-0" 
                                   placeholder="0.02" title="Constante ou nombre">
                            <button type="button" id="formule_builder_add" class="kt-btn kt-btn-sm kt-btn-primary shrink-0">
                                <i class="ki-filled ki-plus me-1"></i>Ajouter
                            </button>
                        </div>
                        <textarea name="formule" id="parametre_formule" class="kt-textarea mt-2" rows="3"
                                  placeholder="Ex: montant_fixe + commissions ou (montant_transactions * taux_commission / 100)"></textarea>
                        <span class="text-xs text-muted-foreground">
                            Variables: montant_transactions, nb_transactions, commissions (somme des commissions des transactions), solde_final, montant_fixe, taux_commission — ou construire ci-dessus.
                        </span>
                    </div>
                </div>

                <div class="kt-alert kt-alert-info mt-5">
                    <i class="ki-filled ki-information-2"></i>
                    <span>La formule personnalisée remplacera le calcul par défaut si elle est définie.</span>
                </div>
            </div>

            <div class="kt-modal-footer">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">
                    Annuler
                </button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-check me-1"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle fields based on type
(function initParametresForm() {
    const typeSelect = document.getElementById('parametre_type');
    const montantFixeField = document.getElementById('field_montant_fixe');
    const commissionField = document.getElementById('field_taux_commission');

    function toggleFields() {
        const type = typeSelect.value;
        
        if (type === 'fixe') {
            montantFixeField.style.display = 'block';
            commissionField.style.display = 'none';
        } else if (type === 'commission') {
            montantFixeField.style.display = 'none';
            commissionField.style.display = 'block';
        } else {
            montantFixeField.style.display = 'block';
            commissionField.style.display = 'block';
        }
    }

    if (typeSelect) {
        // Retirer les anciens listeners pour éviter les doublons
        typeSelect.removeEventListener('change', toggleFields);
        typeSelect.addEventListener('change', toggleFields);
        toggleFields();
    }

    // Aide dynamique pour la base de calcul
    const baseCalculSelect = document.getElementById('parametre_base_calcul');
    const baseCalculHelp = document.getElementById('base_calcul_help');
    const baseCalculHelpText = document.getElementById('base_calcul_help_text');

    const baseCalculExplanations = {
        'transactions': 'Le taux de commission sera appliqué sur le <strong>montant total des transactions</strong> effectuées par l\'agent. Exemple : Si l\'agent fait 1 000 000 FCFA de transactions avec un taux de 2%, il gagne 20 000 FCFA.',
        'commissions': 'Le taux de commission sera appliqué sur la <strong>somme des commissions déjà perçues</strong> par l\'agent. Exemple : Si l\'agent a perçu 50 000 FCFA de commissions avec un taux de 10%, il gagne 5 000 FCFA supplémentaires.',
        'soldes': 'Le taux de commission sera appliqué sur l\'<strong>évolution du solde</strong> de l\'agent. Exemple : Si le solde augmente de 200 000 FCFA avec un taux de 5%, il gagne 10 000 FCFA.',
        'objectifs': 'Un <strong>bonus fixe</strong> sera versé si l\'objectif est atteint. Exemple : Si l\'objectif de 100 transactions est atteint, l\'agent reçoit le montant fixe défini.'
    };

    function updateBaseCalculHelp() {
        const value = baseCalculSelect.value;
        if (value && baseCalculExplanations[value]) {
            baseCalculHelpText.innerHTML = baseCalculExplanations[value];
            baseCalculHelp.style.display = 'flex';
        } else {
            baseCalculHelp.style.display = 'none';
        }
    }

    if (baseCalculSelect) {
        baseCalculSelect.removeEventListener('change', updateBaseCalculHelp);
        baseCalculSelect.addEventListener('change', updateBaseCalculHelp);
        updateBaseCalculHelp();
    }

    // Constructeur de formule : ajouter variable + opérateur + valeur dans le textarea
    const formuleTextarea = document.getElementById('parametre_formule');
    const builderVariable = document.getElementById('formule_builder_variable');
    const builderOperator = document.getElementById('formule_builder_operator');
    const builderValue = document.getElementById('formule_builder_value');
    const builderAdd = document.getElementById('formule_builder_add');

    if (builderAdd && formuleTextarea) {
        // Créer une fonction nommée pour pouvoir la retirer
        const handleBuilderAdd = function() {
            const variable = builderVariable && builderVariable.value ? builderVariable.value.trim() : '';
            const operator = builderOperator && builderOperator.value ? builderOperator.value : '';
            const value = builderValue && builderValue.value ? builderValue.value.trim() : '';
            let toAppend = '';
            if (variable) toAppend += variable + ' ';
            if (operator) toAppend += operator + (operator === '(' || operator === ')' ? '' : ' ');
            if (value) toAppend += value + ' ';
            if (toAppend) {
                const start = formuleTextarea.selectionStart;
                const end = formuleTextarea.selectionEnd;
                const before = formuleTextarea.value.substring(0, start);
                const after = formuleTextarea.value.substring(end);
                formuleTextarea.value = before + toAppend + after;
                formuleTextarea.selectionStart = formuleTextarea.selectionEnd = before.length + toAppend.length;
                formuleTextarea.focus();
            }
        };
        
        // Retirer l'ancien listener pour éviter les doublons
        builderAdd.removeEventListener('click', handleBuilderAdd);
        builderAdd.addEventListener('click', handleBuilderAdd);
    }
})();

function editParametre(parametre) {
    const form = document.getElementById('form_parametre');
    const modal = document.getElementById('modal_nouveau_parametre');
    
    // Update form action and method
    form.action = `/gestion-entreprise/parametres/${parametre.id}`;
    document.getElementById('parametre_method').value = 'PUT';
    
    // Update modal title
    document.getElementById('modal_parametre_title').textContent = 'Modifier le paramètre';
    
    // Fill form fields
    document.getElementById('parametre_nom').value = parametre.nom;
    document.getElementById('parametre_type').value = parametre.type;
    document.getElementById('parametre_montant_fixe').value = parametre.montant_fixe;
    document.getElementById('parametre_taux_commission').value = parametre.taux_commission;
    document.getElementById('parametre_base_calcul').value = parametre.base_calcul || '';
    document.getElementById('parametre_formule').value = parametre.formule || '';
    document.getElementById('parametre_conditions').value = parametre.conditions ? JSON.stringify(parametre.conditions) : '';
    document.getElementById('parametre_actif').checked = parametre.actif;

    // Profils destinataires
    var profilIds = parametre.profils && parametre.profils.map(function(p) { return String(p.id); }) || [];
    document.querySelectorAll('input[name="profil_ids[]"]').forEach(function(cb) {
        cb.checked = profilIds.indexOf(cb.value) !== -1;
    });
    
    // Trigger change event to show/hide fields
    document.getElementById('parametre_type').dispatchEvent(new Event('change'));
    
    // Open modal
    if (window.KTModal) {
        const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
        modalInstance.show();
    }
}

// Validation avant envoi (évite "invalid form control is not focusable" sur les kt-select)
(function initFormValidation() {
    const formParametre = document.getElementById('form_parametre');
    if (formParametre) {
        const handleSubmit = function(e) {
            const nom = document.getElementById('parametre_nom');
            const typeSelect = document.getElementById('parametre_type');
            const nomVal = nom && nom.value ? nom.value.trim() : '';
            const typeVal = typeSelect && typeSelect.value ? typeSelect.value : '';
            if (!nomVal) {
                e.preventDefault();
                nom.focus();
                alert('Veuillez saisir le nom du paramètre.');
                return;
            }
            if (!typeVal) {
                e.preventDefault();
                if (typeSelect) typeSelect.focus();
                alert('Veuillez sélectionner un type (Fixe, Commission ou Mixte).');
                return;
            }
        };
        
        formParametre.removeEventListener('submit', handleSubmit);
        formParametre.addEventListener('submit', handleSubmit);
    }
})();

// Reset form when modal closes
(function initModalReset() {
    const modal = document.getElementById('modal_nouveau_parametre');
    if (modal) {
        const handleHidden = function() {
            const form = document.getElementById('form_parametre');
            if (form) {
                form.reset();
                form.action = '{{ route("gestion-entreprise.parametres.store") }}';
                const methodInput = document.getElementById('parametre_method');
                if (methodInput) methodInput.value = 'POST';
                const titleEl = document.getElementById('modal_parametre_title');
                if (titleEl) titleEl.textContent = 'Nouveau paramètre de salaire';
            }
        };
        
        modal.removeEventListener('hidden.kt.modal', handleHidden);
        modal.addEventListener('hidden.kt.modal', handleHidden);
    }
})();
</script>
