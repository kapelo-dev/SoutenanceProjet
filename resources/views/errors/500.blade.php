@extends('errors.layout')

@section('code', '500')
@section('title', 'Erreur serveur')
@section('icon', 'ki-information-2')
@section('color', 'text-destructive')
@section('message', 'Une erreur inattendue est survenue. Veuillez réessayer plus tard.')
@section('hint', "Si le problème persiste, contactez l'administrateur du système.")
