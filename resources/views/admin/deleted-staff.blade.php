@extends('layouts.admin')

@section('title', 'Deleted Staff History')

@section('content')
<div id="app">
    <deleted-staff-list></deleted-staff-list>
</div>
@endsection

@push('scripts')
<script src="{{ mix('js/app.js') }}"></script>
@endpush
