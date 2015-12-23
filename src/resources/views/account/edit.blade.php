@extends('forone::layouts.master')

@section('title', '更新'.$page_name)

@section('main')

    {!! Form::panel_start('编辑'.$page_name) !!}
    {!! Form::model($data,['method'=>'PUT','route'=>[$uri.'.update',$data->id],'class'=>'form-horizontal']) !!}
        @include('forone::'. $uri.'.form')
    {!! Form::panel_end('保存') !!}
    {!! Form::close() !!}

@stop