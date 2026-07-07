<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMatch extends Model
{
    protected $guarded = [];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class);
    }

    public function aprovado()
    {
        return $this->belongsTo(Aprovado::class);
    }
}
