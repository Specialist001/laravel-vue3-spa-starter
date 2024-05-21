<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class VerifyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone' => ['required', 'numeric', 'digits:12'],
            'login_code' => ['required', 'numeric', 'between:111111,999999']
        ];
    }

    public function failedValidation(Validator | \Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $formattedErrors = [];
        foreach ($errors as $field => $messages) {
            foreach ($messages as $message) {
                foreach (['uz', 'ru', 'en'] as $locale) {
                    $translatedMessage = __(key: 'validation.' . $field . '.' . $this->getRuleFromMessage($message), locale: $locale);
                    $formattedErrors[$field][$locale][] = $translatedMessage;
                }
            }
        }

        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => null,
            'errors'    => $formattedErrors,
        ], 422));
    }

    /**
     * @param mixed $message
     * @return string
     */
    protected  function getRuleFromMessage(mixed $message): string
    {
        if (str_contains($message, 'numeric')) {
            return 'numeric';
        }
        if (str_contains($message, 'required')) {
            return 'required';
        }
        if (str_contains($message, 'digits')) {
            return 'digits';
        }
        return '';
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('phone', 'login_code'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'phone' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'phone' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('phone')).'|'.$this->ip();
    }
}
