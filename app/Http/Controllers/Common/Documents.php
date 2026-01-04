<?php

namespace App\Http\Controllers\Common;

use App\Abstracts\Http\Controller;
use App\Interfaces\Utility\DocumentNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Documents extends Controller
{
    public function getNextNumber(Request $request): JsonResponse
    {
        $type = $request->get('type', 'invoice');
        
        $document_number = app(DocumentNumber::class)->getNextNumber($type, null);

        return response()->json([
            'success' => true,
            'data' => [
                'number' => $document_number,
            ],
        ]);
    }
}
