# Catatan Refactoring SI-LKABK

Dokumen ini mencatat seluruh rencana dan hasil refactoring per iterasi. Prinsip utama:
mengubah fungsi panjang menjadi unit yang lebih kecil, menghilangkan duplikasi kode,
dan mengekstrak logika yang digunakan di banyak tempat menjadi satu sumber kebenaran.

---

## Iterasi 1 — Auth, Akun, & Pasien

### R-01 · Rate Limiting Manual → Middleware Throttle

**File:** [AuthController.php](app/Http/Controllers/AuthController.php)
**Baris sebelum:** 35 baris (lines 25–74)

Implementasi rate limiting saat ini menggunakan cache secara manual dengan dua kunci
terpisah (`login.{ip}` dan `login.{ip}.timer`). Laravel sudah menyediakan mekanisme
throttle bawaan melalui middleware.

**Sebelum:**
```php
$key        = 'login.' . $request->ip();
$maxAttempts = 5;
$decayMinutes = 1;

if (app('cache')->has($key) && app('cache')->get($key) >= $maxAttempts) {
    $seconds = app('cache')->get($key . '.timer') - time();
    return back()->withErrors([...]);
}
// ... validasi ...
if (! Auth::attempt($credentials, $remember)) {
    $attempts = app('cache')->get($key, 0) + 1;
    app('cache')->put($key, $attempts, now()->addMinutes($decayMinutes));
    if ($attempts === 1) {
        app('cache')->put($key . '.timer', time() + ($decayMinutes * 60), ...);
    }
    return back()->withErrors([...]);
}
// ...
app('cache')->forget($key);
app('cache')->forget($key . '.timer');
```

**Sesudah:** Pindahkan ke route middleware dan bersihkan controller.

```php
// routes/web.php
Route::middleware('throttle:5,1')->post('/login', [AuthController::class, 'login']);

// AuthController::login() — 10 baris saja
public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ], [...]);

    if (! Auth::attempt($credentials, $request->boolean('remember'))) {
        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    if (! Auth::user()->is_active) {
        Auth::logout();
        return back()->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi administrator.'])->onlyInput('email');
    }

    $request->session()->regenerate();
    return redirect()->intended(route('dashboard'));
}
```

---

### R-02 · Duplikasi Baris di Login.vue

**File:** [Login.vue](resources/js/pages/Auth/Login.vue)
**Masalah:** `processing` adalah `ref` manual padahal `useForm` sudah menyediakan `form.processing`.

**Sebelum:**
```js
const processing = ref(false)

const submit = () => {
  processing.value = true
  form.post('/login', {
    onFinish: () => { processing.value = false }
  })
}
```

**Sesudah:**
```js
// hapus ref processing, gunakan form.processing langsung
const submit = () => form.post('/login')
```

Template: ganti `processing` → `form.processing`.

---

### R-03 · Nomor Rekam Medis (MRN) — Logika Terpusat di Model

**File saat ini:** `PatientController.php` & `RegistrationController.php` (duplikasi)

Logika pembuatan MRN harus ada satu tempat. Pindahkan ke `Patient::boot()`.

```php
// app/Models/Patient.php
protected static function boot(): void
{
    parent::boot();

    static::creating(function (self $model) {
        if (empty($model->medical_record_number)) {
            $model->medical_record_number = sprintf(
                'RM-%s-%04d',
                now()->format('Ymd'),
                self::max('id') + 1,
            );
        }
    });
}
```

Hapus baris pembuatan MRN dari kedua controller.

---

### R-04 · Scope Pencarian Pasien

**File:** `PatientController.php`, `MedicalRecordController.php`, `RegistrationController.php`
**Masalah:** Query `where name ilike` + `orWhere medical_record_number ilike` diulang di ≥ 3 tempat.

```php
// app/Models/Patient.php — tambahkan scope:
public function scopeSearch($query, ?string $term): void
{
    if (!$term) return;
    $query->where(fn ($q) =>
        $q->where('name', 'ilike', "%{$term}%")
          ->orWhere('medical_record_number', 'ilike', "%{$term}%")
    );
}

// Penggunaan di controller (1 baris):
Patient::search($request->search)->...
```

---

## Iterasi 2 — Jadwal & Pembayaran

### R-05 · Validasi Professional ID — Extract Helper

**File:** [ScheduleController.php](app/Http/Controllers/ScheduleController.php)
**Masalah:** Tiga blok `if` yang identik diulang di `generateWeekSlots()`, `store()`, dan `update()`.

