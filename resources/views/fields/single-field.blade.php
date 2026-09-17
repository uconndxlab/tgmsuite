@include('fields.parts.header')

<div class="row">
    <div class="col-md-3">
        <div class="field-lede">
            <a href="/fields" class="btn mb-2 btn-outline-secondary">&laquo; Back To Fields</a>

            @if (($field->id ?? 0) != 0)
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $field->name }}</h5>
                    @if ($field->address)
                    <p class="card-text">{{ $field->address }}, {{ $field->city }}, {{ $field->state }}</p>
                    @else
                    <p class="card-text">No address provided</p>
                    @endif

                    <form class="d-inline" action="/fields/{{ $field->id }}/delete" method="post" onsubmit="return confirm('Are you sure you want to delete this field?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-link text-danger" style="padding-left: 0;">
                            <i class="bi-trash"></i>
                            Delete Field
                        </button>
                    </form>
                </div>
                <div class="card-footer">
                    <a href="/fields/{{ $field->id }}" class="btn btn-sm btn-outline-secondary @if (!$edit) active @endif">
                        <i class="bi-info-circle"></i>
                        View Field Reports
                    </a>

                    <a href="/fields/{{ $field->id }}/edit" class="btn btn-sm btn-outline-secondary @if ($edit) active @endif">
                        <i class="bi-pencil"></i>
                        Edit
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="col-md-9">
        @if (session('message') || isset($message))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') ?? $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if ($edit)
            @include('fields.components.field-edit', ['row' => $field])
        @else
        <div class="row mb-4 mt-3">
            <div class="col-md-12 d-flex">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <p>Click on "Add Report" to submit a report on the field's turf quality, maintenance actions, upload a photo, or other actions.</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <div class="col-md-12 d-flex">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle btn-lg"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Add Report
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/fields/{{ $field->id }}/quality-checklist">Turf Quality Rating</a></li>
                        <li><a class="dropdown-item" href="/fields/{{ $field->id }}/submit-fertilization">Fertilization Report</a></li>
                        <li><a class="dropdown-item" href="/fields/{{ $field->id }}/submit-cultivation">Cultivation Report</a></li>
                        <li><a class="dropdown-item" href="/fields/{{ $field->id }}/submit-topdressing">Topdressing Report</a></li>
                        <li><a class="dropdown-item" href="/fields/{{ $field->id }}/submit-overseeding">Overseeding Report</a></li>
                        <li><a class="dropdown-item" href="/fields/{{ $field->id }}/submit-color">Color Report</a></li>
                        <li><a class="dropdown-item" href="/fields/{{ $field->id }}/submit-thatch">Thatch Accumulation</a></li>
                        <li><a class="dropdown-item" href="/fields/{{ $field->id }}/submit-soil">Soil Test</a></li>
                        <li><a class="dropdown-item" href="/fields/{{ $field->id }}/submit-pest">Pest Management</a></li>
                        <li><a class="dropdown-item" href="/fields/{{ $field->id }}/submit-photo">Add a Photo</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <h4>Report History</h4>
            </div>
            <div class="col-12">
                <p>Below is a list of all reports for this field. Click on a report to view more details.</p>

                <form action="/fields/{{ $field->id }}" method="get">
                    <div class="row my-3">
                        <div class="col-4">
                            <label for="type" class="form-label">Filter by Type</label>
                            <select class="form-select" name="type" id="type" onchange="this.form.submit()">
                                <option value="">All</option>
                                <option value="evaluation" @if(request('type') == 'evaluation') selected @endif>Turf Quality Rating</option>
                                <option value="fertilization" @if(request('type') == 'fertilization') selected @endif>Fertilization Event</option>                                  
                                <option value="cultivation" @if(request('type') == 'cultivation') selected @endif>Cultivation Report</option>
                                <option value="topdressing" @if(request('type') == 'topdressing') selected @endif>Topdressing Report</option>                          
                                <option value="overseeding" @if(request('type') == 'overseeding') selected @endif>Overseeding Report</option>
                                <option value="color" @if(request('type') == 'color') selected @endif>Color Report</option>
                                <option value="thatch_accumulation" @if(request('type') == 'thatch_accumulation') selected @endif>Thatch Accumulation</option>
                                <option value="soil_test" @if(request('type') == 'soil_test') selected @endif>Soil Test Report</option>
                                <option value="pest" @if(request('type') == 'pest') selected @endif>Pest Management</option>
                                <option value="photo" @if(request('type') == 'photo') selected @endif>Photo Report</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" id="date" value="{{ request('date') }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-4 d-flex align-items-end">
                            <a href="/fields/{{ $field->id }}" class="btn btn-outline-secondary">Clear Filter</a>
                        </div>
                    </div>
                </form>

                <table id="report_results" class="table table-striped show-full-table-mobile">
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Type</th>
                            <th scope="col">Evaluator</th>
                            <th scope="col" class="th-hide">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                        <tr>
                            <td>{{ $report->evaluation_date }}</td>
                            <td>
                                @if ($report->type == 'thatch_accumulation')
                                    Thatch Accumulation
                                @elseif ($report->type == 'soil_test')
                                    Soil Test
                                @elseif ($report->type == 'evaluation')
                                    Turf Quality
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $report->type)) }}
                                @endif
                            </td>
                            <td>{{ $report->evaluator->name ?? $report->evaluator->email ?? 'Evaluator' }}</td>
                            <td class="th-hide">
                                <a href="/report/{{ $report->id }}/view" class="btn btn-md btn-outline-primary">View Report</a>
                                <form class="d-inline" action="/report/{{ $report->id }}/delete" method="post" onsubmit="return confirm('Are you sure you want to delete this report?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-danger">
                                        <i class="bi-trash"></i>
                                        Delete Report
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No reports found for this field.</td>
                        </tr>
                        @endforelse
                    </tbody>                    
                </table>

                <div class="mt-4">
                    <a href="/fields" class="btn btn-outline-secondary">&laquo; Back To Fields</a>
                    <a href="/fields/{{ $field->id }}/view-all-reports" class="btn btn-primary" style="margin-left: 15px;">View All Reports</a>
                    <button onclick="window.print()" class="btn btn-secondary" style="margin-left: 15px;"><i class="bi bi-printer"></i> Print</button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@include('fields.parts.footer')