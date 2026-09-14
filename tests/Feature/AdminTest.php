<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_access_rekap_presensi()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.presensi.rekap'));

        $response->assertStatus(200);
        $response->assertSee('Rekap Presensi Karyawan');
    }

    public function test_admin_can_access_karyawan_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.karyawan.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Karyawan');
    }

    public function test_admin_can_access_karyawan_create_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.karyawan.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Karyawan Baru');
    }

    public function test_admin_can_store_new_karyawan()
    {
        $data = [
            'nik' => 'NIK999',
            'nama' => 'John Doe',
            'email' => 'john@test.com',
            'divisi' => 'IT',
            'jabatan' => 'Developer',
            'password' => 'pass1234',
            'status_karyawan' => 'aktif',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.karyawan.store'), $data);

        $response->assertRedirect(route('admin.karyawan.index'));
        $this->assertDatabaseHas('karyawans', [
            'nik' => 'NIK999',
            'nama' => 'John Doe',
            'email' => 'john@test.com',
            'divisi' => 'IT',
            'jabatan' => 'Developer',
            'status_karyawan' => 'aktif',
        ]);
    }

    public function test_admin_can_access_karyawan_show_page()
    {
        $karyawan = Karyawan::create([
            'nik' => 'NIK111',
            'nama' => 'Jane Smith',
            'email' => 'jane@test.com',
            'divisi' => 'HR',
            'jabatan' => 'Recruiter',
            'password' => bcrypt('password123'),
            'status_karyawan' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.karyawan.show', $karyawan));

        $response->assertStatus(200);
        $response->assertSee('Detail Karyawan');
        $response->assertSee('Jane Smith');
        $response->assertSee('NIK111');
    }

    public function test_admin_can_access_karyawan_edit_page()
    {
        $karyawan = Karyawan::create([
            'nik' => 'NIK111',
            'nama' => 'Jane Smith',
            'email' => 'jane@test.com',
            'divisi' => 'HR',
            'jabatan' => 'Recruiter',
            'password' => bcrypt('password123'),
            'status_karyawan' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.karyawan.edit', $karyawan));

        $response->assertStatus(200);
        $response->assertSee('Edit Karyawan');
        $response->assertSee('Jane Smith');
    }

    public function test_admin_can_update_karyawan()
    {
        $karyawan = Karyawan::create([
            'nik' => 'NIK111',
            'nama' => 'Jane Smith',
            'email' => 'jane@test.com',
            'divisi' => 'HR',
            'jabatan' => 'Recruiter',
            'password' => bcrypt('password123'),
            'status_karyawan' => 'aktif',
        ]);

        $updateData = [
            'nik' => 'NIK111',
            'nama' => 'Jane Doe',
            'email' => 'jane_new@test.com',
            'divisi' => 'HR Operations',
            'jabatan' => 'Lead Recruiter',
            'status_karyawan' => 'cuti',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.karyawan.update', $karyawan), $updateData);

        $response->assertRedirect(route('admin.karyawan.index'));
        $this->assertDatabaseHas('karyawans', [
            'id' => $karyawan->id,
            'nama' => 'Jane Doe',
            'email' => 'jane_new@test.com',
            'divisi' => 'HR Operations',
            'jabatan' => 'Lead Recruiter',
            'status_karyawan' => 'cuti',
        ]);
    }

    public function test_admin_can_export_presensi()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.presensi.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_forgot_password_page_is_accessible()
    {
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
        $response->assertSee('Atur Ulang Password');
    }

    public function test_forgot_password_can_reset_password_with_valid_credentials()
    {
        $karyawan = Karyawan::create([
            'nik' => '777123456',
            'nama' => 'Tester Karyawan',
            'email' => 'karyawan_test@test.com',
            'divisi' => 'QA',
            'jabatan' => 'Tester',
            'password' => bcrypt('oldpassword'),
            'status_karyawan' => 'aktif',
        ]);

        $user = User::create([
            'name' => 'Tester Karyawan',
            'email' => 'karyawan_test@test.com',
            'password' => bcrypt('oldpassword'),
            'role' => 'karyawan',
        ]);

        $resetData = [
            'nik' => '777123456',
            'email' => 'karyawan_test@test.com',
            'password' => 'newpass12',
            'password_confirmation' => 'newpass12',
        ];

        $response = $this->post(route('password.update'), $resetData);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        // Assert user can login with new password
        $loginData = [
            'login_role' => 'karyawan',
            'nik' => '777123456',
            'password' => 'newpass12',
        ];

        $loginResponse = $this->post(route('login'), $loginData);
        $loginResponse->assertRedirect(route('presensi'));
        $this->assertAuthenticatedAs($user);
    }
}
