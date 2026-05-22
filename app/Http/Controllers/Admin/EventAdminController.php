
    public function netStatus() {
        $settings = [
            "net_active"      => \App\Models\Setting::get("net_active", "0"),
            "net_callsign"    => \App\Models\Setting::get("net_callsign", ""),
            "net_frequency"   => \App\Models\Setting::get("net_frequency", ""),
            "net_description" => \App\Models\Setting::get("net_description", ""),
            "net_controller"  => \App\Models\Setting::get("net_controller", ""),
        ];
        return view("admin.events.net-status", compact("settings"));
    }

    public function updateNetStatus(\Illuminate\Http\Request $request) {
        $fields = ["net_active","net_callsign","net_frequency","net_description","net_controller"];
        foreach ($fields as $key) {
            \App\Models\Setting::set($key, $request->input($key, ""));
        }
        return back()->with("success", "Net status updated.");
    }
}