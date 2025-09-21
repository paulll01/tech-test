<?php

namespace App\Repositories\Booking;

use App\Models\BookingDayModel;
use App\Models\BookingModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingRepository implements IBookingRepository
{
    public function createWithDays(array $bookingAttributes, array $days): BookingModel
    {
        return DB::transaction(function () use ($bookingAttributes, $days) {

            // add unique ref
            $bookingAttributes['unique_reference'] = $this->generateUniqueReference($bookingAttributes['vehicle_reg']);

            $booking = new BookingModel;
            foreach ($bookingAttributes as $key => $value) {
                $booking->{$key} = $value;
            }
            $booking->save();

            if (! empty($days)) {
                $timestamp = now();

                foreach ($days as $date) {
                    $day = new BookingDayModel;
                    $day->booking_id = $booking->id;
                    $day->car_park_id = $booking->car_park_id;
                    $day->date = $date;
                    $day->created_at = $timestamp;
                    $day->updated_at = $timestamp;
                    $day->save();
                }
            }

            return $booking;
        });
    }

    /**
     * Generate unique reference
     */
    protected function generateUniqueReference(string $vehicleReg): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z0-9]/', '', $vehicleReg));
        $prefix = $clean !== '' ? substr($clean, -3) : 'REF';

        $attempt = 0;

        while (true) {
            if ($attempt < 5) {
                $token = substr(strtoupper((string) Str::ulid()), -6);
            } elseif ($attempt < 10) {
                $token = substr(strtoupper((string) Str::ulid()), -10);
            } else {
                $token = strtoupper(bin2hex(random_bytes(8)));
            }

            $candidate = "{$prefix}-{$token}";

            $exists = BookingModel::query()
                ->where('unique_reference', $candidate)
                ->exists();

            if (! $exists) {
                return $candidate;
            }

            $attempt++;
        }
    }

    public function updateWithDays(BookingModel $booking, array $attributes, array $days): BookingModel
    {
        return DB::transaction(function () use ($booking, $attributes, $days) {

            foreach ($attributes as $k => $v) {
                $booking->{$k} = $v;
            }
            $booking->save();

            // delete old
            foreach ($booking->bookingDays()->cursor() as $day) {
                $day->delete();
            }

            // insert new
            if (! empty($days)) {
                $ts = now();
                foreach ($days as $d) {
                    $row = new BookingDayModel;
                    $row->booking_id = $booking->id;
                    $row->car_park_id = $booking->car_park_id;
                    $row->date = $d;
                    $row->created_at = $ts;
                    $row->updated_at = $ts;
                    $row->save();
                }
            }

            return $booking->refresh();
        });
    }

    public function cancelAndReleaseDays(BookingModel $booking): BookingModel
    {
        return DB::transaction(function () use ($booking) {
            // free days
            BookingDayModel::query()
                ->where('booking_id', $booking->id)
                ->delete();

            $booking->status = 'cancelled';
            $booking->save();

            return $booking->refresh();
        });
    }
}
