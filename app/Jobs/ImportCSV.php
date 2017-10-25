<?php

namespace RollCall\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

use RollCall\Models\User;
use RollCall\Models\Organization;
use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\ContactFilesRepository;
use RollCall\Notifications\ImportSucceeded;
use RollCall\Notifications\ImportFailed;

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

        $transformer = App::make('RollCall\Contracts\Contacts\CsvTransformer', [$map]);
        $reader = App::make('RollCall\Contracts\Contacts\CsvReader', [$path]);

        $importer = App::make('RollCall\Contracts\Contacts\CsvImporter',
            [$reader, $transformer, $contacts, $people, $this->organization_id]);

        $count = $importer->import();

        Storage::delete($path);

        $user->notify(new ImportSucceeded($organization, $count));
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        $user = User::where('id', $this->user_id)->firstOrFail();
        $organization = Organization::where('id', $this->organization_id)->firstOrFail();

        $user->notify(new ImportFailed($organization, $exception));
    }
}
