# AnglerHub Web

The traditional Laravel/Blade side of the same application as
[AnglerHub API](https://github.com/Voorbeelden/AnglerHubApi) — session-based
web controllers, server-rendered views, and the reusable Alpine.js/Tailwind
component layer the ~250-view dashboard is built from, rather than the
JSON API the mobile app talks to.

This is a **curated excerpt**, not the full application — see
[Repository scope](#repository-scope).

## Screenshots

A few screens from the live application, chosen to show the general look
and flow rather than internal configuration detail. Names, weights and
club data shown are test/demo values, not real club or member data.

<table>
<tr>
<td width="50%"><img src="docs/screenshots/homepage.png" alt="Marketing homepage"><br><sub>Marketing homepage</sub></td>
<td width="50%"><img src="docs/screenshots/dashboard-overview.png" alt="Club dashboard overview"><br><sub>Club dashboard overview</sub></td>
</tr>
<tr>
<td width="50%"><img src="docs/screenshots/competitions-list.png" alt="Competitions list, sortable and searchable"><br><sub>Competitions list</sub></td>
<td width="50%"><img src="docs/screenshots/competition-registration.png" alt="Competition registration screen"><br><sub>Competition registration</sub></td>
</tr>
<tr>
<td width="50%"><img src="docs/screenshots/standings.png" alt="Club standings, filterable by year and date range"><br><sub>Standings, filterable by year/date range</sub></td>
<td></td>
</tr>
</table>

## What's here

- **Session-based web controllers** — classic Laravel MVC: a Blade view or
  a redirect with flashed status, no JSON envelope, no token auth.
- **Account security self-service** — a user's own device/session
  management (view and remotely revoke other active sessions), 2FA setup,
  and account deletion gated behind a fresh password confirmation.
- **A contact form with real spam protection** — server-verified
  reCAPTCHA plus a honeypot field, matching what's actually described as
  standard on [Websexpert](https://www.web-designs.eu) client sites.
- **Form Requests** for validation, kept separate from the controller.
- **A handful of the dashboard's reusable Blade/Alpine.js components**
  (accessible modal, styled confirm-dialog, a small toggle control) and
  the Tailwind v4 design-token layer they're built with.

## Why a separate repo from the API

The dashboard and the API share the same underlying models, services and
Policies in the real application — this split is about audience, not
architecture: someone evaluating "can this person write a traditional,
session-based Laravel app" can look here without the JSON-API-specific
code getting in the way, and vice versa in
[AnglerHub API](https://github.com/Voorbeelden/AnglerHubApi).

## Repository layout

```
anglerhub-web/
├── app/Http/Controllers/
│   ├── ContactController.php               reCAPTCHA + honeypot spam protection
│   └── ProfileController.php               profile, 2FA, session management, account deletion
├── app/Http/Requests/
│   └── ProfileUpdateRequest.php            Form Request validation pattern
└── resources/
    ├── views/components/
    │   ├── modal.blade.php                 accessible modal shell (focus trap, teleport, ESC)
    │   ├── confirm-form.blade.php          styled drop-in replacement for confirm()
    │   └── segmented-toggle.blade.php      small reusable Alpine-driven toggle
    └── css/app.css                         Tailwind v4 @utility extensions, print styles, a11y
```

## Architecture notes

**Session self-service, not just admin-managed security.**
`ProfileController::security()` lets a user see and remotely revoke their
own other active sessions (e.g. after losing a phone) — deliberately
*without* requiring password re-confirmation, since that would defeat the
point if the compromised device is the only one they can still log in on.
Account deletion goes the other way: it *does* require a fresh password
check, since there's no equivalent urgency working against it.

**Front-end interactivity stays declarative.** Alpine.js is used directly
in Blade markup rather than reaching for a full SPA framework for a
server-rendered dashboard — see `modal.blade.php`, where `x-teleport` was
added specifically to escape a real CSS containment quirk
(`backdrop-filter` creates a new containment block for `position: fixed`
descendants), documented in the component itself.

## Security and Privacy

Real client identifiers, the production domain, and anything
credential-shaped are replaced with placeholders or removed outright.
Field and route names are translated from the original Dutch production
code for the same reason as in the API repo: keeping this excerpt's exact
strings distinct from what the live product actually uses.

## Repository scope

- **Only a handful of the ~250 Blade views are included** — the three
  small, genuinely reusable components shown above.
- **The actual business-logic-heavy controllers are not included**
  (competition/schedule management, ~1000+ lines each) — this repo is
  about the MVC/session/component patterns, not the business rules.
- **Payments, admin/support tooling, and email templates are not
  included.**

**This excerpt won't run standalone.**

## What this demonstrates

- Traditional, session-based Laravel MVC alongside (not instead of) a
  JSON API for the same application
- Security-conscious account self-service: session visibility/revocation,
  2FA, and appropriately different confirmation requirements depending on
  the actual risk of each action
- Form Request-based validation kept out of the controller
- Accessible, reusable front-end components (focus trapping, keyboard
  navigation) built with Blade/Alpine.js rather than a separate SPA
  framework

## License

Shared for portfolio purposes only. Not licensed for reuse, redistribution
or use as a starting point for a similar application.
