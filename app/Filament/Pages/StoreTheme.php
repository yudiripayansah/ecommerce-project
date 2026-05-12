<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\ThemeService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class StoreTheme extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Tema Toko';

    protected static ?string $title = 'Tema Toko';

    protected string $view = 'filament.pages.store-theme';

    public string $activeTheme = 'minimal';

    public function mount(): void
    {
        $this->activeTheme = Setting::get('theme', 'minimal');
    }

    public function themes(): array
    {
        return ThemeService::all();
    }

    public function activateTheme(string $key): void
    {
        if (! ThemeService::exists($key)) {
            return;
        }

        $this->activeTheme = $key;
        Setting::set('theme', $key);

        $label = ThemeService::get($key)['label'];

        Notification::make()
            ->title("Tema \"{$label}\" diaktifkan")
            ->success()
            ->send();
    }
}
