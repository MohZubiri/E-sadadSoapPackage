@extends('esadad::layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Verify OTP</div>

                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <p class="mb-4">Please enter the 6-digit OTP sent to your registered mobile number.</p>

                        <form method="POST" action="{{ route('esadad.otp.verify') }}">
                            @csrf

                            <div class="form-group row mb-3">
                                <label for="otp" class="col-md-4 col-form-label text-md-right">OTP</label>
                                <div class="col-md-6">
                                    <input id="otp" type="text" class="form-control @error('otp') is-invalid @enderror" 
                                           name="otp" required autofocus maxlength="6" pattern="\d{6}" 
                                           title="Please enter a 6-digit OTP">

                                    @error('otp')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Verify OTP & Complete Payment
                                    </button>
                                    
                                    <a href="{{ route('esadad.form') }}" class="btn btn-link">
                                        Back to Payment
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
