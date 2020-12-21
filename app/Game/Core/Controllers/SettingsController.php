<?php

namespace App\Game\Core\Controllers;

use Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Flare\Models\User;

class SettingsController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(User $user) {
        return view('game.core.settings.settings', [
            'user' => $user,
        ]);
    }

    public function emailSettings(Request $request, User $user) {
        $user->update([
            'adventure_email'       => $request->has('adventure_email'),
            'can_speak_again_email' => $request->has('can_speak_again_email'),
        ]);

        return redirect()->back()->with('success', 'Updated email prefrences.');
    }

    public function characterSettings(Request $request, User $user) {
        $request->validate([
            'name' => 'required|string|max:15|min:5|unique:characters|regex:/^[a-zA-Z0-9]+$/',
        ]);

        $user->character->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Updated character name.');
    }

    public function securityQuestions(Request $request, User $user) {
        $request->validate([
            'password'     => 'required',
            'question_one' => 'required',
            'question_two' => 'required',
            'answer_one'   => 'required|min:4',
            'answer_two'   => 'required|min:4',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', 'Invalid password.');
        }

        $user->securityQuestions()->truncate();

        if ($request->question_one === $request->question_two) {
            return redirect()->back()->with('error', 'Security questions need to be unique.');
        }

        if ($request->answer_one === $request->answer_two) {
            return redirect()->back()->with('error', 'Security questions answers need to be unique.');
        }

        $this->createSecurityQuestions($request, $user);

        return redirect()->back()->with('success', 'Security Question supdated. Do not forget these answers. We cannot reset them for you.');
    }

    protected function createSecurityQuestions(Request $request, User $user): User {
        $user->securityQuestions()->insert([
            [
                'user_id'    => $user->id,
                'question'   => $request->question_one,
                'answer'     => Hash::make($request->answer_one),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id'    => $user->id,
                'question'   => $request->question_two,
                'answer'     => Hash::make($request->answer_two),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        return $user->refresh();
    }
}