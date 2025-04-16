<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SetupWeatherImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:setup-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up weather images storage directory and check PNG files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up weather images directory...');

        // Create directories if they don't exist
        if (!File::exists(public_path('assets'))) {
            File::makeDirectory(public_path('assets'));
            $this->info('Created assets directory.');
        }

        if (!File::exists(public_path('assets/images'))) {
            File::makeDirectory(public_path('assets/images'));
            $this->info('Created images directory.');
        }

        if (!File::exists(public_path('assets/images/weather-1'))) {
            File::makeDirectory(public_path('assets/images/weather-1'));
            $this->info('Created weather-1 images directory.');
        }

        $this->info('Weather image directories are ready.');

        // Check if PNG files exist
        $requiredPngFiles = [
            '01d.png', // Clear sky (day)
            '01n.png', // Clear sky (night)
            '02d.png', // Few clouds (day)
            '02n.png', // Few clouds (night)
            '03d.png', // Scattered clouds (day)
            '03n.png', // Scattered clouds (night)
            '04d.png', // Broken clouds (day)
            '04n.png', // Broken clouds (night)
            '09d.png', // Shower rain (day)
            '10d.png', // Rain (day)
            '10n.png', // Rain (night)
            '50d.png', // Mist/Fog (day)
            '50n.png'  // Mist/Fog (night)
        ];

        $missingFiles = [];
        foreach ($requiredPngFiles as $file) {
            if (!File::exists(public_path('assets/images/weather-1/' . $file))) {
                $missingFiles[] = $file;
            }
        }

        if (count($missingFiles) > 0) {
            $this->warn('Missing PNG weather icons: ' . implode(', ', $missingFiles));
            $this->info('Please make sure all weather icons are in the public/assets/images/weather-1 directory.');
        } else {
            $this->info('All required weather PNG files are present.');
        }

        // Check for icon-1 directory and required files
        if (!File::exists(public_path('assets/images/icon-1'))) {
            File::makeDirectory(public_path('assets/images/icon-1'));
            $this->info('Created icon-1 directory for auxiliary weather icons.');
        }

        // Required icon files
        $requiredIconFiles = [
            'temperature.svg',
            'humidity-xl.svg',
            'clarity-eye-line.svg',
            'ph-wind.svg',
            'dawn.svg',
            'dewpoint.svg'
        ];

        $missingIconFiles = [];
        foreach ($requiredIconFiles as $file) {
            if (!File::exists(public_path('assets/images/icon-1/' . $file))) {
                $missingIconFiles[] = $file;
            }
        }

        if (count($missingIconFiles) > 0) {
            $this->warn('Missing icon SVG files: ' . implode(', ', $missingIconFiles));
            $this->info('Please make sure all required icons are in the public/assets/images/icon-1 directory.');
        } else {
            $this->info('All required icon SVG files are present.');
        }

        $this->info('Weather images setup completed successfully!');

        return Command::SUCCESS;
    }
}
