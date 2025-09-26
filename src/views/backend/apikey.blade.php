@extends('cms::backend.layout.app', ['title' => 'API Key'])
@section('content')

    <div class="row">
        <div class="col-lg-12">
            <h3 style="font-weight:normal">
                <i class="fa fa-key" aria-hidden="true"></i> API Key
                <div class="btn-group pull-right">
                    <a href="{{ route('panel.dashboard') }}" class="btn btn-danger btn-sm">
                        <i class="fa fa-undo" aria-hidden="true"></i> Kembali
                    </a>
                </div>
            </h3>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="input-group">
                <!-- Tombol Copy -->
                <button class="btn btn-outline-secondary" type="button" id="copyApiKey">
                    <i class="fa fa-copy"></i>
                </button>

                <!-- Input API Key -->
                <input type="password" id="apiKey" class="form-control" value="{{ $key}}"
                    disabled>

                <!-- Tombol Eye -->
                    <form method="POST" action="{{url()->current()}}" class="btn-group">
                        @csrf
                <button type="button" class="btn btn-outline-secondary" type="button" id="toggleApiKey">
                    <i class="fa fa-eye"></i>
                </button>

                <!-- Tombol Generate -->

                    <button type="submit" class="btn btn-primary" onclick="return confirm('Generate ulang KEY ?')">
                        <i class="fa fa-refresh"></i> Regenerate Key
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Toggle tampil/sembunyi API Key
        document.getElementById('toggleApiKey').addEventListener('click', function () {
            let input = document.getElementById('apiKey');
            let icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });

        // Copy API Key ke clipboard
        document.getElementById('copyApiKey').addEventListener('click', function () {
            let input = document.getElementById('apiKey');
            navigator.clipboard.writeText(input.value).then(() => {
                alert("API Key berhasil dicopy!");
            });
        });
    </script>
@endpush