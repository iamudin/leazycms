
@if ($errors->any())
<div class="alert alert-dismissible alert-danger">
    <ul class="p-0 m-0 pl-2">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
