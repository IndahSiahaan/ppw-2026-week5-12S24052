public function up(): void
{
    Schema::table('bukus', function (Blueprint $table) {
        $table->text('deskripsi')->nullable()->after('kategori_id');
    });
}

public function down(): void
{
    Schema::table('bukus', function (Blueprint $table) {
        $table->dropColumn('deskripsi');
    });
}