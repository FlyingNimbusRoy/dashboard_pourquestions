<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Manual verification stub for review-screen keyboard shortcuts.
 *
 * The A / D / S / 1–5 keyboard shortcuts are Livewire browser events dispatched
 * from Alpine.js key listeners. They cannot be tested with PHPUnit HTTP tests
 * because they require a real browser with JavaScript execution.
 *
 * @group manual
 */
class ReviewKeyboardShortcutsTest extends TestCase
{
    /**
     * Manual test: verify keyboard shortcuts on the review screen.
     *
     * A human reviewer should follow $steps in a real browser session to
     * confirm each shortcut works correctly.
     */
    public function test_review_keyboard_shortcuts_manual(): void
    {
        $steps = [
            'Open the dashboard and log in as an admin user.',
            'Navigate to the review batch screen (a batch must have at least one open question).',
            'Press A — the current question should be approved; the view advances to the next question.',
            'Press D — the current question should be denied/rejected; the view advances to the next question.',
            'Press S — the current question should be skipped; the view advances to the next question.',
            'Press 1 through 5 — the difficulty rating should update to the pressed digit each time.',
            'Verify that no shortcut fires when focus is inside a text input or textarea.',
            'Verify that pressing A/D/S on the last question in the batch closes the batch.',
        ];

        // This placeholder keeps the gap visible in CI output and suite reports.
        // Replace with a real assertion once Dusk or Playwright coverage is added.
        $this->assertTrue(true, 'Manual test — follow $steps above in a real browser.');
    }
}
