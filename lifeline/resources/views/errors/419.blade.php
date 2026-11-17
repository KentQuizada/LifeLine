@extends('errors.layout')

@section('title', 'Page expired')
@section('icon', 'bi-clock-history')
@section('message', 'The form was open for too long, so it was not sent. Go back, reload the page and try again.')
@section('actions')
    <a href="javascript:history.back()" class="btn btn-outline-primary">Go back</a>
@endsection
