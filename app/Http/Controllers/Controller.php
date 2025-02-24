<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function getMetadata()
    {
        // Get current URL
        $url = url()->current();

        $currentPath = request()->path();

        // Add prefix slash to path if it doesn't have one
        if (!str_starts_with($currentPath, '/')) {
            $currentPath = '/' . $currentPath;
        }

        // Add suffix slash to path if it doesn't have one
        if (!str_ends_with($currentPath, '/')) {
            $currentPath .= '/';
        }

        // Get custom path settings
        $pathTitles = setting('site_path_title') ? json_decode(setting('site_path_title'), true) : [];
        $pathDescriptions = setting('site_path_description') ? json_decode(setting('site_path_description'), true) : [];

        // Try to find matching path in settings
        $customTitle = null;
        $customDescription = null;

        foreach ($pathTitles as $index => $titleData) {
            $settingPath = array_key_first($titleData);

            // Check if current path matches the setting path
            if ($currentPath === $settingPath || rtrim($currentPath, '/') === rtrim($settingPath, '/')) {
                $customTitle = $titleData[$settingPath];

                // Get matching description if it exists
                if (isset($pathDescriptions[$index][$settingPath])) {
                    $customDescription = $pathDescriptions[$index][$settingPath];
                }
                break;
            }
        }

        return [
            'title' => $customTitle ?? null,
            'description' => $customDescription ?? null
        ];
    }
}
