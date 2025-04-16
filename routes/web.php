<?php

require __DIR__.'/auth.php';

use App\Http\Controllers\Admin\ArticleCategoryController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FooterController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\RssController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SitemapGoogleNewsController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Redirect to Dashboard
    Route::redirect('/', '/admin/dashboard');

    // Article Management
    Route::delete('articles/bulk-destroy', [ArticleController::class, 'bulkDestroy'])->name('articles.bulk-destroy');
    Route::resource('articles', ArticleController::class);

    // Article categories management
    Route::prefix('article-categories')->name('article-categories.')->group(function () {
        Route::get('/', [ArticleCategoryController::class, 'index'])->name('index');
        Route::get('/create', [ArticleCategoryController::class, 'create'])->name('create');
        Route::post('/', [ArticleCategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [ArticleCategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [ArticleCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [ArticleCategoryController::class, 'destroy'])->name('destroy');
    });

    // Pages Management
    Route::resource('pages', PageController::class)->except(['show']);

    // Settings
    Route::controller(SettingsController::class)->group(function () {
        Route::get('/settings', 'index')->name('settings');
        Route::put('/settings', 'update')->name('settings.update');
    });

    // Users Management
    Route::resource('users', UserController::class)->except(['show']);

    // Menu Management
    Route::resource('menus', MenuController::class)->except(['show']);
    Route::post('menus/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');

    // Footer Management
    Route::prefix('footer')->controller(FooterController::class)->name('footer.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/column', 'storeColumn')->name('column.store');
        Route::post('/column/{column}/item', 'storeColumnItem')->name('column.item.store');
        Route::post('/setting', 'updateSetting')->name('setting.update');

        // AJAX routes
        Route::post('/column/update-order', 'updateColumnOrder')->name('column.updateOrder');
        Route::post('/item/update-order', 'updateItemOrder')->name('item.updateOrder');
        Route::post('/item/update-parent', 'updateItemParent')->name('item.updateParent');
        Route::post('/update', 'updateFooter')->name('update');
        Route::delete('/delete', 'deleteFooter')->name('delete');
    });
});

// Weather API endpoints
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/provinces', [WeatherController::class, 'apiGetProvinces'])->name('provinces');
    Route::get('/provinces/{province}/districts', [WeatherController::class, 'apiGetDistricts'])->name('districts');
    Route::get('/districts/{district}/wards', [WeatherController::class, 'apiGetWards'])->name('wards');
});

// Articles, Pages, and other routes remain unchanged
Route::get('/danh-muc/{category:slug}', [App\Http\Controllers\ArticleCategoryController::class, 'show'])->name('article-categories.show');
Route::get('/tin-tuc', [App\Http\Controllers\ArticleController::class, 'index'])->name('articles.index');
Route::get('/tin-tuc/{article:slug}', [App\Http\Controllers\ArticleController::class, 'show'])->name('articles.show');
Route::get('/trang/{page:slug}', [App\Http\Controllers\PageController::class, 'show'])->name('pages.show');
Route::post('images/upload', [ImageController::class, 'store'])->name('images.upload');
Route::get('/sitemap-news.xml', [SitemapGoogleNewsController::class, 'index'])->name('sitemap.news');
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/{path}.xml', [SitemapController::class, 'show'])->where('path', '.*');
Route::get('/feed', [RssController::class, 'index'])->name('rss.index');
Route::get('/feed/{category}', [RssController::class, 'category'])->name('rss.category');

// Home page
Route::get('/', [WeatherController::class, 'index'])->name('home');

// API routes
Route::get('/api/weather/{location?}', [WeatherController::class, 'apiGetWeather'])->name('api.weather');
Route::get('/api/provinces', [WeatherController::class, 'apiGetProvinces'])->name('api.provinces');
Route::get('/api/provinces/{province}/districts', [WeatherController::class, 'apiGetDistricts'])->name('api.districts');
Route::get('/api/districts/{district}/wards', [WeatherController::class, 'apiGetWards'])->name('api.wards');

// Hourly forecast routes
Route::get('/{provinceSlug}/theo-gio', [WeatherController::class, 'showProvinceHourly'])
    ->name('weather.province.hourly')
    ->where('provinceSlug', '[a-zA-Z0-9_-]+');

Route::get('/{provinceSlug}/{districtSlug}/theo-gio', [WeatherController::class, 'showDistrictHourly'])
    ->name('weather.district.hourly')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+'
    ]);

Route::get('/{provinceSlug}/{districtSlug}/{wardSlug}/theo-gio', [WeatherController::class, 'showWardHourly'])
    ->name('weather.ward.hourly')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+',
        'wardSlug' => '[a-zA-Z0-9_-]+'
    ]);

// Tomorrow forecast routes
Route::get('/{provinceSlug}/ngay-mai', [WeatherController::class, 'showProvinceTomorrow'])
    ->name('weather.province.tomorrow')
    ->where('provinceSlug', '[a-zA-Z0-9_-]+');

Route::get('/{provinceSlug}/{districtSlug}/ngay-mai', [WeatherController::class, 'showDistrictTomorrow'])
    ->name('weather.district.tomorrow')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+'
    ]);

Route::get('/{provinceSlug}/{districtSlug}/{wardSlug}/ngay-mai', [WeatherController::class, 'showWardTomorrow'])
    ->name('weather.ward.tomorrow')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+',
        'wardSlug' => '[a-zA-Z0-9_-]+'
    ]);

// Daily forecast routes for provinces
Route::get('/{provinceSlug}/3-ngay-toi', [WeatherController::class, 'showProvinceDaily'])
    ->name('weather.province.daily.3')
    ->where('provinceSlug', '[a-zA-Z0-9_-]+')
    ->defaults('days', 3);

