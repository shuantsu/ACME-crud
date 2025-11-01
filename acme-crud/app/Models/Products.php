<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = ['name', 'price']; // ⬅️ Adicione os campos da sua migração aqui
}
