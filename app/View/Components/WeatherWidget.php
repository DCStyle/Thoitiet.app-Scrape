<?php

namespace App\View\Components;

use App\Services\WeatherService;
use Illuminate\View\Component;

class WeatherWidget extends Component
{
    public $location;
    public $weatherData;
    public $compact;

    /**
     * Create a new component instance.
     *
     * @param string|null $location
     * @param bool $compact
     */
    public function __construct($location = null, $compact = false)
    {
        $this->location = $location ?: 'ha-noi';
        $this->compact = $compact;
        $this->weatherData = $this->getWeatherData();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.weather-widget');
    }
    
    /**
     * Get weather data for the location
     */
    private function getWeatherData()
    {
        $weatherService = app(WeatherService::class);
        return $weatherService->getWeatherData($this->location);
    }
}
