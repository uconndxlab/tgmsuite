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
        <form method="post" action="/fields/{{ $field->id }}/submit-fertilization">
            @csrf
            <div class="row">
                <div class="col-12">
                    <h3>Submit Fertilization Report</h3>
                </div>

                <div class="col-12">
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="product" class="form-label">Product</label>
                        <input type="text" class="form-control" id="product" name="product">
                    </div>

                    <div class="mb-3">
                        <label for="rate" class="form-label">Rate</label>
                        <input type="text" class="form-control" id="rate" name="rate">
                    </div>

                    <div class="mb-3">
                        <label for="npk" class="form-label">NPK Analysis</label>
                        <input type="text" class="form-control" id="npk" name="npk">
                    </div>

                    <div class="mb-3">
                        <label for="compost" class="form-label">Compost Analysis</label>
                        <input type="text" class="form-control" id="compost" name="compost">
                    </div>

                    <div class="mb-3">
                        <label for="biostimulant" class="form-label">Biostimulant?</label>
                        <select class="form-select" id="biostimulant" name="biostimulant">
                            <option value="yes">Yes</option>
                            <option selected value="no">No</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="fert_comments" class="form-label">Comments</label>
                        <textarea class="form-control" id="fert_comments" name="fert_comments" rows="3"></textarea>
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