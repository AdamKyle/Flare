<?php

namespace Tests\Unit\Admin\Imports;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use App\Admin\Import\Items\ItemsImport;

class ItemsImportTest extends TestCase {
    use RefreshDatabase;

    public function testImport() {
        Excel::import(new ItemsImport(), resource_path('data-imports/items.xlsx'));

        $this->assertTrue(true);
    }
}
