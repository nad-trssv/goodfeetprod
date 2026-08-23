<?php

namespace App\Http\Requests\Settings;

use App\Rules\SiteDomainEmail;
use Illuminate\Foundation\Http\FormRequest;

class MainSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'technical_support_sender_email' => ['sometimes', 'required', 'email:rfc', 'max:255', new SiteDomainEmail],
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:255',
            'company_registration_number' => 'nullable|string|max:100',
            'company_bank_account' => 'nullable|string|max:100',
            'company_short_description' => 'nullable|array',
            'company_short_description.*' => 'nullable|string|max:5000',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,webp,svg|max:4096',
            'footer_logo' => 'nullable|file|mimes:png,jpg,jpeg,webp,svg|max:4096',
            'favicon' => 'nullable|file|mimes:png,ico,svg|max:1024',
            'primary_accent_color' => ['sometimes', 'required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'booking_template' => 'required|in:classic,wizard,compact',
            'show_service_images' => 'required|boolean',
            'show_master_images' => 'required|boolean',
            'cancellation_notice_hours' => 'sometimes|required|integer|min:0|max:8760',
            'allow_customer_cancellation' => 'sometimes|required|boolean',
            'google' => 'nullable|iframe_google',
            'waze' => 'nullable|iframe_waze',
            'social_media_facebook' => 'nullable|url|facebook_url|max:255',
            'social_media_youtube' => 'nullable|url|youtube_url|max:255',
            'social_media_instagram' => 'nullable|url|instagram_url|max:255',
            'social_media_twitter' => 'nullable|url|twitter_url|max:255',
        ];
    }

    public function messages()
    {
        return [
            'company_name.required' => __('admin_validation.site.company_name'),
            'company_email.required' => __('admin_validation.site.company_email'),
            'google.iframe_google' => __('admin_validation.site.iframe'),
            'waze.iframe_waze' => __('admin_validation.site.iframe'),
            'social_media_facebook.facebook_url' => __('admin_validation.site.facebook'),
            'social_media_youtube.youtube_url' => __('admin_validation.site.youtube'),
            'social_media_instagram.instagram_url' => __('admin_validation.site.instagram'),
            'social_media_twitter.twitter_url' => __('admin_validation.site.twitter'),
        ];
    }

    public function withValidator($validator)
    {
        $validator->addExtension('iframe_google', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/<iframe[^>]+src="https:\/\/www\.google\.com\/maps/', $value);
        });

        $validator->addExtension('iframe_waze', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/<iframe[^>]+src="https:\/\/embed\.waze\.com/', $value); 
        });

        $validator->addExtension('facebook_url', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^https:\/\/www\.facebook\.com\//', $value);
        });
        $validator->addExtension('instagram_url', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^https:\/\/www\.instagram\.com\//', $value);
        });
        $validator->addExtension('twitter_url', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^https:\/\/twitter\.com\//', $value);
        });

        $validator->addExtension('youtube_url', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^https:\/\/www\.youtube\.com\//', $value);
        });
    }
}
