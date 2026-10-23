<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">SCN Card Trip Details #{{ $trip->trip_id }}</h5>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-3">
                <tbody>
                    <tr>
                        <th style="width: 35%;">SCN Card Status</th>
                        <td>
                            @if($trip->gocab_status === 'COVERED')
                                <span class="badge bg-success">COVERED (Charged)</span>
                            @elseif($trip->gocab_status === 'UNCOVERED')
                                <span class="badge bg-danger">UNCOVERED (Declined)</span>
                            @elseif($trip->gocab_status === 'VOID')
                                <span class="badge bg-secondary">VOID (Reversed)</span>
                            @else
                                <span class="badge bg-warning text-dark">{{ $trip->gocab_status ?? 'N/A' }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>SCN Card Trip ID</th>
                        <td><strong>{{ $trip->gocab_payment_id ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <th>SCN Card Number</th>
                        <td><code>{{ $trip->gocab_card_number ?? 'N/A' }}</code></td>
                    </tr>
                    <tr>
                        <th>Charged Fare Amount</th>
                        <td>${{ number_format((float)$trip->gocab_paid, 2) }}</td>
                    </tr>
                    @if($trip->gocab_reason)
                    <tr class="table-danger">
                        <th>Decline Reason</th>
                        <td class="text-danger fw-bold">{{ $trip->gocab_reason }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Pickup Location</th>
                        <td>
                            {{ $trip->location_from }}
                            @if($trip->pickup_lat && $trip->pickup_lng)
                                <br><small class="text-muted">Coords: ({{ $trip->pickup_lat }}, {{ $trip->pickup_lng }})</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Dropoff Location</th>
                        <td>
                            {{ $trip->location_to }}
                            @if($trip->dropoff_lat && $trip->dropoff_lng)
                                <br><small class="text-muted">Coords: ({{ $trip->dropoff_lat }}, {{ $trip->dropoff_lng }})</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>CarSafe Reference</th>
                        <td><code>{{ $trip->trip_id }}</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($trip->gocab_response)
        <div class="accordion" id="accordionGoCabRaw">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingRaw">
                    <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRaw">
                        View Raw SCN Card API Response
                    </button>
                </h2>
                <div id="collapseRaw" class="accordion-collapse collapse" data-bs-parent="#accordionGoCabRaw">
                    <div class="accordion-body p-2">
                        <pre class="bg-light p-2 rounded mb-0" style="font-size: 11px; max-height: 200px; overflow-y: auto;">{{ json_encode($trip->gocab_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class="modal-footer d-flex justify-content-between">
        <div>
            @if($trip->canVoidGoCab())
                <form method="POST" action="{{ url('admin/trips/' . $trip->trip_id . '/gocab-void') }}" onsubmit="return confirm('Are you sure you want to VOID and refund this SCN Card trip? This cannot be undone.');" style="display:inline-block;">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fa fa-undo me-1"></i> Void SCN Card Trip (Refund)
                    </button>
                </form>
            @endif
        </div>
        <button class="btn btn-dark" type="button" data-bs-dismiss="modal">Close</button>
    </div>
</div>
