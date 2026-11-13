{{-- Page-level messages. Field errors are shown next to each field with @error. --}}
@if (session('status'))
    <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
@endif
