<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Posts</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
       
        
        .print-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .print-header {
            text-align: center;
            border-bottom: 3px solid #007bff;
        }
        
        .print-header h1 {
            font-size: 28px;
            color: #007bff;
            margin-bottom: 8px;
        }
        
        .print-header p {
            color: #666;
            font-size: 14px;
        }
        
        .print-date {
            text-align: right;
            font-size: 12px;
            color: #999;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        thead {
            background: #07c;
        }
        
        thead th {
            text-align: left;
            font-size: 14px;
            padding: 8px 10px;
            border: 1px solid #0056b3;
            color: #ffffff
        }
        
        tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.3s ease;
        }
        
        tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f5f9ff;
        }
        
        tbody td {
            padding: 3px 5px;
            font-size: 10px;
            font-weight: normal;
            border: 1px solid #e0e0e0;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-primary {
            background-color: #007bff;
            color: white;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .status-publish {
            color: #28a745;
            font-weight: 600;
        }
        
        .status-draft {
            color: #ffc107;
            font-weight: 600;
        }
        
        .print-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #999;
        }
        
        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .summary-item {
            flex: 1;
            min-width: 150px;
            padding: 15px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 6px;
            text-align: center;
        }
        
        .summary-item h3 {
            font-size: 24px;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .summary-item p {
            font-size: 12px;
            color: #666;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .print-container {
                box-shadow: none;
                padding: 20px;
            }
            
            table {
                page-break-inside: avoid;
            }
            
            tbody tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Header -->
    
            <h1>Rekapitulasi Data </h1>
            <p>Daftar lengkap posts berdasarkan tipe {{ $module->title }}</p>
        
        <div class="print-date">
            <strong>Tanggal Cetak:</strong> {{ date('d F Y H:i:s', strtotime(now())) }}
        </div>
        
       
        
        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 2%;">No</th>
                    <th style="width: 30%;" style="color:#000">{{ $module->datatable->data_title }}</th>
                    <th style="width: 15%;">Kategori</th>
                    <th style="width: 15%;">Penulis</th>
                    <th style="width: 15%;">Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts ?? [] as $key => $post)
                <tr>
                    <td style="text-align: center;">{{ $key + 1 }}</td>
                    <td>
                       {{ $post->title ?? '-' }}
                        @if($post->description)
                            <br><small style="color: #999;">{{ Str::limit($post->description, 60) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($post->category)
                            <span class="badge badge-info">{{ $post->category->name }}</span>
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>{{ $post->user->name ?? '-' }}</td>
             
                    <td>
                        <small>{{ date('d M Y H:i', strtotime($post->created_at)) }}</small>
                    </td>
                 
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="no-data">
                        Tidak ada data posts untuk ditampilkan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Footer -->
        <div class="print-footer">
            <p>© {{ date('Y') }} - Sistem Manajemen Posts | Dokumen ini dihasilkan secara otomatis</p>
        </div>
    </div>

</body>
</html>