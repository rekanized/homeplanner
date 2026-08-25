<?php

namespace App\Http\Controllers;

use App\Models\Chore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChoreProofController extends Controller
{
    public function __invoke(Request $request, Chore $chore): StreamedResponse
    {
        abort_if($request->user()->is_child && $chore->user_id !== $request->user()->id, 403);
        abort_unless($chore->proof_image_path, 404);

        $disk = Storage::disk('local')->exists($chore->proof_image_path) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($chore->proof_image_path), 404);

        return Storage::disk($disk)->response(
            $chore->proof_image_path,
            null,
            ['Content-Disposition' => 'inline']
        );
    }
}
