@extends('auth.main')

@section('content')
<div class="register-container">
    <h2>Register</h2>
    <form action="/register" method="POST">
        @csrf
        <div class="form-group">
            <input type="text" name="username" class="@error('username')
            is-invalid
            @enderror" placeholder="Username" value="{{ old('username') }}">
            @error('username')
            <div class="form-text invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div class="form-group">
            <input type="email" name="email" class="@error('email')
            is-invalid
            @enderror" placeholder="Email" value="{{ old('email') }}">
            @error('email')
            <div class="form-text invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div class="form-group">
            <input type="password" name="password" class="@error('password')
            is-invalid
            @enderror" placeholder="Password">
            @error('password')
            <div class="form-text invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
        <input type="password" name="password_confirmation" placeholder="Confirm Password">
        <button>Register</button>
    </form>
    <span><a href="/login">Already have an account? Login</a></span>
</div>
@endsection