<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->url(fn () => route('blog.preview', $this->record))
                ->openUrlInNewTab(),
            Actions\ReplicateAction::make()
                ->label('Duplicate')
                ->excludeAttributes(['slug', 'status', 'pub_date'])
                ->beforeReplicaSaved(function ($replica) {
                    $replica->slug = Str::slug($replica->title) . '-copy-' . Str::random(4);
                    $replica->status = 'draft';
                    $replica->pub_date = now();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    // Lightweight autosave: every 30s while editing, silently persist the
    // current form state as-is (whatever status is currently selected —
    // typically "Draft" while still being written) without leaving the page
    // or requiring a manual Save click. Wired up via a small Alpine timer in
    // the Edit page view (see resources/views/filament/... override below);
    // Filament renders this page with its own default view unless
    // overridden, so the timer is instead injected via a render hook in
    // AdminPanelProvider that targets this specific page.
    #[On('autosave-tick')]
    public function autosave(): void
    {
        // Persist whatever's currently in the form as-is, including an
        // in-progress "Draft" status — a background save, not a real
        // Save-button click, so it deliberately skips validation (an
        // incomplete draft shouldn't block itself from being saved).
        $this->record->fill($this->form->getRawState())->save();
        $this->dispatch('autosaved-at', time: now()->format('g:i:s A'));
    }
}
