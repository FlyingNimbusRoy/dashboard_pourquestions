<?php
namespace Tests\Feature;
use Tests\TestCase;
class ZzEnvCheckTest extends TestCase {
    public function test_env(): void {
        $this->assertSame('testing', app()->environment());
        $this->assertSame('pourquestions_testing', config('database.connections.'.config('database.default').'.database'));
        $this->assertSame('source.invalid', env('SOURCE_DB_HOST'));
    }
}