Route::get('/{provinceSlug}/5-ngay-toi', [WeatherController::class, 'showProvinceDaily'])
    ->name('weather.province.daily.5')
    ->where('provinceSlug', '[a-zA-Z0-9_-]+')
    ->defaults('days', 5);

Route::get('/{provinceSlug}/7-ngay-toi', [WeatherController::class, 'showProvinceDaily'])
    ->name('weather.province.daily.7')
    ->where('provinceSlug', '[a-zA-Z0-9_-]+')
    ->defaults('days', 7);

Route::get('/{provinceSlug}/10-ngay-toi', [WeatherController::class, 'showProvinceDaily'])
    ->name('weather.province.daily.10')
    ->where('provinceSlug', '[a-zA-Z0-9_-]+')
    ->defaults('days', 10);

Route::get('/{provinceSlug}/15-ngay-toi', [WeatherController::class, 'showProvinceDaily'])
    ->name('weather.province.daily.15')
    ->where('provinceSlug', '[a-zA-Z0-9_-]+')
    ->defaults('days', 15);

Route::get('/{provinceSlug}/20-ngay-toi', [WeatherController::class, 'showProvinceDaily'])
    ->name('weather.province.daily.20')
    ->where('provinceSlug', '[a-zA-Z0-9_-]+')
    ->defaults('days', 20);

Route::get('/{provinceSlug}/30-ngay-toi', [WeatherController::class, 'showProvinceDaily'])
    ->name('weather.province.daily.30')
    ->where('provinceSlug', '[a-zA-Z0-9_-]+')
    ->defaults('days', 30);

// Daily forecast routes for districts
Route::get('/{provinceSlug}/{districtSlug}/3-ngay-toi', [WeatherController::class, 'showDistrictDaily'])
    ->name('weather.district.daily.3')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 3);

Route::get('/{provinceSlug}/{districtSlug}/5-ngay-toi', [WeatherController::class, 'showDistrictDaily'])
    ->name('weather.district.daily.5')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 5);

Route::get('/{provinceSlug}/{districtSlug}/7-ngay-toi', [WeatherController::class, 'showDistrictDaily'])
    ->name('weather.district.daily.7')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 7);

Route::get('/{provinceSlug}/{districtSlug}/10-ngay-toi', [WeatherController::class, 'showDistrictDaily'])
    ->name('weather.district.daily.10')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 10);

Route::get('/{provinceSlug}/{districtSlug}/15-ngay-toi', [WeatherController::class, 'showDistrictDaily'])
    ->name('weather.district.daily.15')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 15);

Route::get('/{provinceSlug}/{districtSlug}/20-ngay-toi', [WeatherController::class, 'showDistrictDaily'])
    ->name('weather.district.daily.20')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 20);

Route::get('/{provinceSlug}/{districtSlug}/30-ngay-toi', [WeatherController::class, 'showDistrictDaily'])
    ->name('weather.district.daily.30')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 30);

// Daily forecast routes for wards
Route::get('/{provinceSlug}/{districtSlug}/{wardSlug}/3-ngay-toi', [WeatherController::class, 'showWardDaily'])
    ->name('weather.ward.daily.3')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+',
        'wardSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 3);

Route::get('/{provinceSlug}/{districtSlug}/{wardSlug}/5-ngay-toi', [WeatherController::class, 'showWardDaily'])
    ->name('weather.ward.daily.5')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+',
        'wardSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 5);

Route::get('/{provinceSlug}/{districtSlug}/{wardSlug}/7-ngay-toi', [WeatherController::class, 'showWardDaily'])
    ->name('weather.ward.daily.7')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+',
        'wardSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 7);

Route::get('/{provinceSlug}/{districtSlug}/{wardSlug}/10-ngay-toi', [WeatherController::class, 'showWardDaily'])
    ->name('weather.ward.daily.10')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+',
        'wardSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 10);

Route::get('/{provinceSlug}/{districtSlug}/{wardSlug}/15-ngay-toi', [WeatherController::class, 'showWardDaily'])
    ->name('weather.ward.daily.15')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+',
        'wardSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 15);

Route::get('/{provinceSlug}/{districtSlug}/{wardSlug}/20-ngay-toi', [WeatherController::class, 'showWardDaily'])
    ->name('weather.ward.daily.20')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+',
        'wardSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 20);

Route::get('/{provinceSlug}/{districtSlug}/{wardSlug}/30-ngay-toi', [WeatherController::class, 'showWardDaily'])
    ->name('weather.ward.daily.30')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+',
        'wardSlug' => '[a-zA-Z0-9_-]+'
    ])
    ->defaults('days', 30);

// Simple location routes - THESE MUST BE AT THE END
Route::get('/{provinceSlug}', [WeatherController::class, 'showProvince'])
    ->name('weather.province')
    ->where('provinceSlug', '[a-zA-Z0-9_-]+');

Route::get('/{provinceSlug}/{districtSlug}', [WeatherController::class, 'showDistrict'])
    ->name('weather.district')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+'
    ]);

Route::get('/{provinceSlug}/{districtSlug}/{wardSlug}', [WeatherController::class, 'showWard'])
    ->name('weather.ward')
    ->where([
        'provinceSlug' => '[a-zA-Z0-9_-]+',
        'districtSlug' => '[a-zA-Z0-9_-]+',
        'wardSlug' => '[a-zA-Z0-9_-]+'
    ]);

// Fallback to content mirroring for other paths (only if needed for some paths still)
//Route::get('/{path}', [ContentController::class, 'show'])
//    ->where('path', '.*')
//    ->name('content.show');
