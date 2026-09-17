<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Field;
use App\Models\Report;
use App\Models\Evaluation;
use App\Models\FertilizationReport;
use App\Models\Photo;
use App\Models\ColorReport;
use App\Models\TopdressingReport;
use App\Models\OverseedReport;
use App\Models\CultivationReport;
use App\Models\PestManagementReport;
use App\Models\ThatchAccumulationReport;
use App\Models\SoilTest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class FieldReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $fields = $user->is_admin
            ? Field::withCount('reports')->orderBy('name')->get()
            : $user->fields()->withCount('reports')->orderBy('name')->get();

        return view('fields.fields', [
            'fields' => $fields,
            'rows' => $fields,
        ]);
    }

    public function create()
    {
        $field = new Field();
        $field->id = 0;
        $edit = true;

        return view('fields.single-field', compact('field', 'edit'));
    }

    public function show(Field $field, Request $request)
    {
        Gate::authorize('view', $field);

        $reportsQuery = $field->reports()->with('evaluator');

        if ($request->filled('date')) {
            $reportsQuery->where('evaluation_date', $request->date);
        }
        if ($request->filled('type')) {
            $reportsQuery->where('type', $request->type);
        }

        $reports = $reportsQuery->orderByDesc('evaluation_date')->orderByDesc('id')->get();
        $edit = false;

        return view('fields.single-field', compact('field', 'reports', 'edit'));
    }

    public function edit(Field $field)
    {
        Gate::authorize('update', $field);

        $edit = true;
        return view('fields.single-field', compact('field', 'edit'));
    }

    public function storeOrUpdateField(Request $request, $id = 0)
    {
        $data = $request->except(['_token']);

        foreach (['soil_texture', 'water_source', 'sports_played', 'turfgrass_species_present'] as $fieldKey) {
            if ($request->has($fieldKey) && is_array($request->input($fieldKey))) {
                $data[$fieldKey] = implode(',', $request->input($fieldKey));
            }
        }

        if ($id == 0) {
            $field = Field::create($data);
            $field->users()->attach(Auth::id(), ['permission_level' => 'admin']);
        } else {
            $field = Field::findOrFail($id);
            Gate::authorize('update', $field);
            $field->update($data);
        }

        return redirect()->route('fields.show', $field->id)->with('message', 'Field Updated');
    }

    public function destroyField(Field $field)
    {
        Gate::authorize('delete', $field);

        $field->delete();
        return redirect()->route('fields.index')->with('message', 'Field deleted.');
    }

    // Quality Checklist
    public function createQualityChecklist(Field $field)
    {
        Gate::authorize('view', $field);
        return view('fields.quality-checklist', compact('field'));
    }

    public function storeQualityChecklist(Request $request, Field $field)
    {
        Gate::authorize('view', $field);

        $validated = $request->validate([
            'date' => 'nullable|date',
            'turfDensity' => 'required|numeric',
            'smoothness' => 'required|numeric',
            'weedsPercentage' => 'required|numeric',
            'stonesAtSurface' => 'required|numeric',
            'depressions' => 'required|numeric',
            'turfRating' => 'required|numeric',
            'surfaceRating' => 'required|numeric',
            'quality_comments' => 'nullable|string',
        ]);

        $evalDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $overallRating = $validated['turfRating'] - $validated['surfaceRating'];

        DB::transaction(function () use ($field, $validated, $evalDate, $overallRating) {
            $report = $field->reports()->create([
                'evaluator_id' => Auth::id(),
                'evaluation_date' => $evalDate,
                'type' => 'evaluation',
            ]);

            Evaluation::create([
                'report_id' => $report->id,
                'turf_density' => $validated['turfDensity'],
                'smoothness_rating' => $validated['smoothness'],
                'weeds_rating' => $validated['weedsPercentage'],
                'stones_at_surface' => $validated['stonesAtSurface'],
                'depressions' => $validated['depressions'],
                'turf_rating' => $validated['turfRating'],
                'surface_rating' => $validated['surfaceRating'],
                'overall_rating' => $overallRating,
                'quality_comments' => $validated['quality_comments'] ?? null,
            ]);
        });

        return redirect()->route('fields.show', $field->id)->with('message', 'Turf Rating Saved');
    }

    // Photo
    public function createPhoto(Field $field)
    {
        Gate::authorize('view', $field);
        $reports = $field->reports()->where('type', 'evaluation')->orderByDesc('evaluation_date')->get();
        return view('fields.submit-photo', compact('field', 'reports'));
    }

    public function storePhoto(Request $request, Field $field)
    {
        Gate::authorize('view', $field);

        $request->validate([
            'date' => 'nullable|date',
            'photo' => 'required|image|max:10240',
            'photo_comments' => 'nullable|string',
            'associated_report' => 'nullable|integer',
        ]);

        $evalDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $path = $request->file('photo')->store('uploads', 'public');

        DB::transaction(function () use ($field, $path, $request, $evalDate) {
            $report = $field->reports()->create([
                'evaluator_id' => Auth::id(),
                'evaluation_date' => $evalDate,
                'type' => 'photo',
            ]);

            Photo::create([
                'report_id' => $report->id,
                'photo_url' => 'storage/' . $path,
                'photo_comments' => $request->input('photo_comments'),
                'associated_report_id' => $request->input('associated_report'),
            ]);
        });

        return redirect()->route('fields.show', $field->id)->with('message', 'Photo Saved');
    }

    // Fertilization
    public function createFertilization(Field $field)
    {
        Gate::authorize('view', $field);
        return view('fields.submit-fert', compact('field'));
    }

    public function storeFertilization(Request $request, Field $field)
    {
        Gate::authorize('view', $field);

        $validated = $request->validate([
            'date' => 'required|date',
            'product' => 'nullable|string',
            'rate' => 'nullable|string',
            'npk' => 'nullable|string',
            'compost' => 'nullable|string',
            'biostimulant' => 'nullable|string',
            'fert_comments' => 'nullable|string',
        ]);

        DB::transaction(function () use ($field, $validated) {
            $report = $field->reports()->create([
                'evaluator_id' => Auth::id(),
                'evaluation_date' => $validated['date'],
                'type' => 'fertilization',
            ]);

            FertilizationReport::create([
                'report_id' => $report->id,
                'product' => $validated['product'] ?? null,
                'rate' => $validated['rate'] ?? null,
                'npk' => $validated['npk'] ?? null,
                'compost' => $validated['compost'] ?? null,
                'bio_stimulant' => $validated['biostimulant'] ?? null,
                'fert_comments' => $validated['fert_comments'] ?? null,
            ]);
        });

        return redirect()->route('fields.show', $field->id)->with('message', 'Fertilization Report Submitted');
    }

    // Color
    public function createColor(Field $field)
    {
        Gate::authorize('view', $field);
        return view('fields.submit-color', compact('field'));
    }

    public function storeColor(Request $request, Field $field)
    {
        Gate::authorize('view', $field);

        $validated = $request->validate([
            'date' => 'required|date',
            'color_option' => 'required|string',
            'color_comments' => 'nullable|string',
        ]);

        DB::transaction(function () use ($field, $validated) {
            $report = $field->reports()->create([
                'evaluator_id' => Auth::id(),
                'evaluation_date' => $validated['date'],
                'type' => 'color',
            ]);

            ColorReport::create([
                'report_id' => $report->id,
                'color_option' => $validated['color_option'],
                'color_comments' => $validated['color_comments'] ?? null,
            ]);
        });

        return redirect()->route('fields.show', $field->id)->with('message', 'Color Report Submitted');
    }

    // Topdressing
    public function createTopdressing(Field $field)
    {
        Gate::authorize('view', $field);
        return view('fields.submit-topdressing', compact('field'));
    }

    public function storeTopdressing(Request $request, Field $field)
    {
        Gate::authorize('view', $field);

        $validated = $request->validate([
            'date' => 'required|date',
            'topdressing_rate' => 'nullable|string',
            'topdressing_description' => 'nullable|string',
            'topdressing_comments' => 'nullable|string',
        ]);

        DB::transaction(function () use ($field, $validated) {
            $report = $field->reports()->create([
                'evaluator_id' => Auth::id(),
                'evaluation_date' => $validated['date'],
                'type' => 'topdressing',
            ]);

            TopdressingReport::create([
                'report_id' => $report->id,
                'topdressing_rate' => $validated['topdressing_rate'] ?? null,
                'topdressing_description' => $validated['topdressing_description'] ?? null,
                'topdressing_comments' => $validated['topdressing_comments'] ?? null,
            ]);
        });

        return redirect()->route('fields.show', $field->id)->with('message', 'Topdressing Report Submitted');
    }

    // Overseeding
    public function createOverseeding(Field $field)
    {
        Gate::authorize('view', $field);
        return view('fields.submit-overseeding', compact('field'));
    }

    public function storeOverseeding(Request $request, Field $field)
    {
        Gate::authorize('view', $field);

        $validated = $request->validate([
            'date' => 'required|date',
            'rate' => 'nullable|string',
            'formula' => 'nullable|string',
            'pre_germ' => 'nullable|string',
            'species' => 'nullable',
            'overseed_comments' => 'nullable|string',
        ]);

        $species = is_array($request->species) ? implode(', ', $request->species) : $request->species;

        DB::transaction(function () use ($field, $validated, $species) {
            $report = $field->reports()->create([
                'evaluator_id' => Auth::id(),
                'evaluation_date' => $validated['date'],
                'type' => 'overseeding',
            ]);

            OverseedReport::create([
                'report_id' => $report->id,
                'rate' => $validated['rate'] ?? null,
                'formula' => $validated['formula'] ?? null,
                'pre_germ' => $validated['pre_germ'] ?? null,
                'species' => $species,
                'overseed_comments' => $validated['overseed_comments'] ?? null,
            ]);
        });

        return redirect()->route('fields.show', $field->id)->with('message', 'Overseeding Report Submitted');
    }

    // Cultivation
    public function createCultivation(Field $field)
    {
        Gate::authorize('view', $field);
        return view('fields.submit-cultivation', compact('field'));
    }

    public function storeCultivation(Request $request, Field $field)
    {
        Gate::authorize('view', $field);

        $validated = $request->validate([
            'date' => 'required|date',
            'hollow_yes_no' => 'nullable|string',
            'hollow_notes' => 'nullable|string',
            'solid_yes_no' => 'nullable|string',
            'solid_notes' => 'nullable|string',
            'slice_yes_no' => 'nullable|string',
            'slice_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($field, $validated) {
            $report = $field->reports()->create([
                'evaluator_id' => Auth::id(),
                'evaluation_date' => $validated['date'],
                'type' => 'cultivation',
            ]);

            CultivationReport::create([
                'report_id' => $report->id,
                'hollow_yes_no' => $validated['hollow_yes_no'] ?? null,
                'hollow_notes' => $validated['hollow_notes'] ?? null,
                'solid_yes_no' => $validated['solid_yes_no'] ?? null,
                'solid_notes' => $validated['solid_notes'] ?? null,
                'slice_yes_no' => $validated['slice_yes_no'] ?? null,
                'slice_notes' => $validated['slice_notes'] ?? null,
            ]);
        });

        return redirect()->route('fields.show', $field->id)->with('message', 'Cultivation Report Submitted');
    }

    // Pest Management
    public function createPest(Field $field)
    {
        Gate::authorize('view', $field);
        return view('fields.submit-pest', compact('field'));
    }

    public function storePest(Request $request, Field $field)
    {
        Gate::authorize('view', $field);

        $data = $request->all();
        $date = $request->filled('date') ? $request->input('date') : now()->toDateString();

        $booleanColumns = [
            'broadleaf_dandelion', 'broadleaf_plantain', 'narrowleaf_plantain', 'heal_all',
            'common_chickweed', 'oxalis', 'spurge', 'knotweed', 'ground_ivy', 'violet',
            'mouse_ear_chickweed', 'clover_white', 'speedwell', 'other',
            'crabgrass', 'poa_annua', 'quackgrass', 'goosegrass', 'poa_trivialis',
            'bentgrass', 'tall_fescue', 'yellow_nutsedge', 'orchardgrass',
            'insects_grubs', 'insects_sod_webworm', 'insects_chinch_bug', 'insects_billbug',
            'disease_tall_fescue', 'disease_perennial_ryegrass', 'disease_kentucky_bluegrass',
            'disease_fine_fescue', 'disease_other'
        ];

        $reportData = [];
        foreach ($booleanColumns as $col) {
            $reportData[$col] = isset($data[$col]) ? 1 : 0;
        }

        $percentageColumns = [
            'broadleaf_dandelion_percent', 'broadleaf_plantain_percent', 'narrowleaf_plantain_percent',
            'heal_all_percent', 'common_chickweed_percent', 'oxalis_percent', 'spurge_percent',
            'knotweed_percent', 'ground_ivy_percent', 'violet_percent', 'mouse_ear_chickweed_percent',
            'clover_white_percent', 'speedwell_percent', 'other_percent', 'crabgrass_percent',
            'poa_annua_percent', 'quackgrass_percent', 'goosegrass_percent', 'poa_trivialis_percent',
            'bentgrass_percent', 'tall_fescue_percent', 'yellow_nutsedge_percent', 'orchardgrass_percent',
            'other_grasses_percent', 'insects_grubs_percent', 'insects_sod_webworm_percent',
            'insects_chinch_bug_percent', 'insects_billbug_percent', 'other_insects_percent',
            'disease_percent'
        ];

        foreach ($percentageColumns as $col) {
            $reportData[$col] = isset($data[$col]) ? intval($data[$col]) : 0;
        }

        $textColumns = [
            'broadleaf_control', 'crabgrass_control', 'other_grasses', 'insects_grubs_type',
            'other_insects', 'insects_control', 'disease_present', 'disease_control', 'pest_comments'
        ];

        foreach ($textColumns as $col) {
            $reportData[$col] = $data[$col] ?? null;
        }

        DB::transaction(function () use ($field, $date, $reportData) {
            $report = $field->reports()->create([
                'evaluator_id' => Auth::id(),
                'evaluation_date' => $date,
                'type' => 'pest',
            ]);

            $reportData['report_id'] = $report->id;
            PestManagementReport::create($reportData);
        });

        return redirect()->route('fields.show', $field->id)->with('message', 'Pest Management Report Submitted');
    }

    // Thatch
    public function createThatch(Field $field)
    {
        Gate::authorize('view', $field);
        return view('fields.submit-thatch', compact('field'));
    }

    public function storeThatch(Request $request, Field $field)
    {
        Gate::authorize('view', $field);

        $validated = $request->validate([
            'date' => 'required|date',
            'thatch_accumulation' => 'required|string',
            'thatch_comments' => 'nullable|string',
        ]);

        DB::transaction(function () use ($field, $validated) {
            $report = $field->reports()->create([
                'evaluator_id' => Auth::id(),
                'evaluation_date' => $validated['date'],
                'type' => 'thatch_accumulation',
            ]);

            ThatchAccumulationReport::create([
                'report_id' => $report->id,
                'thatch_accumulation' => $validated['thatch_accumulation'],
                'thatch_comments' => $validated['thatch_comments'] ?? null,
            ]);
        });

        return redirect()->route('fields.show', $field->id)->with('message', 'Thatch Report Submitted');
    }

    // Soil
    public function createSoil(Field $field)
    {
        Gate::authorize('view', $field);
        return view('fields.submit-soil', compact('field'));
    }

    public function storeSoil(Request $request, Field $field)
    {
        Gate::authorize('view', $field);

        $validated = $request->validate([
            'date' => 'required|date',
            'action_taken' => 'nullable|string',
            'soil_comments' => 'nullable|string',
        ]);

        DB::transaction(function () use ($field, $validated) {
            $report = $field->reports()->create([
                'evaluator_id' => Auth::id(),
                'evaluation_date' => $validated['date'],
                'type' => 'soil_test',
            ]);

            SoilTest::create([
                'report_id' => $report->id,
                'action_taken' => $validated['action_taken'] ?? null,
                'soil_comments' => $validated['soil_comments'] ?? null,
            ]);
        });

        return redirect()->route('fields.show', $field->id)->with('message', 'Soil Test Report Submitted');
    }

    // View Single Report
    public function viewReport(Report $report)
    {
        $field = $report->field;
        Gate::authorize('view', $field);

        $report->load('evaluator');
        $content = $report->details;
        $photos = Photo::where('associated_report_id', $report->id)->get();

        return view('fields.report', compact('report', 'field', 'content', 'photos'));
    }

    public function destroyReport(Report $report)
    {
        $field = $report->field;
        Gate::authorize('delete', $field);

        $fieldId = $report->field_id;
        $report->delete();

        return redirect()->route('fields.show', $fieldId)->with('message', 'Report deleted.');
    }

    // View All Reports for a Field
    public function viewAllReports(Field $field)
    {
        Gate::authorize('view', $field);

        $reports = $field->reports()
            ->withDetails()
            ->orderByDesc('evaluation_date')
            ->orderByDesc('id')
            ->get();

        return view('fields.view-all-reports', compact('field', 'reports'));
    }
}
