<?php
namespace Leazycms\Web\Http\Controllers;

use App\Http\Controllers\Controller;
use Leazycms\Web\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Cache;
class MediaController extends Controller implements HasMiddleware
    {
        public static function middleware(): array {
            return [
                new Middleware('auth',['destroy','upload','uploadImageSummernote'])
            ];
        }
        public function uploadImageSummernote(Request $request){
            $image = $request->file('file');
            $request->validate([
                'file' =>'required|mimetypes:'.allow_mime(),
             ]);
            $post = $request->user()->posts()->create(['type'=>'media','status'=>'publish']);
            $post->update(['parent_id'=>$post->id,'media_description'=>'upload_summernote','parent_type'=>'post']);
            $asmedia = Post::findOrFail($post->id);
            $url = upload_media($asmedia,$request->file('file'),'upload_summernote','post');
            recache_media();
            return response()->json(['status'=>'success','url'=>'/'.$url]);
        }
    public function upload(Request $request){
        abort_if(!$request->user(),'404');
        if($request->isMethod('post')){
            $request->validate([
                'media' =>'required|mimetypes:'.allow_mime(),
             ]);
            $post = $request->user()->posts()->create(['type'=>'media','status'=>'publish']);
            $post->update(['parent_id'=>$post->id,'media_description'=>'upload_media','parent_type'=>'post']);
            $asmedia = Post::findOrFail($post->id);
            upload_media($asmedia,$request->file('media'),'upload_media','post');
            recache_media();
            return to_route('media')->with('success','File berhasil diupload');

        }else{
            return to_route('media');
        }
    }
    public function destroy(Request $request){
        abort_if(!$request->user(),'404');
        if($media = $request->media){
            $data = Post::whereSlug(basename($media))->first();
            if($data){
                Storage::delete($data->media);
                $data->forceDelete();
                recache_media();
            }
        }
        }
    public function stream_by_id($slug){
        // if (strpos(request()->getRequestUri(), 'index.php') !== false) {
        //     return redirect('http://' . request()->getHost() . str_replace('/index.php', '', request()->getRequestUri()));
        // }
        // $media = Cache::get('media')[$slug] ?? null;
        // abort_if(empty($media),404);
        // $filePath = $media['media'];
        // $mime = $media['mime'];
        // abort_if(!Storage::exists($filePath),404);

        // return response()->stream(function () use ($filePath) {
        //     $stream = Storage::readStream($filePath);
        //     fpassthru($stream);
        //     fclose($stream);
        // }, 200, [
        //     'Content-Type' => $mime,
        //     'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        // ]);

        $media = Cache::get("media_{$slug}");
        abort_if(empty($media), 404);

        $filePath = $media['media'];
        $mime = $media['mime'];
        // Optimisasi dengan mencoba membuka stream langsung
        return response()->stream(function () use ($filePath) {
            $stream = Storage::readStream($filePath);
            abort_if($stream === false, 404);
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            'Cache-Control' => 'public, max-age=3600'
        ]);
    }
}
