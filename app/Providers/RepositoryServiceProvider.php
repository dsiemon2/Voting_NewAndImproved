<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\{
    UserRepositoryInterface,
    EventRepositoryInterface,
    VoteRepositoryInterface,
    DivisionRepositoryInterface,
    ParticipantRepositoryInterface,
    EntryRepositoryInterface,
    VotingTypeRepositoryInterface,
    EventTemplateRepositoryInterface,
};
use App\Repositories\Eloquent\{
    UserRepository,
    EventRepository,
    VoteRepository,
    DivisionRepository,
    ParticipantRepository,
    EntryRepository,
    VotingTypeRepository,
    EventTemplateRepository,
};

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings.
     */
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        EventRepositoryInterface::class => EventRepository::class,
        VoteRepositoryInterface::class => VoteRepository::class,
        DivisionRepositoryInterface::class => DivisionRepository::class,
        ParticipantRepositoryInterface::class => ParticipantRepository::class,
        EntryRepositoryInterface::class => EntryRepository::class,
        VotingTypeRepositoryInterface::class => VotingTypeRepository::class,
        EventTemplateRepositoryInterface::class => EventTemplateRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
