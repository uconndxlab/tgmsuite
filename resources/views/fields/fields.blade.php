@include('fields.parts.header')

<div class="container">
    <h2>My Fields</h2>
    <div class="row mb-3">
        <div class="col-12">
            <a hx-boost="true" href="/field/create" class="btn btn-primary btn-lg">Add Field</a>
        </div>
    </div>

    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <p>Welcome to the UConn Athletic Field Assessment Tool. Below, you can see the fields you have added. Click on "View" to review each field's history or to edit your assessment reports.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    @if (session('message') || isset($message))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') ?? $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        @foreach ($rows as $row)
            <div class="col-md-4 mb-3">
                @include('fields.components.field', ['row' => $row])
            </div>
        @endforeach

        @if ($rows->isEmpty())
            <div class="col-md-12">
                <div class="alert alert-info" role="alert">
                    You have no fields. <a href="/field/create">Add a field</a>
                </div>
            </div>
        @endif
    </div>
</div>

@include('fields.parts.footer')