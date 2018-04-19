<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

use TenFour\Models\User;
use TenFour\Models\Organization;
use TenFour\Contracts\Repositories\PersonRepository;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Contracts\Repositories\ContactFilesRepository;
use TenFour\Notifications\ImportSucceeded;
use TenFour\Notifications\ImportFailed;
use TenFour\Jobs\SendInvite;

use App;
use Exception;

class ImportCSV implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id, $organization_id, $csv_file_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $organization_id, $csv_file_id)
    {
        $this->user_id = $user_id;
        $this->organization_id = $organization_id;
        $this->csv_file_id = $csv_file_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PersonRepository $people, ContactRepository $contacts, ContactFilesRepository $files)
    {
        $user = User::where('id', $this->user_id)->firstOrFail();
        $organization = Organization::where('id', $this->organization_id)->firstOrFail();

        $file_details = $files->find($this->csv_file_id);

        $path = $file_details['filename'];
        $map = $file_details['maps_to'];

        $transformer = App::make('TenFour\Contracts\Contacts\CsvTransformer');
        $transformer->setMap($map);

        $reader = App::make('TenFour\Contracts\Contacts\CsvReader');
        $reader->setPath($path);

        $importer = App::make('TenFour\Contracts\Contacts\CsvImporter');
        $importer->setReader($reader);
        $importer->setTransformer($transformer);
        $importer->setContacts($contacts);
        $importer->setPeople($people);
        $importer->setOrganizationId($this->organization_id);

        $import_results = $importer->import();
        $members = $import_results['members'];
        $duplicates = $import_results['duplicates'];
        $count = count($members);
        $dupe_count = count($duplicates);

        foreach ($members as $member_id) {
            $invitee = $people->find($this->organization_id, $member_id);
            $invitee['invite_token'] = Hash::Make(config('app.key'));
            $invitee['invite_sent'] = true;
            $people->update($this->organization_id, $invitee, $member_id);

            dispatch((new SendInvite($invitee, $organization->toArray())));
        }

        Storage::delete($path);

        $user->notify(new ImportSucceeded($organization, $count, $dupe_count));
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        \Log::error($exception);

        $user = User::where('id', $this->user_id)->firstOrFail();
        $organization = Organization::where('id', $this->organization_id)->firstOrFail();

        $user->notify(new ImportFailed($organization, $exception));
    }
}
