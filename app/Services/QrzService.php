<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QrzService
{
    private const QRZ_URL   = 'https://xmldata.qrz.com/xml/current/';
    private const AGENT     = 'Liverpool-RAYNET-Portal/1.0';
    private const CACHE_KEY = 'qrz_session_key';
    private const CACHE_TTL = 60 * 60 * 23;

    private function sessionKey(): ?string
    {
        if ($key = Cache::store('file')->get(self::CACHE_KEY)) {
            return $key;
        }

        $username = config('services.qrz.username');
        $password = config('services.qrz.password');

        if (!$username || !$password) {
            Log::warning('QRZ: credentials not configured');
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => self::AGENT])
                ->get(self::QRZ_URL, [
                    'username' => $username,
                    'password' => $password,
                    'agent'    => self::AGENT,
                ]);

            $xml = $this->parseXml($response->body());

            if (!$xml) {
                Log::warning('QRZ login: XML parse failed. Raw: ' . substr($response->body(), 0, 600));
                return null;
            }

            $key   = (string) ($xml->Session->Key   ?? '');
            $error = (string) ($xml->Session->Error ?? '');

            if (!$key) {
                Log::warning("QRZ login failed — error: '{$error}'");
                return null;
            }

            Log::info('QRZ: session key obtained');
            Cache::store('file')->put(self::CACHE_KEY, $key, self::CACHE_TTL);
            return $key;

        } catch (\Throwable $e) {
            Log::error('QRZ login exception: ' . $e->getMessage());
            return null;
        }
    }

    public function lookup(string $callsign, string &$reason = ''): ?array
    {
        $callsign = strtoupper(trim($callsign));

        $key = $this->sessionKey();
        if (!$key) {
            $reason = 'QRZ authentication failed — check credentials and subscription';
            return null;
        }

        [$result, $reason] = $this->doLookup($callsign, $key);

        if ($result === 'SESSION_EXPIRED') {
            Log::info('QRZ: session expired, re-authenticating');
            Cache::store('file')->forget(self::CACHE_KEY);
            $key = $this->sessionKey();
            if (!$key) {
                $reason = 'QRZ re-authentication failed';
                return null;
            }
            [$result, $reason] = $this->doLookup($callsign, $key);
        }

        return is_array($result) ? $result : null;
    }

    private function doLookup(string $callsign, string $key): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => self::AGENT])
                ->get(self::QRZ_URL, [
                    's'        => $key,
                    'callsign' => $callsign,
                    'agent'    => self::AGENT,
                ]);

            $raw = $response->body();
            Log::debug("QRZ lookup [{$callsign}] HTTP {$response->status()}: " . substr($raw, 0, 800));

            $xml = $this->parseXml($raw);

            if (!$xml) {
                return [null, 'XML parse failed — see laravel.log'];
            }

            $error      = trim((string) ($xml->Session->Error ?? ''));
            $errorLower = strtolower($error);

            if (str_contains($errorLower, 'invalid session') ||
                str_contains($errorLower, 'session timeout') ||
                str_contains($errorLower, 'session expired') ||
                str_contains($errorLower, 'not logged in')) {
                return ['SESSION_EXPIRED', 'session expired'];
            }

            if (str_contains($errorLower, 'not found')) {
                return [null, 'not_found'];
            }

            if (str_contains($errorLower, 'subscription') ||
                str_contains($errorLower, 'xml access') ||
                str_contains($errorLower, 'upgrade')) {
                return [null, 'QRZ XML subscription required'];
            }

            if ($error) {
                Log::warning("QRZ [{$callsign}] error: {$error}");
                return [null, "QRZ error: {$error}"];
            }

            $callsignNode = $xml->Callsign ?? null;

            if (!$callsignNode || count($callsignNode->children()) === 0) {
                Log::warning("QRZ [{$callsign}]: no Callsign node. Raw: " . substr($raw, 0, 800));
                return [null, 'not_found'];
            }

            return [$this->extractData($callsignNode), ''];

        } catch (\Throwable $e) {
            Log::error("QRZ lookup exception [{$callsign}]: " . $e->getMessage());
            return [null, 'exception: ' . $e->getMessage()];
        }
    }

private function extractData(\SimpleXMLElement $node): array
{
    $get = fn(string $field) => trim((string) ($node->$field ?? '')) ?: null;

    $location = collect([$get('addr2'), $get('state'), $get('country')])->filter()->implode(', ');

    $classMap = [
        'T' => 'Technician', 'G' => 'General',    'E' => 'Extra',
        'N' => 'Novice',     'A' => 'Advanced',    'P' => 'Foundation',
        'I' => 'Intermediate', 'F' => 'Full',
    ];
    $rawClass     = $get('class');
    $licenceClass = $rawClass ? ($classMap[$rawClass] ?? $rawClass) : null;

    $name = collect([$get('fname'), $get('name')])->filter()->implode(' ') ?: null;

    $nickname = $get('nickname');
    $nameFmt  = $get('name_fmt');

return [
        'callsign'      => $get('call'),
        'name'          => $name,
        'name_fmt'      => $nameFmt,
        'nickname'      => $nickname,
        'fname'         => $get('fname'),
        'lname'         => $get('name'),
        'email'         => $get('email'),
        'address'       => $get('addr1'),
        'city'          => $get('addr2'),
        'state'         => $get('state'),
        'zip'           => $get('zip'),
        'county'        => $get('county'),
        'fips'          => $get('fips'),
        'country'       => $get('country'),
        'ccode'         => $get('ccode'),
        'location'      => $location ?: null,
        'licence_class' => $licenceClass,
        'licence_code'  => $rawClass,
        'efdate'        => $get('efdate'),
        'expdate'       => $get('expdate'),
        'image_url'     => $get('image'),
        'imageinfo'     => $get('imageinfo'),
        'grid'          => $get('grid'),
        'lat'           => $get('lat'),
        'lon'           => $get('lon'),
        'geoloc'        => $get('geoloc'),
        'aliases'       => $get('aliases'),
        'xref'          => $get('xref'),
        'p_call'        => $get('p_call'),
        'attn'          => $get('attn'),
        'bio'           => $get('bio'),
        'biodate'       => $get('biodate'),
        'u_views'       => $get('u_views'),
        'serial'        => $get('serial'),
        'moddate'       => $get('moddate'),
        'qrz_user'      => $get('user'),
        'qsl_mgr'       => $get('qslmgr'),
        'born'          => $get('born'),
        'cq_zone'       => $get('cqzone'),
        'itu_zone'      => $get('ituzone'),
        'timezone'      => $get('TimeZone'),
        'gmt_offset'    => $get('GMTOffset'),
        'dst'           => $get('DST'),
        'msa'           => $get('MSA'),
        'area_code'     => $get('AreaCode'),
        'eqsl'          => $get('eqsl'),
        'mqsl'          => $get('mqsl'),
        'lotw'          => $get('lotw'),
        'iota'          => $get('iota'),
        'dxcc'          => $get('dxcc'),
        'land'          => $get('land'),
        'codes'         => $get('codes'),
        'url'           => $get('url') ?? ('https://www.qrz.com/db/' . $get('call')),
    ];
}

    private function parseXml(string $body): ?\SimpleXMLElement
    {
        if (empty(trim($body))) {
            return null;
        }

        libxml_use_internal_errors(true);

        $cleaned = str_replace(' xmlns="http://xmldata.qrz.com"', '', $body);

        $xml = simplexml_load_string($cleaned);
        libxml_clear_errors();

        return $xml === false ? null : $xml;
    }
}