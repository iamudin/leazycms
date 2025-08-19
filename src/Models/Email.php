<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
class Email extends Model
{
    protected $fillable = [
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'attachment_path',
        'direction',
        'status',
        'sent_at',
        'received_at'
    ];
}
