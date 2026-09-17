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
        <form method="post" action="/fields/{{ $field->id }}/submit-color">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <h3>Submit Color Report</h3>
                </div>

                <div class="col-12 mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-12 mb-3">
                    <label for="color_option" class="form-label">Color</label>
                    <select class="form-select" id="color_option" name="color_option" required>
                        <option value="5">Dark Green 5</option>
                        <option value="4">Med Green 4</option>
                        <option value="3">Med/Light Green 3</option>
                        <option value="2">Light Green 2</option>
                        <option value="1">Yellow Green 1</option>
                        <option value="TD">Turf Dormant TD</option>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="color_comments" class="form-label">Comments</label>
                    <textarea class="form-control" id="color_comments" name="color_comments" rows="3"></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('fields.parts.footer')