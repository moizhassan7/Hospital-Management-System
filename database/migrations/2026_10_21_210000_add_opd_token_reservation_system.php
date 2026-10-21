<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opd_appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('opd_appointments', 'session_key')) {
                $table->string('session_key', 32)->default('')->after('appointment_time');
            }
            if (! Schema::hasColumn('opd_appointments', 'preferred_token_number')) {
                $table->unsignedInteger('preferred_token_number')->nullable()->after('token_number');
            }
            if (! Schema::hasColumn('opd_appointments', 'token_status')) {
                $table->string('token_status', 20)->nullable()->after('preferred_token_number');
            }
            if (! Schema::hasColumn('opd_appointments', 'token_reserved_at')) {
                $table->timestamp('token_reserved_at')->nullable();
            }
            if (! Schema::hasColumn('opd_appointments', 'token_confirmed_at')) {
                $table->timestamp('token_confirmed_at')->nullable();
            }
            if (! Schema::hasColumn('opd_appointments', 'amount_paid')) {
                $table->decimal('amount_paid', 10, 2)->default(0)->after('total_amount');
            }
            if (! Schema::hasColumn('opd_appointments', 'payment_status')) {
                $table->string('payment_status', 20)->default('unpaid')->after('amount_paid');
            }
            if (! Schema::hasColumn('opd_appointments', 'payment_method')) {
                $table->string('payment_method', 20)->nullable();
            }
            if (! Schema::hasColumn('opd_appointments', 'payment_collected_at')) {
                $table->timestamp('payment_collected_at')->nullable();
            }
            if (! Schema::hasColumn('opd_appointments', 'payment_collected_by')) {
                $table->unsignedBigInteger('payment_collected_by')->nullable();
            }
            if (! Schema::hasColumn('opd_appointments', 'payment_idempotency_key')) {
                $table->string('payment_idempotency_key', 80)->nullable()->unique();
            }
            if (! Schema::hasColumn('opd_appointments', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable();
            }
            if (! Schema::hasColumn('opd_appointments', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }
            if (! Schema::hasColumn('opd_appointments', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable();
            }
            if (! Schema::hasColumn('opd_appointments', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable();
            }
            if (! Schema::hasColumn('opd_appointments', 'no_show_at')) {
                $table->timestamp('no_show_at')->nullable();
            }
        });

        if (! Schema::hasTable('opd_token_claims')) {
            Schema::create('opd_token_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('opd_appointment_id')->constrained('opd_appointments')->cascadeOnDelete();
                $table->string('doctor_code');
                $table->date('appointment_date');
                $table->string('session_key', 32)->default('');
                $table->unsignedInteger('token_number');
                $table->string('status', 20);
                $table->string('lock_key', 191)->nullable()->unique();
                $table->unsignedInteger('previous_token_number')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->string('reason')->nullable();
                $table->timestamp('reserved_at')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->timestamp('released_at')->nullable();
                $table->timestamps();

                $table->index(['doctor_code', 'appointment_date', 'session_key', 'token_number'], 'opd_token_queue_idx');
                $table->index(['opd_appointment_id', 'status'], 'opd_token_appointment_status_idx');
            });
        }

        $this->backfillExistingAppointments();
    }

    public function down(): void
    {
        Schema::dropIfExists('opd_token_claims');

        Schema::table('opd_appointments', function (Blueprint $table) {
            $columns = [
                'session_key',
                'preferred_token_number',
                'token_status',
                'token_reserved_at',
                'token_confirmed_at',
                'amount_paid',
                'payment_status',
                'payment_method',
                'payment_collected_at',
                'payment_collected_by',
                'payment_idempotency_key',
                'checked_in_at',
                'cancelled_at',
                'cancelled_by',
                'cancellation_reason',
                'no_show_at',
            ];
            $drop = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('opd_appointments', $column)));
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }

    /**
     * Interpret historical rows without rewriting amounts or deleting records.
     * Duplicate used tokens in the same queue keep their history but only the
     * first row receives a uniqueness lock.
     */
    private function backfillExistingAppointments(): void
    {
        $now = now();
        $takenLocks = [];

        DB::table('opd_appointments')->orderBy('id')->chunkById(200, function ($rows) use ($now, &$takenLocks) {
            foreach ($rows as $row) {
                $this->backfillOne($row, $now, $takenLocks);
            }
        });
    }

    private function backfillOne(object $row, $now, array &$takenLocks): void
    {
            $status = $row->status ?: 'booked';
            $token = $row->token_number ? (int) $row->token_number : null;
            $session = $row->session_key ?? '';
            $date = $row->appointment_date;
            $completed = in_array($status, ['completed', null, ''], true);
            $amountPaid = (float) ($row->amount_paid ?? 0);
            $total = (float) $row->total_amount;

            if ($completed) {
                $paymentStatus = 'paid';
                $amountPaid = $amountPaid > 0 ? $amountPaid : $total;
                $tokenStatus = $token ? 'used' : null;
            } elseif (in_array($status, ['cancelled', 'no_show'], true)) {
                $paymentStatus = $amountPaid >= $total && $total > 0 ? 'paid' : ($amountPaid > 0 ? 'partially_paid' : 'unpaid');
                $tokenStatus = $token ? 'released' : null;
            } else {
                $paymentStatus = $amountPaid >= $total && $total > 0 ? 'paid' : ($amountPaid > 0 ? 'partially_paid' : 'unpaid');
                if ($paymentStatus === 'paid' && $token) {
                    $tokenStatus = 'reserved';
                } elseif ($token) {
                    $tokenStatus = 'provisional';
                } else {
                    $tokenStatus = null;
                }
            }

            DB::table('opd_appointments')->where('id', $row->id)->update([
                'session_key' => $session,
                'preferred_token_number' => $token,
                'token_status' => $tokenStatus,
                'amount_paid' => $amountPaid,
                'payment_status' => $paymentStatus,
                'token_reserved_at' => in_array($tokenStatus, ['reserved', 'confirmed', 'used'], true) ? ($row->token_reserved_at ?? $row->created_at ?? $now) : null,
            ]);

            if (! $token || $tokenStatus === null) {
                return;
            }

            $lockKey = null;
            if (in_array($tokenStatus, ['reserved', 'confirmed', 'used'], true)) {
                $lockKey = $row->doctor_code.'|'.$date.'|'.$session.'|'.$token;
                if (isset($takenLocks[$lockKey])) {
                    $lockKey = null;
                } else {
                    $takenLocks[$lockKey] = $row->id;
                }
            }

            DB::table('opd_token_claims')->insert([
                'opd_appointment_id' => $row->id,
                'doctor_code' => $row->doctor_code,
                'appointment_date' => $date,
                'session_key' => $session,
                'token_number' => $token,
                'status' => $tokenStatus === 'released' ? 'released' : $tokenStatus,
                'lock_key' => $lockKey,
                'reserved_at' => in_array($tokenStatus, ['reserved', 'confirmed', 'used'], true) ? ($row->created_at ?? $now) : null,
                'used_at' => $tokenStatus === 'used' ? ($row->updated_at ?? $now) : null,
                'released_at' => $tokenStatus === 'released' ? $now : null,
                'reason' => 'Legacy appointment imported',
                'created_at' => $row->created_at ?? $now,
                'updated_at' => $now,
            ]);
    }
};
