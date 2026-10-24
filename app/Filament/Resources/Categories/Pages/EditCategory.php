<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Questions\Support\ServiceDelete;
use App\Models\Category;
use App\Services\Analytics\CategoryStats;
use App\Services\Crud\CategoryService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(ServiceDelete::single(CategoryService::class)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(CategoryService::class)->update($record, $data);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Group::make([
                    $this->getFormContentComponent(),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Stats')
                        ->schema([
                            View::make('filament.categories.side-panel')
                                ->viewData(fn () => $this->categorySidePanelData()),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    /** @return array<string, mixed> */
    private function categorySidePanelData(): array
    {
        /** @var Category $category */
        $category = $this->getRecord();
        $stats = app(CategoryStats::class);

        return [
            'questionCount' => $stats->questionCount($category->id),
            'appeared'      => $stats->appearedCount($category->id),
            'openIssues'    => $stats->openIssueCount($category->id),
        ];
    }
}