**Sebelum (diulang 3×):**
```php
if ($validated['professional_type'] === 'terapis' && empty($validated['therapist_id'])) {
    return back()->withErrors(['therapist_id' => 'Terapis harus dipilih.']);
}
if ($validated['professional_type'] === 'dokter' && empty($validated['doctor_id'])) {
    return back()->withErrors(['doctor_id' => 'Dokter harus dipilih.']);
}
if ($validated['professional_type'] === 'psikolog' && empty($validated['psychologist_id'])) {
    return back()->withErrors(['psychologist_id' => 'Psikolog harus dipilih.']);
}
```

**Sesudah — 1 method private:**
```php
private const PROFESSIONAL_FIELD = [
    'terapis'  => 'therapist_id',
    'dokter'   => 'doctor_id',
    'psikolog' => 'psychologist_id',
];

private function assertProfessionalSelected(array $validated): void
{
    $field = self::PROFESSIONAL_FIELD[$validated['professional_type']] ?? null;
    if ($field && empty($validated[$field])) {
        $label = ucfirst($validated['professional_type']);
        abort(back()->withErrors([$field => "{$label} harus dipilih."]));
    }
}
```

Panggil: `$this->assertProfessionalSelected($validated);`

---

### R-06 · Conflict Detection — Extract Method

**File:** [ScheduleController.php](app/Http/Controllers/ScheduleController.php)
**Masalah:** Block `match` untuk mengecek konflik jadwal diulang persis 3× (`generateWeekSlots`, `store`, `update`).

**Sebelum (diulang 3×):**
```php
$conflict = match ($validated['professional_type']) {
    'terapis'  => (clone $conflictQuery)->where('therapist_id',   $validated['therapist_id'])->exists(),
    'dokter'   => (clone $conflictQuery)->where('doctor_id',       $validated['doctor_id'])->exists(),
    'psikolog' => (clone $conflictQuery)->where('psychologist_id', $validated['psychologist_id'])->exists(),
    default    => false,
};
```

**Sesudah — 1 method private:**
```php
private function hasScheduleConflict(
    string  $type,
    array   $validated,
    string  $date,
    string  $start,
    string  $end,
    ?int    $excludeId = null,
): bool {
    $field = self::PROFESSIONAL_FIELD[$type] ?? null;
    if (!$field) return false;

    return Schedule::where('schedule_date', $date)
        ->where('status', '!=', 'cancelled')
        ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
        ->where(fn ($q) => $q->where('start_time', '<', $end)->where('end_time', '>', $start))
        ->where($field, $validated[$field])
        ->exists();
}
```

Panggil dari `store()`:
```php
if ($this->hasScheduleConflict($type, $validated, $date, $start, $end)) {
    return back()->withErrors(['start_time' => 'Tenaga profesional sudah memiliki jadwal pada waktu tersebut.']);
}
```

---

### R-07 · generateWeekSlots — Pecah Jadi Sub-method

**File:** [ScheduleController.php](app/Http/Controllers/ScheduleController.php)
**Baris sebelum:** 72 baris (lines 62–134)

```php
// Setelah dipecah:
public function generateWeekSlots(Request $request)
{
    $validated = $request->validate([...]);
    $this->assertProfessionalSelected($validated);

    $monday  = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY);
    [$created, $skipped] = $this->createSlotsForWeek($monday, $validated);

    return redirect()->route('schedules.index')
        ->with('success', $this->slotSummaryMessage($created, $skipped, $monday));
}

private function createSlotsForWeek(Carbon $monday, array $validated): array
{
    $created = $skipped = 0;
    foreach (self::WEEK_TEMPLATE as $offset => $slots) {
        $date = $monday->copy()->addDays($offset);
        foreach ($slots as [$start, $end]) {
            if ($this->hasScheduleConflict($validated['professional_type'], $validated, $date->toDateString(), $start, $end)) {
                $skipped++;
                continue;
            }
            Schedule::create([...$validated, 'schedule_date' => $date->toDateString(),
                'start_time' => $start, 'end_time' => $end, 'status' => 'available',
                'created_by' => Auth::id()]);
            $created++;
        }
    }
    return [$created, $skipped];
}

private function slotSummaryMessage(int $created, int $skipped, Carbon $monday): string
{
    $msg = "Berhasil membuat {$created} slot untuk minggu " . $monday->format('d/m/Y');
    return $skipped > 0 ? $msg . " ({$skipped} dilewati karena konflik)." : $msg . '.';
}
```

---

## Iterasi 3 — Rekam Medis & KPSP

### R-08 · KPSP Section → Komponen Vue Terpisah

**File yang terdampak (HTML identik di 4 file):**
- [Doctor/MedicalRecords/Create.vue](resources/js/pages/Doctor/MedicalRecords/Create.vue)
- [Doctor/MedicalRecords/Edit.vue](resources/js/pages/Doctor/MedicalRecords/Edit.vue)
- [Psychologist/MedicalRecords/Create.vue](resources/js/pages/Psychologist/MedicalRecords/Create.vue)
- [Psychologist/MedicalRecords/Edit.vue](resources/js/pages/Psychologist/MedicalRecords/Edit.vue)

