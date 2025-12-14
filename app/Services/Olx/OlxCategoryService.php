<?php

namespace App\Services\Olx;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Category;
use App\Models\CategoryRole;

class OlxCategoryService
{
    private string $baseUrl = "https://www.olx.com.lb/api";

    public function fetchCategories()
    {
        return Cache::remember('olx_categories', now()->addHours(24), function () {
            return Http::get("{$this->baseUrl}/categories")->json();
        });
    }

    public function storeCategories(array $categories, $parentId = null)
    {
        foreach ($categories as $row) {
            $category = Category::updateOrCreate(
                ['external_id' => $row['externalID']],
                [
                    'olx_id'    => $row['id'],
                    'name'      => $row['name'],
                    'slug'      => $row['slug'] ?? null,
                    'parent_id' => $parentId
                ]
            );

            if (isset($row['roles']) && is_array($row['roles'])) {
                foreach ($row['roles'] as $role) {
                    CategoryRole::updateOrCreate(
                        ['category_id' => $category->id, 'role' => $role]
                    );
                }
            }

            if (!empty($row['children'])) {
                $this->storeCategories($row['children'], $category->id);
            }
        }
    }
}
