<?php
namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class OfflineTokenController extends Controller
{
    private const ALGO  = 'HS256';
    private const TTL   = 43200; // 12 hours

    private static function secret(): string
    {
        return config('app.key');
    }

    public function issue(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->is_admin) abort(403);

        $now     = time();
        $payload = [
            'iss'  => config('app.url'),
            'sub'  => $user->id,
            'name' => $user->name,
            'cs'   => strtoupper($user->callsign ?? ''),
            'adm'  => true,
            'iat'  => $now,
            'exp'  => $now + self::TTL,
            'scp'  => ['net-control'], // scope
        ];

        $token = JWT::encode($payload, self::secret(), self::ALGO);

        return response()->json([
            'token'      => $token,
            'expires_at' => $now + self::TTL,
            'expires_in' => self::TTL,
            'user'       => ['name' => $user->name, 'callsign' => $payload['cs']],
        ]);
    }

    public static function verify(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key(self::secret(), self::ALGO));
            $payload = (array) $decoded;
            if (($payload['exp'] ?? 0) < time()) return null;
            if (!in_array('net-control', (array)($payload['scp'] ?? []))) return null;
            return $payload;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
