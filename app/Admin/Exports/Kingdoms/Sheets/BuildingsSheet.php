<?php

namespace App\Admin\Exports\Kingdoms\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Flare\Models\GameBuilding;

class BuildingsSheet implements FromView, WithTitle, ShouldAutoSize {

    /**
     * @return View
     */
    public function view(): View {
        return view('admin.exports.kingdoms.sheets.buildings', [
            'buildings' => GameBuilding::all(),
        ]);
    }

    /**
     * @return string
     */
    public function title(): string {
        return 'Buildings';
    }
}
