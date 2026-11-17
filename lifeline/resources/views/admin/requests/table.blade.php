{{-- Admin request table. Needs $bloodRequests (hospital and requester loaded). --}}
@if ($bloodRequests->isEmpty())
    <p class="text-secondary">No blood requests found.</p>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle bg-white">
            <thead>
                <tr>
                    <th scope="col">Reference</th>
                    <th scope="col">Status</th>
                    <th scope="col">Blood type</th>
                    <th scope="col">Units</th>
                    <th scope="col">Urgency</th>
                    <th scope="col">Hospital</th>
                    <th scope="col">Requester</th>
                    <th scope="col">Submitted</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bloodRequests as $row)
                    <tr>
                        <td><a href="{{ route('admin.requests.show', $row) }}" class="font-monospace">{{ $row->reference_number }}</a></td>
                        <td>@include('partials.request-status', ['status' => $row->status])</td>
                        <td>{{ $row->blood_type }}</td>
                        <td>{{ $row->units }}</td>
                        <td>{{ ucfirst($row->urgency) }}</td>
                        <td>{{ $row->hospital->name }}</td>
                        <td>{{ $row->requester->first_name }} {{ $row->requester->last_name }}</td>
                        <td class="text-nowrap">{{ $row->created_at->format('M j, Y g:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
