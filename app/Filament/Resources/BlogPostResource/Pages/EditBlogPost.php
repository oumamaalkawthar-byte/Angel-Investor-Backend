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
        // getRawState() includes every form component's value, including
        // UI-only ones with no real column (e.g. the "detected images" hint
        // Placeholder in the Body Images section) — unlike Filament's normal
        // save flow, it doesn't filter those out (that filtering happens via
        // validation/dehydration, which this deliberately skips so an
        // incomplete draft can't fail to autosave on itself). Restricting to
        // the model's actual columns avoids a "column not found" SQL error
        // on every tick.
        $columns = $this->record->getConnection()->getSchemaBuilder()->getColumnListing($this->record->getTable());
        $state = array_intersect_key($this->form->getRawState(), array_flip($columns));

        $this->record->fill($state)->save();
        $this->dispatch('autosaved-at', time: now()->format('g:i:s A'));
    }
}
