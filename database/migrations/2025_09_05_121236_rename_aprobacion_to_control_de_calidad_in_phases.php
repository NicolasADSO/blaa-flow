    <?php

   use Illuminate\Support\Facades\DB;

return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void
    {
        DB::table('phases')
            ->where('name', 'Aprobación final')
            ->update(['name' => 'Control de Calidad']);
    }

    public function down(): void
    {
        DB::table('phases')
            ->where('name', 'Control de Calidad')
            ->update(['name' => 'Aprobación final']);
    }
};
