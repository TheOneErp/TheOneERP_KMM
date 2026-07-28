@extends('layouts.default')
@section('title', '用戶管理')
@section('content')
<script>
    (function($) {
        navbarClick();
    })(jQuery);

</script>
<div class="row">
   @if (count($errors) > 0)
   <div class="col-md-12 errordiv">
    @if(isset($errors))
    @if(is_object($errors))
    @foreach($errors->all() as $key=>$val)
    <span class="btn-danger col-md-12">{{$val}}</span>
    @endforeach
    @else
    <span class="btn-danger col-md-12">{{$errors}}</span>
    @endif
    @endif
    </div>
      @endif
       <div class="col-md-12">
    @if(Session::has('success'))
    <div class="alert alert-success">
        {{Session::get('success')}}
    </div>
    @endif
    <div class="col-md-12">
        <h2 class="txt_center">用戶管理</h2>
        <button class="ts button" style="cursor: pointer;" name="newUser" onclick="location.href='{{ route('users_form', ['type' => 'insert','id' => '']) }}'">新增用戶</button>


        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>用戶帳號</th>
                    <th>用戶名稱</th>
                    <th>EMAIL</th>
                    <th>電話</th>
                    <th>停用</th>
                    <th>備註</th>
                </tr>
            </thead>

            <tbody>
                @foreach($userRes as $key=>$value)
                <tr>
                    <td>
                        <button type="button" class="ts positive button btnicon" style="cursor: pointer;" name="editBtn" onclick="location.href='{{ route('users_form', ['type' => 'update','id' => $value->id]) }}'"><i class="large pencil square icon"></i></button>
                        <form class="deleteform" action="{{ route('users_delete',  ['id' => $value->id]) }}" method="POST">
                            {{ csrf_field() }}
                            <button class="ts negative button btnicon" style="cursor: pointer;" name="deleteBtn" onclick="return confirm('確定刪除此筆資料?');"><i class="large trash icon"></i></button>
                        </form>
                    </td>
                    <td>{{$value->usr_account}}</td>
                    <td>{{$value->usr_name}}</td>
                    <td>{{$value->usr_email}}</td>
                    <td>{{$value->usr_tel}}</td>
                    <td>{{$value->usr_stop==1?"是":"否"}}</td>
                    <td>{{$value->usr_note}}</td>
                </tr>

                @endforeach

            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th>用戶帳號</th>
                    <th>用戶名稱</th>
                    <th>EMAIL</th>
                    <th>電話</th>
                    <th>停用</th>
                    <th>備註</th>
                </tr>
            </tfoot>
        </table>




    </div>
</div>


@endsection
