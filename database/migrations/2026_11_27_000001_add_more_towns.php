<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** More towns for the regions outside Greater Accra (district capitals and well-known towns). Existing towns are left alone. */
    private const MORE = [
        'Ashanti' => ['Asokwa', 'Kwadaso', 'Bantama', 'Adum', 'Santasi', 'Ahodwo', 'Nhyiaeso', 'Atonsu', 'Ayigya', 'Bomso', 'Kentinkrono', 'Abuakwa', 'Agogo', 'Juaso', 'Nkawie', 'Effiduase', 'Ejura', 'New Edubiase', 'Kumawu', 'Tepa', 'Manso Nkwanta', 'Fomena', 'Kuntanase'],
        'Western' => ['Kwesimintsim', 'Effiakuma', 'Anaji', 'Apowa', 'Agona Nkwanta', 'Bogoso', 'Abosso', 'Nkroful', 'Esiama', 'Daboase', 'Huni Valley', 'Wassa Akropong', 'Beyin', 'Mpohor'],
        'Western North' => ['Bodi', 'Sefwi Bekwai', 'Sefwi Akontombra', 'Sefwi Dwenase', 'Debiso', 'Awaso', 'Asankragwa'],
        'Central' => ['Saltpond', 'Apam', 'Fetteh', 'Buduburam', 'Ajumako', 'Breman Asikuma', 'Abura Dunkwa', 'Moree', 'Anomabo', 'Komenda', 'Hemang', 'Diaso'],
        'Eastern' => ['Akosombo', 'Atimpoku', 'Mpraeso', 'Begoro', 'Anyinam', 'Kibi', 'Donkorkrom', 'Asesewa', 'Odumase Krobo', 'Bunso', 'Adeiso', 'Akuse', 'Kpong', 'Apedwa'],
        'Volta' => ['Akatsi', 'Adidome', 'Dzodze', 'Kpetoe', 'Peki', 'Tsito', 'Anyako', 'Abor', 'Battor', 'Sokode', 'Have', 'Juapong', 'Aveyime', 'Tefle', 'Agbozume', 'Dabala'],
        'Oti' => ['Kete Krachi', 'Kpassa', 'Worawora', 'Chinderi', 'Dodi Papase'],
        'Northern' => ['Kumbungu', 'Tolon', 'Karaga', 'Sang', 'Zabzugu', 'Tatale', 'Saboba', 'Wulensi', 'Nanton', 'Kpandai'],
        'Savannah' => ['Daboya', 'Tuna', 'Bamboi', 'Larabanga', 'Yapei', 'Kpembe', 'Kafaba'],
        'North East' => ['Yunyoo', 'Nakpanduri', 'Yagaba'],
        'Upper East' => ['Garu', 'Tempane', 'Binduri', 'Bongo', 'Sirigu', 'Zuarungu', 'Pusiga'],
        'Upper West' => ['Wechiau', 'Nadowli', 'Gwollu', 'Funsi', 'Hamile', 'Han', 'Babile', 'Daffiama'],
        'Bono' => ['Sampa', 'Nsoatre', 'Odumase', 'Fiapre', 'Chiraa', 'Abesim', 'Wamfie', 'Seikwa', 'Banda Ahenkro', 'Jinijini', 'Nsawkaw', 'Kwatire'],
        'Bono East' => ['Prang', 'Kajaji', 'Amantin', 'Jema', 'Busunya', 'Tuobodom', 'Kwame Danso', 'Sene'],
        'Ahafo' => ['Mim', 'Kukuom', 'Ntotroso', 'Acherensua', 'Sankore', 'Kwapong'],
    ];

    public function up(): void
    {
        foreach (self::MORE as $region => $towns) {
            $id = DB::table('regions')->where('name', $region)->value('id');
            if (! $id) {
                continue;
            }
            DB::table('delivery_zones')->insertOrIgnore(array_map(fn ($t) => ['region_id' => $id, 'name' => $t, 'created_at' => now(), 'updated_at' => now()], $towns));
        }

        // Greater Accra is priced town by town. A town in it that has no price yet must not fall back to a general fee,
        // so it stays switched off until it is priced.
        $ga = DB::table('regions')->where('name', 'Greater Accra')->value('id');
        if ($ga) {
            DB::table('delivery_zones')->where('region_id', $ga)->whereNull('fee')->update(['is_active' => false]);
        }
    }

    public function down(): void
    {
        foreach (self::MORE as $region => $towns) {
            $id = DB::table('regions')->where('name', $region)->value('id');
            if ($id) {
                DB::table('delivery_zones')->where('region_id', $id)->whereIn('name', $towns)->delete();
            }
        }
    }
};