Buat file baru: `resources/js/components/KpspSection.vue`

```vue
<template>
  <div class="bg-purple-50 border border-purple-200 rounded-2xl p-6 space-y-4">
    <div class="flex items-center gap-2 pb-3 border-b border-purple-200">
      <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <h3 class="font-semibold text-purple-900">
        Skrining KPSP <span class="text-purple-500 font-normal text-sm">(opsional)</span>
      </h3>
    </div>
    <slot name="description" />

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-purple-800 mb-1.5">Usia Skrining (bulan)</label>
        <input type="number" v-model="kpsp.usia_bulan" min="3" max="72"
          class="w-full px-3 py-2 border border-purple-300 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
          placeholder="mis. 24" />
      </div>
      <div>
        <label class="block text-sm font-medium text-purple-800 mb-1.5">Tanggal Skrining</label>
        <input type="date" v-model="kpsp.tanggal_screening"
          class="w-full px-3 py-2 border border-purple-300 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" />
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-purple-800 mb-1.5">Skor KPSP (0–10)</label>
      <input type="number" v-model="kpsp.skor" min="0" max="10"
        class="w-full px-3 py-2 border border-purple-300 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
        placeholder="Masukkan jumlah jawaban 'Ya'" />
    </div>

    <div>
      <label class="block text-sm font-medium text-purple-800 mb-1.5">Interpretasi</label>
      <div class="px-3 py-2 border border-purple-200 bg-white rounded-xl text-sm font-medium"
        :class="interpretasiClass">
        {{ interpretasi || 'Dihitung otomatis dari skor' }}
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-purple-800 mb-1.5">
        Catatan KPSP <span class="text-purple-400 font-normal">(opsional)</span>
      </label>
      <textarea v-model="kpsp.catatan" rows="2"
        class="w-full px-3 py-2 border border-purple-300 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
        placeholder="Catatan tambahan hasil skrining..."/>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed } from 'vue'

const props = defineProps({
  initial: { type: Object, default: null },
})

const kpsp = reactive({
  usia_bulan:        props.initial?.usia_bulan        ?? '',
  tanggal_screening: props.initial?.tanggal_screening ?? '',
  skor:              props.initial?.skor !== undefined ? props.initial.skor : '',
  catatan:           props.initial?.catatan           ?? '',
})

const interpretasi = computed(() => {
  if (kpsp.skor === '') return ''
  const s = Number(kpsp.skor)
  if (s >= 9) return 'Sesuai'
  if (s >= 7) return 'Meragukan'
  return 'Penyimpangan'
})

const interpretasiClass = computed(() => ({
  'text-green-700':        interpretasi.value === 'Sesuai',
  'text-yellow-700':       interpretasi.value === 'Meragukan',
  'text-red-700':          interpretasi.value === 'Penyimpangan',
  'text-purple-400 italic': !interpretasi.value,
}))

function build() {
  if (kpsp.skor === '') return null
  const obj = { skor: Number(kpsp.skor), interpretasi: interpretasi.value }
  if (kpsp.usia_bulan)        obj.usia_bulan        = Number(kpsp.usia_bulan)
  if (kpsp.tanggal_screening) obj.tanggal_screening = kpsp.tanggal_screening
  if (kpsp.catatan)           obj.catatan           = kpsp.catatan
  return obj
}

defineExpose({ build })
</script>
```

**Cara pemakaian di Create/Edit.vue (kurangi ~50 baris per file):**
```vue
<template>
  <KpspSection ref="kpspRef" :initial="record?.kpsp_result" />
</template>

<script setup>
import { ref } from 'vue'
import KpspSection from '@/components/KpspSection.vue'

const kpspRef = ref(null)

function submit() {
  form.kpsp_result = kpspRef.value?.build() ?? null
  form.post(route(...))
}
</script>
```

---

### R-09 · MedicalRecordController — Duplikasi Doctor vs Psychologist

**File:** [MedicalRecordController.php](app/Http/Controllers/MedicalRecordController.php)
**Masalah:** 7 pasang method (index, create, store, show, edit, update, saveKpsp) untuk
dokter dan psikolog yang isinya hampir identik. Total ~280 baris duplikasi.

Ekstrak authorization dan query builder ke private methods:

