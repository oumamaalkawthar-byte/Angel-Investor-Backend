<?php

namespace App\Http\Controllers;

use App\Mail\SiteMail;
use App\Models\ContactSubmission;
use App\Models\InvestorApplication;
use App\Models\StartupApplication;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FormController extends Controller
{
    /**
     * True if the honeypot field was filled — a real visitor never sees or
     * fills it (styled off-screen in the frontend markup). Callers should
     * pretend success rather than reveal the bot was caught.
     */
    protected function isHoneypotFilled(Request $request): bool
    {
        return trim((string) $request->input('hp_website', '')) !== '';
    }

    public function contact(Request $request)
    {
        if ($this->isHoneypotFilled($request)) {
            return response()->json(['success' => true]);
        }

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        ContactSubmission::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'message'    => $validated['message'],
            'ip_address' => $request->ip(),
        ]);

        app(GoogleSheetsService::class)->push('Contact Forms', [
            now()->toIso8601String(),
            $validated['name'],
            $validated['email'],
            $validated['message'],
        ]);

        $this->dispatchMails(
            formLabel: 'Contact Message',
            lines: [
                ['label' => 'Name', 'value' => $validated['name']],
                ['label' => 'Email', 'value' => $validated['email']],
                ['label' => 'Message', 'value' => $validated['message']],
            ],
            userEmail: $validated['email'],
            userName: $validated['name'],
            thankYouIntro: "Thanks for reaching out to Angel Investor. We've received your message and a member of our team will get back to you within 1–2 business days."
        );

        return response()->json(['success' => true, 'message' => 'Message sent.']);
    }

    public function investor(Request $request)
    {
        if ($this->isHoneypotFilled($request)) {
            return response()->json(['success' => true]);
        }

        $validated = $request->validate([
            'investor_name'          => 'required|string|max:255',
            'investor_email'         => 'required|email|max:255',
            'investor_phone'         => 'required|string|max:50',
            'investor_city'          => 'required|string|max:100',
            'investor_org'           => 'nullable|string|max:255',
            'investor_linkedin'      => 'nullable|string|max:500',
            'sectors_of_interest'    => 'required|string|max:500',
            'ticket_size'            => 'required|string|max:100',
            'preferred_stage'        => 'required|string|max:100',
            'experience'             => 'required|string|max:100',
            'value_add'              => 'nullable|string|max:3000',
            'declaration_confidentiality' => 'accepted',
            'declaration_source_of_funds' => 'accepted',
        ]);

        $application = InvestorApplication::create([
            'investor_name'     => $validated['investor_name'],
            'investor_email'    => $validated['investor_email'],
            'investor_phone'    => $validated['investor_phone'],
            'investor_city'     => $validated['investor_city'],
            'investor_org'      => $validated['investor_org'] ?? null,
            'investor_linkedin' => $validated['investor_linkedin'] ?? null,
            'sectors_of_interest' => $validated['sectors_of_interest'],
            'ticket_size'       => $validated['ticket_size'],
            'preferred_stage'   => $validated['preferred_stage'],
            'experience'        => $validated['experience'],
            'value_add'         => $validated['value_add'] ?? null,
            'declaration_confidentiality' => true,
            'declaration_source_of_funds' => true,
            'ip_address'        => $request->ip(),
        ]);

        $reference = 'AI-INV-' . now()->format('Y') . '-' . str_pad((string) $application->id, 4, '0', STR_PAD_LEFT);
        $application->update(['reference' => $reference]);

        app(GoogleSheetsService::class)->push('Join As Investor', [
            now()->toIso8601String(),
            $reference,
            $validated['investor_name'],
            $validated['investor_email'],
            $validated['investor_phone'],
            $validated['investor_city'],
            $validated['investor_org'] ?? '',
            $validated['investor_linkedin'] ?? '',
            $validated['sectors_of_interest'],
            $validated['ticket_size'],
            $validated['preferred_stage'],
            $validated['experience'],
            $validated['value_add'] ?? '',
        ]);

        $this->dispatchMails(
            formLabel: "Investor Application {$reference}",
            lines: [
                ['label' => 'Reference', 'value' => $reference],
                ['label' => 'Name', 'value' => $validated['investor_name']],
                ['label' => 'Email', 'value' => $validated['investor_email']],
                ['label' => 'Phone', 'value' => $validated['investor_phone']],
                ['label' => 'City', 'value' => $validated['investor_city']],
                ['label' => 'Organization', 'value' => $validated['investor_org'] ?? '-'],
                ['label' => 'LinkedIn', 'value' => $validated['investor_linkedin'] ?? '-'],
                ['label' => 'Sectors of Interest', 'value' => $validated['sectors_of_interest']],
                ['label' => 'Ticket Size', 'value' => $validated['ticket_size']],
                ['label' => 'Preferred Stage', 'value' => $validated['preferred_stage']],
                ['label' => 'Experience', 'value' => $validated['experience']],
                ['label' => 'Value Add', 'value' => $validated['value_add'] ?? '-'],
            ],
            userEmail: $validated['investor_email'],
            userName: $validated['investor_name'],
            thankYouIntro: "Thanks for applying to join the Angel Investor network. Your reference number is {$reference}. A member of our team will reach out within 5–7 working days to schedule a short verification call."
        );

        return response()->json(['success' => true, 'reference' => $reference]);
    }

    public function startup(Request $request)
    {
        if ($this->isHoneypotFilled($request)) {
            return response()->json(['success' => true]);
        }

        $validated = $request->validate([
            'founder_name'         => 'required|string|max:255',
            'founder_email'        => 'required|email|max:255',
            'founder_phone'        => 'required|string|max:50',
            'founder_city'         => 'required|string|max:100',
            'founder_bio'          => 'required|string|max:2000',
            'founder_linkedin'     => 'nullable|string|max:500',
            'startup_name'         => 'required|string|max:255',
            'startup_website'      => 'nullable|string|max:500',
            'one_liner'            => 'required|string|max:120',
            'sector'               => 'required|string|max:100',
            'stage'                => 'required|string|max:100',
            'registration_status'  => 'required|string|max:100',
            'team_size'            => 'nullable|string|max:100',
            'investment_ask'       => 'required|string|max:100',
            'equity_offered'       => 'required|string|max:100',
            'use_of_funds'         => 'required|string|max:2000',
            'traction'             => 'nullable|string|max:2000',
            'declaration_authentic' => 'accepted',
            'declaration_ethical'   => 'accepted',
            'declaration_consent'   => 'accepted',
            'pitch_deck'           => 'required|file|mimes:pdf|max:15360',
            'cofounder_name_1'     => 'nullable|string|max:255',
            'cofounder_role_1'     => 'nullable|string|max:255',
            'cofounder_linkedin_1' => 'nullable|string|max:500',
            'cofounder_name_2'     => 'nullable|string|max:255',
            'cofounder_role_2'     => 'nullable|string|max:255',
            'cofounder_linkedin_2' => 'nullable|string|max:500',
            'cofounder_name_3'     => 'nullable|string|max:255',
            'cofounder_role_3'     => 'nullable|string|max:255',
            'cofounder_linkedin_3' => 'nullable|string|max:500',
        ]);

        $cofounders = [];
        for ($i = 1; $i <= 3; $i++) {
            $name = trim((string) ($validated["cofounder_name_{$i}"] ?? ''));
            if ($name === '') {
                continue;
            }
            $cofounders[] = [
                'name' => $name,
                'role' => $validated["cofounder_role_{$i}"] ?? '',
                'linkedin' => $validated["cofounder_linkedin_{$i}"] ?? '',
            ];
        }

        // Stored on the `public` disk, never the visitor-supplied filename —
        // served via the /storage/{path} route (see routes/web.php), no
        // symlink dependency on this host.
        $pitchDeckPath = $request->file('pitch_deck')->store('pitch-decks', 'public');
        $pitchDeckOriginalName = $request->file('pitch_deck')->getClientOriginalName();

        $application = StartupApplication::create([
            'founder_name'         => $validated['founder_name'],
            'founder_email'        => $validated['founder_email'],
            'founder_phone'        => $validated['founder_phone'],
            'founder_city'         => $validated['founder_city'],
            'founder_bio'          => $validated['founder_bio'],
            'founder_linkedin'     => $validated['founder_linkedin'] ?? null,
            'cofounders'           => $cofounders,
            'startup_name'         => $validated['startup_name'],
            'startup_website'      => $validated['startup_website'] ?? null,
            'one_liner'            => $validated['one_liner'],
            'sector'               => $validated['sector'],
            'stage'                => $validated['stage'],
            'registration_status'  => $validated['registration_status'],
            'team_size'            => $validated['team_size'] ?? null,
            'pitch_deck_path'      => $pitchDeckPath,
            'pitch_deck_original_name' => $pitchDeckOriginalName,
            'investment_ask'       => $validated['investment_ask'],
            'equity_offered'       => $validated['equity_offered'],
            'use_of_funds'         => $validated['use_of_funds'],
            'traction'             => $validated['traction'] ?? null,
            'declaration_authentic' => true,
            'declaration_ethical'   => true,
            'declaration_consent'   => true,
            'ip_address'           => $request->ip(),
        ]);

        $reference = 'AI-' . now()->format('Y') . '-' . str_pad((string) $application->id, 4, '0', STR_PAD_LEFT);
        $application->update(['reference' => $reference]);

        $cofoundersSummary = $application->cofoundersSummary();

        app(GoogleSheetsService::class)->push('Apply As Startup', [
            now()->toIso8601String(),
            $reference,
            $validated['founder_name'],
            $validated['founder_email'],
            $validated['founder_phone'],
            $validated['founder_city'],
            $validated['founder_linkedin'] ?? '',
            $cofoundersSummary,
            $validated['startup_name'],
            $validated['startup_website'] ?? '',
            $validated['one_liner'],
            $validated['sector'],
            $validated['stage'],
            $validated['registration_status'],
            $validated['team_size'] ?? '',
            $validated['investment_ask'],
            $validated['equity_offered'],
            $validated['use_of_funds'],
            $validated['traction'] ?? '',
            $pitchDeckOriginalName,
        ]);

        $this->dispatchMails(
            formLabel: "Startup Application {$reference}",
            lines: [
                ['label' => 'Reference', 'value' => $reference],
                ['label' => 'Founder', 'value' => "{$validated['founder_name']} <{$validated['founder_email']}> {$validated['founder_phone']}, {$validated['founder_city']}"],
                ['label' => 'LinkedIn', 'value' => $validated['founder_linkedin'] ?? '-'],
                ['label' => 'Bio', 'value' => $validated['founder_bio']],
                ['label' => 'Co-founders', 'value' => $cofoundersSummary],
                ['label' => 'Startup', 'value' => "{$validated['startup_name']} ({$validated['startup_website']})"],
                ['label' => 'One-liner', 'value' => $validated['one_liner']],
                ['label' => 'Sector / Stage / Registration', 'value' => "{$validated['sector']} | {$validated['stage']} | {$validated['registration_status']}"],
                ['label' => 'Team Size', 'value' => $validated['team_size'] ?? '-'],
                ['label' => 'Ask', 'value' => "{$validated['investment_ask']} for {$validated['equity_offered']}"],
                ['label' => 'Use of Funds', 'value' => $validated['use_of_funds']],
                ['label' => 'Traction', 'value' => $validated['traction'] ?? '-'],
            ],
            userEmail: $validated['founder_email'],
            userName: $validated['founder_name'],
            thankYouIntro: "Thanks for applying to Angel Investor. Your reference number is {$reference}. Our screening committee will review your application and reach out within 5–7 working days.",
            attachmentPath: storage_path('app/public/' . $pitchDeckPath),
            attachmentName: $pitchDeckOriginalName
        );

        return response()->json(['success' => true, 'reference' => $reference]);
    }

    protected function dispatchMails(
        string $formLabel,
        array $lines,
        string $userEmail,
        string $userName,
        string $thankYouIntro,
        ?string $attachmentPath = null,
        ?string $attachmentName = null,
    ): void {
        $siteName = config('app.name');

        $adminMail = new SiteMail(
            "New {$formLabel} — {$siteName}",
            $formLabel . ' Received',
            $lines,
            'A new submission has been received on the website.',
            'Received on ' . now()->format('d M Y g:i A')
        );
        if ($attachmentPath) {
            $adminMail->attachFile($attachmentPath, $attachmentName);
        }
        $adminRecipient = config('mail.notification.address');
        app()->terminating(function () use ($adminMail, $adminRecipient) {
            Mail::to($adminRecipient)->send($adminMail);
        });

        $thankYouMail = new SiteMail(
            "Thank You for Your {$formLabel} — {$siteName}",
            "Thank You, {$userName}!",
            $lines,
            $thankYouIntro,
            "This is an automated email from {$siteName}. Please do not reply to this email."
        );
        app()->terminating(function () use ($thankYouMail, $userEmail) {
            Mail::to($userEmail)->send($thankYouMail);
        });
    }
}
