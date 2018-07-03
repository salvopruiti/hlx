@extends('errors::layout')

@section('title', __('Page Expired'))

@section('message')
    @lang('The page has expired due to inactivity.')
    <br/><br/>
    @lang('Please refresh and try again.')
@stop
