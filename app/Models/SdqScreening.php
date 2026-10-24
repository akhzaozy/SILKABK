<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SdqScreening extends Model
{
    protected $fillable = [
        'patient_id', 'user_id', 'answers',
        'emotional_score', 'conduct_score', 'hyperactivity_score',
        'peer_score', 'prosocial_score', 'total_score',
        'interpretation', 'notes',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    // ─── Subscale definitions ─────────────────────────────────────
    // items are 1-indexed question numbers; reversed items scored as 2 - value

    public const SUBSCALES = [
        'emotional'     => ['items' => [3, 8, 13, 16, 24], 'reversed' => []],
        'conduct'       => ['items' => [5, 7, 12, 18, 22], 'reversed' => [7]],
        'hyperactivity' => ['items' => [2, 10, 15, 21, 25], 'reversed' => [21, 25]],
        'peer'          => ['items' => [6, 11, 14, 19, 23], 'reversed' => [11, 14]],
        'prosocial'     => ['items' => [1, 4, 9, 17, 20],  'reversed' => []],
    ];

    public const QUESTIONS = [
        1  => 'Mau mempertimbangkan perasaan orang lain',
        2  => 'Gelisah, terlalu aktif, tidak dapat diam dalam waktu lama',
        3  => 'Sering mengeluh sakit kepala, sakit perut, atau rasa mual',
        4  => 'Mau berbagi dengan anak-anak lain (mainan, makanan, pensil, dsb.)',
        5  => 'Sering marah dan tidak dapat mengendalikan amarah',
        6  => 'Agak menyendiri, lebih suka bermain sendiri',
        7  => 'Pada umumnya patuh, biasanya melakukan apa yang diminta orang dewasa',
        8  => 'Banyak kekhawatiran, sering terlihat khawatir',
        9  => 'Suka menolong jika seseorang terluka, kecewa, atau merasa sakit',
        10 => 'Tidak dapat duduk tenang, sering menggeliat dan gelisah',
        11 => 'Memiliki setidaknya satu teman baik',
        12 => 'Sering berkelahi atau mengintimidasi anak-anak lain',
        13 => 'Sering merasa tidak bahagia, sedih, atau menangis',
        14 => 'Pada umumnya disukai oleh anak-anak lain',
        15 => 'Mudah teralihkan perhatiannya, sulit berkonsentrasi',
        16 => 'Gugup atau sulit berpisah dengan orang tua/pengasuh dalam situasi baru, mudah kehilangan kepercayaan diri',
        17 => 'Baik hati dan penuh perhatian terhadap anak-anak yang lebih muda',
        18 => 'Sering berbohong atau berbuat curang',
        19 => 'Diganggu, diejek, atau diintimidasi oleh anak-anak lain',
        20 => 'Sering menawarkan diri untuk membantu orang lain (orang tua, guru, anak-anak lain)',
        21 => 'Mau berpikir dahulu sebelum bertindak',
        22 => 'Mencuri dari rumah, sekolah, atau tempat lain',
        23 => 'Bergaul lebih baik dengan orang dewasa daripada dengan anak-anak seusianya',
        24 => 'Banyak rasa takut, mudah merasa takut',
        25 => 'Mampu menyelesaikan tugas hingga selesai, memiliki perhatian yang baik',
    ];

    // ─── Scoring ──────────────────────────────────────────────────

    public static function computeScores(array $answers): array
    {
        $scores = [];
        foreach (self::SUBSCALES as $name => $cfg) {
            $score = 0;
            foreach ($cfg['items'] as $q) {
                $val = (int) ($answers[$q - 1] ?? 0);
                $score += in_array($q, $cfg['reversed']) ? (2 - $val) : $val;
            }
            $scores[$name] = $score;
        }
        $scores['total'] = $scores['emotional'] + $scores['conduct']
            + $scores['hyperactivity'] + $scores['peer'];

        return $scores;
    }

    public static function interpret(int $total): string
    {
        if ($total <= 15) return 'normal';
        if ($total <= 19) return 'borderline';
        return 'abnormal';
    }

    // ─── Relationships ────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
