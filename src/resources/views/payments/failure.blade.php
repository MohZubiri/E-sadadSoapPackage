@extends('esadad::layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        Payment Failed
                    </div>

                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 5rem;"></i>
                        </div>
                        
                        <h3 class="mb-4">Payment Unsuccessful</h3>
                        <p class="lead">We're sorry, but there was an issue processing your payment.</p>
                        
                        @if(session('error'))
                            <div class="alert alert-warning mt-4">
                                <strong>Reason:</strong> {{ session('error') }}
                            </div>
                        @endif
                        
                        <div class="mt-5">
                            <a href="{{ route('esadad.form') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i> Try Again
                            </a>
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-home me-2"></i> Return to Home
                            </a>
                        </div>
                        
                        <div class="mt-4">
                            <p class="text-muted">
                                If you continue to experience issues, please contact our support team.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
