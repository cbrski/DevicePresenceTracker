<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    const SUCCESS   = 'SUCCESS';
    const FAILED    = 'FAILED';

    public $timestamps = false;

    public $fillable = ['timestamp', 'message'];
}
