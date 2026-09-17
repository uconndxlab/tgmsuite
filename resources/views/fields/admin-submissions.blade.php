@include('fields.parts.header')

<div class="container">
    <h2>Admin: All Submissions</h2>
    
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>Admin View:</strong> You are viewing all submissions across all fields.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filter Submissions</h5>
            <form method="GET" action="/admin/submissions" class="row g-3">
                <div class="col-md-4">
                    <label for="type" class="form-label">Report Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All Types</option>
                        @foreach ($report_types as $type)
                        <option value="{{ $type }}" @if ($selected_type == $type) selected @endif>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ $selected_date }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                    <a href="/admin/submissions" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <p class="lead">
                Total Submissions: <strong>{{ $submissions->count() }}</strong>
                @if ($selected_type || $selected_date)
                <span class="text-muted">(filtered)</span>
                @endif
            </p>
        </div>
    </div>

    @if ($submissions->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Field Name</th>
                    <th>Location</th>
                    <th>Evaluator</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($submissions as $submission)
                <tr>
                    <td>{{ $submission->id }}</td>
                    <td>{{ $submission->evaluation_date }}</td>
                    <td>
                        <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $submission->type)) }}</span>
                    </td>
                    <td>
                        <strong>{{ $submission->field->name ?? 'N/A' }}</strong>
                    </td>
                    <td>{{ $submission->field->city ?? '' }}, {{ $submission->field->state ?? '' }}</td>
                    <td>
                        {{ $submission->evaluator->name ?? 'Unknown' }}<br>
                        <small class="text-muted">{{ $submission->evaluator->email ?? '' }}</small>
                    </td>
                    <td>
                        <a href="/report/{{ $submission->id }}/view" class="btn btn-sm btn-outline-primary">
                            View Report
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-warning" role="alert">
        No submissions found in the system.
    </div>
    @endif
</div>

@include('fields.parts.footer')
