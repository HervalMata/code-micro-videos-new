<?php
namespace App\ModelFilters;


use App\ModelFilters\DefaultModelFilter;

class CategoryFilter extends DefaultModelFilter
{
    protected $sortable = ['name', 'created_at', 'is_active'];

    public function search($search)
    {
        $this->query->where('name', "LIKE", "%$search%");
    }
}
