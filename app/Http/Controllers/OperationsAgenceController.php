<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class OperationsAgenceController extends Controller
{
    /**
     * Afficher la page des opérations en agence avec les vraies données.
     */
    public function index(Request $request)
    {
        // On récupère les transactions les plus récentes avec les relations nécessaires
        $transactions = Transaction::with(['agent.utilisateur', 'operateur'])
            ->latest('date')
            ->paginate(20);

        return $this->ajaxView('pages.operation_agence.index', compact('transactions'));
    }
}

