@extends('layouts.app')
@section('title', 'Application Submitted')
@section('content')
<div style="max-width:600px;margin:4rem auto;padding:0 1rem;text-align:center">
    <div style="font-size:3rem;margin-bottom:1rem">✅</div>
    <h1 style="font-size:1.5rem;font-weight:700;color:var(--navy);margin-bottom:.75rem">Application Submitted</h1>
    <p style="font-size:.95rem;color:var(--text-muted);line-height:1.7;margin-bottom:1.5rem">
        Thank you for applying to join {{ \App\Helpers\RaynetSetting::groupName() }}.<br>
        Your REG-02 form has been sent to the Group Controller for processing.<br>
        Please check your email for a confirmation.
    </p>
    <a href="{{ route('home') }}" style="display:inline-block;background:var(--navy);color:#fff;padding:.7rem 1.5rem;font-weight:700;text-decoration:none;font-size:.85rem;text-transform:uppercase;letter-spacing:.08em">← Return to Home</a>
</div>
@endsection
