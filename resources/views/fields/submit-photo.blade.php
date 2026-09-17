@include('fields.parts.header')

<div class="row">
    <div class="col-md-3">
        <div class="field-lede">
            <a href="/fields/{{ $field->id }}" class="btn mb-2 btn-outline-secondary">&laquo; Back To Field</a>
            @if (isset($field->id))
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $field->name }}</h5>
                    @if ($field->address)
                    <p class="card-text">{{ $field->address }}, {{ $field->city }}, {{ $field->state }}</p>
                    @else
                    <p class="card-text">No address provided</p>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="/fields/{{ $field->id }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi-info-circle"></i>
                        View Field Reports
                    </a>

                    <a href="/fields/{{ $field->id }}/edit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi-pencil"></i>
                        Edit
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="col-md-9">
        <form method="post" action="/fields/{{ $field->id }}/submit-photo" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12">
                    <h3>Submit Photo</h3>
                </div>

                <div class="col-12 mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}">
                </div>

                <div class="col-12 mb-3">
                    <label for="photo" class="form-label">Photo</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required>
                </div>

                <div class="col-12 mb-3">
                    <label for="photo_comments" class="form-label">Comments</label>
                    <textarea class="form-control" id="photo_comments" name="photo_comments" rows="3"></textarea>
                </div>

                <div class="col-12 mb-3">
                    <label for="associated_report" class="form-label">Associated Report (Optional)</label>
                    <select name="associated_report" id="associated_report" class="form-select">
                        <option value="">Select an Associated Report</option>
                        @foreach ($reports as $r)
                        <option value="{{ $r->id }}">{{ ucfirst($r->type) }} Report - {{ $r->evaluation_date }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Submit Photo</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('fields.parts.footer')