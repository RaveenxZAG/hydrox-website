@extends('layouts.app')
@section('title', 'Job Completion Report')
@section('content')
    <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data" @submit="document.dispatchEvent(new CustomEvent('report-submit-start'))">
        @include('reports.form')
    </form>
@endsection
