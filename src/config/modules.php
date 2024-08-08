<?php
return [
    'menu' => [
        'pengumuman' => [
            'position' => 3,
            'name' => 'pengumuman',
            'title' => 'Pengumuman',
            'description' => 'Menu Untuk Mengelola Pengumuman',
            'parent' => false,
            'icon' => 'fa-info',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Judul Pengumuman',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => false,
                'editor' => true,
                'category' => false,
                'tag' => false,
                'looping_name'=>'Arsip',
                'looping_data' => false,
                'custom_field' => array(
                    ['Lampiran', 'file'])
            ],
            'web'=>[
                'api' => true,
                'archive' => true,
                'index' => true,
                'detail' => true,
                'history' => true,
                'auto_query' => true,
                'sortable'=>false,
            ],
            'public' => true,
            'cache' => true,
            'active' => true,
        ],
        'dokumentasi' => [
            'position' => 5,
            'name' => 'dokumentasi',
            'title' => 'Dokumentasi',
            'description' => 'Menu Untuk Mengelola Dokumentasi',
            'parent' => false,
            'icon' => 'fa-camera',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Nama Kegiatan',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => true,
                'editor' => true,
                'category' => true,
                'tag' => false,
                'looping_name'=>'Arsip',
                'looping_data' => false,
                'custom_field' => array(['Link Video','text']),
            ],
            'web'=>[
                'api' => true,
                'archive' => true,
                'index' => true,
                'detail' => true,
                'history' => true,
                'auto_query' => true,
                'sortable'=>false,
            ],
            'public' => true,
            'cache' => false,
            'active' => true,
        ],
        'sambutan' => [
            'position' => 0,
            'name' => 'sambutan',
            'title' => 'Sambutan',
            'description' => 'Menu Untuk Mengelola Sambutan',
            'parent' => false,
            'icon' => 'fa-quote-left',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Nama Sambutan',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => true,
                'editor' => true,
                'category' => false,
                'tag' => false,
                'looping_name'=>'Arsip',
                'looping_data' => false,
                'custom_field' => [
                    ['Pemberi Sambutan','break'],
                    ['Nama','text'],
                    ['Jabatan','text'],

                ],
            ],
            'web'=>[
                'api' => false,
                'archive' => false,
                'index' => false,
                'detail' => false,
                'history' => false,
                'auto_query' => true,
                'sortable'=>false,
            ],
            'public' => true,
            'cache' => true,
            'active' => true,
        ],
        'pegawai' => [
            'position' => 8,
            'name' => 'pegawai',
            'title' => 'Pegawai',
            'description' => 'Menu Untuk Mengelola Pegawai',
            'parent' => false,
            'icon' => 'fa-id-card',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => 'Jabatan',
                'data_title' => 'Nama Pegawai',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => ['Unit Kerja','unit-kerja'],
                'thumbnail' => true,
                'editor' => false,
                'category' => true,
                'tag' => false,
                'looping_name'=>'Arsip',
                'looping_data' => false,
                'custom_field' => [
                    ['NIP','text','required'],
                    ['Kelahiran','text','required'],
                    ['Jabatan','text','required'],
                    ['Pangkat/Golongan','text','required'],
                    ['Pendidikan','text','required'],
                    ['Tahun Mulai','text','required'],
                ],
            ],
            'web'=>[
                'api' => false,
                'archive' => false,
                'index' => true,
                'detail' => false,
                'history' => false,
                'auto_query' => false,
                'sortable'=>false,
            ],
            'public' => true,
            'cache' => false,
            'active' => true,
        ],
        'unit-kerja' => [
            'position' => 7,
            'name' => 'unit-kerja',
            'title' => 'Unit Kerja',
            'description' => 'Menu Untuk Mengelola Unit Kerja',
            'parent' => false,
            'icon' => 'fa-sitemap',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Nama',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => false,
                'editor' => true,
                'category' => false,
                'tag' => false,
                'looping_name'=>'Arsip',
                'looping_data' => false,
                'custom_field' => false,
            ],
            'web'=>[
                'api' => false,
                'archive' => false,
                'index' => true,
                'detail' => true,
                'history' => false,
                'auto_query' => true,
                'sortable'=>true,
            ],
            'public' => true,
            'cache' => false,
            'active' => true,
        ],
        'halaman' => [
            'position' => 5,
            'name' => 'halaman',
            'title' => 'Halaman',
            'description' => 'Menu Untuk Mengelola Halaman',
            'parent' => false,
            'icon' => 'fa-globe',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Nama Halaman',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => false,
                'editor' => true,
                'category' => false,
                'tag' => true,
                'looping_name'=>'Arsip',
                'looping_data' => false,
                'custom_field' => false,
            ],
            'web'=>[
                'api' => false,
                'archive' => false,
                'index' => false,
                'detail' => true,
                'history' => false,
                'auto_query' => true,
                'sortable'=>true,
            ],
            'public' => true,
            'cache' => false,
            'active' => true,
        ],

        'berita' => [
            'position' => 1,
            'name' => 'berita',
            'title' => 'Berita',
            'description' => 'Menu Untuk Mengelola Berita',
            'parent' => false,
            'icon' => 'fa-newspaper-o',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Judul Berita',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => true,
                'editor' => true,
                'category' => true,
                'tag' => true,
                'looping_name'=>'Arsip',
                'looping_data' =>false,
                'custom_field' => array(
                    ['Tanggal Entry', 'datetime']
                ),
            ],
            'web'=>[
                'api' => true,
                'archive' => true,
                'index' => true,
                'detail' => true,
                'history' => true,
                'auto_query' => true,
                'sortable'=>false,
            ],
            'public' => true,
            'cache' => false,
            'active' => true,
        ],
        'document' => [
            'position' => 4,
            'name' => 'document',
            'title' => 'Doukumen',
            'description' => 'Menu Untuk Mengelola Dokumen',
            'parent' => false,
            'icon' => 'fa-download',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Nama Dokumen',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => false,
                'editor' => false,
                'category' => true,
                'tag' => false,
                'looping_name'=>'Arsip',
                'looping_data' => false,
                'custom_field' => array(
                    ['File', 'file'],
                    ['Tanggal Entry','datetime']
                ),
            ],
            'web'=>[
                'api' => true,
                'archive' => true,
                'index' => true,
                'detail' => true,
                'history' => true,
                'auto_query' => true,
                'sortable'=>false,
            ],
            'public' => true,
            'cache' => false,
            'active' => true,
        ],
        'link-terkait' => [
            'position' => 10,
            'name' => 'link-terkait',
            'title' => 'Link Terkait',
            'description' => 'Menu Untuk Mengelola Link Terkait',
            'parent' => false,
            'icon' => 'fa-link',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Nama Link',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => true,
                'editor' => false,
                'category' => false,
                'tag' => false,
                'looping_name'=>'Arsip',
                'looping_data' => false,
                'custom_field' => false,
            ],
            'web'=>[
                'api' => false,
                'archive' => false,
                'index' => true,
                'detail' => true,
                'history' => false,
                'auto_query' => false,
                'sortable'=>false,
            ],
            'public' => true,
            'cache' => true,
            'active' => true,
        ],
        'agenda' => [
            'position' => 2,
            'name' => 'agenda',
            'title' => 'Agenda',
            'description' => 'Menu Untuk Mengelola Agenda',
            'parent' => false,
            'icon' => 'fa-calendar-o',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Tema',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => false,
                'editor' => true,
                'category' => true,
                'tag' => true,
                'looping_name'=>'Arsip',
                'looping_data' => false,
                'custom_field' => array(
                    ['Jam', 'datetime'],
                    ['Tempat', 'text'],
                    ['Alamat', 'text'],
                    ['Pejabat', 'text'],
                    ['Tanggal Entry', 'datetime']
                ),
            ],
            'web'=>[
                'api' => true,
                'archive' => true,
                'index' => true,
                'detail' => true,
                'history' => true,
                'auto_query' => true,
                'sortable'=>false,
            ],
            'public' => true,
            'cache' => false,
            'active' => true,
        ],
        'menu' => [
            'position' => 11,
            'name' => 'menu',
            'title' => 'Menu',
            'description' => 'Menu Untuk Mengelola Agenda',
            'parent' => false,
            'icon' => 'fa-list',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Nama Menu',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => false,
                'editor' => false,
                'category' => false,
                'tag' => false,
                'looping_name'=>'Daftar Menu',
                'looping_data' => array(
                    ['menu_id', 'text'],
                    ['menu_parent', 'text'],
                    ['menu_name', 'text'],
                    ['menu_description', 'text'],
                    ['menu_link', 'text'],
                    ['menu_icon', 'text']
                ),
                'custom_field' => false,
            ],
            'web'=>[
                'api' => false,
                'archive' => false,
                'index' => false,
                'detail' => false,
                'history' => false,
                'auto_query' => false,
                'sortable'=>false,
            ],
            'public' => false,
            'cache' => false,
            'active' => true,
        ],
        'media' => [
            'position' => 12,
            'name' => 'media',
            'title' => 'Media',
            'description' => 'Menu Untuk Melihat Media',
            'parent' => false,
            'icon' => 'fa-users',
            'route' => ['index','update','delete'],
            'datatable'=>[
                'custom_column' => 'Ukuran',
                'data_title' => 'Nama Media',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => false,
                'editor' => false,
                'category' => false,
                'tag' => false,
                'looping_name'=>'Daftar Menu',
                'looping_data' => false,
                'custom_field' => array(
                    ['Ukuran','text'],
                ),
            ],
            'web'=>[
                'api' => false,
                'archive' => false,
                'index' => false,
                'detail' => false,
                'history' => false,
                'auto_query' => false,
                'sortable'=>false,
            ],
            'public' => true,
            'cache' => false,
            'active' => true,
        ],
        'banner' => [
            'position' => 11,
            'name' => 'banner',
            'title' => 'Banner',
            'description' => 'Menu Untuk Melihat Banner',
            'parent' => false,
            'icon' => 'fa-image',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Nama Banner',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => true,
                'editor' => false,
                'category' => true,
                'tag' => false,
                'looping_name'=>'Daftar Menu',
                'looping_data' => false,
                'custom_field' => false,
            ],
            'web'=>[
                'api' => false,
                'archive' => false,
                'index' => false,
                'detail' => false,
                'history' => false,
                'auto_query' => false,
                'sortable'=>false,
            ],
            'public' => true,
            'cache' => false,
            'active' => true,
        ]
        ],
        'config'=> [
            'web_type'=> null,
            'option'=> null,
        ],
        'used'=> array(),
        'current'=> null,
        'detail_visited'=> false,
        'data'=> null,
        'domain'=>null,
        'installed'=>env('APP_INSTALLED',false),
        'public_path'=>env('PUBLIC_PATH',null),
        'version'=>null,
];
