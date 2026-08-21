<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCustomerIndexRequest;
use App\Services\Customer\AdminCustomerDirectory;
use App\Services\Customer\AdminCustomerProfile;
use App\Models\Customer;
use Illuminate\View\View;
use App\Http\Requests\CrmCustomerProfileRequest;
use App\Http\Requests\CrmNoteRequest;
use App\Http\Requests\CrmConsentRequest;
use App\Http\Requests\CrmDocumentRequest;
use App\Models\CustomerDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index(AdminCustomerIndexRequest $request, AdminCustomerDirectory $directory): View
    {
        return view('admin.customers.index', $directory->paginate($request->user(), $request->validated()));
    }

    public function show(Customer $customer, AdminCustomerProfile $profile): View
    {
        abort_unless($profile->canView($customer, request()->user()), 403);

        return view('admin.customers.show', $profile->get($customer, request()->user()));
    }

    public function updateCrm(CrmCustomerProfileRequest $request, Customer $customer, AdminCustomerProfile $profile): RedirectResponse
    {
        abort_unless($profile->canView($customer, $request->user()), 403);
        $data = $request->validated();
        $customer->crmProfile()->updateOrCreate(['customer_id'=>$customer->id], [
            'contraindications'=>$data['contraindications'] ?? null,
            'important_warnings'=>$data['important_warnings'] ?? null,
            'preferred_user_id'=>$data['preferred_user_id'] ?? null,
            'updated_by_user_id'=>$request->user()->id,
        ]);
        $customer->crmTags()->sync($data['tag_ids'] ?? []);
        $customer->preferredServices()->sync($data['preferred_service_ids'] ?? []);
        return back()->with('success', __('crm.saved'));
    }

    public function storeNote(CrmNoteRequest $request, Customer $customer, AdminCustomerProfile $profile): RedirectResponse
    {
        abort_unless($profile->canView($customer, $request->user()), 403);
        $customer->crmNotes()->create($request->validated()+['author_user_id'=>$request->user()->id]);
        return back()->with('success', __('crm.note_added'));
    }

    public function destroyNote(Customer $customer, \App\Models\CustomerCrmNote $note, AdminCustomerProfile $profile): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('crm.update') && $profile->canView($customer, request()->user()) && (int)$note->customer_id===(int)$customer->id, 403);
        $note->delete();
        return back()->with('success', __('crm.note_deleted'));
    }

    public function storeConsent(CrmConsentRequest $request, Customer $customer, AdminCustomerProfile $profile): RedirectResponse
    {
        abort_unless($profile->canView($customer, $request->user()), 403);
        $data=$request->validated();
        $customer->consents()->create($data+[
            'withdrawn_at'=>!$data['is_granted'] ? $data['captured_at'] : null,
            'recorded_by_user_id'=>$request->user()->id,
        ]);
        return back()->with('success', __('crm.consent_added'));
    }

    public function storeDocument(CrmDocumentRequest $request, Customer $customer, AdminCustomerProfile $profile): RedirectResponse
    {
        abort_unless($profile->canView($customer, $request->user()), 403);
        $file=$request->file('file');
        $path=$file->store('crm/customers/'.$customer->id, 'local');
        abort_unless($path, 500);
        $customer->documents()->create([
            'uploaded_by_user_id'=>$request->user()->id,'category'=>$request->validated('category'),
            'original_name'=>mb_substr($file->getClientOriginalName(),0,255),'disk'=>'local','path'=>$path,
            'mime_type'=>$file->getMimeType() ?: 'application/octet-stream','size'=>$file->getSize(),
        ]);
        return back()->with('success', __('crm.document_added'));
    }

    public function downloadDocument(CustomerDocument $document, AdminCustomerProfile $profile)
    {
        abort_unless(request()->user()->hasPermission('crm.documents') && $profile->canView($document->customer, request()->user()), 403);
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);
        return Storage::disk($document->disk)->download($document->path, $document->original_name, ['Content-Type'=>$document->mime_type]);
    }

    public function previewDocument(CustomerDocument $document, AdminCustomerProfile $profile)
    {
        abort_unless(request()->user()->hasPermission('crm.documents') && $profile->canView($document->customer, request()->user()), 403);
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);
        abort_unless(str_starts_with($document->mime_type, 'image/') || $document->mime_type === 'application/pdf', 415);

        return response()->file(Storage::disk($document->disk)->path($document->path), [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="'.str_replace(['"', "\r", "\n"], '', $document->original_name).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroyDocument(CustomerDocument $document, AdminCustomerProfile $profile): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('crm.documents') && $profile->canView($document->customer, request()->user()), 403);
        Storage::disk($document->disk)->delete($document->path);
        $document->delete();
        return back()->with('success', __('crm.document_deleted'));
    }
}
