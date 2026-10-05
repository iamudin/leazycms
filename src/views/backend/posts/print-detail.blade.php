<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $post->title ?? 'Detail Data' }}</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            color: #1f2937;
            background: #ffffff;
            line-height: 1.5;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .kop-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .kop-title {
            font-size: 16pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: -0.01em;
            text-align: center;
        }
        .kop-sub {
            font-size: 9pt;
            color: #475569;
            text-align: center;
            margin-top: 3px;
        }
        .kop-divider {
            border-top: 2px solid #0f172a;
            border-bottom: 1px solid #0f172a;
            height: 3px;
            margin: 8px 0 16px 0;
        }
        .doc-header {
            text-align: center;
            margin-bottom: 18px;
        }
        .doc-title {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #0d9488;
        }
        .doc-subtitle {
            font-size: 9pt;
            color: #64748b;
            margin-top: 2px;
        }
        .reg-box {
            width: 100%;
            background-color: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }
        .reg-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .reg-box td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .reg-label {
            font-size: 8pt;
            font-weight: bold;
            color: #0d9488;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .reg-number {
            font-size: 13pt;
            font-weight: bold;
            color: #0f766e;
            font-family: monospace;
        }
        .section-header {
            font-size: 10pt;
            font-weight: bold;
            color: #0d9488;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin: 16px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9pt;
        }
        table.detail-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        table.detail-table td.field-name {
            width: 32%;
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
        }
        table.detail-table td.field-val {
            width: 68%;
            color: #0f172a;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7.5pt;
            font-weight: bold;
            border-radius: 3px;
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
        .sign-area {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .sign-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sign-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .sign-box {
            width: 220px;
            float: right;
            text-align: center;
        }
        .sign-date {
            font-size: 8.5pt;
            color: #475569;
            margin-bottom: 8px;
        }
        .sign-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e293b;
        }
        .sign-space {
            height: 55px;
        }
        .sign-name {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #334155;
            display: inline-block;
            min-width: 160px;
            padding-bottom: 2px;
        }
    </style>
</head>
<body>
    @php
        $siteName = get_option('site_name') ?: (config('app.name') ?: 'LeazyCMS');
        $siteDesc = get_option('site_description') ?: 'Sistem Informasi Manajemen Terpadu';
        $noReg = $post->data_field['no_pendaftaran'] 
            ?? $post->data_field['nomor_pendaftaran'] 
            ?? $post->data_field['kode_pendaftaran'] 
            ?? null;
    @endphp

    <!-- Kop Surat -->
    <table class="kop-table">
        <tr>
            <td>
                <div class="kop-title">{{ $siteName }}</div>
                <div class="kop-sub">{{ $siteDesc }}</div>
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>

    <!-- Judul Dokumen -->
    <div class="doc-header">
        <div class="doc-title">LEMBAR DATA {{ strtoupper($module->title ?? $module->name) }}</div>
        <div class="doc-subtitle">Dicetak pada {{ date('d F Y, H:i') }} WIB</div>
    </div>

    <!-- Kotak Nomor Registrasi / Identifikasi -->
    <div class="reg-box">
        <table>
            <tr>
                <td style="width: 70%;">
                    <div class="reg-label">{{ !empty($noReg) ? 'Nomor Registrasi / Identifikasi' : ($module->datatable->data_title ?? 'Judul') }}</div>
                    <div class="reg-number">{{ $noReg ?: ($post->title ?? '-') }}</div>
                </td>
                <td style="width: 30%; text-align: right;">
                    <div class="reg-label">Status Data</div>
                    <div style="margin-top: 3px;">
                        @if($post->status === 'publish')
                            <span class="badge badge-publish">Dipublikasi</span>
                        @else
                            <span class="badge badge-draft">Draft</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Pokok -->
    <div class="section-header">Informasi Pokok</div>
    <table class="detail-table">
        @if(!empty($noReg) && !empty($post->title) && $post->title !== $noReg)
            <tr>
                <td class="field-name">{{ $module->datatable->data_title ?? 'Judul / Nama' }}</td>
                <td class="field-val"><strong>{{ $post->title }}</strong></td>
            </tr>
        @endif
        @if(!empty($post->category))
            <tr>
                <td class="field-name">Kategori / Jalur</td>
                <td class="field-val">{{ $post->category->name }}</td>
            </tr>
        @endif
        @if(!empty($post->parent))
            <tr>
                <td class="field-name">Induk / Referensi</td>
                <td class="field-val">{{ $post->parent->title }}</td>
            </tr>
        @endif
        <tr>
            <td class="field-name">Waktu Penginputan</td>
            <td class="field-val">{{ $post->created_at ? $post->created_at->format('d F Y - H:i') . ' WIB' : '-' }}</td>
        </tr>
        @if(!empty($post->user))
            <tr>
                <td class="field-name">Petugas / Penginput</td>
                <td class="field-val">{{ $post->user->name }}</td>
            </tr>
        @endif
    </table>

    <!-- Data Tambahan / Custom Fields Modul -->
    @php
        $rawFields = $module->form->custom_field ?? [];
        $dataField = $post->data_field ?? [];
    @endphp

    @if(!empty($rawFields))
        <div class="section-header">Rincian Data Formulir</div>
        <table class="detail-table">
            @foreach($rawFields as $fieldRow)
                @php
                    if (is_object($fieldRow)) $fieldRow = (array) $fieldRow;
                    $fLabel = $fieldRow[0] ?? '';
                    $fMeta = $fieldRow[1] ?? [];
                    if (is_object($fMeta)) $fMeta = (array) $fMeta;
                    if (is_string($fMeta)) $fMeta = ['type' => $fMeta];
                    $fType = $fMeta['type'] ?? 'text';
                    $fKey = _us($fLabel);
                @endphp

                @if($fType === 'break')
                    <tr>
                        <td colspan="2" style="background-color: #f1f5f9; font-weight: bold; color: #0d9488; font-size: 8.5pt; text-transform: uppercase;">
                            &bull; {{ $fLabel }}
                        </td>
                    </tr>
                @else
                    @php
                        $val = $dataField[$fKey] ?? $dataField[$fLabel] ?? null;
                    @endphp
                    <tr>
                        <td class="field-name">{{ $fLabel }}</td>
                        <td class="field-val">
                            @if(is_array($val))
                                {{ implode(', ', array_filter($val)) }}
                            @elseif(is_string($val) && $val !== '')
                                @if(str_starts_with($val, '/media/'))
                                    <span>{{ basename($val) }}</span>
                                @else
                                    {!! nl2br(e(strip_tags($val))) !!}
                                @endif
                            @else
                                <span style="color: #94a3b8; font-style: italic;">(Kosong)</span>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </table>
    @elseif(!empty($dataField))
        <div class="section-header">Rincian Data</div>
        <table class="detail-table">
            @foreach($dataField as $k => $v)
                @if(!in_array($k, ['no_pendaftaran', 'nomor_pendaftaran', 'kode_pendaftaran', 'id_pendaftaran', 'last_editor', 'captcha', '_captcha']))
                    <tr>
                        <td class="field-name">{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $k)) }}</td>
                        <td class="field-val">
                            @if(is_array($v))
                                {{ implode(', ', array_filter($v)) }}
                            @else
                                {{ is_string($v) ? strip_tags($v) : $v }}
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </table>
    @endif

    <!-- Keterangan Tambahan / Konten jika ada -->
    @if(!empty($post->content))
        <div class="section-header">Keterangan / Catatan</div>
        <div style="border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; background-color: #fafafa; font-size: 8.5pt;">
            {!! strip_tags($post->content, '<p><br><ul><ol><li><strong><em><b><i>') !!}
        </div>
    @endif

    <!-- Area Tanda Tangan -->
    <div class="sign-area">
        <table class="sign-table">
            <tr>
                <td style="width: 50%;">
                    <div style="font-size: 7.5pt; color: #94a3b8; margin-top: 20px;">
                        <em>Dokumen ini dicetak otomatis oleh sistem dan merupakan bukti pencatatan resmi data {{ strtolower($module->title) }}.</em>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="sign-box">
                        <div class="sign-date">{{ date('d F Y') }}</div>
                        <div class="sign-title">Petugas / Panitia Pelaksana,</div>
                        <div class="sign-space"></div>
                        <div class="sign-name">{{ $post->user?->name ?? 'Administrator' }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
