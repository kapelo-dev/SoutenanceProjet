@extends('layouts.demo1.base')

@section('content')
<main class="grow" id="content" role="content">
    <div class="kt-container-fixed">
        <div class="flex items-center justify-between mb-7.5">
            <div>
                <h1 class="text-2xl font-bold text-mono">Mon profil</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Gérez vos informations personnelles et votre photo de profil.
                </p>
            </div>
            <a href="{{ route('password.change') }}" class="kt-btn kt-btn-outline kt-btn-sm" data-ajax="false">
                <i class="ki-filled ki-lock me-1"></i>
                Changer le mot de passe
            </a>
        </div>

        @if($errors->any())
            <div class="kt-alert kt-alert-danger mb-5">
                <i class="ki-filled ki-information-2"></i>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-5 lg:gap-7.5 lg:grid-cols-3">
            <!-- Carte récapitulative -->
            <div class="lg:col-span-1">
                <div class="kt-card">
                    <div class="kt-card-content p-5">
                        <div class="flex flex-col items-center text-center">
                            <div id="avatar_preview" class="mb-4">
                                @if($user->photo_profil)
                                    <img src="{{ asset('storage/' . $user->photo_profil) }}" alt="{{ $user->nom_complet }}" class="size-24 rounded-full border-2 border-primary object-cover" />
                                @else
                                    <div class="size-24 rounded-full border-2 border-primary bg-primary/10 flex items-center justify-center">
                                        <span class="text-2xl font-semibold text-primary">
                                            {{ strtoupper(substr($user->prenom ?? '', 0, 1)) }}{{ strtoupper(substr($user->nom ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold text-foreground">{{ $user->nom_complet }}</h3>
                            <p class="text-sm text-muted-foreground">{{ $user->email }}</p>
                            @if($user->profils->isNotEmpty())
                                <div class="mt-3 flex flex-wrap justify-center gap-1.5">
                                    @foreach($user->profils as $profil)
                                        <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline">{{ $profil->libelle }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($user->statut === 'actif')
                                <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline mt-2">Actif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire de mise à jour -->
            <div class="lg:col-span-2">
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Informations personnelles</h3>
                    </div>
                    <div class="kt-card-content p-5 lg:p-7.5">
                        <form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5" data-ajax="false">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label" for="nom">Nom <span class="text-destructive">*</span></label>
                                    <input type="text" name="nom" id="nom" class="kt-input" value="{{ old('nom', $user->nom) }}" required maxlength="100" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="kt-label" for="prenom">Prénom <span class="text-destructive">*</span></label>
                                    <input type="text" name="prenom" id="prenom" class="kt-input" value="{{ old('prenom', $user->prenom) }}" required maxlength="100" />
                                </div>
                            </div>

                            @if($user->isAgent())
                            <div class="flex flex-col gap-2">
                                <label class="kt-label">Code agent</label>
                                <input type="text" class="kt-input bg-muted/30" value="{{ $user->agent?->code_agent ?? '—' }}" readonly disabled />
                                <span class="text-xs text-muted-foreground">Connexion avec ce code et votre mot de passe.</span>
                            </div>
                            @else
                            <div class="flex flex-col gap-2">
                                <label class="kt-label" for="email">Adresse email <span class="text-destructive">*</span></label>
                                <input type="email" name="email" id="email" class="kt-input" value="{{ old('email', $user->email) }}" required maxlength="100" />
                            </div>
                            @endif

                            <div class="flex flex-col gap-2">
                                <label class="kt-label" for="telephone">Téléphone</label>
                                <input type="text" name="telephone" id="telephone" class="kt-input" value="{{ old('telephone', $user->telephone) }}" maxlength="20" placeholder="Ex: +225 07 00 00 00 00" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="kt-label" for="photo_profil">Photo de profil</label>
                                <input type="file" name="photo_profil" id="photo_profil" class="kt-input" accept="image/jpeg,image/png,image/jpg" />
                                <span class="text-xs text-muted-foreground">JPEG, PNG ou JPG. Taille max. 2 Mo.</span>
                                <div id="photo_preview_new" class="mt-2 hidden">
                                    <span class="text-xs text-muted-foreground">Aperçu : </span>
                                    <img id="photo_preview_img" src="" alt="Aperçu" class="h-16 w-16 rounded-full object-cover border border-border mt-1" />
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2">
                                <button type="submit" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-check me-2"></i>
                                    Enregistrer les modifications
                                </button>
                                <a href="{{ url()->previous() }}" class="kt-btn kt-btn-ghost">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
(function() {
    var photoInput = document.getElementById('photo_profil');
    var photoPreview = document.getElementById('photo_preview_new');
    var photoPreviewImg = document.getElementById('photo_preview_img');
    var avatarPreview = document.getElementById('avatar_preview');

    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            var file = e.target.files && e.target.files[0];
            if (file && file.type.match('image.*')) {
                var reader = new FileReader();
                reader.onload = function(ev) {
                    if (photoPreviewImg) photoPreviewImg.src = ev.target.result;
                    if (photoPreview) {
                        photoPreview.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);
            } else {
                if (photoPreview) photoPreview.classList.add('hidden');
            }
        });
    }
})();
</script>
@endsection
