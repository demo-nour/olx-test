<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\Olx\OLXCategoryService;

class OlxCategoriesSeeder extends Seeder
{
    public function run()
    {
        $service = new OLXCategoryService();

        $categories = $service->fetchCategories();

        if (!is_array($categories)) {
            dump("Could not fetch categories from OLX.");
            return;
        }

        $service->storeCategories($categories);

        dump("Categories and roles imported successfully.");
    }
}
