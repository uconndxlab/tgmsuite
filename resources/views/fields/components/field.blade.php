<!-- bootstrap card-->
<div class="card">
    <div class="card-body">
      <h5 class="card-title">{{ $row->name }}</h5>

      @if ($row->address)
        <p class="card-text">{{ $row->address }}, {{ $row->city }}, {{ $row->state }}</p>
      @endif

      <p class="card-text">
        <small class="text-muted">
          <i class="bi bi-file-earmark-text"></i> {{ $row->reports_count ?? $row->reports()->count() }} report{{ ($row->reports_count ?? $row->reports()->count()) != 1 ? 's' : '' }}
        </small>
      </p>

      <a class="btn btn-primary card-link" href="/fields/{{ $row->id }}">View</a>
    </div>
</div>