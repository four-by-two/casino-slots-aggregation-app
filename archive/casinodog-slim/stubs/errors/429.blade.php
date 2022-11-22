@extends('errors::minimal')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('message', __('Slowdown on your requests, please try again in 1 minute.'))