@extends('errors::minimal')

@section('title', __('Unknown error'))
@section('code', '400')
@section('message', __($exception->getMessage() ?: 'Unknown error.'))
