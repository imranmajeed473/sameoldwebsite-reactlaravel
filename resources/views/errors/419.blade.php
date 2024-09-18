@extends('errors.layouts.full')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message', __('The page you were trying to access has expired.'))
@section('content')
    <div class="text-center">
        <p>{{ __('Page Expired! The page you were trying to access has expired.') }}</p>
    </div>
@endsection
