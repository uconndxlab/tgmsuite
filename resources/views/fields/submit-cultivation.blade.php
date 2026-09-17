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
        <form method="post" action="/fields/{{ $field->id }}/submit-cultivation">
            @csrf
            <div class="row">
                <div class="col-12">
                    <h3>Submit Cultivation Report</h3>
                </div>

                <div class="col-12">
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cultivation Type</label>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="hollow" name="hollow_yes_no" value="yes">
                            <label class="form-check-label" for="hollow">Hollow</label>
                            <input type="text" id="hollow_note" name="hollow_notes" placeholder="Additional Notes" class="form-control mt-1" style="display: none;">
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="solid" name="solid_yes_no" value="yes">
                            <label class="form-check-label" for="solid">Solid</label>
                            <input type="text" id="solid_note" name="solid_notes" placeholder="Additional Notes" class="form-control mt-1" style="display: none;">
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="slice" name="slice_yes_no" value="yes">
                            <label class="form-check-label" for="slice">Slice</label>
                            <input type="text" id="slice_note" name="slice_notes" placeholder="Additional Notes" class="form-control mt-1" style="display: none;">
                        </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
      function toggleCoverageInput(checkboxId, inputId) {
        const checkbox = document.getElementById(checkboxId);
        const input = document.getElementById(inputId);
        if (checkbox && input) {
            checkbox.addEventListener('change', function () {
              input.style.display = checkbox.checked ? 'block' : 'none';
            });
        }
      }
  
      toggleCoverageInput('hollow', 'hollow_note');
      toggleCoverageInput('solid', 'solid_note');
      toggleCoverageInput('slice', 'slice_note');
    });
</script>

@include('fields.parts.footer')