```php
// ─── Helper private ────────────────────────────────────────────

private function resolveProfile(string $role): array
{
    $user = Auth::user();
    return match ($role) {
        'dokter'   => ['doctor',       $user->doctor,       'doctor_id',       'Doctor/MedicalRecords/'],
        'psikolog' => ['psychologist', $user->psychologist, 'psychologist_id', 'Psychologist/MedicalRecords/'],
    };
}

private function authorizeRecord(MedicalRecord $record, string $idField, mixed $profile): void
{
    abort_unless(
        Auth::user()->role === 'super_admin' || ($profile && $record->{$idField} === $profile->id),
        403
    );
}

private function baseQuery(string $idField, mixed $profile, ?string $search): Builder
{
    return MedicalRecord::with(['patient', str_replace('_id', '', $idField)])
        ->whereNotNull($idField)
        ->when($profile, fn ($q) => $q->where($idField, $profile->id))
        ->when($search, fn ($q) => $q->whereHas('patient', fn ($sq) =>
            $sq->where('name', 'ilike', "%{$search}%")
               ->orWhere('medical_record_number', 'ilike', "%{$search}%")
        ))
        ->orderBy('visit_date', 'desc');
}
```

Setiap method menjadi ~5 baris:
```php
public function index(Request $request): Response
{
    [, $profile, $idField, $view] = $this->resolveProfile('dokter');
    abort_unless($profile || Auth::user()->role === 'super_admin', 403);
    return Inertia::render($view . 'Index', [
        'records' => $this->baseQuery($idField, $profile, $request->search)->paginate(15)->withQueryString(),
        'filters' => $request->only(['search']),
    ]);
}
```

---

### R-10 · Policy untuk Otorisasi Rekam Medis

**File:** [MedicalRecordController.php](app/Http/Controllers/MedicalRecordController.php)
**Masalah:** `abort_unless(...)` yang sama diulang di `show()`, `edit()`, `update()`, dan `saveKpsp()` untuk kedua aktor (8× total).

Daftarkan policy:
```bash
php artisan make:policy MedicalRecordPolicy --model=MedicalRecord
```

```php
// app/Policies/MedicalRecordPolicy.php
public function view(User $user, MedicalRecord $record): bool
{
    if ($user->role === 'super_admin') return true;
    return ($user->doctor?->id && $record->doctor_id === $user->doctor->id)
        || ($user->psychologist?->id && $record->psychologist_id === $user->psychologist->id);
}

// update, saveKpsp pakai aturan yang sama:
public function update(User $user, MedicalRecord $record): bool
{
    return $this->view($user, $record);
}
```

Di controller, ganti 4 baris menjadi 1:
```php
// Sebelum:
abort_unless(
    $user->role === 'super_admin' || ($doctor && $record->doctor_id === $doctor->id),
    403, 'Anda tidak memiliki akses ke rekam medis ini.'
);

// Sesudah:
$this->authorize('view', $record);
```

---

## Ringkasan Dampak

| Kode | Target | Baris Sebelum | Baris Sesudah | Reduksi |
|:----:|--------|:-------------:|:-------------:|:-------:|
| R-01 | `AuthController::login()` | 56 | 18 | ~68% |
| R-02 | `Login.vue` script | 10 | 5 | ~50% |
| R-03 | MRN generation (2 file) | 4 baris × 2 | 8 baris × 1 | eliminasi duplikasi |
| R-04 | Search scope (3 controller) | 4 baris × 3 | 1 baris × 3 | ~75% per penggunaan |
| R-05 | Professional validation (3×) | 9 baris × 3 | 1 baris × 3 | ~89% |
| R-06 | Conflict detection (3×) | 5 baris × 3 | 1 baris × 3 | ~80% |
| R-07 | `generateWeekSlots()` | 72 | 8 + 3 sub | lebih mudah diuji |
| R-08 | KPSP section (4 file Vue) | ~50 baris × 4 | 1 komponen | ~75% total |
| R-09 | Doctor vs Psikolog methods | ~280 baris duplikasi | ~60 shared | ~79% |
| R-10 | Authorization checks (8×) | 4 baris × 8 | 1 baris × 8 | ~75% |

**Total estimasi pengurangan:** ±350 baris kode duplikasi dihilangkan.

---

## Urutan Pengerjaan yang Disarankan

1. **R-04** — Scope `Patient::search()` (10 menit, dampak luas)
2. **R-05 + R-06** — Helper conflict & professional di `ScheduleController` (20 menit)
3. **R-01** — Login rate limiting ke middleware (15 menit)
4. **R-08** — Komponen `KpspSection.vue` (30 menit, hilangkan ~200 baris Vue)
5. **R-10** — `MedicalRecordPolicy` (20 menit)
6. **R-09** — Refactor doctor/psikolog methods (45 menit, paling kompleks)
7. **R-03 + R-07 + R-02** — Sisanya (masing-masing < 15 menit)
