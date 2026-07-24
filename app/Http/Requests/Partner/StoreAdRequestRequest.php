<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAdRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isRewardedPopup = $this->isRewardedPopup();
        $requiresStaticImage = $isRewardedPopup || $this->hasPublicFeedPlacement();
        $requiresAssetUrl = $requiresStaticImage && ! $this->hasFile('asset_file');
        $requiresAssetFile = $requiresStaticImage && blank($this->input('asset_url'));

        return [
            'title' => ['required', 'string', 'max:160'],
            'body_copy' => [Rule::requiredIf($isRewardedPopup), 'nullable', 'string', 'max:1200'],
            'cta_text' => ['nullable', 'string', 'max:80'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'hub_id' => ['nullable', 'uuid'],
            'ad_type' => ['required', 'string', Rule::in(['standalone', 'display_takeover', 'reward_moment', 'rewarded_content'])],
            'creative_type' => ['required', 'string', Rule::in($requiresStaticImage ? ['image'] : ['image', 'video', 'text_card', 'display_banner'])],
            'placement_type' => ['required', 'string', Rule::in(['fixed_display', 'mobile_display', 'public_feed', 'qr_landing', 'reward_page', 'map_route', 'post_mission'])],
            'online_placements' => ['nullable', 'array', 'max:5'],
            'online_placements.*' => ['string', 'distinct', Rule::in(['public_feed', 'qr_landing', 'reward_page', 'map_route', 'post_mission'])],
            'asset_url' => [Rule::requiredIf($requiresAssetUrl), 'nullable', 'url', 'max:2048'],
            'asset_file' => [
                Rule::requiredIf($requiresAssetFile),
                'nullable',
                'image',
                'mimes:jpg,jpeg,webp',
                'max:250',
                'dimensions:ratio=16/9,min_width=800,min_height=450',
            ],
            'rewarded_points' => [Rule::requiredIf($isRewardedPopup), 'nullable', 'integer', 'min:1', 'max:100'],
            'required_seconds' => [Rule::requiredIf($isRewardedPopup), 'nullable', 'integer', 'min:8', 'max:15'],
            'game_stage_index' => [Rule::requiredIf($isRewardedPopup), 'nullable', 'integer', Rule::in([2, 3, 4, 5])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'budget_amount' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'impression_cap' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'click_cap' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->isRewardedPopup()) {
                    return;
                }

                $placementTypes = collect([$this->input('placement_type')])
                    ->merge((array) $this->input('online_placements', []));

                if ($placementTypes->contains('public_feed')) {
                    $validator->errors()->add(
                        'online_placements',
                        'ویترین عمومی بدون امتیاز است و نمی‌تواند هم‌زمان جایگاه پاپ‌آپ امتیازآور باشد.',
                    );
                }

                if ($placementTypes->intersect(['map_route', 'post_mission', 'reward_page'])->isEmpty()) {
                    $validator->errors()->add(
                        'placement_type',
                        'پاپ‌آپ امتیازآور باید در یکی از جایگاه‌های مرحله بازی، پس از مأموریت یا صفحه پاداش نمایش داده شود.',
                    );
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'body_copy.required' => 'متن کوتاه تبلیغ برای پاپ‌آپ امتیازآور الزامی است.',
            'creative_type.in' => 'پاپ‌آپ امتیازآور و ویترین عمومی فقط از تصویر ثابت پشتیبانی می‌کنند؛ ویدیو مخصوص نمایشگرهای محیطی است.',
            'asset_url.required' => 'تصویر ثابت را بارگذاری کنید یا نشانی معتبر آن را وارد کنید.',
            'asset_file.required' => 'تصویر ثابت را بارگذاری کنید یا نشانی معتبر آن را وارد کنید.',
            'asset_file.image' => 'فایل انتخاب‌شده باید تصویر معتبر باشد.',
            'asset_file.mimes' => 'فرمت تصویر فقط JPEG یا WebP مجاز است.',
            'asset_file.max' => 'حجم تصویر نباید بیشتر از ۲۵۰ کیلوبایت باشد.',
            'asset_file.dimensions' => 'تصویر باید نسبت ۱۶:۹ و حداقل اندازه ۸۰۰×۴۵۰ پیکسل داشته باشد.',
            'rewarded_points.required' => 'مقدار امتیاز تبلیغ را مشخص کنید.',
            'rewarded_points.between' => 'امتیاز تبلیغ باید بین ۱ تا ۱۰۰ باشد.',
            'required_seconds.required' => 'زمان لازم برای مشاهده تبلیغ را مشخص کنید.',
            'required_seconds.min' => 'زمان مشاهده پاپ‌آپ نباید کمتر از ۸ ثانیه باشد.',
            'required_seconds.max' => 'زمان مشاهده پاپ‌آپ نباید بیشتر از ۱۵ ثانیه باشد.',
            'game_stage_index.required' => 'مرحله نمایش تبلیغ را مشخص کنید.',
            'game_stage_index.in' => 'مرحله نمایش تبلیغ باید یکی از مراحل ۲ تا ۵ بازی باشد.',
        ];
    }

    private function isRewardedPopup(): bool
    {
        return $this->input('ad_type') === 'rewarded_content';
    }

    private function hasPublicFeedPlacement(): bool
    {
        return collect([$this->input('placement_type')])
            ->merge((array) $this->input('online_placements', []))
            ->contains('public_feed');
    }
}
