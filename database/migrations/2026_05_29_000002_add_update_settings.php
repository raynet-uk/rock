<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
class AddUpdateSettings extends Migration {
    public function up(): void {
        $keys = ['update_available','update_remote_version','update_checked_at','last_updated_version','last_updated_at','show_update_interstitial'];
        foreach ($keys as $key) {
            DB::table('settings')->insertOrIgnore(['key'=>$key,'value'=>'','created_at'=>now(),'updated_at'=>now()]);
        }
    }
    public function down(): void {}
}
