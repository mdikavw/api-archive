@extends('auth.main')

@section('content')
<div class="login-container">
    <h2>Login</h2>
    <form action="/login" method="POST">
        @csrf
        <input type="text" name="username" placeholder="Username">
        <input type="password" name="password" placeholder="Password">
        <button>Login</button>
    </form>
    <span><a href="/register">Don't have an account? Register now</a></span>
</div>
@endsection