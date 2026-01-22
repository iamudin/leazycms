<?php
namespace Leazycms\Web\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Leazycms\Web\Models\Tag;
use Leazycms\Web\Models\Post;
use Illuminate\Validation\Rule;
use Leazycms\Web\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class PostController extends Controller implements HasMiddleware
{

    public static function middleware(): array {
        return [
            new Middleware('auth')
        ];
    }
    public function index(Request $request) {
        $request->user()->hasRole(get_post_type(), __FUNCTION__);
        return view('cms::backend.posts.index');
    }
    public function uploadImageSummernote(Request $request) {
        $post = Post::findOrFail($request->post);
        $result = $post->addFile([
            'file' => $request->file('file'),
            'purpose' => 'image from summernote',
            'child_id' => Str::random(6),
            'mime_type' => ['image/jpeg', 'image/png']
        ]);
        return response()->json(['status' => 'success', 'url' => $result]);
    }
    public function restore(Request $request) {
        if ($request->user()->isAdmin()) {
            $post = Post::withTrashed()->findOrFail($request->post);
            $post->update(['status' => 'draft']);
            $post->restore();
            return back()->with('success', 'Data Berhasil dipulihkan');

        } else {
            return redirect(admin_path());
        }

    }
    public function create(Request $request) {
        $request->user()->hasRole(get_post_type(), __FUNCTION__);
        if ($blankexists = query()->onType(get_post_type())->whereStatus('draft')->whereUserId($request->user()->id)->where('title', null)->first()) {
            $newpost = $blankexists;
        } else {
            $newpost = $request->user()->posts()->create([
                'type' => get_post_type(),
                'url' => get_post_type() . '/' . rand(),
                'status' => 'draft',
                'shortcut' => Str::random(6)
            ]);
        }

        return to_route(get_post_type() . '.edit', $newpost->id);
    }

    public function edit(Request $request, Post $post, $id) {
        abort_if(!is_numeric($id), '403');
        $request->user()->hasRole(get_post_type(), 'update');
        $module = current_module();

        $data = $request->user()->isAdmin() ? $post->with('category', 'user', 'tags')->whereType(get_post_type())->find($id) : $post->whereBelongsTo($request->user())->with('category', 'user', 'tags')->whereType(get_post_type())->find($id);
        if (!$data) {
            return redirect(admin_url(get_post_type()))->with('danger', get_module_info('title') . ' Tidak Ditemukan');
        }
        $field = (!empty($data->data_field)) ? collect($data->data_field) : [];
        $looping_data = $data->data_loop ? (collect($module->form->looping_data)->where([0], 'Sort')->first() ? collect($data->data_loop)->sortBy('sort') : $data->data_loop) : [];
        return view('cms::backend.posts.form', [
            'post' => $data,
            'looping_data' => $looping_data,
            'field' => $field,
            'module' => $module,
            'tags' => Tag::get(),
            'category' => $module->form->category ? Category::query()->whereType(get_post_type())->select('id', 'name')->orderBy('sort')->get() : null
        ]);
    }
    public function destroy(Request $request) {
        $request->user()->hasRole(get_post_type(), 'delete');
        $post = Post::withTrashed()->find($request->post);
        if ($post->trashed() && $request->user()->isAdmin()) {
            $post->forceDelete();
        }
        if ($request->user()->isAdmin() || (!$request->user()->isAdmin() && $post->user_id == $request->user()->id)) {
            if (empty($post->title) && $post->status == 'draft') {
                $post->forceDelete();
            } else {
                $post->delete();
            }
        }
        $this->recache(get_post_type());
    }
    public function show(Post $post, $id) {
        abort_if(!is_numeric($id), '403');

        $data = $post->with('category', 'user', 'tags')->find($id);
        if (!$data || $data->type != get_post_type()) {
            return redirect(admin_url(get_post_type()))->with('danger', get_module_info('title') . ' Tidak Ditemukan');
        }
        return $data;
    }
    public function update(Request $request, Post $post) {
        $request->user()->hasRole(get_post_type(), 'update');

        $module = current_module();
        if ($post->type == 'page' && in_array(str($request->title)->lower(), not_allow_adminpath())) {
            return back()->with('danger', 'Nama Halaman tidak di izinkan');

        }
        if ($module->form->custom_field) {

            foreach (collect($module->form->custom_field)->whereNotIn([1], ['break']) as $row) {
                $custom_f[_us($row[0])] = (isset($row[2]) ? 'required' : 'nullable');
            }

            foreach (array_keys($custom_f) as $row) {
                $msg[$row . '.required'] = str($row)->headline() . ' tidak boleh kosong';
            }
            foreach (collect($module->form->custom_field)->whereIn([1], ['file']) as $row) {
                $k = _us($row[0]);
                if ($request->hasFile($k)) {
                    $required = isset($row[1]) ? 'required' : 'nullable';
                    $mime = isset($row[3]) ? $row[3] : allow_mime();

                    $request->validate([
                        $k => $required . '|file|mimetypes:' . $mime,
                    ], [$k . '.mimetypes' => 'Format file ' . str($k)->headline() . ' tidak didukung']);
                }
            }
        }
        $uniq = $module->form->unique_title ? '|' . Rule::unique('posts')->where('type', $post->type)->whereNull('deleted_at')->ignore($post->id) : '';

        $post_field = [
            'title' => 'required|string|regex:/^[0-9a-zA-Z\s\p{P}\,\(\)]+$/u|min:5|max:200' . $uniq,
            'media' => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp,image/gif',
            'content' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (strpos($value, '<?php') !== false) {
                        $fail("The $attribute field contains invalid content.");
                    }
                }
            ],
            'sort' => 'nullable|numeric',
            'parent_id' => 'nullable|exists:posts,id',
            'keyword' => 'nullable|string|regex:/^[a-zA-Z,]+$/u|max:200',
            'description' => 'nullable|string|regex:/^[0-9a-zA-Z\s\p{P}]+$/u|max:200',
            'redirect_to' => 'nullable|url|max:200',
            'category_id' => 'nullable|string',
            'media_description' => 'nullable|string|regex:/^[0-9a-zA-Z\s\p{P}]+$/u|max:200',
            'pinned' => 'nullable|in:N,Y',
            'allow_comment' => 'nullable|in:N,Y',
            'status' => 'required|string|in:draft,publish'
        ];
        $custommsg = [
            'title.required' => $module->datatable->data_title . ' Tidak boleh kosong',
            'title.unique' => $module->datatable->data_title . ' Sudah digunakan',
            'title.min' => $module->datatable->data_title . ' minimal 5 karakter',
            'title.max' => $module->datatable->data_title . ' maksimal 200 karakter',
        ];
        $request->validate(array_merge($post_field, $custom_f ?? []), array_merge($custommsg, $msg ?? []));
        if (strlen($post->slug) == 0 || $post->type == 'docs') {
            $slug = str($request->title)->slug();
        } else {
            if ($post->slug_edited == '1' && !$request->custom_slug) {
                $slug = $post->slug;
            } elseif (($post->slug_edited == '1' && $request->custom_slug) || ($post->slug_edited == '0' && $request->custom_slug)) {
                $slug = $request->custom_slug;
            } else {
                $slug = $post->slug;
            }
        }

        $data = $request->validate($post_field);
        $allowed_tags = '<p><b><i><u><strong><em><ul><ol><li><br><hr><img><a><iframe><figcaption><figure><blockquote><quote>';
        $data['content'] = isset($data['content']) ? ($post->type != 'docs' ? strip_tags($data['content'], $allowed_tags) : $data['content']) : null;

        if (Post::onType($post->type)->whereNotIn('id', [$post->id])->whereSlug($slug)->count() > 0) {
            $data['slug'] = $post->slug ?? str($request->title . ' ' . Str::random(4))->slug();
        } else {
            $data['slug'] = $slug;
        }
        $data['slug_edited'] = $request->custom_slug && strlen($request->custom_slug) > 0 ? '1' : '0';
        $data['pinned'] = isset($request->pinned) ? 'Y' : 'N';
        if ($module->web->detail && strlen($post->shortcut) < 6) {
            $data['shortcut'] = Str::random(6);
        }
        if ($module->web->detail) {
            $data['custom_page'] = View::exists('template.' . template() . '.' . $post->type . '.' . $post->slug) ? '1' : '0';
        }
        $data['short_content'] = isset($data['content']) && strlen($data['content']) > 0 ? short_content($data['content']) : null;
        $post->tags()->sync($request->tags, true);
        $data['allow_comment'] = isset($request->allow_comment) ? 'Y' : 'N';

        if ($pp = $module->form->post_parent) {
            if ($pid = $request->parent_id) {
                $custom_field[_us($pp[0])] = $post->parent?->title;

            }
        }
        if ($module->form->custom_field) {
            foreach (collect($module->form->custom_field)->where([1], '!=', 'break') as $key => $value) {
                $fieldname = _us($value[0]);
                switch ($value[1]) {
                    case 'file':
                        $custom_field[$fieldname] = $request->hasFile($fieldname) ?
                            $post->addFile(['file' => $request->file($fieldname), 'purpose' => $fieldname, 'mime_type' => explode(',', allow_mime())]) : strip_tags($request->$fieldname);
                        break;
                    default:
                        $custom_field[$fieldname] = strip_tags($request->$fieldname) ?? null;
                        break;
                }
            }
        }
        if ($module->form->custom_field || $module->form->post_parent) {
            $data['data_field'] = $custom_field ?? null;
        }

        if ($request->hasFile('media')) {
            $data['media'] = $post->addFile([
                'file' => $request->file('media'),
                'purpose' => 'thumbnail',
                'width' => $module->name == 'banner' ? 1700 : 1200,
                'mime_type' => ['image/png', 'image/jpeg', 'image/webp', 'image/gif'],
            ]);
        }
        if ($request->tanggal_entry) {
            $timedate = $request->tanggal_entry ?? date('Y-m-d H:i:s');
            $data['created_at'] = $timedate;
        }
        $data['url'] = $post->type != 'page' ? $post->type . '/' . $data['slug'] : $data['slug'];

        if ($looping_data = $module->form->looping_data) {

            $datanya = [];
            $jmlh = 0;
            foreach ($looping_data as $y) {
                if ($y[1] != 'file') {
                    $r = _us($y[0]);
                    $jmlh = ($request->$r) ? count($request->$r) : 0;
                }
            }

            if ($jmlh > 0) {
                for ($i = 0; $i < $jmlh; $i++) {

                    foreach ($looping_data as $y) {
                        $r = _us($y[0]);
                        $as = $request->$r;
                        if (isset($as[$i])) {

                            $h[$r] = ($y[1] == 'file') ? (is_file($as[$i]) ? $post->addFile(['file' => $as[$i], 'purpose' => $r, 'child_id' => $i, 'mime_type' => explode(',', allow_mime())]) : $as[$i]) : strip_tags($as[$i]);
                        } else {
                            $h[$r] = null;
                        }
                    }
                    array_push($datanya, $h);
                }
            }
            $data['data_loop'] = $datanya;
            if (get_post_type() == 'menu') {

                $fixd = json_decode($request->menu_json, true);
                $mnews = [];
                processMenu($fixd, $datanya, $mnews);
                $data['data_loop'] = $mnews;
            }
        }

        $post->update($data);
        Cache::forget($post->type);
        Cache::forget($post->id);
        $this->recache(get_post_type());
        return back()->with('success', $module->title . ' Berhasil diperbarui');
    }
    public function recache($type) {

        if (in_array($type, collect(config('modules.used'))->where('active', true)->where('public', true)->where('cache', true)->pluck('name')->toArray())) {
            regenerate_cache();
        }
        if ($type == 'menu') {
            recache_menu();
        }
        if ($type == 'banner') {
            recache_banner();
        }
    }

    public function datatable(Request $req) {
        $data = $req->user()->isAdmin() ? Post::select(array_merge((new Post)->selected, ['data_loop']))->with('user', 'category', 'tags')->with('parent.parent.parent')->withCount('childs', 'comments')->whereType(get_post_type()) : Post::select((new Post)->selected)->with('user', 'category', 'tags')->with('parent.parent.parent')->withCount('childs', 'comments')->whereType(get_post_type())->whereBelongsTo($req->user());
        $current_module = current_module();
        if ($current_module->web->sortable) {
            $data->orderBy('sort', 'ASC');
        }
        if (isset($current_module->datatable?->timestamps)) {
            if (!$current_module->datatable?->timestamps) {
                $data->orderBy('created_at', 'DESC');

            }
        }

        $customColumns = $current_module->datatable->custom_column;

        if ($customColumns && !is_array($customColumns)) {
            $customColumns = [_us($customColumns)];
        } elseif (is_array($customColumns)) {
            $customColumns = array_map('_us', $customColumns);
        } else {
            $customColumns = [];
        }
        $dt = DataTables::of($data)
            ->addIndexColumn()
            ->filter(function ($instance) use ($req) {
                if ($parent_id = $req->parent_id) {
                    $instance->where('parent_id', $parent_id);
                }
                if ($category_id = $req->category_id) {
                    $instance->where('category_id', $category_id);
                }
                if ($tag_id = $req->tag_id) { // Menambahkan pencarian berdasarkan tag_id
                    $instance->whereHas('tags', function ($query) use ($tag_id) {
                        $query->where('tags.id', $tag_id); // Pastikan untuk menggunakan nama tabel yang benar
                    });
                }
                if ($search = $req->search) {
                    $instance->where('type', get_post_type()) // Batasi hanya pada type 'berita'
                        ->where(function ($query) use ($search) {
                            $q = $query->orWhere('title', 'like', '%' . $search . '%')
                                ->orWhere('data_field', 'like', '%' . $search . '%')
                                ->orWhere('content', 'like', '%' . $search . '%')
                                ->orWhere('description', 'like', '%' . $search . '%')
                                ->orWhere('keyword', 'like', '%' . $search . '%')
                                ->orWhere('media_description', 'like', '%' . $search . '%');
                            if (current_module()->form->post_parent) {
                                $q->orWhereHas('parent', function ($q) use ($search) {
                                    $q->select('id')->where('title', 'like', '%' . $search . '%')->orWhereHas('parent', function ($q) use ($search) {
                                        $q->select('id')->where('title', 'like', '%' . $search . '%');
                                    })->orWhereHas('parent', function ($q) use ($search) {
                                        $q->select('id')->where('title', 'like', '%' . $search . '%')->orWhereHas('parent', function ($q) use ($search) {
                                            $q->select('id')->where('title', 'like', '%' . $search . '%');
                                        });
                                        ;
                                    });
                                });
                            }
                        });
                }
                if ($status = $req->status) {
                    $conditions = [
                        'publish' => function ($query) {
                            $query->where('status', 'publish');
                        },
                        'draft' => function ($query) {
                            $query->where('status', 'draft');
                        },
                        'sampah' => function ($query) {
                            $query->onlyTrashed();
                        },
                        'disematkan' => function ($query) {
                            $query->wherePinned('Y');
                        },
                    ];
                    if (array_key_exists($status, $conditions)) {
                        $conditions[$status]($instance);
                    }
                }
                if ($user_id = $req->user_id) {
                    $instance->whereUserId($user_id);
                }
                if ($req->from_date || $req->to_date) {
                    if ($req->from_date) {
                        // Jika hanya from_date yang ada
                        if (!$req->to_date) {
                            $instance->whereDate('created_at', '>=', $req->from_date);
                        } else {
                            // Jika ada from_date dan to_date
                            $from_timestamp = strtotime($req->from_date);
                            $to_timestamp = strtotime($req->to_date);

                            if ($from_timestamp < $to_timestamp) {
                                $instance->whereBetween('created_at', [$req->from_date, $req->to_date]);
                            } else {
                                // Jika from_date sama dengan to_date
                                $instance->whereDate('created_at', $req->from_date);
                            }
                        }
                    } elseif ($req->to_date) {
                        // Jika hanya to_date yang ada
                        $instance->whereDate('created_at', '<=', $req->to_date);
                    }
                }
            })
            ->order(function ($query) use ($req) {
                if ($req->has('order')) {
                    $columns = $req->columns;
                    foreach ($req->order as $order) {
                        $column = $columns[$order['column']]['data'];
                        $dir = $order['dir'];
                        $query->orderBy($column, $dir);
                    }
                }
            });
        $dt->addColumn('title', function ($row) use ($current_module) {

            $category = $current_module->form->category ? (!empty($row->category) ? "<i class='fa fa-tag'></i> " . $row->category?->name : "") : '';
            $tags = '';
            foreach ($row->tags ? $row->tags->pluck('name') : [] as $item) {
                $tags .= ' <b>#' . $item . '</b>';
            }

            $label = $row->allow_comment == 'Y' ? "<i title='Lihat Komentar' onclick=\"show_comment('" . $row->id . "')\" class='fa fa-comments-o pointer text-primary'></i> " . $row->comments_count : '';
            $redirect = $row->redirect_to ? '<br><small class="text-dark"><i class="fa fa-mail-forward"></i> Dialihkan ke: ' . $row->redirect_to . '</small>' : null;
            $tit = ($current_module->web->detail) ? ((!empty($row->title)) ? ($row->status == 'publish' ? '<a title="Klik untuk melihat di tampilan web" href="' . url($row->url . '/') . '" target="_blank">' . $row->title . '</a> ' . $redirect . ' ' . ($row->custom_page == 1 ? '<sup class="badge badge-dark"><small>Custom Page</small></sup>' : '') : $row->title) : '<i class="text-muted">__Tanpa ' . $current_module->datatable->data_title . '__</i>') : ((!empty($row->title)) ? $row->title : '<i class="text-muted">__Tidak ada data__</i>');


            $pin = $row->pinned == 'Y' ? '<span class="badge badge-danger"> <i class="fa fa-star"></i> Disematkan</span>&nbsp;' : '';
            $shortcut = $current_module->web->detail && $row->shortcut && $row->status == 'publish' ? ' <a href="javascript:void(0)" class="pointer" onclick="copy(\'' . url($row->shortcut) . '\')" title="Pengunjung / pembaca dari Shortcut Link. Klik untuk copy shortcut link"><i class="fa fa-qrcode"></i> ' . $row->shortcut_counter . '</a>' : '';

            $b = '<b class="text-primary">' . $tit . '</b><br>';
            $b .= '<small class="text-muted"> ' . $pin . ' <i class="fa fa-user-o"></i> ' . $row->user->name . '  ' . $category . ' ' . $label . ' ' . $tags . ' ' . $shortcut . '</small>';
            return $b;
        });


        $dt->addColumn('created_at', function ($row) {
            return '<small class="badge badge-pill py-1" style="border:1px solid green;">' . date('d M Y H:i', strtotime($row->created_at)) . '</small>';
        });
        $dt->addColumn('visited', function ($row) {
            return '<center><small class="badge badge-pill badge-dark py-1" style="border:1px solid lime;"> <i class="fa fa-line-chart"></i> <b>' . $row->visited . '</b></small></center>';
        });
        $dt->addColumn('updated_at', function ($row) {
            return ($row->updated_at) ? '<small class="badge badge-pill py-1" style="border:1px solid orange;">' . date('d M Y H:i', strtotime($row->updated_at)) . '</small>' : '<small class="badge text-muted">NULL</small>';
        });
        $dt->addColumn('thumbnail', function ($row) {
            return '<img class="rounded lazyload" src="/shimmer.gif" style="width:100%" data-src="' . $row->thumbnail . '?size=small"/>';
        });
        foreach ($customColumns as $field) {
            $dt->addColumn($field, function ($row) use ($field) {

                if (empty($row->data_field) || empty($row->data_field[$field])) {
                    return '<span>-</span>';
                }

                $value = $row->data_field[$field];

                if (is_string($value)) {

                    // 🔗 0️⃣ Jika /media/ + ada nama file & ekstensi
                    if (str_starts_with($value, '/media/')) {

                        $filename = basename($value);

                        // Pastikan ada nama file + ekstensi (misal: file.jpg)
                        if (
                            $filename &&
                            str_contains($filename, '.') &&
                            preg_match('/^[^\/]+\.[a-zA-Z0-9]+$/', $filename)
                        ) {

                            // Cek media exists
                            if (media_exists($value)) {
                                return '<a href="' . e($value) . '" 
                                    target="_blank" 
                                    class="badge badge-pill py-1" style="border:1px solid green;">
                                    <i class="fa fa-eye"></i> Lihat
                                </a>';
                            }
                        }

                        // Tidak valid / tidak ada file
                        return '<span>-</span>';
                    }

                    // 1️⃣ ISO 8601: Y-m-dTH:i
                    $dtValue = \DateTime::createFromFormat('Y-m-d\TH:i', $value);
                    if ($dtValue !== false) {
                        return '<small class="badge badge-pill py-1" style="border:1px solid green;">'
                            . $dtValue->format('d F Y H:i')
                            . '</small>';
                    }

                    // 2️⃣ Y-m-d H:i:s
                    $dtValue = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                    if ($dtValue !== false) {
                        return '<small class="badge badge-pill py-1" style="border:1px solid green;">'
                            . $dtValue->format('d F Y H:i')
                            . '</small>';
                    }

                    // 3️⃣ Y-m-d
                    $dtValue = \DateTime::createFromFormat('Y-m-d', $value);
                    if ($dtValue !== false) {
                        return '<small class="badge badge-pill py-1" style="border:1px solid green;">'
                            . $dtValue->format('d F Y')
                            . '</small>';
                    }
                }

                // Default
                return '<span>' . e($value) . '</span>';
            });
        }





        $dt->addColumn('parents', function ($row) use ($current_module) {
            if ($current_module->form->post_parent) {
                $parent = null;
                if ($row->parent) {
                    $parent .= $row->parent->title;
                    if ($row->parent->parent) {
                        $parent .= ' - ' . $row->parent->parent->title;
                        if ($row->parent->parent->parent) {
                            $parent .= ' - ' . $row->parent->parent->parent->title;
                        }
                    }
                }
                return $parent ?? '_';
            }

        });


        $dt->addColumn('action', function ($row) use ($current_module) {

            $btn = '<div style="text-align:right"><div class="btn-group ">';

            $btn .= !$row->trashed() && $current_module->web->detail && $row->status == 'publish' ? '<a title="Libat di web" target="_blank" href="' . url($row->url . '/') . '"  class="btn btn-outline-info btn-sm fa fa-eye"></a>' : '';
            if (empty($row->deleted_at)) {
                $btn .= Route::has($row->type . '.edit') ? '<a title="Edit data" href="' . route(get_post_type() . '.edit', $row->id) . '"  class="btn btn-outline-warning btn-sm fa fa-edit"></a>' : '';
            } else {
                $btn .= '<a title="Pulihkan data" href="' . route(get_post_type() . '.restore', $row->id) . '"  class="btn btn-info btn-sm fa fa-trash-restore" onclick="return confirm(\'Pulihkan data ini ?\')" title="Pulihkan Data"></a>';
            }
            $titledelete = $row->trashed() ? 'Hapus Permanent' : 'Hapus Data';
            $btn .= Route::has($row->type . '.destroyer') && empty($row->childs_count) ? ($row->type == 'menu' && !empty($row->data_loop) ? '' : '<button title="' . $titledelete . '" onclick="deleteAlert(\'' . route($row->type . '.destroyer', $row->id) . '\')" class="btn btn-outline-danger btn-sm fa fa-trash-o"></button>') : '';
            $btn .= '</div></div>';
            return $btn;
        });
        $dt->addColumn('checkbox', function ($row) {
            return Route::has($row->type . '.destroyer') && empty($row->childs_count) ? ($row->type == 'menu' && !empty($row->data_loop) ? '' : '
                 <div class="animated-checkbox">
                        <label>
                            <input type="checkbox" name="id[]" value="' . $row->id . '" class="dt-checkbox">
                            <span class="label-text"></span>
                        </label>
                </div>

               ') : '';

        });
        $dt->addColumn('status', function ($row) {
            return '<input ' . (strlen($row->title) < 5 ? 'disabled ' : 'data-id="' . $row->id . '"') . ' 
                type="checkbox" 
                class="toggle-status"
                
                data-on="Publish" 
                data-off="Draft" 
                data-toggle="toggle" 
                data-onstyle="outline-success" 
                data-offstyle="outline-danger" 
                data-size="sm"
                ' . ($row->status == 'publish' ? 'checked' : '') . '
            >';
        });
        $rawColumns = array_merge(
            ['status', 'checkbox', 'created_at', 'updated_at', 'visited', 'action', 'title', 'parents', 'thumbnail'],
            $customColumns
        );

        $dt->rawColumns($rawColumns);
        $dt->orderColumn('visited', '-visited $1');
        $dt->orderColumn('updated_at', '-updated_at $1');
        $dt->orderColumn('created_at', '-created_at $1');
        $dt->only(array_merge(
            ['status', 'checkbox', 'visited', 'action', 'title', 'created_at', 'updated_at', 'parents', 'thumbnail'],
            $customColumns
        ));
        return $dt
            ->orderColumn('visited', '-visited $1')
            ->orderColumn('updated_at', '-updated_at $1')
            ->orderColumn('created_at', '-created_at $1')
            ->toJson();
    }

    public function updateStatus(Request $request) {
        $post = Post::findOrFail($request->id);

        $post->status = $request->status;
        $post->save();
        $this->recache($post->type);
        return response()->json(['success' => true]);
    }
    function bulkaction(Request $request) {
        $ids = $request->id;

        if (!empty($ids)) {
            if ($action = $request->action) {
                switch ($action) {
                    case 'delete':
                        $request->user()->hasRole(get_post_type(), 'delete');
                        foreach (query()->whereIn('id', $ids)->withTrashed()->onType(get_post_type())->get() as $post) {
                            if ($post->trashed() && $request->user()->isAdmin()) {
                                $post->forceDelete();
                            }
                            if ($request->user()->isAdmin() || ($request->user()->isOperator() && $post->user_id != $request->user()->id)) {
                                if (empty($post->title) && $post->status == 'draft') {
                                    $post->forceDelete();
                                } else {
                                    $post->delete();
                                }
                            }
                        }
                        break;
                    case 'draft':
                        query()->withTrashed()->onType(get_post_type())->whereIn('id', $ids)->update(['status' => 'draft', 'deleted_at' => null]);
                        break;
                    case 'publish':
                        query()->withTrashed()->onType(get_post_type())->whereIn('id', $ids)->update(['status' => 'publish', 'deleted_at' => null]);
                        break;
                    default:
                        break;
                }
                return response()->json(['message' => 'Success'], 200);

            }

            return response()->json(['message' => 'No files selected.'], 400);
        }

    }
}
