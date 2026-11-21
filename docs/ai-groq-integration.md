# Integrasi Groq AI Question Generator

## Status implementasi

CBT.EDUKA menggunakan `QuestionGenerationProvider` sebagai boundary. `GroqQuestionGenerationProvider` menangani HTTP ke Groq, sedangkan `AiQuestionValidator` memeriksa response sebelum draft dikirim ke UI. Hasil generation tidak disimpan otomatis.

Alurnya:

1. Guru mengirim parameter generation melalui `POST /teacher/ai-generator`.
2. Request divalidasi dan dibatasi oleh authorization, ukuran materi, jumlah soal, serta rate limit.
3. Provider mengirim chat completion ke Groq melalui endpoint OpenAI-compatible.
4. Structured Outputs digunakan bila diaktifkan. Model yang mendukung strict JSON Schema memakai `strict: true`; jika provider menolak format tersebut, adapter melakukan satu fallback ke JSON Object Mode dan tetap memvalidasi response di server.
5. Draft tampil di halaman generator untuk diedit, dipilih, atau dihapus.
6. Hanya draft yang dipilih yang dikirim ke `POST /teacher/ai-generator/questions` dan disimpan sebagai `draft` dalam transaction.

## Konfigurasi environment

Tambahkan nilai berikut pada environment server. Jangan menyimpan API key ke source code, browser, screenshot, atau repository.

```env
AI_ENABLED=true
AI_PROVIDER=groq
AI_API_KEY=your-groq-api-key
AI_MODEL=openai/gpt-oss-20b
AI_BASE_URL=https://api.groq.com/openai/v1
AI_TIMEOUT=60
AI_CONNECT_TIMEOUT=10
AI_MAX_TOKENS=8192
AI_MAX_QUESTIONS=20
AI_MAX_MATERIAL_CHARS=10000
AI_RATE_LIMIT=10
AI_STRUCTURED_OUTPUTS=true
```

Untuk menonaktifkan fitur tanpa menghapus konfigurasi lain:

```env
AI_ENABLED=false
```

Setelah mengubah environment pada deployment yang menggunakan cached configuration:

```bash
php artisan config:clear
php artisan config:cache
```

## Model dan structured output

Default model adalah `openai/gpt-oss-20b`. Model dapat diganti melalui `AI_MODEL`, tetapi kemampuan Structured Outputs harus diverifikasi kembali. Provider tidak mengganti model secara diam-diam.

Adapter mengirim schema tertutup (`additionalProperties: false`) dengan field wajib untuk memenuhi persyaratan strict mode. Untuk model yang tidak termasuk daftar strict model pada `config/cbt.php`, request menggunakan JSON Schema best-effort; apabila format schema ditolak, adapter melakukan fallback terbatas ke JSON Object Mode. Kedua jalur tetap melewati validasi aplikasi.

## Status provider

| Status | Arti |
|---|---|
| `NOT_CONFIGURED` | Provider bukan Groq atau URL, model, atau API key belum lengkap. |
| `DISABLED` | `AI_ENABLED=false`. |
| `READY` | Konfigurasi valid dan siap digunakan; konektivitas live diverifikasi melalui smoke test atau generation. |
| `UNAVAILABLE` | Koneksi timeout atau layanan sementara tidak tersedia. |
| `RATE_LIMITED` | Provider atau endpoint aplikasi mengembalikan batas rate. |
| `AUTHENTICATION_FAILED` | API key tidak valid atau tidak memiliki akses. |
| `PROVIDER_ERROR` | Error provider, model, atau output yang tidak lolos validasi. |

## Dukungan tipe soal

Generator memvalidasi schema yang sama dengan question bank untuk:

- `multiple_choice`: minimal dua opsi dan tepat satu jawaban benar.
- `multiple_select`: minimal dua opsi dan minimal satu jawaban benar.
- `true_false`: `definition.correct` berupa boolean.
- `short_answer`: `definition.accepted_answers`.
- `essay`: `definition.rubric` opsional; penilaian siswa tetap manual.
- `matching`: pasangan `left` dan `right` tanpa duplikasi.
- `ordering`: daftar `definition.correct_order` tanpa item duplikat.
- `fill_blank`: daftar jawaban untuk setiap blank.

Validator juga menolak prompt kosong, field asing, pertanyaan duplikat, opsi duplikat, panjang teks berlebihan, dan jawaban yang tidak konsisten dengan tipe soal.

## Smoke test live

Perintah ini memeriksa endpoint `/models` memakai konfigurasi yang sama dengan generator. Perintah tidak membuat atau menyimpan soal:

```bash
php artisan ai:smoke-test
```

Jika API key belum tersedia, hasil yang benar adalah `NOT_CONFIGURED`; jangan mengganti hasil tersebut dengan data demo. Smoke test live hanya dapat dinyatakan berhasil setelah server menerima request nyata dari environment yang memiliki API key.

## Observability dan keamanan

Audit log menyimpan metadata minimum: user, provider, model, status, jumlah soal, durasi, dan token usage bila dikirim provider. Isi prompt, isi jawaban, API key, dan raw provider response tidak dicatat.

Endpoint generation dibatasi middleware `throttle:10,1`, request memvalidasi role guru/admin, dan penyimpanan draft memeriksa ownership institusi mata pelajaran. Soal hanya disimpan setelah review dan submission eksplisit guru.

## Troubleshooting

- `AI dinonaktifkan`: set `AI_ENABLED=true`, lalu clear/cache config.
- `AI belum dikonfigurasi`: periksa `AI_PROVIDER`, `AI_API_KEY`, `AI_MODEL`, dan `AI_BASE_URL`.
- `API key AI tidak valid`: buat key baru di Groq dan pasang hanya di environment server.
- `Batas penggunaan AI tercapai`: tunggu reset rate limit dan kurangi frekuensi atau jumlah soal per request.
- `Hasil AI belum memenuhi format`: coba lagi dengan materi lebih ringkas; response yang gagal tidak disimpan.
- Model tidak tersedia: gunakan model yang aktif di akun Groq dan periksa kembali dukungan structured output.

## Verifikasi otomatis

Test provider memakai Laravel HTTP fake sehingga tidak pernah memanggil Groq live. Cakupan meliputi konfigurasi, response sukses, error authentication, rate limit, provider error, timeout, JSON invalid, seluruh delapan tipe, authorization, preview non-persisten, batch persistence, dan rollback.

```bash
php artisan test --compact tests/Unit/AiQuestionValidatorTest.php tests/Feature/GroqQuestionGenerationProviderTest.php tests/Feature/AiGeneratorTest.php tests/Feature/AiQuestionPersistenceTest.php
```

Test tersebut tidak membuktikan koneksi live. Bukti live hanya berasal dari `php artisan ai:smoke-test` atau generation nyata setelah `AI_API_KEY` dikonfigurasi.
