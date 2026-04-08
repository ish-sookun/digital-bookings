<?php

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->reservation = Reservation::factory()->create();
});

// --- Async upload via AJAX endpoint ---

it('uploads a PDF via the async endpoint', function () {
    $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

    $response = $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'signed_ro',
    ]);

    $response->assertOk()->assertJsonStructure(['success', 'download_url']);
    $this->reservation->refresh();
    expect($this->reservation->signed_ro_path)->not->toBeNull();
    Storage::disk('local')->assertExists($this->reservation->signed_ro_path);
});

it('uploads a DOC file via the async endpoint', function () {
    $file = UploadedFile::fake()->create('document.doc', 512, 'application/msword');

    $response = $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'purchase_order',
    ]);

    $response->assertOk();
    $this->reservation->refresh();
    expect($this->reservation->purchase_order_path)->not->toBeNull();
});

it('uploads a DOCX file via the async endpoint', function () {
    $file = UploadedFile::fake()->create('document.docx', 512);

    $response = $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'invoice',
    ]);

    $response->assertOk();
    $this->reservation->refresh();
    expect($this->reservation->invoice_path)->not->toBeNull();
});

it('uploads an XLS file via the async endpoint', function () {
    $file = UploadedFile::fake()->create('spreadsheet.xls', 256);

    $response = $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'purchase_order',
    ]);

    $response->assertOk();
});

it('uploads an XLSX file via the async endpoint', function () {
    $file = UploadedFile::fake()->create('spreadsheet.xlsx', 256);

    $response = $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'invoice',
    ]);

    $response->assertOk();
});

it('uploads an image file via the async endpoint', function () {
    $file = UploadedFile::fake()->image('photo.jpg', 640, 480);

    $response = $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'signed_ro',
    ]);

    $response->assertOk();
    $this->reservation->refresh();
    expect($this->reservation->signed_ro_path)->not->toBeNull();
});

it('uploads a PNG image via the async endpoint', function () {
    $file = UploadedFile::fake()->image('scan.png', 800, 600);

    $response = $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'purchase_order',
    ]);

    $response->assertOk();
});

it('rejects an exe file upload', function () {
    $file = UploadedFile::fake()->create('malware.exe', 1024);

    $response = $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'signed_ro',
    ]);

    $response->assertUnprocessable();
});

it('rejects an invalid document type', function () {
    $file = UploadedFile::fake()->create('document.pdf', 512, 'application/pdf');

    $response = $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'invalid_type',
    ]);

    $response->assertStatus(422);
});

it('replaces an existing document on re-upload', function () {
    $oldFile = UploadedFile::fake()->create('old.pdf', 512, 'application/pdf');

    $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $oldFile,
        'type' => 'signed_ro',
    ]);

    $this->reservation->refresh();
    $oldPath = $this->reservation->signed_ro_path;

    $newFile = UploadedFile::fake()->create('new.pdf', 512, 'application/pdf');

    $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $newFile,
        'type' => 'signed_ro',
    ]);

    $this->reservation->refresh();
    expect($this->reservation->signed_ro_path)->not->toBe($oldPath);
    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertExists($this->reservation->signed_ro_path);
});

it('stores files in the correct directory structure', function () {
    $file = UploadedFile::fake()->create('document.pdf', 512, 'application/pdf');

    $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'invoice',
    ]);

    $this->reservation->refresh();
    $expectedPrefix = 'documents/'.now()->format('Y').'/'.now()->format('m');
    expect($this->reservation->invoice_path)->toStartWith($expectedPrefix);
});

// --- Document download ---

it('downloads an uploaded document', function () {
    $file = UploadedFile::fake()->create('document.pdf', 512, 'application/pdf');

    $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'signed_ro',
    ]);

    $response = $this->get(route('reservations.document', [$this->reservation, 'signed-ro']));
    $response->assertOk();
});

it('returns 404 when downloading a non-existent document', function () {
    $response = $this->get(route('reservations.document', [$this->reservation, 'signed-ro']));
    $response->assertNotFound();
});

it('returns 404 for an invalid document type download', function () {
    $response = $this->get(route('reservations.document', [$this->reservation, 'invalid']));
    $response->assertNotFound();
});

// --- Form-based upload (create/update) ---

it('uploads documents via the reservation update form', function () {
    $poFile = UploadedFile::fake()->create('po.pdf', 512, 'application/pdf');
    $invoiceFile = UploadedFile::fake()->create('invoice.pdf', 512, 'application/pdf');

    $data = [
        'client_id' => $this->reservation->client_id,
        'product' => $this->reservation->product,
        'placement_id' => $this->reservation->placement_id,
        'channel' => $this->reservation->channel,
        'scope' => $this->reservation->scope,
        'dates_booked' => json_encode($this->reservation->dates_booked),
        'gross_amount' => $this->reservation->gross_amount,
        'total_amount_to_pay' => $this->reservation->total_amount_to_pay,
        'vat_exempt' => $this->reservation->vat_exempt ? '1' : '0',
        'status' => $this->reservation->status->value,
        'purchase_order_file' => $poFile,
        'invoice_file' => $invoiceFile,
    ];

    $response = $this->put(route('reservations.update', $this->reservation), $data);

    $response->assertRedirect(route('reservations.index'));
    $this->reservation->refresh();
    expect($this->reservation->purchase_order_path)->not->toBeNull();
    expect($this->reservation->invoice_path)->not->toBeNull();
});

it('uploads a webp image via the async endpoint', function () {
    $file = UploadedFile::fake()->image('photo.webp', 640, 480);

    $response = $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'signed_ro',
    ]);

    $response->assertOk();
});

it('rejects files exceeding 10MB', function () {
    $file = UploadedFile::fake()->create('huge.pdf', 11000, 'application/pdf');

    $response = $this->postJson(route('reservations.upload-document', $this->reservation), [
        'file' => $file,
        'type' => 'signed_ro',
    ]);

    $response->assertUnprocessable();
});
