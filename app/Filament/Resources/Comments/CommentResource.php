<?php

namespace App\Filament\Resources\Comments;

use App\Filament\Resources\Comments\Pages\CreateComment;
use App\Filament\Resources\Comments\Pages\EditComment;
use App\Filament\Resources\Comments\Pages\ListComments;
use App\Models\Comment;
use App\Services\Crud\CommentService;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Game setup';

    protected static ?int $navigationSort = 28;

    protected static ?string $modelLabel = 'comment';

    protected static ?string $pluralModelLabel = 'comments';

    protected static ?string $recordTitleAttribute = 'comment';

    public static function getEloquentQuery(): Builder
    {
        return app(CommentService::class)->query();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('comment')->required()->maxLength(255)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()->paginationPageOptions([10, 25, 50])->defaultPaginationPageOption(25)
            ->defaultSort('id', 'desc')
            ->columns([
            TextColumn::make('id')->sortable(),
            TextColumn::make('comment')->searchable()->wrap(),
            TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
            
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(fn (Comment $record) => static::deleteViaService($record)),
            ]);
    }

    /** Delete through the CRUD service; service rule violations become a danger notification. */
    public static function deleteViaService(Comment $record): bool
    {
        try {
            app(CommentService::class)->delete($record);

            return true;
        } catch (ValidationException $e) {
            Notification::make()->danger()->title(collect($e->errors())->flatten()->implode(' '))->send();

            return false;
        }
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComments::route('/'),
            'create' => CreateComment::route('/create'),
            'edit' => EditComment::route('/{record}/edit'),
        ];
    }
}
