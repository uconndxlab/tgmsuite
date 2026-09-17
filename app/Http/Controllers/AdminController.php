<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function submissions(Request $request)
    {
        $query = Report::with(['field', 'evaluator'])
            ->orderByDesc('evaluation_date')
            ->orderByDesc('id');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date')) {
            $query->where('evaluation_date', $request->input('date'));
        }

        $submissions = $query->get();
        $report_types = Report::select('type')->distinct()->orderBy('type')->pluck('type')->toArray();

        return view('fields.admin-submissions', [
            'submissions' => $submissions,
            'report_types' => $report_types,
            'selected_type' => $request->input('type'),
            'selected_date' => $request->input('date'),
        ]);
    }
}
