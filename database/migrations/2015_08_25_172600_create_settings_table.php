<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Utilisation d'une table de configuration par défaut ou personnalisée
        $tableName = config('settings.table', 'settings');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id(); // Equivalent à increments('id')
            $table->string('key')->index(); // Index sur la clé
            $table->text('value'); // Valeur des paramètres
            $table->timestamps(); // Colonnes created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = config('settings.table', 'settings');

        Schema::dropIfExists($tableName);
    }
}
