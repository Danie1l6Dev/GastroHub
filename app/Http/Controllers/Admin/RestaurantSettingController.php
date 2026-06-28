<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantSettingRequest;
use App\Models\RestaurantSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RestaurantSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'setting' => $this->setting(),
        ]);
    }

    public function update(RestaurantSettingRequest $request): RedirectResponse
    {
        $setting = $this->setting();
        $data = $request->safe()->except(['logo', 'cover_image']);
        $data['slug'] = Str::slug($data['name']);
        $data['is_open'] = $request->boolean('is_open');

        if ($request->hasFile('logo')) {
            $this->deletePublicFile($setting->logo_path);
            $data['logo_path'] = $request->file('logo')->store('restaurant', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $this->deletePublicFile($setting->cover_image_path);
            $data['cover_image_path'] = $request->file('cover_image')->store('restaurant', 'public');
        }

        $setting->update($data);

        return redirect()->route('admin.settings.edit')->with('status', 'Configuracion actualizada.');
    }

    private function setting(): RestaurantSetting
    {
        return RestaurantSetting::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'GastroHub Bistro',
                'slug' => 'gastrohub-bistro',
                'description' => 'Cocina casual con sabores frescos y pedidos digitales desde la mesa.',
                'primary_color' => '#059669',
                'secondary_color' => '#111827',
                'is_open' => true,
            ]
        );
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
