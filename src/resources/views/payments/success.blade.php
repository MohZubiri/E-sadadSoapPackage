@extends('esadad::layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        Payment Successful
                    </div>

                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        </div>
                        
                        <h3 class="mb-4">Thank You!</h3>
                        <p class="lead">Your payment has been processed successfully.</p>
                        
                        @if(isset($transaction))
                            <div class="mt-4 text-start">
                                <h5>Transaction Details</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Invoice ID</th>
                                        <td>{{ $transaction->invoice_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Amount</th>
                                        <td>{{ number_format($transaction->amount, 2) }} YER</td>
                                    </tr>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <td>{{ $transaction->bank_trx_id ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date</th>
                                        <td>{{ $transaction->created_at->format('M d, Y H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                        
                        <div class="mt-5">
                            <a href="{{ url('/') }}" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i> Return to Home
                            </a>
                            <a href="#" class="btn btn-outline-secondary ms-2" onclick="window.print()">
                                <i class="fas fa-print me-2"></i> Print Receipt
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
