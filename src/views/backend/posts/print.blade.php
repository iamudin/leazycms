<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekapitulasi Data {{ $module->title ?? 'Post' }}</title>
    <style>
        @page {
            margin: 1.2cm 1cm 1.5cm 1cm;
        }

        * {
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #1f2937;
            background: #ffffff;
            line-height: 1.4;
        }

        .header-wrap {
            width: 100%;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .site-title {
            font-size: 15pt;
            font-weight: bold;
            color: #0d9488;
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }

        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            color: #374151;
            margin-top: 2px;
        }

        .meta-info {
            text-align: right;
            font-size: 8pt;
            color: #6b7280;
        }

        .filter-badge-bar {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 12px;
            font-size: 8pt;
            color: #475569;
        }

        .filter-badge-bar strong {
            color: #0f172a;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }

        table.data-table thead {
            display: table-header-group;
        }

        table.data-table th {
            background-color: #0d9488;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.03em;
            padding: 6px 6px;
            border: 1px solid #0f766e;
            text-align: left;
            vertical-align: middle;
        }

        table.data-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            word-wrap: break-word;
        }

        table.data-table tbody tr {
            page-break-inside: avoid;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 7.5pt;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }

        .badge-publish {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .badge-draft {
            background-color: #fef9c3;
            color: #a16207;
            border: 1px solid #fde047;
        }

        .badge-sampah {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        .badge-category {
            background-color: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }

        .footer-note {
            margin-top: 15px;
            width: 100%;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
            font-size: 7.5pt;
            color: #94a3b8;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    @php
        $siteName = get_option('site_title') ?: (config('app.name') ?: 'LeazyCMS');
        $customCols = $customColumns ?? [];
        $colCount = 2; // No, Title
        if (!empty($module->form->thumbnail))
            $colCount++;
        if (!empty($module->form->post_parent))
            $colCount++;
        if (!empty($module->form->category))
            $colCount++;
        if (!empty($module->datatable->child_count))
            $colCount += count($module->datatable->child_count);
        $colCount += count($customCols);
        if (($module->datatable->timestamps ?? true) !== false)
            $colCount++;
        $colCount++; // Status
    @endphp

    <!-- Header Dokumen -->
    <div class="header-wrap">
        <table class="header-table">
            <tr>
                <td style="width: 65%;">
                    <div class="site-title">{{ $siteName }}</div>
                    <div class="doc-title">Rekapitulasi Data {{ $module->title }}</div>
                </td>
                <td style="width: 35%;" class="meta-info">
                    <div><strong>Dicetak:</strong> {{ date('d F Y, H:i') }} WIB</div>
                    <div><strong>Total Data:</strong> {{ count($posts) }} entri</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Ringkasan Filter (jika ada filter yang diterapkan) -->
    <div class="filter-badge-bar">
        <strong>Filter Aktif:</strong>
        Status: <strong>{{ !empty($filters['status']) ? ucfirst($filters['status']) : 'Semua (Aktif)' }}</strong>
        @if(!empty($filters['search']))
            | Cari: <strong>"{{ $filters['search'] }}"</strong>
        @endif
        @if(!empty($filters['category_id']))
            @php $catObj = \Leazycms\Web\Models\Category::find($filters['category_id']); @endphp
            | Kategori: <strong>{{ $catObj?->name ?? $filters['category_id'] }}</strong>
        @endif
        @if(!empty($filters['from_date']) || !empty($filters['to_date']))
            | Periode: <strong>{{ $filters['from_date'] ?? '-' }} s/d {{ $filters['to_date'] ?? '-' }}</strong>
        @endif
    </div>

    <!-- Tabel Data Rekapitulasi Sesuai Kolom Datatable -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">#</th>
                @if(!empty($module->form->thumbnail))
                    <th style="width: 50px;" class="text-center">Foto</th>
                @endif
                <th>{{ $module->datatable->data_title ?? 'Judul' }}</th>
                @if(!empty($module->form->post_parent) && !empty($module->form->post_parent[0]))
                    <th>{{ $module->form->post_parent[0] }}</th>
                @endif
                @if(!empty($module->form->category))
                    <th style="width: 80px;">Kategori</th>
                @endif
                @if(!empty($module->datatable->child_count))
                    @foreach($module->datatable->child_count as $childType)
                        <th style="width: 60px;" class="text-center">
                            {{ \Illuminate\Support\Str::headline(str_replace('-', ' ', $childType)) }}
                        </th>
                    @endforeach
                @endif
                @foreach($customCols as $cCol)
                    <th>{{ $cCol }}</th>
                @endforeach
                @if(($module->datatable->timestamps ?? true) !== false)
                    <th style="width: 75px;">Dibuat</th>
                @endif
                <th style="width: 55px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>

                    @if(!empty($module->form->thumbnail))
                        <td class="text-center">
                            @if(!empty($item->thumbnail))
                                @php
                                    $thumb = $item->thumbnail;
                                    if (str_starts_with($thumb, 'http')) {
                                        $thumbPath = $thumb;
                                    } elseif (str_starts_with($thumb, '/media/')) {
                                        $thumbPath = media($thumb)->url();
                                    } else {
                                        $thumbPath = media($thumb)->url() ?: $thumb;
                                    }
                                @endphp
                                @if(!empty($thumbPath))
                                    <img src="{{ $thumbPath }}"
                                        style="width: 36px; height: 36px; object-fit: cover; border-radius: 3px;" alt="foto">
                                @else
                                    <span style="font-size: 7pt; color: #94a3b8;">Ada</span>
                                @endif
                            @else
                                <span style="font-size: 7pt; color: #cbd5e1;">-</span>
                            @endif
                        </td>
                    @endif

                    <td>
                        <strong>{{ $item->title ?? '-' }}</strong>
                        @if(!empty($item->data_field['no_pendaftaran']) && $item->title !== $item->data_field['no_pendaftaran'])
                            <br><small style="color: #64748b;">No: {{ $item->data_field['no_pendaftaran'] }}</small>
                        @endif
                        @if($item->pinned == 'Y' || $item->pinned == 1)
                            <span style="color: #dc2626; font-size: 7pt; font-weight: bold;">[Disematkan]</span>
                        @endif
                    </td>

                    @if(!empty($module->form->post_parent) && !empty($module->form->post_parent[0]))
                        <td>{{ $item->parent?->title ?? '-' }}</td>
                    @endif

                    @if(!empty($module->form->category))
                        <td>
                            @if($item->category)
                                <span class="badge badge-category">{{ $item->category->name }}</span>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                    @endif

                    @if(!empty($module->datatable->child_count))
                        @foreach($module->datatable->child_count as $childType)
                            @php
                                $cnt = \Leazycms\Web\Models\Post::where('type', $childType)->where('parent_id', $item->id)->count();
                            @endphp
                            <td class="text-center">{{ $cnt }}</td>
                        @endforeach
                    @endif

                    @foreach($customCols as $cCol)
                        @php
                            $cKey = _us($cCol);
                            $rawVal = $item->data_field[$cKey] ?? $item->data_field[$cCol] ?? null;
                        @endphp
                        <td>
                            @if(is_array($rawVal))
                                {{ implode(', ', array_filter($rawVal)) }}
                            @elseif(is_string($rawVal) && $rawVal !== '')
                                @if(str_starts_with($rawVal, '/media/'))
                                    {{ basename($rawVal) }}
                                @else
                                    {{ strip_tags($rawVal) }}
                                @endif
                            @else
                                <span style="color: #cbd5e1;">-</span>
                            @endif
                        </td>
                    @endforeach

                    @if(($module->datatable->timestamps ?? true) !== false)
                        <td style="font-size: 7.5pt; color: #475569;">
                            {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}
                        </td>
                    @endif

                    <td class="text-center">
                        @if($item->trashed())
                            <span class="badge badge-sampah">Sampah</span>
                        @elseif($item->status === 'publish')
                            <span class="badge badge-publish">Publish</span>
                        @else
                            <span class="badge badge-draft">Draft</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colCount }}" class="text-center"
                        style="padding: 25px; color: #94a3b8; font-style: italic;">
                        Tidak ada data {{ strtolower($module->title) }} yang memenuhi kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Catatan Kaki / Footer Dokumen -->
    <div class="footer-note">
        <table class="footer-table">
            <tr>
                <td style="width: 50%;">
                    Dokumen ini di-generate otomatis oleh Sistem Administrasi {{ $siteName }}
                </td>
                <td style="width: 50%; text-align: right;">
                    Hal. 1 &bull; Modul: {{ $module->title }} ({{ $module->name }})
                </td>
            </tr>
        </table>
    </div>
</body>

</html>