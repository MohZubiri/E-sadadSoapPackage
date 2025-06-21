@extends('esadad::layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">e-SADAD Payment</div>

                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('esadad.process') }}">
                            @csrf

                            <div class="form-group row mb-3">
                                <label for="customer_id" class="col-md-4 col-form-label text-md-right">Customer ID</label>
                                <div class="col-md-6">
                                    <input id="customer_id" type="text" class="form-control @error('customer_id') is-invalid @enderror" 
                                           name="customer_id" value="{{ old('customer_id') }}" required autofocus>

                                    @error('customer_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="customer_password" class="col-md-4 col-form-label text-md-right">Password</label>
                                <div class="col-md-6">
                                    <input id="customer_password" type="password" 
                                           class="form-control @error('customer_password') is-invalid @enderror" 
                                           name="customer_password" required>

                                    @error('customer_password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="amount" class="col-md-4 col-form-label text-md-right">Amount (YER)</label>
                                <div class="col-md-6">
                                    <input id="amount" type="number" step="0.01" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           name="amount" value="{{ old('amount') }}" required>

                                    @error('amount')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="invoice_id" class="col-md-4 col-form-label text-md-right">Invoice ID</label>
                                <div class="col-md-6">
                                    <input id="invoice_id" type="text" 
                                           class="form-control @error('invoice_id') is-invalid @enderror" 
                                           name="invoice_id" value="{{ old('invoice_id', 'INV-' . time()) }}" required>

                                    @error('invoice_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Proceed to Payment
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
