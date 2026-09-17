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
        <form method="post" action="/fields/{{ $field->id }}/submit-thatch">
            @csrf
            <div class="row">
                <div class="col-12">
                    <h3>Submit Thatch Accumulation Report</h3>
                </div>

                <div class="col-12">
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="thatch_accumulation" class="form-label">Thatch Accumulation</label>
                        <select class="form-select" id="thatch_accumulation" name="thatch_accumulation" required>
                            <option value="0-0.5">0 - 1/2"</option>
                            <option value="0.5-1">1/2" to 1"</option>
                            <option value="1+">1" or More</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="thatch_comments" class="form-label">Comments</label>
                        <textarea class="form-control" id="thatch_comments" name="thatch_comments" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('fields.parts.footer')