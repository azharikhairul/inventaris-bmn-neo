<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BpsImapAuthService
{
    /**
     * Authenticate against BPS IMAP servers (mail.bps.go.id and fallback mailhost.bps.go.id).
     *
     * @param  string  $username  Username or email (e.g. azhar or azhar@bps.go.id)
     * @param  string  $password  User password
     * @return array Result array with status, message, and server information
     */
    public function authenticate(string $username, string $password): array
    {
        // Strip domain if provided: e.g. 'azhar@bps.go.id' -> 'azhar'
        $cleanUsername = str_contains($username, '@') ? explode('@', $username)[0] : $username;

        if (empty($cleanUsername) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Username dan password wajib diisi.',
                'connection_error' => false,
            ];
        }

        // List of candidate IMAP servers
        $servers = [
            config('services.bps_mail.server', 'mail.bps.go.id'),
            config('services.bps_mail.fallback_server', 'mailhost.bps.go.id'),
        ];
        // Remove duplicates if config points to same server
        $servers = array_values(array_unique(array_filter($servers)));

        $port = (int) config('services.bps_mail.port', 993);
        $timeout = (int) config('services.bps_mail.timeout', 5);

        $lastError = null;
        $connectionFailedCount = 0;

        foreach ($servers as $server) {
            $attempt = $this->tryImapLogin($server, $port, $cleanUsername, $password, $timeout);

            if ($attempt['connected']) {
                if ($attempt['success']) {
                    return [
                        'success' => true,
                        'server' => $server,
                        'message' => 'Autentikasi BPS berhasil.',
                        'connection_error' => false,
                    ];
                } else {
                    // Connected successfully, but credentials are wrong
                    return [
                        'success' => false,
                        'server' => $server,
                        'message' => 'Username atau password akun BPS salah.',
                        'connection_error' => false,
                    ];
                }
            }

            // Connection failed on this server, record error and try fallback
            $connectionFailedCount++;
            $lastError = $attempt['message'] ?? "Gagal terhubung ke {$server}:{$port}";
            Log::warning("BPS IMAP Auth connection failed to {$server}: ".$lastError);
        }

        return [
            'success' => false,
            'server' => null,
            'message' => 'Tidak dapat terhubung ke server mail BPS (mail.bps.go.id / mailhost.bps.go.id). '.$lastError,
            'connection_error' => true,
        ];
    }

    /**
     * Perform socket IMAP SSL connection and send LOGIN command.
     */
    protected function tryImapLogin(string $host, int $port, string $username, string $password, int $timeout): array
    {
        $ctx = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);

        if (! $fp) {
            return [
                'connected' => false,
                'success' => false,
                'message' => $errstr ?: "Koneksi ke {$host} gagal (error code: {$errno})",
            ];
        }

        // Set stream read timeout
        stream_set_timeout($fp, $timeout);

        // Read server banner / greeting (e.g. '* OK ...')
        $greeting = fgets($fp, 512);
        if (! $greeting || ! str_starts_with(trim($greeting), '* OK')) {
            @fclose($fp);

            return [
                'connected' => false,
                'success' => false,
                'message' => "Banner server IMAP {$host} tidak valid.",
            ];
        }

        // Send IMAP LOGIN command
        $tag = 'A'.rand(1000, 9999);
        $escapedUser = addcslashes($username, '\\"');
        $escapedPass = addcslashes($password, '\\"');
        $loginCmd = "{$tag} LOGIN \"{$escapedUser}\" \"{$escapedPass}\"\r\n";

        fwrite($fp, $loginCmd);

        $authed = false;
        $responseMsg = '';

        while (! feof($fp)) {
            $line = fgets($fp, 512);
            if ($line === false) {
                break;
            }

            $trimmed = trim($line);

            if (str_starts_with($trimmed, $tag.' OK')) {
                $authed = true;
                $responseMsg = $trimmed;
                break;
            } elseif (str_starts_with($trimmed, $tag.' NO') || str_starts_with($trimmed, $tag.' BAD')) {
                $authed = false;
                $responseMsg = $trimmed;
                break;
            }
        }

        // Graceful logout and close
        @fwrite($fp, "{$tag}2 LOGOUT\r\n");
        @fclose($fp);

        return [
            'connected' => true,
            'success' => $authed,
            'message' => $responseMsg ?: ($authed ? 'OK' : 'Autentikasi gagal'),
        ];
    }
}
