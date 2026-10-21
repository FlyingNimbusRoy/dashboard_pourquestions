<?php
use Livewire\Livewire;
use Filament\Actions\Testing\TestAction;
use App\Models\{Question, Category};
auth()->login(App\Models\User::where('email', 'roy@dashboard.local')->first());
$c = Category::withCount('questions')->where('id','>',0)->get()->first(fn($c)=>$c->questions_count>0);
$other = Category::whereKeyNot($c->id)->first();
$t = Livewire::test(App\Filament\Resources\Categories\Pages\ListCategories::class)
  ->mountAction(TestAction::make('merge')->table($c))->fillForm(['target'=>$other->id]);
echo "merge data: ".json_encode($t->get('mountedActions.0.data'))."\n";
$t = Livewire::test(App\Filament\Resources\Categories\Pages\ListCategories::class)
  ->mountAction(TestAction::make('delete')->table($c))->fillForm(['target'=>$other->id]);
echo "delete data: ".json_encode($t->get('mountedActions.0.data'))."\n";
$html = $t->html(); echo "has modal text: ".(str_contains($html,'Choose the category') ? 'yes':'no')."\n";
$asked = array_key_first(app(App\Services\Insights\QuestionUsage::class)->counts());
$t2 = Livewire::test(App\Filament\Resources\Questions\Pages\ListQuestions::class)->call('loadTable')
  ->selectTableRecords([$asked])->mountAction(TestAction::make('delete')->table()->bulk());
$h = $t2->html(); echo "selected: ".json_encode($t2->get('selectedTableRecords'))." ; has 'Warning': ".(str_contains($h,'Warning')?'yes':'no')." ; 'Are you sure': ".(str_contains($h,'Are you sure')?'yes':'no')."\n";
