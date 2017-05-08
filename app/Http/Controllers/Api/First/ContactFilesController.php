<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\ContactFilesRepository;
use RollCall\Http\Requests\FileUpload\CreateFileUploadRequest;
use RollCall\Http\Requests\FileUpload\UpdateFileUploadRequest;
use RollCall\Http\Requests\FileUpload\ImportRequest;
use RollCall\Http\Transformers\FileUploadTransformer;
use RollCall\Http\Response;
use Illuminate\Support\Facades\Storage;
use App;

/**
 * @Resource("Files", uri="/api/v1/organizations/{orgId}/files")
 */
class ContactFilesController extends ApiController
{
    public function __construct(ContactFilesRepository $files, Response $response)
    {
        $this->files = $files;
        $this->response = $response;
    }

    /**
     * File Upload
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Request({"file": "sample.csv"}, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "file": {
     *         "columns": [
     *             "Name",
     *             "Description",
     *             "Phone",
     *             "Email",
     *             "Address",
     *            "Twitter"
     *         ],
     *         "id": 2,
     *         "organization": {
     *             "id": 2,
     *             "uri": "/organizations/2"
     *         },
     *         "uri": "organizations/2/files/2"
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function create(CreateFileUploadRequest $request, $organization_id)
    {
        $file = $request->file('file');
        $path = $file->store('contacts');

        $header = App::make('RollCall\Contracts\Contacts\CsvReader', [$path])->fetchHeader();

        $input = [
            'organization_id' => $organization_id,
            'size'            => $file->getClientSize(),
            'filename'        => $path,
            'mime'            => $file->getClientMimeType(),
            'columns'         => $header,
        ];

        return $this->response->item($this->files->create($input),
                                         new FileUploadTransformer, 'file');
    }

    /**
     * Update File Upload
     *
     * @Put("/{file_id}")
     * @Versions({"v1"})
     * @Request({
     *     "columns": ['name','description', 'phone', 'email', 'address', 'twitter']
     *     "maps_to": ['name', null, 'phone', 'email', null, 'twitter']
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "columns": ['name','description', 'phone', 'email', 'address', 'twitter']
     *     "maps_to": ['name', null, 'phone', 'email', null, 'twitter']
     * })
     *
     * @param Request $request
     * @param integer $organization_id
     * @param integer $file_id
     * @return Response
     */
    public function update(UpdateFileUploadRequest $request, $organization_id, $file_id)
    {
        $input = $request->all() + [
            'organization_id' => $organization_id
        ];

        return $this->response->item($this->files->update($input, $file_id),
                                     new FileUploadTransformer, 'file');
    }

    /**
     * Import contacts
     *
     * @Post("/{file_id}/contacts")
     * @Versions({"v1"})
     * @Request({}, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     'count' : 2
     * })
     *
     * @param Request $request
     * @param integer $organization_id
     * @param integer $file_id
     * @return Response
     */
    public function importContacts(ImportRequest $request, $organization_id, $file_id)
    {
        $file_details = $this->files->find($file_id);

        $path = $file_details['filename'];
        $map = $file_details['maps_to'];

        $transformer = App::make('RollCall\Contracts\Contacts\CsvTransformer', [$map]);

        $reader = App::make('RollCall\Contracts\Contacts\CsvReader', [$path]);

        $people = App::make('RollCall\Contracts\Repositories\PersonRepository');
        $contacts = App::make('RollCall\Contracts\Repositories\ContactRepository');

        $importer = App::make('RollCall\Contracts\Contacts\CsvImporter',
                              [$reader, $transformer, $contacts, $people, $organization_id]);

        $count = $importer->import();

        Storage::delete($path);

        return [
            'count' => $count
        ];
    }
}
