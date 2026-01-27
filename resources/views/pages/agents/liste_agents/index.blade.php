@extends('layouts.demo1.base')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-2xl font-semibold leading-none text-mono">
                Gestion des Agents
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Vue d'ensemble de tous les agents.
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a class="kt-btn kt-btn-primary" href="#">
                <i class="ki-filled ki-plus"></i>
                Nouvel Agent
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
            <input data-kt-datatable-search="#agents_table" placeholder="Rechercher un agent" type="text" value="">
            </input>
            </label>
            <label class="kt-label whitespace-nowrap">
            Agents Actifs
            <input class="kt-switch kt-switch-sm" name="check" type="checkbox" value="1"/>
            </label>
            </div>
            </div>
            <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10">
            <div class="kt-scrollable-x-auto">
            <table class="kt-table kt-table-border" data-kt-datatable-table="true" id="agents_table">
                <thead>
                <tr>
                <th class="w-[60px] text-center">
                <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-check="true" type="checkbox">
                </input>
                </th>
                <th class="min-w-[250px]">
                <span class="kt-table-col">
                    <span class="kt-table-col-label">
                    Agent
                    </span>
                    <span class="kt-table-col-sort">
                    </span>
                </span>
                </th>
                <th class="w-[180px]">
                <span class="kt-table-col">
                    <span class="kt-table-col-label">
                    Code Agent
                    </span>
                    <span class="kt-table-col-sort">
                    </span>
                </span>
                </th>
                <th class="w-[200px]">
                <span class="kt-table-col">
                    <span class="kt-table-col-label">
                    Téléphone
                    </span>
                    <span class="kt-table-col-sort">
                    </span>
                </span>
                </th>
                <th class="w-[150px]">
                <span class="kt-table-col">
                    <span class="kt-table-col-label">
                    Statut
                    </span>
                    <span class="kt-table-col-sort">
                    </span>
                </span>
                </th>
                <th class="w-[180px]">
                <span class="kt-table-col">
                    <span class="kt-table-col-label">
                    Date d'ajout
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
                    <div class="">
                    <img class="h-9 rounded-full" src="{{ asset('assets/media/avatars/300-3.png') }}"/>
                    </div>
                    <div class="flex flex-col gap-0.5">
                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                    Tyler Hero
                    </a>
                    <span class="text-xs text-secondary-foreground font-normal">
                    tyler@example.com
                    </span>
                    </div>
                </div>
                </td>
                <td>
                <div class="flex flex-col gap-0.5">
                    <span class="leading-none font-medium text-sm text-mono">
                    AGT-001
                    </span>
                    <span class="text-xs text-secondary-foreground font-normal">
                    Point de vente
                    </span>
                </div>
                </td>
                <td class="text-foreground font-normal">
                +221 77 123 45 67
                </td>
                <td>
                <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-success">
                    Actif
                </span>
                </td>
                <td class="text-foreground font-normal">
                15 Jan 2024
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
                        Voir
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
                        Modifier
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-copy">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Dupliquer
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-separator">
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-trash">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Supprimer
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
                    <div class="">
                    <img class="h-9 rounded-full" src="{{ asset('assets/media/avatars/300-2.png') }}"/>
                    </div>
                    <div class="flex flex-col gap-0.5">
                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                    Esther Howard
                    </a>
                    <span class="text-xs text-secondary-foreground font-normal">
                    esther@example.com
                    </span>
                    </div>
                </div>
                </td>
                <td>
                <div class="flex flex-col gap-0.5">
                    <span class="leading-none font-medium text-sm text-mono">
                    AGT-002
                    </span>
                    <span class="text-xs text-secondary-foreground font-normal">
                    Distributeur
                    </span>
                </div>
                </td>
                <td class="text-foreground font-normal">
                +221 77 234 56 78
                </td>
                <td>
                <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-warning">
                    En attente
                </span>
                </td>
                <td class="text-foreground font-normal">
                10 Jan 2024
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
                        Voir
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
                        Modifier
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-copy">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Dupliquer
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-separator">
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-trash">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Supprimer
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
                    <div class="">
                    <img class="h-9 rounded-full" src="{{ asset('assets/media/avatars/300-11.png') }}"/>
                    </div>
                    <div class="flex flex-col gap-0.5">
                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                    Jacob Jones
                    </a>
                    <span class="text-xs text-secondary-foreground font-normal">
                    jacob@example.com
                    </span>
                    </div>
                </div>
                </td>
                <td>
                <div class="flex flex-col gap-0.5">
                    <span class="leading-none font-medium text-sm text-mono">
                    AGT-003
                    </span>
                    <span class="text-xs text-secondary-foreground font-normal">
                    Super Agent
                    </span>
                </div>
                </td>
                <td class="text-foreground font-normal">
                +221 77 345 67 89
                </td>
                <td>
                <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-success">
                    Actif
                </span>
                </td>
                <td class="text-foreground font-normal">
                05 Jan 2024
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
                        Voir
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
                        Modifier
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-copy">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Dupliquer
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-separator">
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-trash">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Supprimer
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
                    <div class="">
                    <img class="h-9 rounded-full" src="{{ asset('assets/media/avatars/300-5.png') }}"/>
                    </div>
                    <div class="flex flex-col gap-0.5">
                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                    Cody Fisher
                    </a>
                    <span class="text-xs text-secondary-foreground font-normal">
                    cody@example.com
                    </span>
                    </div>
                </div>
                </td>
                <td>
                <div class="flex flex-col gap-0.5">
                    <span class="leading-none font-medium text-sm text-mono">
                    AGT-004
                    </span>
                    <span class="text-xs text-secondary-foreground font-normal">
                    Point de vente
                    </span>
                </div>
                </td>
                <td class="text-foreground font-normal">
                +221 77 456 78 90
                </td>
                <td>
                <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-destructive">
                    Suspendu
                </span>
                </td>
                <td class="text-foreground font-normal">
                28 Déc 2023
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
                        Voir
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
                        Modifier
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-copy">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Dupliquer
                        </span>
                    </a>
                    </div>
                    <div class="kt-menu-separator">
                    </div>
                    <div class="kt-menu-item">
                    <a class="kt-menu-link" href="#">
                        <span class="kt-menu-icon">
                        <i class="ki-filled ki-trash">
                        </i>
                        </span>
                        <span class="kt-menu-title">
                        Supprimer
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
@endsection
