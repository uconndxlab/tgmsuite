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
        <form method="post" action="/fields/{{ $field->id }}/submit-overseeding">
            @csrf
            <div class="row">
                <div class="col-12">
                    <h3>Submit Overseeding Report</h3>
                </div>

                <div class="col-12">
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="rate" class="form-label">Rate</label>
                        <input type="text" class="form-control" id="rate" name="rate">
                    </div>

                    <div class="mb-3">
                        <label for="formula" class="form-label">Formula</label>
                        <input type="text" class="form-control" id="formula" name="formula">
                    </div>

                    <div class="mb-3">
                        <label for="pre_germ" class="form-label">Pre-Germ Y/N</label>
                        <select class="form-select" id="pre_germ" name="pre_germ">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="species" class="form-label">Species</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tall_fescue" name="species[]" value="tall fescue">
                            <label for="tall_fescue">Tall fescue</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perennial_ryegrass" name="species[]" value="perennial ryegrass">
                            <label for="perennial_ryegrass">Perennial Ryegrass</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="kentucky_bluegrass" name="species[]" value="kentucky bluegrass">
                            <label for="kentucky_bluegrass">Kentucky Bluegrass</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="fine_fescue" name="species[]" value="fine fescue">
                            <label for="fine_fescue">Fine Fescue</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="other_species" name="species[]" value="other">
                            <label for="other_species">Other</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="overseed_comments" class="form-label">Comments</label>
                        <textarea class="form-control" id="overseed_comments" name="overseed_comments" rows="3"></textarea>
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