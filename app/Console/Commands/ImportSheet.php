<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\User;
use App\Support\Phone;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

class ImportSheet extends Command
{
    protected $signature = 'drezwel:import-sheet {path : Path to the "Drezwel Go.xlsx" workbook} {--dry-run : Show what would be imported without saving}';

    protected $description = 'Import riders and deliveries from the Google-Sheets workbook (Riders + App_Deliveries tabs)';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true); // use the values Sheets last calculated, not the formulas
        $book = $reader->load($path);

        $dry = $this->option('dry-run');
        $stats = ['riders' => 0, 'customers' => 0, 'deliveries' => 0, 'skipped' => 0];
        $newLogins = [];

        DB::beginTransaction();
        try {
            // Riders: name + email
            if ($sheet = $book->getSheetByName('Riders')) {
                foreach (array_slice($sheet->toArray(null, true, false, false), 1) as $row) {
                    $name = trim((string) ($row[0] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $email = trim((string) ($row[1] ?? '')) ?: null;
                    $rider = User::where('role', 'rider')->where('name', $name)->first();
                    if (! $rider) {
                        $password = Str::password(10, symbols: false);
                        User::create(['name' => $name, 'email' => $email, 'password' => $password, 'role' => 'rider', 'is_active' => true]);
                        $newLogins[] = [$name, $email ?? '(set a phone number)', $password];
                        $stats['riders']++;
                    }
                }
            }

            // Deliveries
            $sheet = $book->getSheetByName('App_Deliveries');
            abort_unless($sheet, 1, 'App_Deliveries tab not found.');
            foreach (array_slice($sheet->toArray(null, true, false, false), 1) as $i => $r) {
                [$ref, $date, $name, $contact, $location, $package, $mode, $riderName, $total, $payStatus, $paid, , $dStatus, $notes, $type, $sent, $selected] = array_pad($r, 17, null);
                $ref = trim((string) $ref);
                $phone = Phone::clean((string) $contact);
                if ($ref === '' || ! $phone || trim((string) $name) === '') {
                    $stats['skipped']++;
                    continue;
                }
                if (Delivery::where('reference', $ref)->exists()) {
                    $stats['skipped']++;
                    continue;
                }

                $customer = Customer::firstOrNew(['phone' => $phone]);
                if (! $customer->exists) {
                    $stats['customers']++;
                }
                $customer->name = trim((string) $name);
                $customer->location = trim((string) $location);
                $customer->save();

                $rider = $riderName ? User::where('role', 'rider')->where('name', trim((string) $riderName))->first() : null;
                $status = match (strtolower(trim((string) $dStatus))) {
                    'delivered' => 'delivered', 'undelivered' => 'undelivered',
                    default => $rider ? 'assigned' : 'pending',
                };
                $total = (float) $total;
                $amountPaid = strtolower((string) $payStatus) === 'paid' ? $total : (float) $paid;

                Delivery::create([
                    'reference' => $ref,
                    'customer_id' => $customer->id,
                    'location' => trim((string) $location) ?: 'N/A',
                    'package_description' => trim((string) $package) ?: 'Order',
                    'delivery_mode' => $this->mode((string) $mode),
                    'order_type' => $this->type((string) $type),
                    'notes' => $notes ? (string) $notes : null,
                    'delivery_date' => $this->date($date),
                    'total_amount' => $total,
                    'amount_paid' => min($amountPaid, $total),
                    'items_sent' => max((int) $sent, 1),
                    'items_kept' => $status === 'delivered' ? ($selected === null || $selected === '' ? max((int) $sent, 1) : (int) $selected) : null,
                    'status' => $status,
                    'rider_id' => $rider?->id,
                    'delivered_at' => $status === 'delivered' ? $this->date($date) : null,
                ]);
                $stats['deliveries']++;
            }

            $dry ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Import failed, nothing was saved: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info(($dry ? '[dry run] ' : '') . "Imported {$stats['deliveries']} deliveries, {$stats['customers']} new customers, {$stats['riders']} new riders ({$stats['skipped']} rows skipped).");
        if ($newLogins && ! $dry) {
            $this->warn('New rider logins (shown once - change them from the Riders page):');
            $this->table(['Rider', 'Login', 'Password'], $newLogins);
        }

        return self::SUCCESS;
    }

    private function mode(string $v): string
    {
        return match (strtolower(trim($v))) {
            'station bus' => 'station_bus', 'parcel office' => 'parcel_office', 'pick up' => 'pick_up', default => 'rider',
        };
    }

    private function type(string $v): string
    {
        return match (strtolower(trim($v))) {
            'selection trial' => 'selection_trial', 'exchange' => 'exchange', default => 'standard',
        };
    }

    private function date(mixed $v): Carbon
    {
        if (is_numeric($v)) {
            return Carbon::instance(XlsDate::excelToDateTimeObject($v));
        }

        return $v ? Carbon::parse($v) : today();
    }
}
