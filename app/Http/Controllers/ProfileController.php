<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Overzicht: alle profielgegevens read-only, met knoppen naar
     * bewerken/beveiliging/account verwijderen - het startpunt bij het
     * klikken op "Profiel", i.p.v. meteen op een bewerk-formulier te
     * belanden.
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Beveiliging: wachtwoord + 2FA + actieve sessies, bewust gescheiden
     * van de gewone profielgegevens - dit zijn gevoeligere instellingen
     * die niet tussen naam/adres/telefoonnummer moeten verdwijnen.
     */
    public function security(Request $request): View
    {
        $pendingSecret = $request->session()->get('2fa.pending_secret');

        return view('profile.security', [
            'user' => $request->user(),
            'twoFactorQrUri' => $pendingSecret
                ? app(\App\Services\TwoFactorAuthenticationService::class)->qrCodeUri($request->user(), $pendingSecret)
                : null,
            // Laat de gebruiker zijn eigen actieve sessies (toestellen/
            // browsers) zien en op afstand afmelden - bv. bij een
            // verloren/gestolen telefoon. De sessions-tabel (database
            // session-driver) bevat hiervoor alle nodige gegevens.
            'activeSessions' => DB::table('sessions')
                ->where('user_id', $request->user()->id)
                ->orderByDesc('last_activity')
                ->get()
                ->map(fn ($session) => [
                    'id' => $session->id,
                    'is_current' => $session->id === $request->session()->getId(),
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_active' => Carbon::createFromTimestamp($session->last_activity),
                ]),
        ]);
    }

    /**
     * Meldt een ANDER, actief toestel/browser af (bv. bij een verloren
     * telefoon) - bewust GEEN wachtwoordbevestiging vereist zoals bij
     * password.confirm elders in de app, want dit is net het
     * beveiligingsmiddel voor wanneer je wachtwoord/toestel
     * gecompromitteerd is en je NIET meer op dat andere toestel kan
     * inloggen om het te bevestigen. Enkel de eigen sessies van deze
     * gebruiker kunnen getroffen worden (where user_id), en de huidige
     * sessie kan hier bewust niet via afgemeld worden - daarvoor bestaat
     * de gewone "uitloggen"-knop al.
     */
    public function destroySession(Request $request, string $sessionId): RedirectResponse
    {
        if ($sessionId === $request->session()->getId()) {
            return back()->with('error', __('app.cannot_revoke_current_session'));
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->delete();

        return back()->with('success', __('app.session_revoked_success'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($request->user()->avatar) {
                Storage::disk('public')->delete($request->user()->avatar);
            }

            $data['avatar'] = app(\App\Services\ImageResizeService::class)->storeResized($request->file('avatar'), 'avatars');
        }

        $request->user()->fill($data);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.show')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
