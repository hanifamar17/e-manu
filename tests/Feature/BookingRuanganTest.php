<?php

namespace Tests\Feature;

use App\Mail\BookingStatusMail;
use App\Models\BookingRuangan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

// BELUM DIJALANKAN (tidak ada composer di sandbox). Jalankan: php artisan test --filter=BookingRuanganTest
class BookingRuanganTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $o = []): array
    {
        return array_merge([
            'nama_pemesan' => 'Budi', 'email' => 'budi@example.com', 'eksternal' => '0',
            'nama_kegiatan' => 'Workshop', 'tanggal' => Carbon::now('Asia/Jakarta')->addDays(3)->toDateString(),
            'waktu_mulai' => '09:00', 'waktu_selesai' => '11:00',
        ], $o);
    }

    public function test_submit_creates_pending(): void
    {
        $this->post(route('booking_ruangan.booking_store'), $this->payload())->assertRedirect();
        $this->assertDatabaseHas('booking_ruangan', ['nama_pemesan' => 'Budi', 'status' => 'pending']);
    }

    public function test_h_min_1_enforced(): void
    {
        $this->post(route('booking_ruangan.booking_store'), $this->payload(['tanggal' => Carbon::now('Asia/Jakarta')->toDateString()]))
            ->assertSessionHasErrors('tanggal');
    }

    public function test_overlap_with_approved_rejected_but_adjacent_ok(): void
    {
        $p = $this->payload();
        BookingRuangan::create(['nama_pemesan' => 'A', 'email' => 'a@x.id', 'nama_kegiatan' => 'K', 'tanggal' => $p['tanggal'],
            'waktu_mulai' => '09:00:00', 'waktu_selesai' => '11:00:00', 'status' => 'disetujui']);

        $this->post(route('booking_ruangan.booking_store'), $this->payload(['waktu_mulai' => '10:00', 'waktu_selesai' => '12:00']))
            ->assertSessionHasErrors('waktu_mulai');
        $this->post(route('booking_ruangan.booking_store'), $this->payload(['waktu_mulai' => '11:00', 'waktu_selesai' => '12:00']))
            ->assertSessionHasNoErrors();
    }

    public function test_only_admin_can_approve_and_email_sent(): void
    {
        Mail::fake();
        $b = BookingRuangan::create(['nama_pemesan' => 'A', 'email' => 'a@x.id', 'nama_kegiatan' => 'K', 'tanggal' => $this->payload()['tanggal'],
            'waktu_mulai' => '09:00:00', 'waktu_selesai' => '11:00:00']);

        $this->post(route('admin.booking.setujui', $b))->assertRedirect(route('login'));

        $guru = User::factory()->create(['role' => 'guru']);
        $this->actingAs($guru)->post(route('admin.booking.setujui', $b))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.booking.setujui', $b))->assertSessionHas('status');
        $this->assertSame('disetujui', $b->fresh()->status);
        Mail::assertSent(BookingStatusMail::class);
    }

    public function test_public_calendar_hides_email_and_pending(): void
    {
        BookingRuangan::create(['nama_pemesan' => 'A', 'email' => 'rahasia@x.id', 'nama_kegiatan' => 'K', 'tanggal' => $this->payload()['tanggal'],
            'waktu_mulai' => '09:00:00', 'waktu_selesai' => '11:00:00']);
        $this->get(route('booking_ruangan.jadwal_penggunaan'))->assertOk()->assertDontSee('rahasia@x.id');
    }
}
