<?php
// throwaway: php artisan tinker is flaky in -T mode, so use a bootstrapped script
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
if (($argv[1] ?? '') === 'setup') {
    DB::statement('SET FOREIGN_KEY_CHECKS=0'); DB::table('users')->truncate(); DB::table('dash_review_batches')->truncate();
    $u = App\Models\User::create(['name'=>'Dash','email'=>'dash@example.com','password'=>'supersecret12','is_admin'=>true]);
    DB::table('users')->where('id',$u->id)->update(['id'=>1]);
    DB::table('dash_review_batches')->insert(['name'=>'B1','reviewer_id'=>1]);
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
}
foreach (DB::table('users')->orderBy('id')->get(['id','name','email','remember_token','is_admin']) as $u) echo json_encode($u)."\n";
echo 'distinct player hashes: '.DB::table('users')->where('email','like','%@players.invalid')->distinct()->count('password')."\n";
echo 'batch reviewer_ids: '.json_encode(DB::table('dash_review_batches')->pluck('reviewer_id'))."\n";
echo 'admin hash ok: '.(Hash::check('supersecret12', DB::table('users')->where('email','dash@example.com')->value('password')) ? 'yes':'no')."\n";
echo 'databases: '.json_encode(array_column(DB::select('show databases'),'Database'))."\n";
