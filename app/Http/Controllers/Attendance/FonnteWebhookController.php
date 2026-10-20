<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Kehadiran;
use App\Models\User;
use App\Services\FingerprintReminderNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FonnteWebhookController extends Controller
{
    public function handle(Request $request, FingerprintReminderNotifier $reminderNotifier)
    {
        $message = (string) $request->input('message', '');
        $sender  = trim((string) $request->input('sender', ''));
        $allowedGroup = trim((string) config('services.fonnte.absent_group_target'));

        // 1. Hanya proses pesan dari grup FONNTE_ABSENT_GROUP_TARGET
        if ($sender === '' || $allowedGroup === '' || $sender !== $allowedGroup) {
            return response()->json(['ok' => true, 'ignored' => true, 'reason' => 'unauthorized_sender']);
        }

        if (trim($message) === '') {
            return response()->json(['ok' => true, 'ignored' => true, 'reason' => 'empty_message']);
        }

        // 2. Hanya proses jika pesan merupakan format balasan list jamaah belum absen
        // (mengandung judul "Daftar Jamaah Belum Absen" atau header section "*Laki-laki (" / "*Perempuan (")
        $isAttendanceListFormat = str_contains($message, 'Daftar Jamaah Belum Absen')
            || (bool) preg_match('/\*(Jamaah\s+)?(Laki-laki|Perempuan)\s*\(\d+\)\s*:\*/iu', $message);

        if (!$isAttendanceListFormat) {
            return response()->json(['ok' => true, 'ignored' => true, 'reason' => 'not_attendance_list_format']);
        }

        // 3. Cek apakah pesan memiliki tanda ✅ / ✔️ / ☑ atau 🙏 pada baris bernomor
        if (!preg_match('/^\s*\d+[\.\)\-]\s+.*(✅|✔️|✔|☑️|☑|🙏)/mu', $message)) {
            return response()->json(['ok' => true, 'ignored' => true, 'reason' => 'no_marked_numbered_lines']);
        }

        // Cek apakah hari ini ada kegiatan aktif
        [$isLibur, $alasan] = $reminderNotifier->checkLiburHariIni();
        if ($isLibur) {
            return response()->json([
                'ok'      => true,
                'skipped' => true,
                'reason'  => $alasan,
            ]);
        }

        $today      = Carbon::now('Asia/Jakarta');
        $dateStr    = $today->toDateString();
        $waktuHadir = $today->copy()->setTime(17, 50, 0);

        $lines         = preg_split('/\r\n|\r|\n/', $message);
        $currentGender = null;

        $recordedHadir = [];
        $recordedIzin  = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            // Deteksi header kategori gender jika ikut ter-copy
            if (preg_match('/(Jamaah\s+)?Laki-laki\s*(\(\d+\))?\s*\*?\s*:/iu', $trimmed)) {
                $currentGender = 1;
                continue;
            }
            if (preg_match('/(Jamaah\s+)?Perempuan\s*(\(\d+\))?\s*\*?\s*:/iu', $trimmed)) {
                $currentGender = 2;
                continue;
            }

            // Hanya proses baris daftar bernomor (misal: "1. Bpk. Ahmad ✅")
            if (!preg_match('/^\d+[\.\)\-]\s+/u', $trimmed)) {
                continue;
            }

            // Cek apakah baris ini mengandung emoji hadir atau izin
            $hasHadir = (bool) preg_match('/(✅|✔️|✔|☑️|☑)/u', $trimmed);
            $hasIzin  = (bool) preg_match('/🙏/u', $trimmed);

            if (!$hasHadir && !$hasIzin) {
                continue;
            }

            $isIzin = $hasIzin ? 1 : 0;

            // Bersihkan emoji, skin-tone modifiers, variation selectors, dan zero-width joiner
            $clean = preg_replace('/(✅|✔️|✔|☑️|☑|🙏|[\x{1F3FB}-\x{1F3FF}]|\x{FE0F}|\x{200D})/u', '', $trimmed);

            // Hapus nomor urut di awal baris (contoh: "1. ", "12) ")
            $clean = preg_replace('/^\d+[\.\)\-]\s*/u', '', trim($clean));

            // Deteksi panggilan/sapaan untuk membantu akurasi gender dan membersihkan nama
            $inferredGender = $currentGender;
            if (preg_match('/^(Bpk\.?|Bapak|Mas)\s+/iu', $clean)) {
                $inferredGender = $inferredGender ?? 1;
                $clean = preg_replace('/^(Bpk\.?|Bapak|Mas)\s+/iu', '', $clean);
            } elseif (preg_match('/^(Ibu|Bu|Mbak|Mba)\s+/iu', $clean)) {
                $inferredGender = $inferredGender ?? 2;
                $clean = preg_replace('/^(Ibu|Bu|Mbak|Mba)\s+/iu', '', $clean);
            }

            // Bersihkan karakter sisa di ujung nama (seperti tanda kurung, titik dua, strip)
            $cleanName = trim($clean, " \t\n\r\0\x0B-:=.");

            if ($cleanName === '' || mb_strlen($cleanName) < 2) {
                continue;
            }

            $user = $this->findUserByName($cleanName, $inferredGender);

            if (!$user) {
                continue;
            }

            // Cek apakah user sudah punya data kehadiran hari ini
            $existing = Kehadiran::where('user_id', $user->id)
                ->whereDate('local_time', $dateStr)
                ->first();

            if ($existing) {
                // Jika sudah absen dari mesin fingerprint (raw_id > 0), jangan timpa dengan izin manual
                // Namun jika sebelumnya diinput manual/WA (raw_id == 0), izinkan update status is_izin
                if ((int) $existing->raw_id === 0 && (int) $existing->is_izin !== $isIzin) {
                    $existing->update(['is_izin' => $isIzin]);
                }
            } else {
                Kehadiran::create([
                    'user_id'              => $user->id,
                    'event_time'           => $waktuHadir,
                    'local_time'           => $waktuHadir,
                    'method'               => 'wa',
                    'device'               => 'fonnte',
                    'raw_id'               => 0,
                    'is_in_session_window' => 1,
                    'is_izin'              => $isIzin,
                ]);
            }

            $displayName = $this->formatUserDisplayName($user);
            if ($isIzin) {
                $recordedIzin[$user->id] = $displayName;
            } else {
                $recordedHadir[$user->id] = $displayName;
            }
        }

        // Hanya kirim balasan jika ada minimal 1 jamaah valid yang berhasil dicatat
        if (empty($recordedHadir) && empty($recordedIzin)) {
            return response()->json(['ok' => true, 'ignored' => true, 'reason' => 'no_valid_entries']);
        }

        // Susun pesan balasan konfirmasi ke grup FONNTE_ABSENT_GROUP_TARGET
        $replyText   = $this->buildReplyText(array_values($recordedHadir), array_values($recordedIzin));
        $replyResult = $this->sendFonnteReply($allowedGroup, $replyText);

        return response()->json([
            'ok'             => true,
            'recorded_hadir' => array_values($recordedHadir),
            'recorded_izin'  => array_values($recordedIzin),
            'reply'          => $replyResult,
        ]);
    }

    /**
     * Cari user non-admin berdasarkan nama (prioritaskan exact match + gender).
     */
    protected function findUserByName(string $name, ?int $gender = null): ?User
    {
        $baseQuery = User::where('is_admin', 0);

        // 1. Cari exact match (case-insensitive) dengan gender jika diketahui
        if ($gender !== null) {
            $user = (clone $baseQuery)
                ->where('jenis_kelamin', $gender)
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
                ->first();
            if ($user) {
                return $user;
            }
        }

        // 2. Cari exact match tanpa filter gender
        $user = (clone $baseQuery)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->first();
        if ($user) {
            return $user;
        }

        // 3. Fallback LIKE match jika nama sedikit terpotong
        if ($gender !== null) {
            $user = (clone $baseQuery)
                ->where('jenis_kelamin', $gender)
                ->where('name', 'LIKE', $name)
                ->first();
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Format sapaan nama jamaah untuk pesan konfirmasi.
     */
    protected function formatUserDisplayName(User $user): string
    {
        if ($user->is_muda_mudi == 1) {
            return $user->name;
        }
        if ($user->jenis_kelamin == 2) {
            $panggilan = $user->is_usia_nikah == 1 ? 'Mbak' : 'Ibu';
            return "{$panggilan} {$user->name}";
        }
        $panggilan = $user->is_usia_nikah == 1 ? 'Mas' : 'Bpk.';
        return "{$panggilan} {$user->name}";
    }

    /**
     * Susun pesan balasan konfirmasi setelah input presensi via WA diproses.
     */
    protected function buildReplyText(array $hadir, array $izin): string
    {
        $lines = [];
        $lines[] = "🤖 *Konfirmasi Input Presensi via WhatsApp*";

        if (!empty($hadir)) {
            $lines[] = "";
            $lines[] = "✅ *Tercatat Hadir (" . count($hadir) . "):*";
            foreach ($hadir as $idx => $name) {
                $no = $idx + 1;
                $lines[] = "{$no}. {$name}";
            }
        }

        if (!empty($izin)) {
            $lines[] = "";
            $lines[] = "🙏 *Tercatat Izin (" . count($izin) . "):*";
            foreach ($izin as $idx => $name) {
                $no = $idx + 1;
                $lines[] = "{$no}. {$name}";
            }
        }

        $lines[] = "";
        $lines[] = "Alhamdulillah jaza kumullahu khoiro.";

        return implode("\n", $lines);
    }

    /**
     * Kirim balasan ke grup/pengirim via Fonnte API.
     */
    protected function sendFonnteReply(string $target, string $text): array
    {
        $token = (string) config('services.fonnte.token');

        try {
            $res = Http::withHeaders([
                    'Authorization' => $token,
                ])
                ->asForm()
                ->timeout(15)
                ->retry(2, 500)
                ->post('https://api.fonnte.com/send', [
                    'target'      => $target,
                    'message'     => $text,
                    'countryCode' => '62',
                ]);

            $body = rescue(fn() => $res->json(), $res->body(), report: false);
            $statusFonnte = is_array($body) ? ($body['status'] ?? false) : false;

            return [
                'ok'     => $res->successful() && $statusFonnte,
                'status' => $res->status(),
                'body'   => $body,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 0, 'body' => ['error' => $e->getMessage()]];
        }
    }
}
