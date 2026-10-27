<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    /**
     * Login challenge — shown after password if the user has 2FA enabled.
     */
    public function challenge(Request $request): View|RedirectResponse
    {
        abort_unless($request->session()->has('pending_2fa_user_id'), 404);

        return view('app.auth.two-factor');
    }

    /**
     * Verify the OTP (or a recovery code) and complete login.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|max:20']);
        $userId = $request->session()->pull('pending_2fa_user_id');
        $user = \App\Models\User::findOrFail($userId);
        $g2fa = new Google2FA();

        $valid = $g2fa->verifyKey($user->google2fa_secret, $request->code);

        // Recovery codes as fallback
        if (! $valid) {
            $codes = json_decode($user->two_factor_recovery_codes ?? '[]', true);
            if (($key = array_search($request->code, $codes)) !== false) {
                unset($codes[$key]);
                $user->update(['two_factor_recovery_codes' => json_encode(array_values($codes))]);
                $valid = true;
            }
        }

        return $valid
            ? tap(redirect()->intended(route('tenant.dashboard', absolute: false)), fn () => Auth::loginUsingId($user->id))
            : back()->withErrors(['code' => __('Invalid code.')]);
    }

    /**
     * Show QR + generate a pending secret (not yet activated).
     */
    public function enable(Request $request): RedirectResponse
    {
        $g2fa = new Google2FA();
        $request->session()->put('pending_2fa_secret', $g2fa->generateSecretKey());

        return back();
    }

    /**
     * Confirm with a live OTP → activate 2FA + issue recovery codes.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|max:10']);
        $secret = $request->session()->pull('pending_2fa_secret');
        abort_unless($secret, 404);

        $g2fa = new Google2FA();
        abort_unless($g2fa->verifyKey($secret, $request->code), 422, 'Invalid code — try again.');

        $recovery = collect(range(1, 8))->map(fn () => str()->random(10))->all();
        $request->user()->update([
            'google2fa_secret' => $secret,
            'two_factor_recovery_codes' => json_encode($recovery),
        ]);

        return back()->with('recovery_codes', $recovery);
    }

    /**
     * Disable 2FA — requires current password.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required']);
        abort_unless(
            \Illuminate\Support\Facades\Hash::check($request->password, $request->user()->password),
            422, 'Incorrect password.'
        );

        $request->user()->update(['google2fa_secret' => null, 'two_factor_recovery_codes' => null]);

        return back();
    }

    /**
     * QR PNG data URI for the pending secret.
     */
    public static function qrDataUri(string $secret): string
    {
        $g2fa = new Google2FA();
        $url = $g2fa->getQRCodeUrl(config('app.name'), auth()->user()->email, $secret);
        $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        ));

        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($url));
    }
}
