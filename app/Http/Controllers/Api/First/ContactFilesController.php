<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Contracts\Repositories\ContactFilesRepository;
use TenFour\Http\Requests\FileUpload\CreateFileUploadRequest;
use TenFour\Http\Requests\FileUpload\UpdateFileUploadRequest;
use TenFour\Http\Requests\FileUpload\ImportRequest;
use TenFour\Http\Transformers\FileUploadTransformer;
use TenFour\Http\Response;
use Illuminate\Support\Facades\Storage;
use App;
use TenFour\Jobs\ImportCSV;
use Dingo\Api\Auth\Auth;
use Exception;

/**
 * @Resource("Files", uri="/api/v1/organizations")
 */
class ContactFilesController extends ApiController
{
    public function __construct(ContactFilesRepository $files, Response $response, Auth $auth)
    {
        $this->files = $files;
        $this->response = $response;
        $this->auth = $auth;
    }

    /**
     * File Upload
     *
     * @Post("/{org_id}/files")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id")
     * })
     *
     * @Request({"file": "sample.csv"}, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "file": {
     *         "columns": {
     *             "Name",
     *             "Description",
     *             "Phone",
     *             "Email",
     *             "Address",
     *            "Twitter"
     *         },
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

        $reader = App::make('TenFour\Contracts\Contacts\CsvReader');
        $reader->setPath($path);
        $header = $reader->fetchHeader();

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
     * @Put("/{orgId}/files/{file_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("file_id", type="number", required=true, description="File id")
     * })
     *
     * @Request({
     *     "columns": {"name", "description", "phone", "email", "address", "twitter"},
     *     "maps_to": {"name", null, "phone", "email", null, "twitter"}
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "columns": {"name","description", "phone", "email", "address", "twitter"},
     *     "maps_to": {"name", null, "phone", "email", null, "twitter"}
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
     * @Post("/{orgId}/files/{file_id}/contacts")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("file_id", type="number", required=true, description="File id")
     * })
     *
     * @Request({}, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     * })
     *
     * @param Request $request
     * @param integer $organization_id
     * @param integer $file_id
     * @return Response
     */
    public function importContacts(ImportRequest $request, $organization_id, $file_id)
    {
        try {
            dispatch((new ImportCSV($this->auth->user()['id'], $organization_id, $file_id))/*->onQueue('import_csv')*/);
        } catch (Exception $e) {
            return response('UNPROCESSABLE ENTITY', 422);
        }

        return response('OK', 200);
    }
}
