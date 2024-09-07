@extends('layouts.admin.app')

@section('title',\App\CPU\translate('update_brand'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('assets/admin')}}/css/custom.css"/>
@endpush

@section('content')



<div class="container mt-5">
    <h2>Add New Admin</h2>
    <form action="{{ route('admin.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="admin_f_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="admin_f_name" name="admin_f_name" required>
        </div>
        <div class="mb-3">
            <label for="admin_l_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="admin_l_name" name="admin_l_name" required>
        </div>
        <div class="mb-3">
            <label for="admin_email" class="form-label">Email</label>
            <input type="email" class="form-control" id="admin_email" name="admin_email" required>
        </div>
        <div class="mb-3">
            <label for="phone_code" class="form-label">Phone Code</label>
            <input type="text" class="form-control" id="phone_code" name="phone_code" required>
        </div>
        <div class="mb-3">
            <label for="admin_phone" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="admin_phone" name="admin_phone" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Admin</button>
    </form>
</div>



@endsection

@push('script_2')
    <script src={{asset("assets/admin/js/global.js")}}></script>
@endpush

