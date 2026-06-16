<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_nodes', function (Blueprint $table) {
            // Geo foundation for the nearest-centre finder (Wave 1, A1). Populated from
            // postcodes.io geocoding (lat/lng) + ops-entered address/postcode. `we_book_here`
            // flags centres where UKVisaCo holds appointment slots (badge + tie-break boost).
            $table->string('address')->nullable()->after('name');          // free-text street address
            $table->string('postcode')->nullable()->after('address');      // UK postcode (geocode source)
            $table->decimal('lat', 9, 6)->nullable()->after('postcode');   // WGS84 latitude
            $table->decimal('lng', 9, 6)->nullable()->after('lat');        // WGS84 longitude
            $table->boolean('we_book_here')->default(false)->after('lng'); // we hold slots here
        });
    }

    public function down(): void
    {
        Schema::table('supply_nodes', function (Blueprint $table) {
            $table->dropColumn(['address', 'postcode', 'lat', 'lng', 'we_book_here']);
        });
    }
};
