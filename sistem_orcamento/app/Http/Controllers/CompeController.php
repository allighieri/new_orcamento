<?php

namespace App\Http\Controllers;

use App\Models\Compe;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompeController extends Controller
{
    /**
     * Autocomplete para busca de bancos
     */
    public function autocomplete(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $compes = Compe::active()
            ->where(function($q) use ($query) {
                $q->where('bank_name', 'LIKE', "%{$query}%")
                  ->orWhere('code', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'bank_name', 'code']);
        
        $results = $compes->map(function($compe) {
            return [
                'id' => $compe->id,
                'text' => "{$compe->code} - {$compe->bank_name}",
                'bank_name' => $compe->bank_name,
                'code' => $compe->code
            ];
        });
        
        return response()->json($results);
    }
}
