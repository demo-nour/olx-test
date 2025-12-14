<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Services\Olx\OlxCategoryFieldService;

class OlxCategoryFieldsSeeder extends Seeder
{
    public function run()
    {
        $service = new OlxCategoryFieldService();

        $categories = Category::all();

        if ($categories->isEmpty()) {
            dump("No categories found. Run OlxCategoriesSeeder first.");
            return;
        }

        foreach ($categories as $category) {

            dump("Fetching fields for category: {$category->external_id}");

            $fields = $service->fetchCategoryField($category->external_id);

            if (!is_array($fields)) {
                dump("Could not fetch fields for category {$category->external_id}");
                continue;
            }

            $service->storeCategoryFields($category->external_id);
        }

        dump("Category fields and options imported successfully.");
    }
}
