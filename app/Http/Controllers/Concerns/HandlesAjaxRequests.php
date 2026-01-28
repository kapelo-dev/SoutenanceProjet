<?php

namespace App\Http\Controllers\Concerns;

trait HandlesAjaxRequests
{
    /**
     * Retourner une vue avec gestion AJAX
     * Si c'est une requête AJAX, retourne seulement le contenu sans le layout
     * Sinon, retourne la vue complète avec le layout
     */
    protected function ajaxView($view, $data = [], $layout = 'layouts.demo1.base')
    {
        if (request()->ajax() || request()->wantsJson()) {
            // Pour les requêtes AJAX, on rend la vue complète avec le layout
            // puis on extrait seulement le contenu du main
            $fullHtml = view($view, $data)->render();
            
            // Si la vue retourne déjà du HTML pur (sans layout), l'utiliser directement
            if (!str_contains($fullHtml, '<!DOCTYPE') && !str_contains($fullHtml, '<html')) {
                $content = $fullHtml;
            } else {
                // Extraire le contenu du main depuis le HTML complet
                $dom = new \DOMDocument();
                @$dom->loadHTML('<?xml encoding="UTF-8">' . $fullHtml);
                $xpath = new \DOMXPath($dom);
                
                // Chercher le conteneur principal
                $mainNodes = $xpath->query('//main[@role="content"] | //main[@id="content"] | //main');
                
                if ($mainNodes->length > 0) {
                    $mainNode = $mainNodes->item(0);
                    $content = '';
                    foreach ($mainNode->childNodes as $child) {
                        $content .= $dom->saveHTML($child);
                    }
                    
                    // Extraire aussi les scripts du body (pour @push('scripts'))
                    $bodyNodes = $xpath->query('//body');
                    if ($bodyNodes->length > 0) {
                        $bodyNode = $bodyNodes->item(0);
                        $scriptNodes = $xpath->query('.//script', $bodyNode);
                        foreach ($scriptNodes as $scriptNode) {
                            $content .= $dom->saveHTML($scriptNode);
                        }
                    }
                } else {
                    // Si on ne trouve pas de main, retourner tout le body
                    $bodyNodes = $xpath->query('//body');
                    if ($bodyNodes->length > 0) {
                        $bodyNode = $bodyNodes->item(0);
                        $content = '';
                        foreach ($bodyNode->childNodes as $child) {
                            $content .= $dom->saveHTML($child);
                        }
                    } else {
                        $content = $fullHtml;
                    }
                }
            }
            
            // Si on veut retourner du JSON avec le HTML
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'html' => $content
                ]);
            }
            
            // Sinon, on retourne directement le HTML
            return response($content)->header('Content-Type', 'text/html');
        }
        
        // Pour les requêtes normales, on retourne la vue avec le layout
        return view($view, $data);
    }
    
    /**
     * Retourner une réponse JSON standardisée
     */
    protected function ajaxResponse($success = true, $data = [], $message = null, $status = 200)
    {
        $response = [
            'success' => $success,
        ];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        $response = array_merge($response, $data);
        
        return response()->json($response, $status);
    }
    
    /**
     * Retourner une réponse de succès AJAX
     */
    protected function ajaxSuccess($data = [], $message = null)
    {
        return $this->ajaxResponse(true, $data, $message);
    }
    
    /**
     * Retourner une réponse d'erreur AJAX
     */
    protected function ajaxError($message, $errors = [], $status = 422)
    {
        $data = [];
        if (!empty($errors)) {
            $data['errors'] = $errors;
        }
        return $this->ajaxResponse(false, $data, $message, $status);
    }
    
    /**
     * Retourner une redirection pour les requêtes AJAX
     */
    protected function ajaxRedirect($url, $message = null)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => $url,
                'message' => $message
            ]);
        }
        
        return redirect($url)->with('success', $message);
    }
}
