<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    // ─── Helper: stream CSV dengan BOM UTF-8 ─────────────────

    private function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM agar Excel baca UTF-8 dengan benar
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─── Export Pasien ────────────────────────────────────────

    public function patients(): StreamedResponse
    {
        $patients = Patient::with('guardian')
            ->orderBy('name')
            ->get();

        $headers = ['No', 'No. Rekam Medis', 'Nama Pasien', 'NIK', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Orang Tua/Wali', 'Status'];

        $rows = $patients->map(fn ($p, $i) => [
            $i + 1,
            $p->medical_record_number,
            $p->name,
            $p->nik ?? '-',
            $p->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            $p->birth_place ?? '-',
            $p->birth_date?->format('d/m/Y') ?? '-',
            $p->address ?? '-',
            $p->guardian?->name ?? '-',
            $p->is_active ? 'Aktif' : 'Nonaktif',
        ]);

        return $this->csv('pasien_' . now()->format('Ymd_His') . '.csv', $headers, $rows);
    }

    // ─── Export Pendaftaran ───────────────────────────────────

    public function registrations(Request $request): StreamedResponse
    {
        $query = Registration::with(['patient', 'therapyType', 'schedule.therapist', 'schedule.doctor', 'schedule.psychologist'])
            ->when($request->from, fn ($q) => $q->whereDate('registration_date', '>=', $request->from))
            ->when($request->to,   fn ($q) => $q->whereDate('registration_date', '<=', $request->to))
            ->orderBy('registration_date', 'desc');

        $headers = ['No', 'Tgl Pendaftaran', 'Nama Pasien', 'No. Rekam Medis', 'Jenis Layanan', 'Tenaga Profesional', 'Tgl Jadwal', 'Jam', 'Status'];

        $rows = $query->get()->map(fn ($r, $i) => [
            $i + 1,
            $r->registration_date?->format('d/m/Y'),
            $r->patient?->name ?? '-',
            $r->patient?->medical_record_number ?? '-',
            $r->therapyType?->name ?? '-',
            $this->professionalName($r->schedule),
            $r->schedule?->schedule_date ?? '-',
            $r->schedule ? $r->schedule->start_time . '–' . $r->schedule->end_time : '-',
            ucfirst($r->status),
        ]);

        return $this->csv('pendaftaran_' . now()->format('Ymd_His') . '.csv', $headers, $rows);
    }

    // ─── Export Pembayaran ────────────────────────────────────

    public function payments(Request $request): StreamedResponse
    {
        $query = Payment::with(['patient', 'schedule.therapyType'])
            ->when($request->from, fn ($q) => $q->whereDate('payment_date', '>=', $request->from))
            ->when($request->to,   fn ($q) => $q->whereDate('payment_date', '<=', $request->to))
            ->orderBy('payment_date', 'desc');

        $headers = ['No', 'Kode Pembayaran', 'Tgl Pembayaran', 'Nama Pasien', 'Jenis Layanan', 'Metode', 'Jumlah (Rp)', 'Status'];

        $rows = $query->get()->map(fn ($p, $i) => [
            $i + 1,
            $p->payment_code,
            $p->payment_date?->format('d/m/Y'),
            $p->patient?->name ?? '-',
            $p->schedule?->therapyType?->name ?? '-',
            $p->payment_method ?? '-',
            number_format($p->amount, 0, ',', '.'),
            $p->status === 'paid' ? 'Lunas' : 'Belum Lunas',
        ]);

        return $this->csv('pembayaran_' . now()->format('Ymd_His') . '.csv', $headers, $rows);
    }

    // ─── Export Jadwal ────────────────────────────────────────

    public function schedules(Request $request): StreamedResponse
    {
        $query = Schedule::with(['patient', 'therapist', 'doctor', 'psychologist', 'therapyType'])
            ->when($request->from, fn ($q) => $q->whereDate('schedule_date', '>=', $request->from))
            ->when($request->to,   fn ($q) => $q->whereDate('schedule_date', '<=', $request->to))
            ->orderBy('schedule_date', 'desc')
            ->orderBy('start_time');

        $headers = ['No', 'Tanggal', 'Jam Mulai', 'Jam Selesai', 'Nama Pasien', 'Jenis Layanan', 'Tenaga Profesional', 'Tipe Profesional', 'Status'];

        $rows = $query->get()->map(fn ($s, $i) => [
            $i + 1,
            $s->schedule_date,
            $s->start_time,
            $s->end_time,
            $s->patient?->name ?? '(Slot Kosong)',
            $s->therapyType?->name ?? '-',
            $this->professionalName($s),
            ucfirst($s->professional_type ?? '-'),
            match ($s->status) {
                'available'  => 'Tersedia',
                'scheduled'  => 'Dijadwalkan',
                'done'       => 'Selesai',
                'cancelled'  => 'Dibatalkan',
                default      => $s->status,
            },
        ]);

        return $this->csv('jadwal_' . now()->format('Ymd_His') . '.csv', $headers, $rows);
    }

    // ─── Export Laporan Ringkasan ─────────────────────────────

    public function report(Request $request): StreamedResponse
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->endOfMonth()->toDateString();

        $payments = Payment::where('status', 'paid')
            ->whereBetween('payment_date', [$from, $to])
            ->with(['patient', 'schedule.therapyType'])
            ->orderBy('payment_date', 'desc')
            ->get();

        $registrations = Registration::whereBetween('registration_date', [$from, $to])
            ->with(['patient', 'therapyType'])
            ->orderBy('registration_date', 'desc')
            ->get();

        $handle = null;

        return response()->streamDownload(function () use ($from, $to, $payments, $registrations) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            // Ringkasan
            fputcsv($handle, ['LAPORAN RINGKASAN KLINIK'], ';');
            fputcsv($handle, ['Periode', $from . ' s/d ' . $to], ';');
            fputcsv($handle, ['Dicetak', now()->format('d/m/Y H:i')], ';');
            fputcsv($handle, [], ';');

            // Statistik
            fputcsv($handle, ['STATISTIK PERIODE'], ';');
            fputcsv($handle, ['Total Pendaftaran', $registrations->count()], ';');
            fputcsv($handle, ['Pendaftaran Disetujui', $registrations->where('status', 'approved')->count()], ';');
            fputcsv($handle, ['Pendaftaran Pending', $registrations->where('status', 'pending')->count()], ';');
            fputcsv($handle, ['Total Pembayaran (Lunas)', 'Rp ' . number_format($payments->sum('amount'), 0, ',', '.')], ';');
            fputcsv($handle, [], ';');

            // Detail Pembayaran
            fputcsv($handle, ['DETAIL PEMBAYARAN LUNAS'], ';');
            fputcsv($handle, ['No', 'Kode', 'Tgl Bayar', 'Nama Pasien', 'Layanan', 'Jumlah (Rp)'], ';');
            foreach ($payments as $i => $p) {
                fputcsv($handle, [
                    $i + 1,
                    $p->payment_code,
                    $p->payment_date?->format('d/m/Y'),
                    $p->patient?->name ?? '-',
                    $p->schedule?->therapyType?->name ?? '-',
                    number_format($p->amount, 0, ',', '.'),
                ], ';');
            }

            fclose($handle);
        }, 'laporan_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }

    // ─── Helper ───────────────────────────────────────────────

    private function professionalName(?Schedule $schedule): string
    {
        if (!$schedule) return '-';
        if ($schedule->therapist)    return $schedule->therapist->name;
        if ($schedule->doctor)       return 'Dr. ' . $schedule->doctor->name;
        if ($schedule->psychologist) return 'Psi. ' . $schedule->psychologist->nama;
        return '-';
    }
}
