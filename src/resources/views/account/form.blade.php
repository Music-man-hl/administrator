
{!! Form::group_text('name','用户名字','请输入用户名称') !!}
{!! Form::group_text('email','邮箱','请输入邮箱') !!}
{!! Form::group_password('new_password','新密码','请输入新密码') !!}

@section('js')
    @parent
@stop