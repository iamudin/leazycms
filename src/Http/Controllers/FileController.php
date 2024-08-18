<?php
namespace Leazycms\Web\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Leazycms\Web\Models\File;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class FileController extends Controller implements HasMiddleware
    {
        public static function middleware(): array {
            return [
                new Middleware('auth',['index',
                'datatable','destroy','upload','uploadImageSummernote'])
            ];
        }
        public function index(){
            return view('cms::backend.files.index',['file'=>null]);
        }
        public function datatable(Request $request)
        {
            $data = File::query()->with('user')->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button target="_blank" onclick="copy(\'' .url('/media/'.$row->file_name).'\')"  class="btn btn-warning btn-sm fa fa-copy copy"></button>';
                    $urlpreve = str_starts_with($row->file_type,'image/') ?  url('/media/'.$row->file_name) : 'https://docs.google.com/viewer?url='.url('media/'.$row->file_name).'&embedded=true';
                    $btn .= '<a target="_blank" onclick="preview(\'' .$urlpreve.'\')"  class="btn btn-info btn-sm fa fa-eye"></a>';
                    $btn .= '<button onclick="deleteAlert(\''.$row->file_name.'\')" class="btn btn-danger btn-sm fa fa-trash"></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('preview', function ($row) {
                    $preview = '<img style="width:100%" src="/shimmer.gif" data-src="'.mime_thumbnail($row->file_name).'" class="rounded lazyload"/>';
                    return $preview;
                })
                ->addColumn('name', function ($row) {
                    return '<span class="text-primary">'.$row->file_name.'</span><br><small class="text-muted"><i class="fa fa-user"></i> Upload by '. ($row->user ? $row->user->name : 'Uknown') .'</small>';

                })
                ->addColumn('file_size', function ($row) {
                    return size_as_kb($row->file_size);

                })
                ->addColumn('ref', function ($row) {
                    return class_basename($row->fileable_type);

                })
                ->addColumn('created_at', function ($row) {
                    return '<code>'.$row->created_at->diffForHumans().'</code>';
                })
                ->rawColumns(['ref','file_size','preview','action','name','created_at'])
                ->toJson();
    }
        public function edit(){
            // return view('cms::backend.tags.index',['tag'=>$tag]);
        }
    public function upload(Request $request){
        abort_if(!$request->user(),'404');
        if($request->isMethod('post')){
            $request->validate([
                'media' =>'required|mimetypes:'.allow_mime(),
             ]);
            if( $file = $request->file('media')){

                (new File)->addFile([
                    'file'=>$file,
                    'purpose'=>'Upload Media',
                    'child_id'=>Str::random(6),
                    'mime_type'=> explode(',',allow_mime()),
                    'self_upload'=>true
                ]);
                return to_route('files.index')->with('success','File berhasil diupload');
            }
       }else{
            return to_route('media');
        }
    }
    public function destroy(Request $request){
        abort_if(!$request->user(),'404');
        if($media = $request->media){
            $data = File::whereFileName(basename($media))->first();
            if($data){
                Cache::forget("media_".basename($media));
                Storage::delete($data->file_path);
                $data->forceDelete();
            }
        }
        }
        public function stream_by_id($slug)
        {
            $media = Cache::remember("media_{$slug}", 60 * 60 * 24, function () use ($slug) {
                $file = File::select('file_path', 'file_type', 'file_auth')
                    ->whereFileName($slug)
                    ->first();
                    if($file){
                        return json_decode(json_encode([
                            'file_path' => $file->file_path,
                            'file_type' => $file->file_type,
                            'file_auth' => $file->file_auth,
                        ]));
                    }
                    return null;
            });
            abort_if(empty($media),404);

            $auth = $media->file_auth;
            if ($auth === null) {
            } elseif ($auth == 0) {
                abort_if(!auth()->check(), 403, 'You need to be logged in to access this resource.');
            } elseif ($auth > 0) {
                abort_if($auth != auth()->id(), 403, 'You do not have permission to access this resource.');
            }

            // Stream file
            return response()->stream(function () use ($media) {
                $stream = Storage::readStream($media->file_path);
                abort_if($stream === false, 404);
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => $media->file_type,
                'Content-Disposition' => 'inline; filename="' . basename($media->file_path) . '"',
                'Cache-Control' => 'public, max-age=31536000, immutable'
            ]);
        }

}
