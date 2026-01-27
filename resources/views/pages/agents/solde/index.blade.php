@extends('layouts.demo1.base')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Gestion des Soldes
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Vue d'ensemble des soldes de tous les agents.
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a class="kt-btn kt-btn-outline" href="#">
                <i class="ki-filled ki-file-down"></i>
                Exporter
            </a>
        </div>
    </div>
</div>
<!-- End of Container -->
<!-- Container -->
<div class="kt-container-fixed">
    <div class="kt-card kt-card-grid min-w-full">
            <div class="kt-card-header py-5 flex-wrap gap-2">
            <div class="flex items-center gap-5">
            <label class="kt-input">
            <i class="ki-filled ki-magnifier">
            </i>
            <input data-kt-datatable-search="#soldes_table" placeholder="Rechercher un agent" type="text" value="">
            </input>
            </label>
            <label class="kt-label whitespace-nowrap">
            Soldes Positifs
            <input class="kt-switch kt-switch-sm" name="check" type="checkbox" value="1"/>
            </label>
            </div>
            </div>
            <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10">
            <div class="kt-scrollable-x-auto">
            <table class="kt-table kt-table-border" data-kt-datatable-table="true" id="soldes_table">
                <thead>
                <tr>
                <th class="w-[60px] text-center">
                <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-check="true" type="checkbox">
                </input>
                </th>
                <th class="min-w-[200px]">
                <span class="kt-table-col">
                    <span class="kt-table-col-label">
                    Agent
                    </span>
                    <span class="kt-table-col-sort">
                    </span>
                </span>
                </th>
                <th class="w-[150px]">
                <span class="kt-table-col">
                    <span class="kt-table-col-label">
                    Montant Initial
                    </span>
                    <span class="kt-table-col-sort">
                    </span>
                </span>
                </th>
                <th class="w-[150px]">
                <span class="kt-table-col">
                    <span class="kt-table-col-label">
                    Espèce
                    </span>
                    <span class="kt-table-col-sort">
                    </span>
                </span>
                </th>
                <th class="min-w-[200px]">
                <span class="kt-table-col">
                    <span class="kt-table-col-label">
                    Montant Virtuel
                    </span>
                    <span class="kt-table-col-sort">
                    </span>
                </span>
                </th>
                <th class="w-[150px]">
                <span class="kt-table-col">
                    <span class="kt-table-col-label">
                    Commissions
                    </span>
                    <span class="kt-table-col-sort">
                    </span>
                </span>
                </th>
                <th class="w-[180px]">
                <span class="kt-table-col">
                    <span class="kt-table-col-label">
                    Dernière MAJ
                    </span>
                    <span class="kt-table-col-sort">
                    </span>
                </span>
                </th>
                <th class="w-[60px]">
                </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                <td class="text-center">
                <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="1">
                </input>
                </td>
                <td>
                <div class="flex items-center gap-2.5">
                    <div class="relative">
                    <img class="h-9 rounded-full" src="{{ asset('assets/media/avatars/300-3.png') }}"/>
                    <span class="absolute -bottom-0.5 -right-0.5 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-success border border-white"></span>
                    </span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                    Justin Dansouvi
                    </a>
                    <span class="text-xs text-secondary-foreground font-normal">
                    AGT-001
                    </span>
                    </div>
                </div>
                </td>
                <td class="text-foreground font-semibold">
                60 000 FCFA
                </td>
                <td class="text-warning font-semibold">
                20 000 FCFA
                </td>
                <td>
                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between gap-2">
                    <span class="text-xs text-primary font-medium">40 000 FCFA</span>
                    <button class="kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost" onclick="toggleDropdown(this)">
                        <i class="ki-filled ki-down text-xs transition-transform duration-200"></i>
                    </button>
                    </div>
                    <div class="hidden dropdown-content mt-1 border-t border-border pt-1">
                    <div class="text-xs text-secondary-foreground">
                        <div class="flex justify-between py-1">
                        <span class="text-primary">Mixx by YAS</span>
                        <span class="font-medium">20 000 FCFA</span>
                        </div>
                        <div class="flex justify-between py-1">
                        <span class="text-primary">Flooz</span>
                        <span class="font-medium">20 000 FCFA</span>
                        </div>
                    </div>
                    </div>
                </div>
                </td>
                <td class="text-foreground font-normal">
                0 FCFA
                </td>
                <td class="text-foreground text-sm font-normal">
                24 janv. 2026, 13:56
                </td>
                <td>
                <div class="kt-menu" data-kt-menu="true">
                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                    <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                    <i class="ki-filled ki-dots-vertical text-lg">
                    </i>
                    </button>
                    <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-search-list">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Détails
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-file-up">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Exporter
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-separator">
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-pencil">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Ajuster
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-arrows-circle">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Historique
                        </span>
                    </a>
                    </div>
                    </div>
                    </div>
                </div>
                </td>
                </tr>
                <tr>
                <td class="text-center">
                <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="2">
                </input>
                </td>
                <td>
                <div class="flex items-center gap-2.5">
                    <div class="relative">
                    <img class="h-9 rounded-full" src="{{ asset('assets/media/avatars/300-2.png') }}"/>
                    <span class="absolute -bottom-0.5 -right-0.5 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-success border border-white"></span>
                    </span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                    qwer lome2
                    </a>
                    <span class="text-xs text-secondary-foreground font-normal">
                    AGT-002
                    </span>
                    </div>
                </div>
                </td>
                <td class="text-foreground font-semibold">
                60 000 FCFA
                </td>
                <td class="text-warning font-semibold">
                20 000 FCFA
                </td>
                <td>
                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between gap-2">
                    <span class="text-xs text-primary font-medium">40 000 FCFA</span>
                    <button class="kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost" onclick="toggleDropdown(this)">
                        <i class="ki-filled ki-down text-xs transition-transform duration-200"></i>
                    </button>
                    </div>
                    <div class="hidden dropdown-content mt-1 border-t border-border pt-1">
                    <div class="text-xs text-secondary-foreground">
                        <div class="flex justify-between py-1">
                        <span class="text-primary">Mixx by YAS</span>
                        <span class="font-medium">20 000 FCFA</span>
                        </div>
                        <div class="flex justify-between py-1">
                        <span class="text-primary">Flooz</span>
                        <span class="font-medium">20 000 FCFA</span>
                        </div>
                    </div>
                    </div>
                </div>
                </td>
                <td class="text-foreground font-normal">
                0 FCFA
                </td>
                <td class="text-foreground text-sm font-normal">
                24 janv. 2026, 13:52
                </td>
                <td>
                <div class="kt-menu" data-kt-menu="true">
                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                    <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                    <i class="ki-filled ki-dots-vertical text-lg">
                    </i>
                    </button>
                    <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-search-list">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Détails
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-file-up">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Exporter
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-separator">
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-pencil">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Ajuster
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-arrows-circle">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Historique
                        </span>
                    </a>
                    </div>
                    </div>
                    </div>
                </div>
                </td>
                </tr>
                <tr>
                <td class="text-center">
                <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="3">
                </input>
                </td>
                <td>
                <div class="flex items-center gap-2.5">
                    <div class="relative">
                    <img class="h-9 rounded-full" src="{{ asset('assets/media/avatars/300-11.png') }}"/>
                    <span class="absolute -bottom-0.5 -right-0.5 flex h-2.5 w-2.5">
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-warning border border-white"></span>
                    </span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                    olm koplon
                    </a>
                    <span class="text-xs text-secondary-foreground font-normal">
                    AGT-003
                    </span>
                    </div>
                </div>
                </td>
                <td class="text-foreground font-semibold">
                36 334 FCFA
                </td>
                <td class="text-warning font-semibold">
                12 334 FCFA
                </td>
                <td>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-primary font-medium">24 000 FCFA</span>
                </div>
                </td>
                <td class="text-foreground font-normal">
                0 FCFA
                </td>
                <td class="text-foreground text-sm font-normal">
                24 janv. 2026, 12:35
                </td>
                <td>
                <div class="kt-menu" data-kt-menu="true">
                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                    <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                    <i class="ki-filled ki-dots-vertical text-lg">
                    </i>
                    </button>
                    <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-search-list">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Détails
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-file-up">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Exporter
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-separator">
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-pencil">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Ajuster
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-arrows-circle">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Historique
                        </span>
                    </a>
                    </div>
                    </div>
                    </div>
                </div>
                </td>
                </tr>
                <tr>
                <td class="text-center">
                <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="4">
                </input>
                </td>
                <td>
                <div class="flex items-center gap-2.5">
                    <div class="relative">
                    <img class="h-9 rounded-full" src="{{ asset('assets/media/avatars/300-5.png') }}"/>
                    <span class="absolute -bottom-0.5 -right-0.5 flex h-2.5 w-2.5">
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-destructive border border-white"></span>
                    </span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                    Landry Dansou
                    </a>
                    <span class="text-xs text-secondary-foreground font-normal">
                    AGT-004
                    </span>
                    </div>
                </div>
                </td>
                <td class="text-foreground font-semibold">
                40 000 FCFA
                </td>
                <td class="text-warning font-semibold">
                20 000 FCFA
                </td>
                <td>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-primary font-medium">20 000 FCFA</span>
                </div>
                </td>
                <td class="text-foreground font-normal">
                0 FCFA
                </td>
                <td class="text-foreground text-sm font-normal">
                19 janv. 2026, 09:20
                </td>
                <td>
                <div class="kt-menu" data-kt-menu="true">
                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                    <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                    <i class="ki-filled ki-dots-vertical text-lg">
                    </i>
                    </button>
                    <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-search-list">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Détails
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-file-up">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Exporter
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-separator">
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-pencil">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Ajuster
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-arrows-circle">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Historique
                        </span>
                    </a>
                    </div>
                    </div>
                    </div>
                </div>
                </td>
                </tr>
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

<script>
function toggleDropdown(button) {
    const parentCell = button.closest('td');
    const dropdownContent = parentCell.querySelector('.dropdown-content');
    const icon = button.querySelector('i');
    
    if (dropdownContent) {
        dropdownContent.classList.toggle('hidden');
        icon.classList.toggle('rotate-180');
    }
}
</script>
@endsection
