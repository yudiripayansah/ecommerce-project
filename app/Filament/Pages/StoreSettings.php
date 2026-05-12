<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Store Settings';

    protected static ?string $title = 'Store Settings';

    protected string $view = 'filament.pages.store-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'store_name'    => Setting::get('store_name', config('app.name', '')),
            'store_email'   => Setting::get('store_email', ''),
            'store_phone'   => Setting::get('store_phone', ''),
            'bank_accounts' => json_decode(Setting::get('bank_accounts', '[]'), true) ?: [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Store information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('store_name')
                            ->label('Store name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('store_email')
                            ->label('Contact email')
                            ->email()
                            ->maxLength(150),
                        TextInput::make('store_phone')
                            ->label('Contact phone')
                            ->tel()
                            ->maxLength(20),
                    ]),

                Section::make('Bank accounts')
                    ->description('Shown to customers who choose Bank Transfer at checkout.')
                    ->schema([
                        Repeater::make('bank_accounts')
                            ->label(false)
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Bank name')
                                    ->required()
                                    ->placeholder('BCA, Mandiri, BNI…')
                                    ->maxLength(50),
                                TextInput::make('account_number')
                                    ->label('Account number')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('account_holder')
                                    ->label('Account holder name')
                                    ->required()
                                    ->maxLength(100),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add bank account')
                            ->reorderable(false)
                            ->defaultItems(0),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save settings')
                ->icon('heroicon-o-check')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::setMany([
            'store_name'    => $data['store_name'] ?? '',
            'store_email'   => $data['store_email'] ?? '',
            'store_phone'   => $data['store_phone'] ?? '',
            'bank_accounts' => json_encode(array_values($data['bank_accounts'] ?? [])),
        ]);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
