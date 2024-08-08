<?php
$config['web_type'] = 'Sekolah';
$config['option'] = array(
    ['Nama Sekolah','text'],
    ['Status Negeri','text','required'],
    ['Foto Gedung','file']
);
$config['template_info'] = array(
    ['path','default'],
    ['name','Default'],
    ['developer','Official'],
    ['contact','official@gmail.com'],
    ['website','http://official.com']
);
add_module([
            'position' => 3,
            'name' => 'itsa',
            'title' => 'ITSA',
            'description' => 'Menu Untuk Mengelola ITSA',
            'parent' => false,
            'icon' => 'fa-check',
            'route' => ['index','create','show','update','delete'],
            'datatable'=>[
                'custom_column' => false,
                'data_title' => 'Judul',
            ],
            'form'=>[
                'unique_title' => false,
                'post_parent' => ['Aplikasi','aplikasi'],
                'thumbnail' => false,
                'editor' => false,
                'category' => false,
                'tag' => false,
                'looping_name'=>'Lampiran',
                'looping_data' => array(
                    ['Temuan','text'],
                    ['Path','text'],
                    ['Perbaikan','file']

                ),
                'custom_field' => array(
                    ['Tanggal Pelaksanaan','date'],
                    ['Pelaksana','text'],
                    ['Penanggung Jawab','text'],
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
]);
