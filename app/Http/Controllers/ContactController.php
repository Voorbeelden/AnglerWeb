<?php

namespace App\Http\Controllers;

use App\Services\RecaptchaService;
use App\Services\Support\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Klassiek Laravel-MVC-patroon: geen API-envelope, gewoon een Blade-view
 * en redirects met flash-data (with('success'/'error')) - dit is de
 * "gewone" webkant van de applicatie, naast de JSON-API die in de
 * aparte anglerhub-api-repo staat.
 */
class ContactController extends Controller
{
    public function __construct(
        protected SupportTicketService $tickets,
        protected RecaptchaService $recaptcha,
    ) {
    }

    public function show(): View
    {
        return view('website.contact');
    }

    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Onzichtbare reCAPTCHA, serverside geverifieerd - naast het
        // honeypot-veld dat op formulierniveau al wordt afgehandeld (een
        // verborgen veld dat een mens nooit invult, maar een bot vaak
        // wel; zie website.contact zelf).
        if (! $this->recaptcha->verify($request->input('recaptcha_token'), 'contact')) {
            return back()->withInput()->with('error', __('app.recaptcha_failed'));
        }

        $this->tickets->create([...$validated, 'type' => 'contact'], $request->user());

        return back()->with('success', __('app.contact_sent_success'));
    }
}
