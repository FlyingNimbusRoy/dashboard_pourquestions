<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class CharacterImages extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Game setup';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'character-images';

    protected static ?string $title = 'Character images';

    public function getSubheading(): ?string
    {
        return 'Images uploaded here are stored in public/img/characters/ and can be selected on the Characters page.';
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Upload image')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->schema([
                    FileUpload::make('image')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                        ->maxSize(5120)
                        ->disk('public_root')
                        ->directory('img/characters')
                        ->required(),
                ])
                ->action(function () {
                    Notification::make()->success()->title('Image uploaded.')->send();
                }),
        ];
    }

    public function deleteImage(string $filename): void
    {
        $clean = basename($filename);
        Storage::disk('public_root')->delete('img/characters/'.$clean);
        Notification::make()->success()->title('Image deleted.')->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.pages.character-images')
                ->viewData(['images' => $this->getImages()]),
        ]);
    }

    private function getImages(): array
    {
        $dir = public_path('img/characters');

        if (! File::isDirectory($dir)) {
            return [];
        }

        return collect(File::files($dir))
            ->map(fn ($f) => $f->getFilename())
            ->sort()
            ->values()
            ->all();
    }
}
