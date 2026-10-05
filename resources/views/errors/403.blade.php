@extends('errors.layout')

@section('code', '403')
@section('title', 'Accès refusé')
@section('icon', 'ki-shield-cross')
@section('color', 'text-destructive')
@section('message', "Vous n'avez pas les autorisations nécessaires pour accéder à cette page.")
@section('hint', "Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur du système.")
