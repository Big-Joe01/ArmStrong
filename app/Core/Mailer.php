<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight SMTP mailer (socket based, no external dependency).
 *
 * When mail is disabled or misconfigured the message is written to
 * storage/logs/mail-*.log instead, so a failed email never invalidates a
 * successfully recorded order.
 */
final class Mailer
{
    /**
     * @param array<string,string> $headers
     * @return bool true when the message was accepted (or intentionally logged)
     */
    public static function send(string $to, string $subject, string $htmlBody, string $textBody = '', array $headers = []): bool
    {
        $enabled = (bool) Config::get('mail.enabled', false);
        $from    = (string) Config::get('mail.from_email', 'no-reply@localhost');

        if (!$enabled || $from === '') {
            self::logMessage($to, $subject, $htmlBody);
            return true;
        }

        try {
            return self::smtpSend($to, $subject, $htmlBody, $textBody, $headers);
        } catch (\Throwable $e) {
            Logger::error('Mail send failed: ' . $e->getMessage(), ['to' => $to, 'subject' => $subject]);
            self::logMessage($to, $subject, $htmlBody);
            return false;
        }
    }

    /** @param array<string,string> $headers */
    private static function smtpSend(string $to, string $subject, string $html, string $text, array $headers): bool
    {
        $host = (string) Config::get('mail.host');
        $port = (int) Config::get('mail.port', 587);
        $user = (string) Config::get('mail.username');
        $pass = (string) Config::get('mail.password');
        $enc  = (string) Config::get('mail.encryption', 'tls');
        $from = (string) Config::get('mail.from_email');
        $name = (string) Config::get('mail.from_name', 'Website');

        $transport = $enc === 'ssl' ? 'ssl://' : '';
        $socket    = @stream_socket_client($transport . $host . ':' . $port, $errno, $errstr, 20);
        if (!$socket) {
            throw new \RuntimeException(sprintf('SMTP connect failed: %s (%d)', $errstr, $errno));
        }

        $read = static function () use ($socket): string {
            $data = '';
            while ($line = fgets($socket, 515)) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $data;
        };
        $write = static function (string $cmd) use ($socket): void {
            fwrite($socket, $cmd . "\r\n");
        };
        $expect = static function (string $response, string $code) use ($read): void {
            if (!str_starts_with($response, $code)) {
                throw new \RuntimeException('SMTP unexpected response: ' . trim($response));
            }
        };

        $expect($read(), '220');
        $write('EHLO ' . (parse_url(Config::baseUrl(), PHP_URL_HOST) ?: 'localhost'));
        $read();

        if ($enc === 'tls') {
            $write('STARTTLS');
            $expect($read(), '220');
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException('Unable to start TLS.');
            }
            $write('EHLO ' . (parse_url(Config::baseUrl(), PHP_URL_HOST) ?: 'localhost'));
            $read();
        }

        if ($user !== '') {
            $write('AUTH LOGIN');
            $expect($read(), '334');
            $write(base64_encode($user));
            $expect($read(), '334');
            $write(base64_encode($pass));
            $expect($read(), '235');
        }

        $write('MAIL FROM:<' . $from . '>');
        $expect($read(), '250');
        $write('RCPT TO:<' . $to . '>');
        $expect($read(), '250');
        $write('DATA');
        $expect($read(), '354');

        $boundary = 'arm_' . bin2hex(random_bytes(8));
        $body     = self::buildMime($from, $name, $to, $subject, $html, $text, $boundary, $headers);
        fwrite($socket, $body . "\r\n.\r\n");
        $expect($read(), '250');
        $write('QUIT');
        fclose($socket);
        return true;
    }

    /** @param array<string,string> $headers */
    private static function buildMime(
        string $from,
        string $fromName,
        string $to,
        string $subject,
        string $html,
        string $text,
        string $boundary,
        array $headers
    ): string {
        $lines   = [];
        $lines[] = 'From: ' . self::encodeName($fromName) . ' <' . $from . '>';
        $lines[] = 'To: <' . $to . '>';
        $lines[] = 'Subject: ' . self::encodeHeader($subject);
        $lines[] = 'MIME-Version: 1.0';
        foreach ($headers as $k => $v) {
            $lines[] = $k . ': ' . $v;
        }
        $lines[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
        $lines[] = '';
        $lines[] = '--' . $boundary;
        $lines[] = 'Content-Type: text/plain; charset=UTF-8';
        $lines[] = 'Content-Transfer-Encoding: 8bit';
        $lines[] = '';
        $lines[] = $text !== '' ? $text : strip_tags($html);
        $lines[] = '';
        $lines[] = '--' . $boundary;
        $lines[] = 'Content-Type: text/html; charset=UTF-8';
        $lines[] = 'Content-Transfer-Encoding: 8bit';
        $lines[] = '';
        $lines[] = $html;
        $lines[] = '';
        $lines[] = '--' . $boundary . '--';

        // Dot-stuffing per RFC 5321.
        return implode("\r\n", array_map(static fn ($l) => str_starts_with($l, '.') ? '.' . $l : $l, $lines));
    }

    private static function encodeHeader(string $value): string
    {
        return preg_match('/[^\x20-\x7E]/', $value) ? '=?UTF-8?B?' . base64_encode($value) . '?=' : $value;
    }

    private static function encodeName(string $value): string
    {
        return preg_match('/[^\x20-\x7E]/', $value) ? '=?UTF-8?B?' . base64_encode($value) . '?=' : '"' . $value . '"';
    }

    private static function logMessage(string $to, string $subject, string $html): void
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $entry = sprintf(
            "==== %s ====\nTo: %s\nSubject: %s\n\n%s\n\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $html
        );
        @file_put_contents($dir . '/mail-' . date('Y-m-d') . '.log', $entry, FILE_APPEND | LOCK_EX);
    }
}
