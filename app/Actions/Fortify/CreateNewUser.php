<?php

namespace App\Actions\Fortify;

use App\Actions\Events\RecordDomainEventAction;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private readonly RecordDomainEventAction $recordEvent) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => UserRole::Visitor,
        ]);

        $this->recordEvent->execute('user_registered', $user, '', 'user', (string) $user->id, [
            'source' => 'web_registration',
            'quality_flag' => false,
        ]);

        return $user;
    }
}
