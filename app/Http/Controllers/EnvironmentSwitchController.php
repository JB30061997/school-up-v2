<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnvironmentSwitchController extends Controller
{
    public function __invoke(
        Request $request,
        Environment $environment
    ): RedirectResponse {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->hasEnvironmentAccess($environment->id)) {
            throw ValidationException::withMessages([
                'environment' => 'Vous n’avez pas accès à cet environnement.',
            ]);
        }

        session([
            'current_environment_id' => $environment->id,
        ]);

        return back();
    }
}
