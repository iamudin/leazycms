<?php

namespace Leazycms\Web\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Leazycms\Web\Models\Tag;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Leazycms\Web\Models\PollingTopic;
use Leazycms\Web\Models\PollingOption;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class PollingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth')
        ];
    }
    public function index()
    {
        return view('cms::backend.polling.index', ['tag' => null]);
    }
    public function datatable(Request $request)
    {
        $data =  PollingTopic::with(['options' => function ($query) {
            $query->withCount('responses');
        }]);
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<a href="' . route('polling.edit', $row->id) . '"  class="btn btn-warning btn-sm fa fa-edit"></a>';
                $btn .=  '<button onclick="deleteAlert(\'' . route('polling.destroy', $row->id) . '\')" class="btn btn-danger btn-sm fa fa-trash"></button>' ;
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('response', function ($row) {
                $total = $row->options->sum('responses_count');

                $p = '<h6>Jumlah  <b class="float-right">' . $total . '</b></h6>';
                $p .= '<ul class="p-0 m-0" style="list-style:none;">';
                foreach ($row->options as $option) {
                    $persen = $option->responses_count ? $option->responses_count / $total * 100 : 0 ;
                    $p .= '<li><small>' . $option->name . '  <b class="float-right">' . $option->responses_count . '</b></small><br>
                    <div class="progress">
  <div class="progress-bar ' . $this->persen($persen) . ' progress-bar-striped progress-bar-animated" role="progressbar" style="width: ' . $persen . '%;" aria-valuenow="' . $persen . '" aria-valuemin="0" aria-valuemax="100">' . ($persen ? round($persen, 2) :0 ). '%</div>
</div>

                    </li>';
                }
                $p .= '<ul>';

                return $p;
            })
            ->rawColumns(['action', 'response'])
            ->toJson();
    }
    function persen($percentage)
    {
        return match (true) {
            $percentage >= 75 => 'bg-success', // Hijau
            $percentage >= 50 => 'bg-info',    // Biru
            $percentage >= 25 => 'bg-warning', // Kuning
            default => 'bg-danger',            // Merah
        };
    }
    public function edit(PollingTopic $polling)
    {
        return view('cms::backend.polling.form', ['polling' => $polling]);
    }
    public function indexOption(PollingTopic $polling)
    {

        return view('cms::backend.polling.option.index', ['data'=>$polling->options,'option'=>null, 'polling' => $polling]);
    }
    public function editOption(PollingTopic $polling,PollingOption $option)
    {
        return view('cms::backend.polling.option.index', ['data'=>$polling->options->where('id','!=',$option->id), 'option' => $option,'polling'=>$polling]);
    }
    public function destroyOption(PollingOption $option)
    {
        return $option->delete();
    }
    public function updateOption(Request $request,PollingTopic $polling,PollingOption $option)
    {
        $option->update([
            'name'=>$request->name
        ]);

        if($request->hasFile('image')){

           $image =  $option->addFile([
                'file'=>$request->file('image'),
                'purpose'=>'image_polling_'.$option->id,
                'mime_type'=>['image/jpeg','image/png']
            ]);

            $option->update([
                'image'=>$image
            ]);
        }
        return back()->with('success','Berhasil Update Opsi');

    }
    public function storeOption(Request $request,PollingTopic $polling)
    {
        $result = $polling->options()->create([
            'name'=>$request->name
        ]);

        if($request->hasFile('image')){

           $image =  $result->addFile([
                'file'=>$request->file('image'),
                'purpose'=>'image_polling_'.$result->id,
                'mime_type'=>['image/jpeg','image/png']
            ]);

            $result->update([
                'image'=>$image
            ]);
        }
        return back()->with('success','Berhasil Menambah Opsi');

    }
    public function create()
    {
        return view('cms::backend.polling.form', ['polling' => '']);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|' . Rule::unique('polling_topics'),
            'description' => 'nullable|string',
            'keyword' => 'nullable|string|' . Rule::unique('polling_topics'),
            'duration' => 'required|numeric',
            'status' => 'required|in:draft,publish',
        ]);
        PollingTopic::create($data);
        return to_route('polling')->with('success', 'Polling Berhasil dibuat');
    }
    public function update(Request $request, PollingTopic $polling)
    {
        $data = $request->validate([
            'title' => 'required|string|' . Rule::unique('polling_topics')->ignore($polling->id),
            'keyword' => 'nullable|string|' . Rule::unique('polling_topics')->ignore($polling->id),
            'description' => 'nullable|string',
            'duration' => 'required|numeric',
            'status' => 'required|in:draft,publish',
        ]);
        $polling->update($data);
        return to_route('polling')->with('success', 'Polling diperbaharui');
    }
    public function destroy(PollingTopic $polling)
    {

        $polling->delete();
    }
}
