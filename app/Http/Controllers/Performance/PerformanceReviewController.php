<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\PerformanceReview;
use App\Models\PerformanceTaskAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceReviewController extends Controller
{
    public function store(Request $request, PerformanceTaskAssignment $performanceTaskAssignment)
    {
        $this->authorizeManage();
        $this->ensureOwnership($performanceTaskAssignment);

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'efficiency_score' => 'nullable|integer|min:0|max:100',
            'quality_score' => 'nullable|integer|min:0|max:100',
            'summary' => 'nullable|string|max:1000',
            'strengths' => 'nullable|string|max:1000',
            'improvements' => 'nullable|string|max:1000',
            'review_period_start' => 'nullable|date',
            'review_period_end' => 'nullable|date|after_or_equal:review_period_start',
        ]);

        PerformanceReview::create([
            'employee_id' => $performanceTaskAssignment->employee_id,
            'reviewer_id' => Auth::id(),
            'task_assignment_id' => $performanceTaskAssignment->id,
            'review_type' => 'task',
            'rating' => $data['rating'],
            'efficiency_score' => $data['efficiency_score'] ?? 0,
            'quality_score' => $data['quality_score'] ?? 0,
            'summary' => $data['summary'] ?? null,
            'strengths' => $data['strengths'] ?? null,
            'improvements' => $data['improvements'] ?? null,
            'review_period_start' => $data['review_period_start'] ?? null,
            'review_period_end' => $data['review_period_end'] ?? null,
            'score_breakdown' => [
                'turnaround_minutes' => $performanceTaskAssignment->turnaround_minutes,
                'progress_percent' => $performanceTaskAssignment->progress_percent,
            ],
            'created_by' => Auth::user()->creatorId(),
        ]);

        return back()->with('success', __('Performance review recorded.'));
    }

    public function destroy(PerformanceReview $performanceReview)
    {
        $this->authorizeManage();
        abort_if($performanceReview->created_by !== Auth::user()->creatorId(), 403);

        $performanceReview->delete();

        return back()->with('success', __('Performance review removed.'));
    }

    private function authorizeManage(): void
    {
        abort_unless(Auth::user()->can('Manage Performance Pulse'), 403);
    }

    private function ensureOwnership(PerformanceTaskAssignment $assignment): void
    {
        abort_if($assignment->created_by !== Auth::user()->creatorId(), 403);
    }
}

