<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ContactSettingsController extends Controller
{
    private const SOCIAL = ['facebook', 'instagram', 'x', 'youtube', 'telegram'];

    public function update(Request $request)
    {
        $rules = [
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+\-\s()]+$/'],
            'address' => ['nullable', 'string', 'max:200'],
        ];
        $messages = ['phone.regex' => 'Use digits, spaces, + and - only.'];

        foreach (self::SOCIAL as $key) {
            $rules[$key] = ['nullable', 'url:http,https', 'max:255'];
            $messages["{$key}.url"] = 'Enter a full link that starts with https://';
        }

        $data = $request->validateWithBag('contact', $rules, $messages);

        $values = [
            'contact.email' => $data['email'] ?? null,
            'contact.phone' => $data['phone'] ?? null,
            'contact.address' => $data['address'] ?? null,
        ];
        foreach (self::SOCIAL as $key) {
            $values["social.{$key}"] = $data[$key] ?? null;
        }

        Setting::put($values);

        return redirect()->to(route('admin.pages.edit', 'contact') . '#contact-details')
            ->with('success', 'Contact details saved.');
    }
}
