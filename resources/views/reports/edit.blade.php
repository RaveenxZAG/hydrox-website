@extends('layouts.app')
@section('title', 'Edit Completion Report')
@section('content')
    <form method="POST" action="{{ route('reports.update', $report) }}" enctype="multipart/form-data" @submit="document.dispatchEvent(new CustomEvent('report-submit-start'))">
        @method('PUT')
        @include('reports.form')
    </form>
@endsection
