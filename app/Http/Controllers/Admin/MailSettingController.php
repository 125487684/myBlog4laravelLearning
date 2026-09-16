<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailSetting;
use Illuminate\Http\Request;

class MailSettingController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        $mailSetting = MailSetting::current();

        return view('admin.mail-settings.edit', ['mailSetting' => $mailSetting]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['nullable', 'in:tls,ssl'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'from_address' => ['required', 'email'],
            'from_name' => ['required', 'string', 'max:255'],
        ]);

        $mailSetting = MailSetting::current();

        $data = $request->only([
            'host', 'port', 'encryption', 'username',
            'password', 'from_address', 'from_name',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $mailSetting->update($data);

        return redirect()
            ->route('mail-settings.edit')
            ->with('status_mail', __('Mail settings updated.'));
    }
}
