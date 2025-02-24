@extends('layouts.admin')

@section('title', 'Quản lý Menu')
@section('header', 'Quản lý Menu Header')

@section('content')
    @include('admin.menus.partials.form', ['menu' => $menu ?? null])
@endsection