<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /** Ghana's 16 regions with their main towns. Towns start with no fee: customers only see a town once the manager prices it. */
    private const DATA = [
        'Greater Accra' => ['Accra', 'Tema', 'Madina', 'Adenta', 'Ashaiman', 'Dansoman', 'Osu', 'Teshie', 'Nungua', 'Spintex', 'Kaneshie', 'Achimota', 'Dome', 'Lapaz', 'East Legon', 'Amasaman', 'Dodowa', 'Prampram', 'Ada'],
        'Ashanti' => ['Kumasi', 'Obuasi', 'Ejisu', 'Mampong', 'Konongo', 'Bekwai', 'Offinso', 'Suame', 'Tafo'],
        'Western' => ['Takoradi', 'Sekondi', 'Tarkwa', 'Axim', 'Prestea', 'Half Assini', 'Shama', 'Elubo'],
        'Western North' => ['Sefwi Wiawso', 'Bibiani', 'Juaboso', 'Enchi', 'Essam'],
        'Central' => ['Cape Coast', 'Kasoa', 'Winneba', 'Mankessim', 'Elmina', 'Swedru', 'Assin Fosu', 'Dunkwa-on-Offin', 'Twifo Praso'],
        'Eastern' => ['Koforidua', 'Nsawam', 'Suhum', 'Nkawkaw', 'Akim Oda', 'Akropong', 'Aburi', 'Somanya', 'Asamankese', 'Kade'],
        'Volta' => ['Ho', 'Keta', 'Sogakope', 'Aflao', 'Hohoe', 'Kpando', 'Anloga', 'Denu'],
        'Oti' => ['Dambai', 'Nkwanta', 'Kadjebi', 'Jasikan', 'Krachi'],
        'Northern' => ['Tamale', 'Yendi', 'Savelugu', 'Bimbilla', 'Gushegu'],
        'Savannah' => ['Damongo', 'Bole', 'Salaga', 'Buipe', 'Sawla'],
        'North East' => ['Nalerigu', 'Walewale', 'Gambaga', 'Bunkpurugu', 'Chereponi'],
        'Upper East' => ['Bolgatanga', 'Bawku', 'Navrongo', 'Zebilla', 'Sandema', 'Paga'],
        'Upper West' => ['Wa', 'Tumu', 'Lawra', 'Jirapa', 'Nandom'],
        'Bono' => ['Sunyani', 'Berekum', 'Dormaa Ahenkro', 'Wenchi', 'Drobo'],
        'Bono East' => ['Techiman', 'Kintampo', 'Atebubu', 'Nkoranza', 'Yeji'],
        'Ahafo' => ['Goaso', 'Bechem', 'Kenyasi', 'Hwidiem', 'Duayaw Nkwanta'],
    ];

    public function up(): void
    {
        Schema::create('regions', function (Blueprint $t) {
            $t->id();
            $t->string('name', 60)->unique();
            $t->string('slug', 70)->unique();
            $t->decimal('default_fee', 8, 2)->nullable();   // charged for "another town in this region" when no town is picked
            $t->unsignedTinyInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('delivery_zones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('region_id')->constrained()->cascadeOnDelete();
            $t->string('name', 80);                          // city, town or area
            $t->decimal('fee', 8, 2)->nullable();            // null = not priced yet, so not offered to customers
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['region_id', 'name']);
        });

        $order = 0;
        foreach (self::DATA as $region => $towns) {
            $id = DB::table('regions')->insertGetId(['name' => $region, 'slug' => Str::slug($region), 'sort_order' => ++$order, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('delivery_zones')->insert(array_map(fn ($town) => ['region_id' => $id, 'name' => $town, 'created_at' => now(), 'updated_at' => now()], $towns));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
        Schema::dropIfExists('regions');
    }
};
