<?php

namespace App\Validation;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

trait Rules
{
    protected function startDate($req = null): array
    {
        if($req !== null){
            return ['required', 'date_format:Y-m-d', 'after_or_equal:today'];
        }
        return ['date_format:Y-m-d', 'after_or_equal:today'];
    }

    protected function endDate($req = null): array
    {
        if($req !== null){
            return ['required', 'date_format:Y-m-d', 'after:start_at'];
        }
        return ['date_format:Y-m-d', 'after:start_at'];
    }

    protected function title($req = null): array
    {
        if($req !== null){
            return ['required', 'min:10', 'max:150'];
        }
        return ['min:10', 'max:150'];
    }

    protected function description($req = null): array
    {
        if($req !== null){
            return ['required', 'min:25', 'max:1500'];
        }
        return ['min:25', 'max:1500'];
    }

    protected function status($req = null): array
    {
        if($req !== null){
            return ['required', Rule::in(['available-soon','in-progress', 'done'])];
        }
        return [Rule::in(['available-soon','in-progress', 'done'])];
    }

    protected function priority($req = null): array
    {
        if($req !== null){
            return ['required', Rule::in(['low', 'medium', 'high'])];
        }
        return [Rule::in(['low', 'medium', 'high'])];
    }

    protected function name($req = null): array
    {
        if($req !== null){
            return ['required', 'string'];
        }
        return ['string'];
    }

    protected function emailUnique($req = null): array
    {
        if($req !== null){
            return ['required', 'email', 'unique:users,email'];
        }
        return ['email', 'unique:users,email'];
    }

    protected function emailExists($req = null): array
    {
        if($req !== null){
            return ['required', 'email', 'exists:users,email'];
        }
        return ['email', 'exists:users,email'];
    }

    protected function password(): array 
    {
        return ['required', Password::min(8)->letters()->mixedCase()->numbers()->symbols()];
    }

    protected function content(): array
    {
        return ['required', 'min:5', 'max:1500'];
    }

}