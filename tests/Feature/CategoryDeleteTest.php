<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionChange;
use App\Models\User;
use App\Services\Crud\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Tests for category deletion and the move-then-delete workflow.
 *
 * The Filament table's DeleteAction calls merge() then delete() when a move
 * target is specified. CategoryService::beforeDelete() throws ValidationException
 * when questions still belong to the category, so the test exercises the same
 * sequence of service calls.
 */
class CategoryDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function service(): CategoryService
    {
        return app(CategoryService::class);
    }

    // -------------------------------------------------------------------------
    // 1. Delete non-empty category without a move target → ValidationException
    // -------------------------------------------------------------------------

    public function test_deleting_non_empty_category_without_move_target_throws_validation_exception(): void
    {
        $category = Category::factory()->create();
        Question::factory(3)->create(['category_id' => $category->id]);

        $this->expectException(ValidationException::class);

        $this->service()->delete($category);
    }

    public function test_validation_exception_contains_helpful_message(): void
    {
        $category = Category::factory()->create();
        Question::factory()->create(['category_id' => $category->id]);

        try {
            $this->service()->delete($category);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertStringContainsStringIgnoringCase('questions', implode(' ', array_merge(...array_values($e->errors()))));
        }
    }

    // -------------------------------------------------------------------------
    // 2. Move questions to target, then delete
    // -------------------------------------------------------------------------

    public function test_deleting_with_move_target_moves_all_questions_then_deletes_category(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $from = Category::factory()->create(['name' => 'Source']);
        $into = Category::factory()->create(['name' => 'Target']);
        Question::factory(3)->create(['category_id' => $from->id]);

        // Mirror the Filament action: merge first, then delete
        $moved = $this->service()->merge($from, $into);
        $this->service()->delete($from);

        $this->assertSame(3, $moved);
        $this->assertSame(3, $into->questions()->count());
        $this->assertSame(0, $from->questions()->count());
        $this->assertDatabaseMissing('categories', ['id' => $from->id]);
    }

    // -------------------------------------------------------------------------
    // 3. Deleting an empty category succeeds directly
    // -------------------------------------------------------------------------

    public function test_deleting_empty_category_succeeds_without_merge(): void
    {
        $category = Category::factory()->create();

        $this->service()->delete($category);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    // -------------------------------------------------------------------------
    // 4. Moving questions is recorded in dash_question_changes
    // -------------------------------------------------------------------------

    public function test_moving_questions_during_category_delete_is_recorded_in_audit_trail(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $from = Category::factory()->create(['name' => 'Old']);
        $into = Category::factory()->create(['name' => 'New']);
        $question = Question::factory()->create(['category_id' => $from->id]);

        $this->service()->merge($from, $into);

        $change = QuestionChange::where('question_id', $question->id)->first();
        $this->assertNotNull($change, 'A QuestionChange record should exist after moving the question.');
        $this->assertSame($user->id, $change->user_id);
        $this->assertSame($from->id, $change->changes['category_id']['old']);
        $this->assertSame($into->id, $change->changes['category_id']['new']);
    }
}
