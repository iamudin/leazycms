
@push('styles')
<link rel="stylesheet" href="{{url('backend/css/comment.css')}}">
@endpush
<div class="comments-body">
        <div class="be-comment-block">
            <h3 class="comments-title"><i class="fa fa-comments" aria-hidden="true"></i> Tanggapan {{$data->count() ? '('. $data->count() .')' : ''}}</h3>
            <div class="comments-list">
                @forelse($data as $row)
            <div class="be-comment">

                <div class="be-comment-content">
                    <span class="be-comment-name">
                        <i class="fa fa-user" aria-hidden="true"></i> {{$row->name}}
                    </span>
                    <span class="be-comment-time">
                        <i class="fa fa-clock-o"></i>
                        {{time_ago($row->created_at)}}
                    </span>
                    <p class="be-comment-text">
                        {!!$row->content!!}
                    </p>
                </div>
            </div>
            @empty
            <div class="alert">Belum ada tanggapan !</div>
            @endforelse
        </div>
            <h3 class="comments-title"><i class="fa fa-envelope" aria-hidden="true"></i> Kirim Tanggapan Baru</h3>

            <form class="form-block" action="" method="POST">
                @csrf

                <div class="row">
                    @if(session('success'))
                    <div class="col-xs-12">
                        <div class="alert alert-success">Tanggapan berhasil dikirim, Akan tampil setelah di setujui.</div>
                    </div>
                    @endif
                    <div class="col-xs-12 col-sm-4">
                        <div class="form-group"><input required class="form-input" name="name" type="text" placeholder="Nama Anda...">
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-4">
                        <div class="form-group">

                            <input class="form-input" name="email"  required type="text" placeholder="Alamat Email...">
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-4">
                        <div class="form-group">
                            <input class="form-input" name="link"  required type="text" placeholder="Link Web / Profile Medsos...">
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <div class="form-group">
                            <textarea name="content"  required class="form-input" required="" placeholder="Tulis tanggapan..."></textarea>
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <div class="form-group">
                            <button name="comment_sender" value="true" class="btn btn-primary btn-md" style="float:right"><i class="fa fa-envelope" aria-hidden="true"></i> Kirim Tanggapan </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
</div>
