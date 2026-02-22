@extends('cms::backend.layout.app', ['title' => 'Setting › Cache'])
@section('content')
    @push('styles')
        <style>
            .btn-toggle-group label {
                min-width: 80px;
            }
        </style>
    @endpush
    <form action="{{URL::full()}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <h3 style="font-weight:normal">
                    <i class="fa fa-flash" aria-hidden="true"></i> Setting › Cache
                    <div class="btn-group pull-right">
                        <button name="save_setting" value="true" class="btn btn-primary btn-sm"> <i class="fa fa-save"
                                aria-hidden></i>
                            Simpan</button>
                        <a href="{{route('panel.dashboard')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo"
                                aria-hidden></i>
                            Kembali</a>
                    </div>
                </h3>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label class="font-weight-bold">Cache Config</label><br>
                    <div class="btn-group btn-toggle-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-danger {{!app()->configurationIsCached() ? 'active' : null}}">
                                <input type="radio" name="cache_config" id="config_off" value="N" autocomplete="off"
                                    {{!app()->configurationIsCached() ? 'checked' : null}}> OFF
                            </label>
                        <label class="btn btn-outline-success {{app()->configurationIsCached() ? 'active' : null}}">
                            <input type="radio" name="cache_config" id="config_on" value="Y" autocomplete="off"
                                {{app()->configurationIsCached() ? 'checked' : null}}> ON
                        </label>

                    </div>
                </div>

                <!-- Cache Route -->
                <div class="form-group">
                    <label class="font-weight-bold">Cache Module</label><br>
                    <div class="btn-group btn-toggle-group btn-group-toggle" data-toggle="buttons">

                        <label class="btn btn-outline-danger  {{!app()->routesAreCached() ? 'active' : null}}">
                            <input type="radio" name="cache_route" id="route_off" value="N" autocomplete="off"
                                {{!app()->routesAreCached() ? 'checked' : null}}> OFF
                        </label>
                                <label class="btn btn-outline-success  {{app()->routesAreCached() ? 'active' : null}}">
                            <input type="radio" name="cache_route" id="route_on" value="Y" autocomplete="off"
                                {{app()->routesAreCached() ? 'checked' : null}}> ON
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Cache Media & Data</label><br>
                    <div class="btn-group btn-toggle-group btn-group-toggle" data-toggle="buttons">
                          <label class="btn btn-outline-danger  {{!cache()->has('media') ? 'active' : null}}">
                            <input type="radio" name="cache_media" id="route_off" value="N" autocomplete="off"
                                {{!cache()->has('media') ? 'checked' : null}}> OFF
                        </label>
                        <label class="btn btn-outline-success  {{cache()->has('media') ? 'active' : null}}">
                            <input type="radio" name="cache_media" id="route_on" value="Y" autocomplete="off"
                                {{cache()->has('media') ? 'checked' : null}}> ON
                        </label>

                    </div>
                </div>
            </div>
        </div>
    </form>
    @push('scripts')
        @include('cms::backend.layout.js')

    @endpush
@endsection